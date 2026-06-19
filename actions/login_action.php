<?php
session_start();

include("../config/db.php");
include("../lib/validators.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit();
}

$login_input = trim($_POST['login_input'] ?? '');
$password    = $_POST['password'] ?? '';
$user_type   = $_POST['user_type'] ?? '';
$remember_me = isset($_POST['remember_me']) ? 1 : 0;

if (empty($login_input) || empty($password)) {
    $_SESSION['errors'] = ["Please enter your customer number / email and password."];
    header("Location: ../login.php");
    exit();
}

if (!in_array($user_type, ['personal', 'business'], true)) {
    $_SESSION['errors'] = ["Invalid user type selected."];
    header("Location: ../login.php");
    exit();
}

$sql  = "SELECT * FROM users WHERE (email = ? OR customer_no = ?) AND user_type = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['errors'] = ["Database error. Please try again."];
    header("Location: ../login.php");
    exit();
}

$stmt->bind_param("sss", $login_input, $login_input, $user_type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['errors'] = ["No account found with these credentials."];
    header("Location: ../login.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['errors'] = ["Incorrect password. Please try again."];
    header("Location: ../login.php");
    exit();
}

/* Prevent session fixation after successful login */
session_regenerate_id(true);

$_SESSION['user_id']     = $user['user_id'];
$_SESSION['full_name']   = $user['full_name'];
$_SESSION['email']       = $user['email'];
$_SESSION['customer_no'] = $user['customer_no'];
$_SESSION['user_type']   = $user['user_type'];

/* Simple remember me flag for project demonstration */
if ($remember_me) {
    $_SESSION['remember_me'] = true;
}

header("Location: ../dashboard.php");
exit();
?>