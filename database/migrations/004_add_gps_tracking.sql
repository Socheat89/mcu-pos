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
    chat_title VARCHAR(255) NULL COMMENT 'Group name (auto-detected)',
    setup_code VARCHAR(10) NULL COMMENT '6-char setup code for easy pairing',
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

-- Pending Telegram links (temporary pairing codes before tenant claims them)
CREATE TABLE IF NOT EXISTS telegram_pending_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setup_code VARCHAR(10) NOT NULL UNIQUE COMMENT '6-char code user enters in POS',
    chat_id VARCHAR(100) NOT NULL COMMENT 'Detected group chat ID',
    chat_title VARCHAR(255) NULL COMMENT 'Group name',
    chat_type VARCHAR(20) NULL COMMENT 'group / supergroup',
    bot_token VARCHAR(255) NULL COMMENT 'Bot token used',
    claimed_by_tenant_id INT NULL COMMENT 'NULL until claimed',
    claimed_at DATETIME NULL,
    expires_at DATETIME NOT NULL COMMENT 'Code expires after 24h',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_setup_code (setup_code),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
