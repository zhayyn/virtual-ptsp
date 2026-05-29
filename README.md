# Virtual PTSP
### Omnichannel Customer Service Platform with AI

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-11-green.svg" alt="Laravel">
  <img src="https://img.shields.io/badge/Docker-Ready-blue.svg" alt="Docker">
  <img src="https://img.shields.io/badge/License-Proprietary-red.svg" alt="License">
</p>

<p align="center">
  Built with ❤️ by <strong>zhayyn</strong> (+6281317361689)
</p>

---

## 🎯 What is Virtual PTSP?

**Virtual PTSP** is a comprehensive omnichannel customer service platform that enables businesses to manage customer conversations from multiple channels (WhatsApp, Web Chat, Instagram, etc.) in a single unified inbox, powered by AI for 24/7 automated responses.

### Key Features

- 📱 **Omnichannel Inbox** — WhatsApp, Web Chat, Instagram, Facebook, TikTok, Telegram
- 🤖 **AI Auto-Reply with RAG** — Knowledge base powered responses
- 📚 **Knowledge Base** — Upload files, manual text, or scrape websites
- ⚙️ **Multi AI Provider** — Gemini, Claude, OpenAI (bring your own API key)
- 🔐 **License System** — Domain-bound validation for commercial use
- 🚀 **One-Click Deploy** — Docker-based deployment

---

## 🚀 Quick Start

### Prerequisites

- Docker & Docker Compose
- Git

### Installation

```bash
# Clone the repository
git clone https://github.com/zhayyn/virtual-ptsp.git
cd virtual-ptsp

# Copy environment file
cp src/.env.example src/.env

# Start with Docker
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Create admin user
docker-compose exec app php artisan tinker
>>> \App\Models\User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>\Illuminate\Support\Facades\Hash::make('password'),'role'=>'super_admin']);
```

Access at: `http://localhost`

---

## 🐳 Docker Deployment

```yaml
# docker-compose.yml
services:
  app:
    build: .
    volumes:
      - app-data:/var/www/html
    environment:
      - APP_ENV=production
      - DB_HOST=mysql
      - REDIS_HOST=redis

  mysql:
    image: mysql:8.0
    environment:
      - MYSQL_DATABASE=virtual_ptsp
      - MYSQL_USER=virtual_ptsp
      - MYSQL_PASSWORD=secret

  redis:
    image: redis:alpine

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
```

---

## ⚙️ Configuration

### AI Provider Setup

```env
# Choose your AI provider
DEFAULT_AI_PROVIDER=gemini

# Gemini (Recommended - Free tier available)
GEMINI_API_KEY=your_gemini_api_key

# OR Claude
ANTHROPIC_API_KEY=your_anthropic_key

# OR OpenAI
OPENAI_API_KEY=your_openai_key
```

### WhatsApp Gateway

```env
WA_GATEWAY_URL=http://your-wa-gateway:8790
WA_GATEWAY_API_KEY=your_wa_api_key
```

### License Server

```env
LICENSE_SERVER_URL=https://your-license-server.com
LICENSE_SERVER_SECRET=your_secret
```

---

## 📚 Knowledge Base

Virtual PTSP supports multiple ways to populate your AI's knowledge:

### 1. Manual Text
```php
$kb->addText('FAQ', 'Produk kami garansi 1 tahun...');
```

### 2. File Upload
- PDF
- DOCX
- TXT
- CSV
- JSON

### 3. Web Scraping
```php
$kb->addUrl('https://example.com/pricing');
```

---

## 🔐 License System

Virtual PTSP uses a domain-bound license validation system:

1. Each installation is bound to a specific domain
2. License is validated against your license server
3. Expired or invalid licenses are blocked

### Generate License (Admin)

```bash
curl -X POST https://your-license-server.com/register \
  -H "Content-Type: application/json" \
  -d '{
    "admin_secret": "your_admin_secret",
    "domain": "customer.com",
    "plan": "pro",
    "months": 12
  }'
```

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    VIRTUAL PTSP                              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐        │
│  │  WhatsApp   │  │  Web Chat   │  │   Others    │        │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘        │
│         │                │                │                 │
│         └────────────────┼────────────────┘                 │
│                          ▼                                  │
│              ┌───────────────────────┐                     │
│              │    Message Broker     │                     │
│              └───────────┬───────────┘                     │
│                          ▼                                  │
│  ┌─────────────────────────────────────────┐               │
│  │         AI Service (RAG Engine)          │               │
│  │   ┌─────────┬─────────┬─────────┐      │               │
│  │   │ Gemini  │ Claude  │ OpenAI  │      │               │
│  │   └─────────┴─────────┴─────────┘      │               │
│  └─────────────────────────────────────────┘               │
│                          │                                  │
│         ┌────────────────┼────────────────┐                 │
│         ▼                ▼                ▼                 │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐        │
│  │  Dashboard │  │ Knowledge   │  │ Conversation│        │
│  │   Admin    │  │   Base      │  │   Manager   │        │
│  └─────────────┘  └─────────────┘  └─────────────┘        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 Project Structure

```
virtual-ptsp/
├── docker/                    # Docker configuration
│   ├── nginx/
│   ├── php/
│   └── mysql/
├── license-server/            # License validation server
├── src/                       # Application source
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Middleware/
│   │   ├── Models/
│   │   └── Services/
│   ├── config/
│   ├── resources/
│   │   └── views/
│   └── routes/
├── docker-compose.yml
├── Dockerfile
└── README.md
```

---

## 🛡️ Security

- HTTPS enforced
- CSRF protection
- SQL injection prevention
- XSS protection
- Encrypted credentials storage
- Rate limiting
- Domain-bound licensing

---

## 📄 License

This is proprietary software. See [LICENSE](LICENSE) file for details.

**Copyright © 2024 zhayyn. All rights reserved.**

Built with ❤️ by **zhayyn** (+6281317361689)

---

## 🤝 Support

- 📧 Email: support@virtual-ptsp.com
- 💬 WhatsApp: +6281317361689
- 📖 Documentation: [docs.virtual-ptsp.com](https://docs.virtual-ptsp.com)

---

<p align="center">
  <strong>Virtual PTSP</strong> — Pelayanan Prima, Tanpa Batas
</p>
