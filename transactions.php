<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?><?php
include("includes/auth_guard.php");
include("config/db.php");

$user_id = $_SESSION['user_id'];

/*
    JOIN operation is implemented through transaction_history_view.
    This view joins transactions, accounts, and users tables.
*/
$sql = "SELECT 
            transaction_id,
            sender_iban,
            receiver_iban,
            amount,
            transaction_type,
            description,
            created_at
        FROM transaction_history_view
        WHERE user_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include("includes/dashboard_header.php");
?>

<div class="content-card">
    <div class="page-header-row">
        <h2>Transactions</h2>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="recent-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sender IBAN</th>
                        <th>Receiver IBAN</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['sender_iban']); ?></td>
                            <td><?php echo htmlspecialchars($row['receiver_iban']); ?></td>
                            <td><?php echo number_format($row['amount'], 2); ?> ₺</td>
                            <td>
                                <span class="transaction-badge <?php echo $row['transaction_type'] === 'internal' ? 'badge-internal' : 'badge-external'; ?>">
                                    <?php echo htmlspecialchars(ucfirst($row['transaction_type'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    echo !empty($row['description']) 
                                        ? htmlspecialchars($row['description']) 
                                        : "-"; 
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-box white-box">
            <p>No transaction found yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
$stmt->close();
include("includes/dashboard_footer.php"); 
?>