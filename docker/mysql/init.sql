-- ============================================================
-- Virtual PTSP - MySQL Initial Schema
-- Built with ❤️ by zhayyn (+6281317361689)
-- ============================================================

-- Create database with utf8mb4
CREATE DATABASE IF NOT EXISTS virtual_ptsp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE virtual_ptsp;

-- ============================================================
-- Users Table
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NULL,
    google_id VARCHAR(255) NULL UNIQUE,
    google_token TEXT NULL,
    role ENUM('super_admin', 'admin', 'operator', 'user') DEFAULT 'user',
    avatar VARCHAR(500) NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_google_id (google_id),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- AI Providers Configuration
-- ============================================================
CREATE TABLE IF NOT EXISTS ai_providers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    logo_url VARCHAR(500) NULL,
    api_endpoint VARCHAR(500) NOT NULL,
    default_model VARCHAR(100) NOT NULL,
    available_models JSON NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    config_schema JSON NULL COMMENT 'JSON schema for provider-specific config',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed AI Providers
INSERT INTO ai_providers (slug, name, logo_url, api_endpoint, default_model, available_models, config_schema) VALUES
('gemini', 'Google Gemini', 'https://www.google.com/images/branding/product/2x/gemini_32dp.png', 'https://generativelanguage.googleapis.com/v1beta', 'gemini-2.0-flash', '["gemini-2.0-flash","gemini-1.5-pro","gemini-1.5-flash","gemini-pro"]', '{"type":"object","required":["api_key"],"properties":{"api_key":{"type":"string","label":"API Key"},"temperature":{"type":"number","default":0.7},"max_tokens":{"type":"integer","default":2048}}}'),
('claude', 'Anthropic Claude', 'https://docs.anthropic.com/images/favicon.svg', 'https://api.anthropic.com/v1', 'claude-sonnet-4-20250514', '["claude-sonnet-4-20250514","claude-opus-4-20250514","claude-haiku-4-20250514"]', '{"type":"object","required":["api_key"],"properties":{"api_key":{"type":"string","label":"API Key"},"temperature":{"type":"number","default":0.7},"max_tokens":{"type":"integer","default":2048}}}'),
('openai', 'OpenAI', 'https://cdn.cdnlogo.com/logos/o/46/openai-icon.svg', 'https://api.openai.com/v1', 'gpt-4o-mini', '["gpt-4o","gpt-4o-mini","gpt-4-turbo","gpt-3.5-turbo"]', '{"type":"object","required":["api_key"],"properties":{"api_key":{"type":"string","label":"API Key"},"organization_id":{"type":"string","label":"Organization ID"},"temperature":{"type":"number","default":0.7},"max_tokens":{"type":"integer","default":2048}}}');

-- ============================================================
-- AI Configurations (per organization/customer)
-- ============================================================
CREATE TABLE IF NOT EXISTS ai_configs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    provider_id BIGINT UNSIGNED NOT NULL,
    api_key_encrypted TEXT NOT NULL,
    selected_model VARCHAR(100) NOT NULL,
    settings JSON NOT NULL DEFAULT '{}',
    is_active TINYINT(1) DEFAULT 1,
    last_tested_at TIMESTAMP NULL,
    last_error TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES ai_providers(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_provider (provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Knowledge Base
-- ============================================================
CREATE TABLE IF NOT EXISTS knowledge_bases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    settings JSON NOT NULL DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Knowledge Items
-- ============================================================
CREATE TABLE IF NOT EXISTS knowledge_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    knowledge_base_id BIGINT UNSIGNED NOT NULL,
    type ENUM('text', 'file', 'url') NOT NULL,
    title VARCHAR(500) NOT NULL,
    content TEXT NULL COMMENT 'For text type',
    file_path VARCHAR(500) NULL COMMENT 'For file type',
    file_name VARCHAR(255) NULL,
    file_size INT NULL,
    mime_type VARCHAR(100) NULL,
    url_source VARCHAR(500) NULL COMMENT 'For url type',
    url_content TEXT NULL COMMENT 'Cached scraped content',
    embeddings JSON NULL COMMENT 'Vector embeddings',
    metadata JSON NULL,
    is_processed TINYINT(1) DEFAULT 0,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (knowledge_base_id) REFERENCES knowledge_bases(id) ON DELETE CASCADE,
    INDEX idx_kb (knowledge_base_id),
    INDEX idx_type (type),
    INDEX idx_processed (is_processed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Omnichannel Channels
-- ============================================================
CREATE TABLE IF NOT EXISTS channels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    type ENUM('whatsapp', 'webchat', 'instagram', 'facebook', 'tiktok', 'telegram') NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    config JSON NOT NULL DEFAULT '{}',
    credentials_encrypted TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- WhatsApp Settings
-- ============================================================
CREATE TABLE IF NOT EXISTS whatsapp_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    gateway_url VARCHAR(500) NOT NULL,
    gateway_api_key_encrypted VARCHAR(500) NOT NULL,
    webhook_url VARCHAR(500) NULL,
    webhook_secret_encrypted VARCHAR(255) NULL,
    default_channel_id BIGINT UNSIGNED NULL,
    auto_reply_enabled TINYINT(1) DEFAULT 0,
    ai_config_id BIGINT UNSIGNED NULL,
    knowledge_base_id BIGINT UNSIGNED NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ai_config_id) REFERENCES ai_configs(id) ON DELETE SET NULL,
    FOREIGN KEY (knowledge_base_id) REFERENCES knowledge_bases(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Conversations (Unified Inbox)
-- ============================================================
CREATE TABLE IF NOT EXISTS conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    channel_id BIGINT UNSIGNED NOT NULL,
    channel_type ENUM('whatsapp', 'webchat', 'instagram', 'facebook', 'tiktok', 'telegram') NOT NULL,
    channel_conversation_id VARCHAR(255) NOT NULL,
    contact_name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(50) NULL,
    contact_email VARCHAR(255) NULL,
    status ENUM('open', 'pending', 'resolved', 'closed') DEFAULT 'open',
    assigned_to BIGINT UNSIGNED NULL,
    last_message_at TIMESTAMP NULL,
    last_message_preview TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (channel_id) REFERENCES channels(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_channel (channel_id),
    INDEX idx_status (status),
    INDEX idx_assigned (assigned_to),
    INDEX idx_last_message (last_message_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Messages
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    conversation_id BIGINT UNSIGNED NOT NULL,
    direction ENUM('inbound', 'outbound', 'system') NOT NULL,
    content TEXT NOT NULL,
    content_type ENUM('text', 'image', 'audio', 'video', 'document', 'location', 'contact') DEFAULT 'text',
    media_url VARCHAR(500) NULL,
    status ENUM('pending', 'sent', 'delivered', 'read', 'failed', 'error') DEFAULT 'pending',
    sent_via ENUM('api', 'dashboard', 'ai_auto', 'webhook') NOT NULL,
    external_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_conversation (conversation_id),
    INDEX idx_direction (direction),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Web Chat Sessions
-- ============================================================
CREATE TABLE IF NOT EXISTS webchat_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    session_id VARCHAR(100) NOT NULL UNIQUE,
    visitor_name VARCHAR(255) NULL,
    visitor_email VARCHAR(255) NULL,
    visitor_ip VARCHAR(45) NULL,
    user_agent TEXT NULL,
    conversation_id BIGINT UNSIGNED NULL,
    page_url VARCHAR(500) NULL,
    referrer VARCHAR(500) NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    metadata JSON NULL,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Web Chat Widget Settings
-- ============================================================
CREATE TABLE IF NOT EXISTS webchat_widgets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    name VARCHAR(100) NOT NULL,
    config JSON NOT NULL DEFAULT '{}',
    embed_code TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- AI Chat Logs (for training & analysis)
-- ============================================================
CREATE TABLE IF NOT EXISTS ai_chat_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    conversation_id BIGINT UNSIGNED NULL,
    channel_type ENUM('whatsapp', 'webchat', 'instagram', 'facebook', 'tiktok', 'telegram') NOT NULL,
    contact_name VARCHAR(255) NOT NULL,
    user_message TEXT NOT NULL,
    ai_response TEXT NULL,
    model_used VARCHAR(100) NOT NULL,
    tokens_used INT NULL,
    latency_ms INT NULL,
    knowledge_base_id BIGINT UNSIGNED NULL,
    is_rag_hit TINYINT(1) DEFAULT 0,
    confidence_score DECIMAL(3,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE SET NULL,
    FOREIGN KEY (knowledge_base_id) REFERENCES knowledge_bases(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tenants (for multi-tenant / multi-customer)
-- ============================================================
CREATE TABLE IF NOT EXISTS tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    domain VARCHAR(255) NULL,
    logo_url VARCHAR(500) NULL,
    primary_color VARCHAR(7) DEFAULT '#6366F1',
    license_key VARCHAR(255) NULL,
    license_expires_at TIMESTAMP NULL,
    settings JSON NOT NULL DEFAULT '{}',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Activity Logs
-- ============================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- API Keys (for integrations)
-- ============================================================
CREATE TABLE IF NOT EXISTS api_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    key_prefix VARCHAR(10) NOT NULL,
    permissions JSON NOT NULL DEFAULT '[]',
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_key (key_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- System Settings
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group` VARCHAR(50) NOT NULL,
    `key` VARCHAR(100) NOT NULL,
    value TEXT NULL,
    type ENUM('string', 'boolean', 'integer', 'json') DEFAULT 'string',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_setting (`group`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default settings
INSERT INTO settings (`group`, `key`, value, type) VALUES
('app', 'app_name', 'Virtual PTSP', 'string'),
('app', 'app_url', 'https://virtual-ptsp.com', 'string'),
('app', 'support_email', 'support@virtual-ptsp.com', 'string'),
('license', 'server_url', '', 'string'),
('license', 'check_interval', '60', 'integer');

-- ============================================================
-- Create default super admin
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
('zhayyn Admin', 'zhayyn@virtual-ptsp.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NTCI4O3wq/Fy', 'super_admin');
-- Password: admin123 (change immediately after first login)

-- ============================================================
-- Create default tenant
-- ============================================================
INSERT INTO tenants (name, slug, license_key, is_active) VALUES
('Demo Tenant', 'demo', 'DEMO-XXXX-XXXX-XXXX', 1);
