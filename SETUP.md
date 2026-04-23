# GrimbaNews — Local Development Setup

**Sprint:** 1 (Echo CMS Baseline)  
**Date:** 2026-04-23  
**Status:** Audit complete → Manual setup required

---

## Prerequisites

### 1. Start Docker Desktop

Docker is installed but not running. Before proceeding:

```bash
# macOS: Open Docker Desktop
open -a Docker

# Wait for Docker to fully start (whale icon in menu bar is stable)
# Then verify:
docker ps
# Should show: CONTAINER ID   IMAGE   COMMAND   CREATED   STATUS   PORTS   NAMES
```

---

## Setup Steps

### Step 1: Install Composer Dependencies

Since PHP/Composer are not installed locally, use Docker to run composer:

```bash
cd /Users/vb/GrimbaNews

# Option A: Use Docker to run composer
docker run --rm \
    -v $(pwd):/app \
    -w /app \
    php:8.2-cli \
    composer install --no-interaction --prefer-dist

# Option B: After Docker Desktop is running, use Sail (recommended)
# First, install Sail via docker:
docker run --rm \
    -v $(pwd):/app \
    -w /app \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

### Step 2: Configure Environment

```bash
# Copy environment template
cp .env.example .env

# Edit .env with GrimbaNews config:
# Use a text editor or run:
sed -i '' 's/APP_NAME="Your App"/APP_NAME="GrimbaNews"/g' .env
sed -i '' 's/APP_URL=http:\/\/localhost/APP_URL=http:\/\/localhost/g' .env
sed -i '' 's/DB_DATABASE="laravel"/DB_DATABASE="grimba_news"/g' .env
sed -i '' 's/DB_USERNAME="root"/DB_USERNAME="sail"/g' .env
sed -i '' 's/DB_PASSWORD="your_db_password"/DB_PASSWORD="password"/g' .env
```

### Step 3: Start Sail Environment

```bash
# Make sail executable (after composer install creates vendor/bin/sail)
chmod +x vendor/bin/sail

# Start all containers
./vendor/bin/sail up -d

# Wait ~30 seconds for MySQL to initialize
# Check container status:
./vendor/bin/sail ps

# Should show: laravel.test (healthy), mysql (healthy)
```

### Step 4: Run Database Migrations

```bash
# Generate app key
./vendor/bin/sail artisan key:generate

# Run migrations
./vendor/bin/sail artisan migrate --seed

# Verify database
./vendor/bin/sail artisan db:show
```

### Step 5: Access the Application

- **Homepage:** http://localhost
- **Admin Panel:** http://localhost/admin
- **Default Admin Credentials:** (check database.sql or .env for defaults)

---

## Troubleshooting

### Docker Not Starting
```bash
# Check Docker status
docker ps

# If error: "Cannot connect to Docker daemon"
# → Open Docker Desktop app and wait for it to fully start
# → May require macOS restart if Docker Desktop hangs
```

### Port Already in Use
```bash
# If port 80 is in use, change APP_PORT in .env:
APP_PORT=8080
# Then restart: ./vendor/bin/sail down && ./vendor/bin/sail up -d
```

### Composer Install Fails
```bash
# Try with ignore platform reqs:
docker run --rm \
    -v $(pwd):/app \
    -w /app \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

---

## Next Steps (After Setup)

1. **Activate Plugins** (admin panel):
   - RSS Feed
   - AI Writer
   - Newsletter
   - Language (FR/EN)

2. **Configure Languages**:
   - Set French (FR) as default
   - Keep English (EN) as secondary

3. **Test Core Features**:
   - Create a test article
   - Test RSS feed import
   - Test AI writer integration

---

## Sprint 1 Completion Checklist

- [ ] Docker Desktop running
- [ ] Composer dependencies installed
- [ ] .env configured for GrimbaNews
- [ ] Sail containers running (laravel.test, mysql)
- [ ] Database migrated
- [ ] Homepage accessible (http://localhost)
- [ ] Admin panel accessible (http://localhost/admin)
- [ ] Git commit + push to `darkvaderfr/grimbanews`

---

*When setup is complete, run:*
```bash
cd /Users/vb/GrimbaNews
git add .env
git commit -m "Sprint 1: Local dev environment configured"
git push origin main
```
