<?php
session_start();
include("includes/header.php");

$submitted = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reset_input = trim($_POST['reset_input'] ?? '');

    if (!empty($reset_input)) {
        /*
            NOTE: In a production environment, this would:
            1. Check if the email/customer_no exists in the database
            2. Generate a secure token and store it
            3. Send a reset link via email (e.g. PHPMailer + SMTP)
            For this academic project, the request is acknowledged without email delivery.
        */
        $submitted = true;
    }
}
?>

<div class="container">
    <div class="container-box">
        <h2>Forgot Password</h2>

        <?php if ($submitted): ?>
            <div class="alert alert-success mt-3">
                If an account exists with the provided information, a password reset 
                link has been sent. Please check your email.
            </div>
            <a href="login.php" class="btn btn-primary mt-2">Back to Login</a>

        <?php else: ?>
            <p class="text-muted mb-4">
                Enter your registered email address or customer number. 
                We will send you a password reset link.
            </p>

            <form action="forgot_password.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email or Customer Number</label>
                    <input 
                        type="text" 
                        name="reset_input" 
                        class="form-control" 
                        placeholder="Enter your email or customer number"
                        required
                    >
                </div>

                <div class="d-flex gap-3 align-items-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <a href="login.php" class="text-muted">Back to Login</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include("includes/footer.php"); ?>