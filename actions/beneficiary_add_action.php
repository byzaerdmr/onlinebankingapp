<?php
session_start();

include("../config/db.php");
include("../lib/validators.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../beneficiaries.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$beneficiary_name = trim($_POST['beneficiary_name'] ?? '');
$beneficiary_iban = cleanIBAN($_POST['beneficiary_iban'] ?? '');
$bank_name = trim($_POST['bank_name'] ?? '');

// Validate IBAN format
if (!validateIBAN($beneficiary_iban)) {
    $_SESSION['errors'] = ["Invalid IBAN format. Please enter 24 digits after TR."];
    header("Location: ../beneficiaries.php");
    exit();
}

// Check duplicate beneficiary
$check_sql = "SELECT beneficiary_id FROM beneficiaries WHERE user_id = ? AND beneficiary_iban = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("is", $user_id, $beneficiary_iban);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $_SESSION['errors'] = ["This beneficiary is already saved."];
    header("Location: ../beneficiaries.php");
    exit();
}

$check_stmt->close();

// Insert beneficiary securely
$sql = "INSERT INTO beneficiaries (user_id, beneficiary_name, beneficiary_iban, bank_name) 
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['errors'] = ["Database error. Please try again."];
    header("Location: ../beneficiaries.php");
    exit();
}

$stmt->bind_param("isss", $user_id, $beneficiary_name, $beneficiary_iban, $bank_name);

if ($stmt->execute()) {
    $_SESSION['success'] = "Beneficiary added successfully.";
} else {
    $_SESSION['errors'] = ["Beneficiary could not be added."];
}

$stmt->close();

header("Location: ../beneficiaries.php");
exit();
?>