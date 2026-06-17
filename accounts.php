<?php
include("includes/auth_guard.php");
include("config/db.php");

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM accounts WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include("includes/dashboard_header.php");
?>

<div class="content-card">
    <div class="page-header-row">
        <h2>My Accounts</h2>
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

    <form action="actions/account_create_action.php" method="POST" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Account Type</label>
                <select name="account_type" class="form-select" required>
                    <option value="">Select account type</option>
                    <option value="current">Current Account</option>
                    <option value="savings">Savings Account</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-danger w-100">Create New Account</button>
            </div>
        </div>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>Account ID</th>
                        <th>IBAN</th>
                        <th>Account Type</th>
                        <th>Balance</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['account_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['iban']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($row['account_type'])); ?></td>
                            <td><?php echo number_format($row['balance'], 2); ?> ₺</td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-box white-box">
            <p>You do not have any account yet. Create your first account above.</p>
        </div>
    <?php endif; ?>
</div>

<?php include("includes/dashboard_footer.php"); ?>