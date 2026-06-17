<?php
session_start();
include("includes/header.php");
?>

<div class="bank-topbar">
    <div class="container d-flex justify-content-end gap-4">
        <a href="#">SECURITY</a>
        <a href="#">HELP</a>
        <a href="#">FAQ</a>
    </div>
</div>

<div class="bank-header">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="bank-logo">
            <span class="logo-box">B</span>
            <span class="logo-text">Bank</span>
        </div>

        <div class="bank-support text-end">
            <div class="fw-bold">Customer Contact Center</div>
            <small>0850 000 00 00</small>
        </div>
    </div>
</div>

<section class="login-hero">
    <div class="container">
        <div class="row align-items-start g-5">
            <div class="col-lg-6">
                <div class="login-panel">
                    <h1 class="login-title">Welcome to Internet Banking</h1>

                    <?php if (!empty($_SESSION['errors'])): ?>
                     <div class="alert alert-danger mt-3">
                            <?php foreach ($_SESSION['errors'] as $e): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($e); ?></p>
                            <?php endforeach; ?>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['success'])): ?>
                        <div class="alert alert-success mt-3">
                            <?php echo htmlspecialchars($_SESSION['success']); ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <form action="actions/login_action.php" method="POST" class="mt-4">
                        <input type="hidden" name="user_type" id="userTypeInput" value="personal">

                        <div class="login-tabs">
                            <button type="button" class="login-tab active" data-user-type="personal">
                                PERSONAL
                            </button>
                            <button type="button" class="login-tab" data-user-type="business">
                                BUSINESS
                            </button>
                        </div>

                        <div class="mb-3 mt-4">
                            <input 
                                type="text" 
                                name="login_input" 
                                class="form-control bank-input" 
                                placeholder="Customer Number or Email" 
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <input 
                                type="password" 
                                name="password" 
                                class="form-control bank-input" 
                                placeholder="Password" 
                                required
                            >
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="remember_me" 
                                    value="1" 
                                    id="rememberMe"
                                >
                                <label class="form-check-label text-white" for="rememberMe">
                                    Remember Me
                                </label>
                            </div>

                            <a href="forgot_password.php" class="login-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn bank-login-btn w-100">LOGIN</button>

                        <div class="apply-box text-center mt-4">
                            <p>If you are not a digital banking customer yet</p>
                            <a href="register.php" class="btn bank-outline-btn">APPLY NOW</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="promo-card">
                    <div class="promo-phone"></div>

                    <div>
                        <h3>Our mobile banking is always with you</h3>
                        <p>Access your accounts, transfers and transactions quickly and securely.</p>
                        <button class="btn promo-btn">DOWNLOAD NOW</button>
                    </div>
                </div>

                <div class="security-box mt-4">
                    <div class="security-icon">🔒</div>
                    <div>
                        <strong>Secure Banking</strong>
                        <p class="mb-0">Your session is protected with secure login controls.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.querySelectorAll(".login-tab").forEach(function(tab) {
    tab.addEventListener("click", function() {
        document.querySelectorAll(".login-tab").forEach(function(item) {
            item.classList.remove("active");
        });

        this.classList.add("active");
        document.getElementById("userTypeInput").value = this.dataset.userType;
    });
});
</script>

<?php include("includes/footer.php"); ?>