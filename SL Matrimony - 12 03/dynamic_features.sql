CREATE TABLE IF NOT EXISTS interests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_interest_pair (sender_id, recipient_id),
    KEY recipient_created_at (recipient_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS subscriptions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id INT NOT NULL,
    package_name VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending_payment',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY customer_created_at (customer_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
