<?php
session_start();

include("../config/db.php");
include("../lib/validators.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../register.php");
    exit();
}

/* Input */
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$user_type = $_POST['user_type'] ?? '';

/* Server-side validation */
$errors = [];

if (!validateFullName($full_name)) {
    $errors[] = "Invalid full name. Use only letters and spaces. It must be between 2 and 100 characters.";
}

if (!validateEmail($email)) {
    $errors[] = "Invalid email address.";
}

if (!validatePassword($password)) {
    $errors[] = "Password must be at least 8 characters and include uppercase, lowercase and a number.";
}

if (!in_array($user_type, ['personal', 'business'], true)) {
    $errors[] = "Please select a valid user type.";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: ../register.php");
    exit();
}

/* Duplicate email check */
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");

if (!$stmt) {
    $_SESSION['errors'] = ["Database error: " . $conn->error];
    header("Location: ../register.php");
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['errors'] = ["This email address is already registered."];
    $stmt->close();
    header("Location: ../register.php");
    exit();
}

$stmt->close();

/* Generate unique customer number */
do {
    $customer_no = str_pad((string)rand(0, 9999999999), 10, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE customer_no = ?");

    if (!$stmt) {
        $_SESSION['errors'] = ["Database error: " . $conn->error];
        header("Location: ../register.php");
        exit();
    }

    $stmt->bind_param("s", $customer_no);
    $stmt->execute();
    $stmt->store_result();

    $exists = $stmt->num_rows > 0;
    $stmt->close();

} while ($exists);

/* Insert user */
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (customer_no, user_type, full_name, email, password_hash)
     VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    $_SESSION['errors'] = ["Database error: " . $conn->error];
    header("Location: ../register.php");
    exit();
}

$stmt->bind_param("sssss", $customer_no, $user_type, $full_name, $email, $password_hash);

if ($stmt->execute()) {
    $_SESSION['success'] = "Registration successful! Your customer number is: " . $customer_no;
    $stmt->close();
    header("Location: ../login.php");
    exit();
}

$_SESSION['errors'] = ["Registration failed. Please try again."];
$stmt->close();

header("Location: ../register.php");
exit();
?>