<?php
include("includes/auth_guard.php");
include("config/db.php");

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM beneficiaries WHERE user_id = ? ORDER BY beneficiary_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include("includes/dashboard_header.php");
?>

<div class="content-card">
    <div class="page-header-row">
        <h2>Beneficiaries</h2>
    </div>

    <form action="actions/beneficiary_add_action.php" method="POST" class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Beneficiary Name</label>
                <input type="text" name="beneficiary_name" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Beneficiary IBAN</label>
                <div class="input-group">
                    <span class="input-group-text">TR</span>
                    <input 
                        type="text" 
                        name="beneficiary_iban" 
                        class="form-control" 
                        maxlength="24"
                        pattern="[0-9]{24}"
                        placeholder="____ ____ ____ ____ ____ ____"
                        required
                        oninput="formatIBAN(this)"
                    >
                </div>
                <small class="text-muted">Enter 24 digits after TR</small>
            </div>

            <div class="col-md-4">
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-control" required>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-danger">Add Beneficiary</button>
        </div>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <table class="recent-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>IBAN</th>
                    <th>Bank</th>
                    <th style="width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['beneficiary_name']); ?></td>
                        <td>TR<?php echo htmlspecialchars($row['beneficiary_iban']); ?></td>
                        <td><?php echo htmlspecialchars($row['bank_name']); ?></td>
                        <td>
                            <a 
                                href="actions/beneficiary_delete_action.php?id=<?php echo $row['beneficiary_id']; ?>" 
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Are you sure you want to delete this beneficiary?');"
                            >
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-box white-box">
            <p>No beneficiaries found yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php include("includes/dashboard_footer.php"); ?>