-- Migration: Add GPS Tracking tables
-- Date: 2026-07-21
-- Description: GPS location tracking for mobile sellers with real-time owner dashboard

-- GPS Tracking Sessions (linked to POS sessions)
CREATE TABLE IF NOT EXISTS gps_tracking_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    store_id INT NULL,
    user_id INT NOT NULL,
    pos_session_id INT NULL,
    status ENUM('active', 'stopped') DEFAULT 'active',
    device_info VARCHAR(255) NULL COMMENT 'User agent / device identifier',
    started_at DATETIME NOT NULL,
    ended_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant_user (tenant_id, user_id),
    INDEX idx_status (status),
    INDEX idx_pos_session (pos_session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- GPS Location Points
CREATE TABLE IF NOT EXISTS gps_locations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tracking_session_id INT NOT NULL,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    accuracy DECIMAL(8, 2) NULL COMMENT 'GPS accuracy in meters',
    altitude DECIMAL(8, 2) NULL,
    speed DECIMAL(6, 2) NULL COMMENT 'Speed in m/s if moving',
    heading DECIMAL(5, 2) NULL COMMENT 'Heading in degrees',
    battery_level DECIMAL(5, 2) NULL COMMENT 'Device battery percentage',
    recorded_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tracking_session (tracking_session_id),
    INDEX idx_tenant_user (tenant_id, user_id),
    INDEX idx_recorded_at (recorded_at),
    FOREIGN KEY (tracking_session_id) REFERENCES gps_tracking_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Telegram Bot Configuration per tenant
CREATE TABLE IF NOT EXISTS tenant_telegram_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    bot_token VARCHAR(255) NULL COMMENT 'Custom bot token (if user provides their own)',
    chat_id VARCHAR(100) NULL COMMENT 'Group or channel chat ID',
    notify_session_open TINYINT(1) DEFAULT 1 COMMENT 'Notify when POS session opens',
    notify_session_close TINYINT(1) DEFAULT 1 COMMENT 'Notify when POS session closes',
    notify_sales_report TINYINT(1) DEFAULT 1 COMMENT 'Send daily sales report',
    notify_gps_start TINYINT(1) DEFAULT 1 COMMENT 'Notify when GPS tracking starts',
    notify_gps_stop TINYINT(1) DEFAULT 1 COMMENT 'Notify when GPS tracking stops',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_telegram (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
