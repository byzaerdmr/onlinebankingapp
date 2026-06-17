<?php
session_start();
include("includes/header.php");
?>

<div class="container">
    <div class="container-box">
        <h2>Register</h2>

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

        <form action="actions/register_action.php" method="POST" onsubmit="return validateRegisterForm();">

            <div class="mb-3">
                <label class="form-label text-dark">Full Name</label>
                <input 
                    type="text" 
                    name="full_name" 
                    class="form-control" 
                    placeholder="Enter your full name"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label text-dark">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="Enter your email address"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label text-dark">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    class="form-control" 
                    placeholder="Enter your password"
                    required
                    oninput="checkPasswordStrength();"
                >
                <small class="text-muted">
                    Min. 8 characters, must include uppercase, lowercase and a number.
                </small>
                <div id="passwordWarning" class="text-danger mt-2" style="display:none;">
                    Password must be at least 8 characters and include uppercase, lowercase and a number.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label d-block text-dark">User Type</label>

                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        name="user_type" 
                        id="typePersonal" 
                        value="personal" 
                        required
                    >
                    <label class="form-check-label text-dark" for="typePersonal">
                        Personal
                    </label>
                </div>

                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        name="user_type" 
                        id="typeBusiness" 
                        value="business"
                    >
                    <label class="form-check-label text-dark" for="typeBusiness">
                        Business
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                Register
            </button>

            <p class="mt-3 mb-0 text-dark">
                Already have an account? <a href="login.php">Login</a>
            </p>

        </form>
    </div>
</div>

<script>
function isPasswordValid(password) {
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
    return passwordRegex.test(password);
}

function checkPasswordStrength() {
    const password = document.getElementById("password").value;
    const warning = document.getElementById("passwordWarning");

    if (password.length === 0 || isPasswordValid(password)) {
        warning.style.display = "none";
    } else {
        warning.style.display = "block";
    }
}

function validateRegisterForm() {
    const password = document.getElementById("password").value;
    const warning = document.getElementById("passwordWarning");

    if (!isPasswordValid(password)) {
        warning.style.display = "block";
        return false;
    }

    return true;
}
</script>

<?php include("includes/footer.php"); ?>