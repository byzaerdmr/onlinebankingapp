DROP DATABASE IF EXISTS banking_app;
CREATE DATABASE banking_app;
USE banking_app;

-- USERS TABLE
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_no VARCHAR(10) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('personal', 'business') NOT NULL DEFAULT 'personal',
    phone VARCHAR(20),
    profile_photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ACCOUNTS TABLE
CREATE TABLE accounts (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    iban VARCHAR(34) NOT NULL UNIQUE,
    account_type ENUM('current', 'savings') NOT NULL DEFAULT 'current',
    balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- BENEFICIARIES TABLE
CREATE TABLE beneficiaries (
    beneficiary_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    beneficiary_name VARCHAR(100) NOT NULL,
    beneficiary_iban VARCHAR(34) NOT NULL,
    bank_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- TRANSACTIONS TABLE
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_account_id INT NOT NULL,
    receiver_iban VARCHAR(34) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_type ENUM('internal', 'external') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sender_account_id) REFERENCES accounts(account_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- AUDIT LOGS TABLE
CREATE TABLE audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT,
    action_type VARCHAR(50) NOT NULL,
    log_message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- STORED PROCEDURE FOR MONEY TRANSFER
DELIMITER $$

CREATE PROCEDURE transfer_money(
    IN p_sender_account_id INT,
    IN p_receiver_iban VARCHAR(34),
    IN p_amount DECIMAL(10,2),
    IN p_description TEXT
)
BEGIN
    DECLARE v_sender_balance DECIMAL(10,2);
    DECLARE v_receiver_account_id INT DEFAULT NULL;

    START TRANSACTION;

    SELECT balance
    INTO v_sender_balance
    FROM accounts
    WHERE account_id = p_sender_account_id
    FOR UPDATE;

    IF v_sender_balance IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Sender account not found.';
    END IF;

    IF p_amount <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Transfer amount must be greater than zero.';
    END IF;

    IF v_sender_balance < p_amount THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Insufficient balance.';
    END IF;

    UPDATE accounts
    SET balance = balance - p_amount
    WHERE account_id = p_sender_account_id;

    SELECT account_id
    INTO v_receiver_account_id
    FROM accounts
    WHERE iban = p_receiver_iban
    LIMIT 1;

    IF v_receiver_account_id IS NOT NULL THEN
        UPDATE accounts
        SET balance = balance + p_amount
        WHERE account_id = v_receiver_account_id;

        INSERT INTO transactions (
            sender_account_id,
            receiver_iban,
            amount,
            transaction_type,
            description
        )
        VALUES (
            p_sender_account_id,
            p_receiver_iban,
            p_amount,
            'internal',
            p_description
        );
    ELSE
        INSERT INTO transactions (
            sender_account_id,
            receiver_iban,
            amount,
            transaction_type,
            description
        )
        VALUES (
            p_sender_account_id,
            p_receiver_iban,
            p_amount,
            'external',
            p_description
        );
    END IF;

    COMMIT;
END$$

DELIMITER ;

-- TRIGGER FOR AUTOMATIC AUDIT LOGGING
DELIMITER $$

CREATE TRIGGER after_transaction_insert
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (
        transaction_id,
        action_type,
        log_message
    )
    VALUES (
        NEW.transaction_id,
        'TRANSFER_CREATED',
        CONCAT(
            'A transfer of ',
            NEW.amount,
            ' was created from account ID ',
            NEW.sender_account_id,
            ' to IBAN ',
            NEW.receiver_iban
        )
    );
END$$

DELIMITER ;

-- JOIN VIEW FOR TRANSACTION HISTORY
CREATE VIEW transaction_history_view AS
SELECT
    t.transaction_id,
    u.user_id,
    u.full_name,
    u.email,
    a.iban AS sender_iban,
    t.receiver_iban,
    t.amount,
    t.transaction_type,
    t.description,
    t.created_at
FROM transactions t
JOIN accounts a
    ON t.sender_account_id = a.account_id
JOIN users u
    ON a.user_id = u.user_id;