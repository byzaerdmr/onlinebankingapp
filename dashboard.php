<?php
include("includes/auth_guard.php");
include("config/db.php");
include("includes/header.php");

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'User';
$customer_no = $_SESSION['customer_no'] ?? '-';
$user_type = $_SESSION['user_type'] ?? 'personal';

// Total accounts
$sql_total_accounts = "SELECT COUNT(*) as total_accounts FROM accounts WHERE user_id = ?";
$stmt_total_accounts = $conn->prepare($sql_total_accounts);
$stmt_total_accounts->bind_param("i", $user_id);
$stmt_total_accounts->execute();
$result_total_accounts = $stmt_total_accounts->get_result();
$total_accounts_data = $result_total_accounts->fetch_assoc();

// Main account
$sql_main_account = "SELECT * FROM accounts WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt_main_account = $conn->prepare($sql_main_account);
$stmt_main_account->bind_param("i", $user_id);
$stmt_main_account->execute();
$result_main_account = $stmt_main_account->get_result();
$main_account = $result_main_account->fetch_assoc();

// Total beneficiaries
$sql_beneficiaries = "SELECT COUNT(*) as total_beneficiaries FROM beneficiaries WHERE user_id = ?";
$stmt_beneficiaries = $conn->prepare($sql_beneficiaries);
$stmt_beneficiaries->bind_param("i", $user_id);
$stmt_beneficiaries->execute();
$result_beneficiaries = $stmt_beneficiaries->get_result();
$beneficiary_data = $result_beneficiaries->fetch_assoc();

// Total transactions
$sql_transactions_count = "SELECT COUNT(*) as total_transactions
                           FROM transactions t
                           INNER JOIN accounts a ON t.sender_account_id = a.account_id
                           WHERE a.user_id = ?";
$stmt_transactions_count = $conn->prepare($sql_transactions_count);
$stmt_transactions_count->bind_param("i", $user_id);
$stmt_transactions_count->execute();
$result_transactions_count = $stmt_transactions_count->get_result();
$transaction_data = $result_transactions_count->fetch_assoc();

// Total balance
$sql_balance = "SELECT SUM(balance) as total_balance FROM accounts WHERE user_id = ?";
$stmt_balance = $conn->prepare($sql_balance);
$stmt_balance->bind_param("i", $user_id);
$stmt_balance->execute();
$result_balance = $stmt_balance->get_result();
$balance_data = $result_balance->fetch_assoc();

// Recent transactions
$sql_recent = "SELECT t.transaction_id, t.receiver_iban, t.amount, t.transaction_type, t.created_at
               FROM transactions t
               INNER JOIN accounts a ON t.sender_account_id = a.account_id
               WHERE a.user_id = ?
               ORDER BY t.created_at DESC
               LIMIT 5";
$stmt_recent = $conn->prepare($sql_recent);
$stmt_recent->bind_param("i", $user_id);
$stmt_recent->execute();
$result_recent = $stmt_recent->get_result();
?>

<div class="dashboard-layout">
    <aside class="dashboard-sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-box">B</div>
            <div class="sidebar-logo-text">Bank</div>
        </div>

        <nav class="sidebar-menu">
            <a href="dashboard.php" class="active">🏠 Dashboard</a>
            <a href="accounts.php">💳 Accounts</a>
            <a href="transfer.php">🔁 Transfers</a>
            <a href="beneficiaries.php">👥 Beneficiaries</a>
            <a href="transactions.php">📄 Transactions</a>
            <a href="profile.php">👤 Profile</a>
            <a href="logout.php">⏻ Logout</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="dashboard-topbar-card">
        <div class="topbar-left">
            <h1>My Banking Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($full_name); ?></p>
            <div class="topbar-tags">
                <span class="topbar-tag">Secure Session</span>
                <span class="topbar-tag">Internet Branch</span>
            </div>
        </div>

        <div class="user-mini-card">
            <div><strong>Customer No:</strong> <?php echo htmlspecialchars($customer_no); ?></div>
            <div><strong>User Type:</strong> <?php echo ucfirst(htmlspecialchars($user_type)); ?></div>
            <div><strong>Today:</strong> <?php echo date("d/m/Y H:i"); ?></div>
        </div>
    </div>

        <div class="dashboard-grid-v2">
            <section class="account-card-large">
                <div class="account-visual-card">
    <div class="visual-chip"></div>
    <div class="visual-bank-name">B Bank</div>
    <div class="visual-type">Main Account</div>
</div>
                <div class="card-header-row">
                    <h2>My Main Account</h2>
                    <span class="account-badge">Active</span>
                </div>

                <?php if ($main_account): ?>
                    <div class="main-balance">
                        <?php echo number_format($main_account['balance'], 2); ?> ₺
                    </div>

                    <div class="account-info-grid">
                        <div>
                            <span class="info-label">Account ID</span>
                            <span class="info-value"><?php echo $main_account['account_id']; ?></span>
                        </div>

                        <div>
                            <span class="info-label">IBAN</span>
                            <span class="info-value"><?php echo htmlspecialchars($main_account['iban']); ?></span>
                        </div>

                        <div>
                            <span class="info-label">Created At</span>
                            <span class="info-value"><?php echo $main_account['created_at']; ?></span>
                        </div>
                    </div>

                    <div class="quick-actions">
                        <a href="transfer.php" class="quick-btn">Transfer Money</a>
                        <a href="transactions.php" class="quick-btn dark-btn">View Transactions</a>
                        <a href="accounts.php" class="quick-btn">Account Details</a>
                    </div>
                <?php else: ?>
                    <div class="empty-box">
                        <p>You do not have any bank account yet.</p>
                        <p class="mb-3">Create your first account to start transfers and transactions.</p>
                        <a href="accounts.php" class="quick-btn">Create Account</a>
                    </div>
                <?php endif; ?>
            </section>

                    <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💳</div>
                <span class="stat-label">Accounts</span>
                <strong><?php echo $total_accounts_data['total_accounts']; ?></strong>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <span class="stat-label">Beneficiaries</span>
                <strong><?php echo $beneficiary_data['total_beneficiaries']; ?></strong>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📄</div>
                <span class="stat-label">Transactions</span>
                <strong><?php echo $transaction_data['total_transactions']; ?></strong>
            </div>

            <div class="stat-card">
                <div class="stat-icon">₺</div>
                <span class="stat-label">Total Balance</span>
                <strong><?php echo number_format($balance_data['total_balance'] ?? 0, 2); ?> ₺</strong>
            </div>
        </section>
        </div>

        <div class="bottom-cards">
            <div class="mini-panel">
                <h4>Transfers</h4>
                <p>Send money securely to internal or external accounts.</p>
                <a href="transfer.php" class="mini-link">Go to Transfers</a>
            </div>

            <div class="mini-panel">
                <h4>Beneficiaries</h4>
                <p>Manage your saved beneficiaries and make faster transfers.</p>
                <a href="beneficiaries.php" class="mini-link">Manage Beneficiaries</a>
            </div>

            <div class="mini-panel">
                <h4>Accounts</h4>
                <p>Review your bank accounts and create new ones if needed.</p>
                <a href="accounts.php" class="mini-link">View Accounts</a>
            </div>
        </div>

        <section class="recent-transactions-panel">
            <div class="recent-header">
                <h3>Recent Transactions</h3>
                <a href="transactions.php" class="mini-link">See All</a>
            </div>

            <?php if ($result_recent->num_rows > 0): ?>
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Receiver IBAN</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_recent->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['transaction_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['receiver_iban']); ?></td>
                                <td><?php echo number_format($row['amount'], 2); ?> ₺</td>
                                <td><?php echo ucfirst($row['transaction_type']); ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-box white-box">
                    <p>No transaction found yet.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<?php include("includes/footer.php"); ?>