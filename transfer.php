<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?> 

<?php
include("includes/auth_guard.php");
include("config/db.php");

$user_id = $_SESSION['user_id'];

$sql = "SELECT account_id, iban, balance, account_type FROM accounts WHERE user_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$accounts_result = $stmt->get_result();
$accounts = $accounts_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include("includes/dashboard_header.php");
?>

<div class="content-card">
    <div class="page-header-row">
        <h2>Transfer Money</h2>
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

    <?php if (count($accounts) === 0): ?>
        <div class="empty-box white-box">
            <p>You do not have any account yet. Please create an account first from the Accounts page.</p>
        </div>
    <?php else: ?>
        <form action="actions/transfer_action.php" method="POST">

            <div class="mb-3">
                <label class="form-label">Select Sender Account</label>
                <select name="sender_account_id" class="form-select" required>
                    <option value="">Choose an account</option>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?php echo htmlspecialchars($account['account_id']); ?>">
                            <?php echo htmlspecialchars($account['iban']); ?>
                            - <?php echo ucfirst(htmlspecialchars($account['account_type'])); ?>
                            - <?php echo number_format($account['balance'], 2); ?> ₺
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Receiver IBAN</label>
                <div class="input-group">
                    <span class="input-group-text">TR</span>
                    <input
                        type="text"
                        name="receiver_iban"
                        id="receiverIban"
                        class="form-control"
                        maxlength="24"
                        pattern="[0-9]{24}"
                        placeholder="Enter 24 digits after TR"
                        required
                    >
                </div>
                <small class="text-muted">Enter only the 24 digits after TR. Example: 000000000000000000000000</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Amount (₺)</label>
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="amount"
                    class="form-control"
                    placeholder="Enter transfer amount"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea
                    name="description"
                    class="form-control"
                    rows="4"
                    maxlength="255"
                    placeholder="Optional transfer description"
                ></textarea>
            </div>

            <div class="form-check mb-3">
                <input
                    class="form-check-input transfer-checkbox"
                    type="checkbox"
                    name="save_beneficiary"
                    value="1"
                    id="saveBeneficiary"
                >
                <label class="form-check-label transfer-checkbox-label" for="saveBeneficiary">
                    Save as beneficiary
                </label>
            </div>

            <div id="beneficiaryFields" style="display:none;">
                <div class="mb-3">
                    <label class="form-label">Beneficiary Name</label>
                    <input
                        type="text"
                        name="beneficiary_name"
                        id="beneficiaryName"
                        class="form-control"
                        placeholder="Enter beneficiary full name"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Bank Name</label>
                    <input
                        type="text"
                        name="bank_name"
                        id="bankName"
                        class="form-control"
                        placeholder="Enter bank name"
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-danger">Send Transfer</button>
        </form>
    <?php endif; ?>
</div>

<script>
const receiverIban = document.getElementById("receiverIban");

if (receiverIban) {
    receiverIban.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 24);
    });
}

const saveBeneficiary = document.getElementById("saveBeneficiary");
const beneficiaryFields = document.getElementById("beneficiaryFields");
const beneficiaryName = document.getElementById("beneficiaryName");
const bankName = document.getElementById("bankName");

if (saveBeneficiary) {
    saveBeneficiary.addEventListener("change", function () {
        if (this.checked) {
            beneficiaryFields.style.display = "block";
            beneficiaryName.required = true;
            bankName.required = true;
        } else {
            beneficiaryFields.style.display = "none";
            beneficiaryName.required = false;
            bankName.required = false;
            beneficiaryName.value = "";
            bankName.value = "";
        }
    });
}
</script>

<?php include("includes/dashboard_footer.php"); ?>