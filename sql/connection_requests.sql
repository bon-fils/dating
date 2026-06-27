-- Run once: mysql -u root matchme < sql/connection_requests.sql

CREATE TABLE IF NOT EXISTS connection_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_request (sender_id, receiver_id),
    KEY idx_receiver_status (receiver_id, status),
    KEY idx_sender_status (sender_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
