<?php
session_start();

include("../config/db.php");
include("../lib/validators.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../profile.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Input */
$full_name = trim($_POST['full_name'] ?? '');
$notification_preferences = null;

if (!empty($_POST['notification_preferences']) && is_array($_POST['notification_preferences'])) {
    $allowed_prefs = ['Email', 'SMS', 'Push Notification', 'Phone Call'];
    $filtered = array_filter(
        $_POST['notification_preferences'],
        fn($p) => in_array($p, $allowed_prefs)
    );
    $notification_preferences = implode(',', $filtered) ?: null;
}

/* Validation */
if (!validateFullName($full_name)) {
    $_SESSION['errors'] = ["Invalid full name. Use only letters and spaces (2-100 characters)."];
    header("Location: ../profile.php");
    exit();
}

/* Get current profile photo */
$stmt = $conn->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();
$stmt->close();

$profile_photo_name = $current_user['profile_photo'] ?? null;

/* File upload */
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $file_name = $_FILES['profile_photo']['name'];
    $file_tmp  = $_FILES['profile_photo']['tmp_name'];
    $file_size = $_FILES['profile_photo']['size'];
    $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    /* Extension check */
    if (!in_array($file_ext, $allowed_extensions)) {
        $_SESSION['errors'] = ["Invalid file type. Only jpg, jpeg, png and webp are allowed."];
        header("Location: ../profile.php");
        exit();
    }

    /* Size check — max 2MB */
    if ($file_size > 2 * 1024 * 1024) {
        $_SESSION['errors'] = ["File size must be less than 2MB."];
        header("Location: ../profile.php");
        exit();
    }

    /* MIME type check (double security) */
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    $mime_type = mime_content_type($file_tmp);
    if (!in_array($mime_type, $allowed_mimes)) {
        $_SESSION['errors'] = ["Invalid file content. Please upload a real image file."];
        header("Location: ../profile.php");
        exit();
    }

    /* Build upload path — use absolute path to avoid working directory issues */
    $upload_dir = dirname(__DIR__) . "/assets/uploads/";

    /* Create directory if it doesn't exist */
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $profile_photo_name = uniqid("profile_", true) . "." . $file_ext;
    $upload_path = $upload_dir . $profile_photo_name;

    if (!move_uploaded_file($file_tmp, $upload_path)) {
        $_SESSION['errors'] = ["Profile photo upload failed. Please check folder permissions."];
        header("Location: ../profile.php");
        exit();
    }

    /* Delete old photo if exists */
    if (!empty($current_user['profile_photo'])) {
        $old_path = $upload_dir . $current_user['profile_photo'];
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    }

} elseif (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    /* A file was selected but something went wrong */
    $_SESSION['errors'] = ["File upload error. Please try again."];
    header("Location: ../profile.php");
    exit();
}

/* Update user */
$stmt = $conn->prepare(
    "UPDATE users
     SET full_name = ?, profile_photo = ?, notification_preferences = ?
     WHERE user_id = ?"
);

if (!$stmt) {
    $_SESSION['errors'] = ["Database error. Please try again."];
    header("Location: ../profile.php");
    exit();
}

$stmt->bind_param("sssi", $full_name, $profile_photo_name, $notification_preferences, $user_id);

if ($stmt->execute()) {
    $_SESSION['full_name'] = $full_name;
    $_SESSION['success']   = "Profile updated successfully.";
    header("Location: ../profile.php");
} else {
    $_SESSION['errors'] = ["Update failed. Please try again."];
    header("Location: ../profile.php");
}

$stmt->close();
exit();
?>