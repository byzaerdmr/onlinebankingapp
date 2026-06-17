<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$beneficiary_name = trim($_POST['beneficiary_name']);
$beneficiary_iban = "TR" . str_replace(' ', '', $_POST['beneficiary_iban']);
$bank_name = trim($_POST['bank_name']);

// Server-side IBAN validation
if (!preg_match('/^TR[0-9]{24}$/', $beneficiary_iban)) {
    die("Invalid IBAN format. IBAN must start with TR and contain 24 digits.");
}

// Duplicate beneficiary control
$check_sql = "SELECT beneficiary_id FROM beneficiaries WHERE user_id = ? AND beneficiary_iban = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("is", $user_id, $beneficiary_iban);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    die("This beneficiary is already saved.");
}

$sql = "INSERT INTO beneficiaries (user_id, beneficiary_name, beneficiary_iban, bank_name) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $user_id, $beneficiary_name, $beneficiary_iban, $bank_name);

if ($stmt->execute()) {
    header("Location: ../beneficiaries.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}
?>