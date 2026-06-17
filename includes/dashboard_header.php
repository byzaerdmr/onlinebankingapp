<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B Bank Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="dashboard-layout">
    <aside class="dashboard-sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-box">B</div>
            <div class="sidebar-logo-text">Bank</div>
        </div>

        <nav class="sidebar-menu">
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">🏠 Dashboard</a>
            <a href="accounts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'accounts.php' ? 'active' : ''; ?>">💳 Accounts</a>
            <a href="transfer.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'transfer.php' ? 'active' : ''; ?>">🔁 Transfers</a>
            <a href="beneficiaries.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'beneficiaries.php' ? 'active' : ''; ?>">👥 Beneficiaries</a>
            <a href="transactions.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">📄 Transactions</a>
            <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">👤 Profile</a>
            <a href="logout.php">⏻ Logout</a>
        </nav>
    </aside>

    <main class="dashboard-main">