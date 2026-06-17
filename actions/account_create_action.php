<?php
session_start();

include("../config/db.php");
include("../lib/validators.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../accounts.php");
    exit();
}

$user_id      = $_SESSION['user_id'];
$account_type = $_POST['account_type'] ?? '';

/* Validate account type */
if (!in_array($account_type, ['current', 'savings'])) {
    $_SESSION['errors'] = ["Invalid account type selected."];
    header("Location: ../accounts.php");
    exit();
}

/* Generate unique 24-digit TR IBAN */
function generateIBAN($conn) {
    do {
        /* Generate 24 random digits in two 12-digit parts to avoid int overflow */
        $part1 = str_pad((string) mt_rand(0, 999999999999), 12, "0", STR_PAD_LEFT);
        $part2 = str_pad((string) mt_rand(0, 999999999999), 12, "0", STR_PAD_LEFT);
        $iban  = "TR" . $part1 . $part2;

        $stmt = $conn->prepare("SELECT account_id FROM accounts WHERE iban = ?");
        $stmt->bind_param("s", $iban);
        $stmt->execute();
        $stmt->store_result();

    } while ($stmt->num_rows > 0);

    $stmt->close();
    return $iban;
}

$iban = generateIBAN($conn);

/* Insert new account */
$stmt = $conn->prepare(
    "INSERT INTO accounts (user_id, iban, account_type, balance) VALUES (?, ?, ?, 0.00)"
);

if (!$stmt) {
    $_SESSION['errors'] = ["Database error. Please try again."];
    header("Location: ../accounts.php");
    exit();
}

$stmt->bind_param("iss", $user_id, $iban, $account_type);

if ($stmt->execute()) {
    $_SESSION['success'] = "Account created successfully. Your IBAN: " . $iban;
    header("Location: ../accounts.php");
} else {
    $_SESSION['errors'] = ["Account creation failed. Please try again."];
    header("Location: ../accounts.php");
}

$stmt->close();
exit();
?>