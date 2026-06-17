<?php

function validateFullName($name) {
    return preg_match('/^[a-zA-ZğüşöçıİĞÜŞÖÇ\s]{2,100}$/u', $name);
}

function validateEmail($email) {
    return preg_match('/^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,}$/', $email);
}

function validatePassword($password) {
    return preg_match('/^(?=.*[A-Za-z])(?=.*\d).{6,}$/', $password);
}

function validatePhone($phone) {
    return preg_match('/^\+?[0-9\s]{10,20}$/', $phone);
}

function validateIBAN($iban) {
    return preg_match('/^TR[0-9]{24}$/', $iban);
}

function validateAmount($amount) {
    return preg_match('/^\d+(\.\d{1,2})?$/', $amount) && floatval($amount) > 0;
}

function validateDescription($description) {
    return preg_match('/^[a-zA-Z0-9ğüşöçıİĞÜŞÖÇ\s.,!?-]{0,255}$/u', $description);
}

function cleanIBAN($iban) {
    $iban = strtoupper(str_replace(' ', '', trim($iban)));

    if (substr($iban, 0, 2) !== "TR") {
        $iban = "TR" . $iban;
    }

    return $iban;
}
?>