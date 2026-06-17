<?php
session_start();
include("includes/auth_guard.php");
include("config/db.php");

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$selected_preferences = [];
if (!empty($user['notification_preferences'])) {
    $selected_preferences = explode(',', $user['notification_preferences']);
}

include("includes/dashboard_header.php");
?>

<div class="content-card">
    <div class="page-header-row">
        <h2>Profile</h2>
    </div>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <?php foreach ($_SESSION['errors'] as $e): ?>
                <p class="mb-0"><?php echo htmlspecialchars($e); ?></p>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="actions/profile_update_action.php" method="POST" enctype="multipart/form-data">
        <div class="row g-4">

            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input
                    type="text"
                    name="full_name"
                    class="form-control"
                    value="<?php echo htmlspecialchars($user['full_name']); ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input
                    type="email"
                    class="form-control"
                    value="<?php echo htmlspecialchars($user['email']); ?>"
                    disabled
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Customer Number</label>
                <input
                    type="text"
                    class="form-control"
                    value="<?php echo htmlspecialchars($user['customer_no']); ?>"
                    disabled
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">User Type</label>
                <input
                    type="text"
                    class="form-control"
                    value="<?php echo ucfirst(htmlspecialchars($user['user_type'])); ?>"
                    disabled
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Profile Photo</label>
                <input type="file" name="profile_photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <small class="text-muted">Max 2MB. Allowed formats: jpg, jpeg, png, webp.</small>
                <?php if (!empty($user['profile_photo'])): ?>
                    <div class="mt-3">
                        <img
                            src="assets/uploads/<?php echo htmlspecialchars($user['profile_photo']); ?>"
                            alt="Profile Photo"
                            class="profile-preview"
                        >
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label">Notification Preferences</label>
                <select name="notification_preferences[]" class="form-select" multiple size="4">
                    <option value="Email" <?php echo in_array('Email', $selected_preferences) ? 'selected' : ''; ?>>Email</option>
                    <option value="SMS" <?php echo in_array('SMS', $selected_preferences) ? 'selected' : ''; ?>>SMS</option>
                    <option value="Push Notification" <?php echo in_array('Push Notification', $selected_preferences) ? 'selected' : ''; ?>>Push Notification</option>
                    <option value="Phone Call" <?php echo in_array('Phone Call', $selected_preferences) ? 'selected' : ''; ?>>Phone Call</option>
                </select>
                <small class="text-muted">Hold Command / Ctrl to select multiple options.</small>
            </div>

        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-danger">Update Profile</button>
        </div>
    </form>
</div>

<?php include("includes/dashboard_footer.php"); ?>