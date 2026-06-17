<?php
session_start();

include("../config/db.php");
include("../lib/validators.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../transfer.php");
    exit();
}

$sender_account_id  = isset($_POST['sender_account_id']) ? intval($_POST['sender_account_id']) : 0;
$receiver_iban_input = isset($_POST['receiver_iban'])    ? trim($_POST['receiver_iban'])        : "";
$amount             = isset($_POST['amount'])            ? trim($_POST['amount'])               : "";
$description        = isset($_POST['description'])       ? trim($_POST['description'])          : "";
$save_beneficiary   = isset($_POST['save_beneficiary'])  ? 1                                   : 0;

$receiver_iban = cleanIBAN($receiver_iban_input);

/* Server-side validation */
if ($sender_account_id <= 0) {
    $_SESSION['errors'] = ["Invalid sender account."];
    header("Location: ../transfer.php");
    exit();
}

if (!validateIBAN($receiver_iban)) {
    $_SESSION['errors'] = ["Invalid IBAN format. Example: TR000000000000000000000000"];
    header("Location: ../transfer.php");
    exit();
}

if (!validateAmount($amount)) {
    $_SESSION['errors'] = ["Invalid amount. Please enter a positive number."];
    header("Location: ../transfer.php");
    exit();
}

if ($description !== "" && !validateDescription($description)) {
    $_SESSION['errors'] = ["Invalid description. Max 255 characters, no special symbols."];
    header("Location: ../transfer.php");
    exit();
}

$amount_val = floatval($amount);

/* Check whether the sender account belongs to the logged-in user */
$stmt = $conn->prepare("SELECT account_id FROM accounts WHERE account_id = ? AND user_id = ?");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ii", $sender_account_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['errors'] = ["Invalid sender account."];
    header("Location: ../transfer.php");
    exit();
}
$stmt->close();

/* Call stored procedure */
$amount_str = number_format($amount_val, 2, '.', '');

$stmt = $conn->prepare("CALL transfer_money(?, ?, ?, ?)");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("isss", $sender_account_id, $receiver_iban, $amount_str, $description);

if (!$stmt->execute()) {
    $_SESSION['errors'] = ["Transfer failed: " . $stmt->error];
    header("Location: ../transfer.php");
    exit();
}
$stmt->close();

/* Clear possible MySQLi results after stored procedure */
while ($conn->more_results() && $conn->next_result()) {
    if ($extraResult = $conn->store_result()) {
        $extraResult->free();
    }
}

/* Save receiver as beneficiary if selected */
if ($save_beneficiary) {
    $beneficiary_name = isset($_POST['beneficiary_name']) ? trim($_POST['beneficiary_name']) : "";
    $bank_name        = isset($_POST['bank_name'])        ? trim($_POST['bank_name'])        : "";

    if (!validateFullName($beneficiary_name)) {
        $beneficiary_name = "Saved Beneficiary";
    }

    if (empty($bank_name) || strlen($bank_name) > 100) {
        $bank_name = "Unknown Bank";
    }

    $check_stmt = $conn->prepare(
        "SELECT beneficiary_id FROM beneficiaries WHERE user_id = ? AND beneficiary_iban = ?"
    );

    if (!$check_stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $check_stmt->bind_param("is", $user_id, $receiver_iban);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $insert_stmt = $conn->prepare(
            "INSERT INTO beneficiaries (user_id, beneficiary_name, beneficiary_iban, bank_name)
             VALUES (?, ?, ?, ?)"
        );

        if (!$insert_stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $insert_stmt->bind_param("isss", $user_id, $beneficiary_name, $receiver_iban, $bank_name);

        if (!$insert_stmt->execute()) {
            die("Beneficiary save failed: " . $insert_stmt->error);
        }

        $insert_stmt->close();
    }

    $check_stmt->close();
}

$_SESSION['success'] = "Transfer completed successfully.";
header("Location: ../transactions.php");
exit();
?>