# Project Requirements and Setup Guide

This document explains everything a new user must install and configure to run the Laravel E-Services Platform and all major features.

## 1. Required Software

Install these first:

| Requirement | Version / Notes |
| --- | --- |
| PHP | PHP `8.2` or newer |
| Composer | Latest stable Composer |
| Node.js | Node.js `20+` recommended |
| npm | Comes with Node.js |
| MySQL / MariaDB | Required by the default `.env.example` |
| Git | Required to clone the repository |
| Browser | Chrome, Edge, or Firefox |

Recommended local stacks:

- XAMPP, Laragon, WAMP, or standalone PHP + MySQL
- MySQL Workbench or phpMyAdmin for inspecting the database

## 2. Required PHP Extensions

Make sure these PHP extensions are enabled:

```text
bcmath
ctype
curl
dom
fileinfo
gd
intl
json
mbstring
openssl
pdo
pdo_mysql
tokenizer
xml
zip
```

Important notes:

- `pdo_mysql` is needed for MySQL database access.
- `fileinfo` is needed for file upload MIME validation.
- `dom`, `mbstring`, and `gd` help PDF/QR/image-related features.
- `curl` is needed for external API calls such as OCR, Google, Gmail OAuth, and Twilio.
- `openssl` is needed for secure requests and OAuth integrations.

On Windows/XAMPP, enable missing extensions from `php.ini`, then restart Apache/terminal.

## 3. Clone and Install Dependencies

Clone the repository:

```bash
git clone <repository-url>
cd e-services-platform
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create your environment file:

```bash
copy .env.example .env
```

On macOS/Linux:

```bash
cp .env.example .env
```

Generate the app key:

```bash
php artisan key:generate
```

## 4. Database Setup

Create a MySQL database, for example:

```text
e_services_platform
```

Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e_services_platform
DB_USERNAME=root
DB_PASSWORD=
```

Important: make sure there are no leading spaces before `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, or `DB_PASSWORD`.

Run migrations and seeders:

```bash
php artisan migrate --seed
```

This creates:

- admin user
- municipalities
- offices
- services
- categories
- demo citizens
- office staff
- appointments
- service-related demo data

## 5. Storage Setup

Run:

```bash
php artisan storage:link
```

The project uses two storage styles:

- `public` disk for files that are safe to expose, such as some profile/ID paths depending on feature flow
- `private` disk for citizen service documents and official response PDFs

Do not move private request documents into public storage. Private files are served through authorized controller routes or signed temporary links.

## 6. Frontend Assets

For normal local usage, build assets once:

```bash
npm run build
```

Then run Laravel:

```bash
php artisan serve
```

For active frontend development, run:

```bash
npm run dev
```

If the app tries to load assets from Vite port `5173` but Vite is not running, delete:

```text
public/hot
```

Then run:

```bash
npm run build
```

## 7. Running the App

Basic run:

```bash
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

For QR scanning from a phone on the same Wi-Fi, run:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Then the laptop still opens:

```text
http://127.0.0.1:8000
```

The phone opens:

```text
http://YOUR_LAPTOP_IPV4:8000
```

Example:

```env
APP_URL=http://127.0.0.1:8000
QR_PUBLIC_URL=http://192.168.10.67:8000
```

If the phone cannot open the laptop IP, check:

- phone and laptop are on the same Wi-Fi
- Laravel is running with `--host=0.0.0.0`
- Windows Firewall allows port `8000`
- `QR_PUBLIC_URL` matches the laptop IPv4 address

For different Wi-Fi networks, use a public tunnel such as ngrok or deploy the app online, then set `QR_PUBLIC_URL` to that public HTTPS URL.

## 8. Queue Worker

The project uses database queues for some notifications and background behavior.

Set:

```env
QUEUE_CONNECTION=database
```

Run a queue worker in a separate terminal:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

If you do not run the queue worker, some queued notifications/emails may not send until the queue is processed.

## 9. Realtime Features: Laravel Reverb

Realtime chat, notification counts, appointment updates, and revenue updates use Laravel Reverb/Echo.

Required packages are installed by Composer and npm:

- `laravel/reverb`
- `laravel-echo`
- `pusher-js`

Environment example:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=local-app
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY=local-key
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

Run Reverb in a separate terminal:

```bash
php artisan reverb:start
```

If realtime is not running, some pages still have polling fallback, but instant updates will not be fully active.

## 10. One-Command Development Mode

The project has a Composer script:

```bash
composer run dev
```

It starts:

- Laravel server
- queue listener
- log viewer
- Reverb server
- Vite dev server

This is useful for full development mode.

If it fails on Windows, run each command in a separate terminal instead:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
php artisan reverb:start
npm run dev
```

## 11. Admin Account

The admin seeder reads these variables:

```env
ADMIN_NAME="Admin"
ADMIN_EMAIL="admin@admin.com"
ADMIN_PASSWORD="admin123"
```

After seeding, log into the Filament admin panel at:

```text
http://127.0.0.1:8000/admin
```

Use whatever values you set in `.env`.

## 12. Feature-Specific External Accounts

The core app can run without all external services, but some features require API accounts.

### Google OAuth Login

Used for citizen Google sign-in/sign-up.

Create Google OAuth credentials and set:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

In Google Cloud Console, add the same redirect URI.

### Facebook OAuth Login

Optional, if using Facebook social login:

```env
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI=http://127.0.0.1:8000/auth/facebook/callback
```

### OCR.space

Used for citizen ID OCR extraction.

Set:

```env
OCR_SPACE_API_KEY=
OCR_SPACE_LANGUAGE=auto
```

If this is empty, OCR-based extraction may not work, but manual identity entry can still be used depending on the flow.

### Gmail OAuth Mailer

Used for Gmail-based email notifications and appointment reminders.

Set:

```env
MAIL_MAILER=gmail-oauth
GMAIL_CLIENT_ID=
GMAIL_CLIENT_SECRET=
GMAIL_REFRESH_TOKEN=
GMAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"
```

Helper commands exist:

```bash
php artisan gmail:authorize
php artisan gmail:test
```

After changing Gmail values:

```bash
php artisan config:clear
```

If Gmail OAuth is not configured, use the log mailer for local development:

```env
MAIL_MAILER=log
```

Emails will be written to `storage/logs/laravel.log`.

### Twilio WhatsApp

Used to send WhatsApp messages when official response documents are ready.

Set:

```env
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
TWILIO_WHATSAPP_TO_OVERRIDE=
TWILIO_SMS_FROM=
```

Testing notes:

- For Twilio Sandbox, the recipient phone must join the sandbox first.
- `TWILIO_WHATSAPP_TO_OVERRIDE` is useful when seeded users have fake phone numbers.
- Trial accounts may have daily message limits.
- Check `storage/logs/laravel.log` and Twilio Console for message status.

### Payment Configuration

Used for displaying payment instructions.

Set:

```env
PAYMENT_WHISH_NUMBER=
PAYMENT_CRYPTO_USDT_TRC20=
PAYMENT_CRYPTO_BTC=
```

These values are displayed to citizens during the payment flow.

## 13. PDF Features

The project uses:

```text
barryvdh/laravel-dompdf
```

PDFs are used for:

- citizen invoices
- official response documents

Requirements:

- PHP `dom` extension
- PHP `mbstring` extension
- Composer package installed

If PDF generation fails, run:

```bash
composer install
php artisan config:clear
```

## 14. QR Code Features

The QR tracking feature uses server-side QR SVG generation.

Important variables:

```env
APP_URL=http://127.0.0.1:8000
QR_PUBLIC_URL=http://YOUR_LAPTOP_IPV4:8000
```

Important runtime command:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

If using a public tunnel:

```env
QR_PUBLIC_URL=https://your-public-url.ngrok-free.app
```

## 15. Private Document Links

Official response documents are stored privately and served through signed temporary URLs.

This requires:

- `APP_KEY` generated
- correct `APP_URL` / `QR_PUBLIC_URL`
- `php artisan config:clear` after changing environment values

Do not expose `storage/app/private` publicly.

## 16. Suggested Fresh Setup Checklist

Run this sequence for a new local setup:

```bash
git clone <repository-url>
cd e-services-platform
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

For full realtime development, open extra terminals:

```bash
php artisan queue:listen --tries=1 --timeout=0
php artisan reverb:start
npm run dev
```

## 17. Common Problems

### 419 Page Expired

Cause: browser URL and `APP_URL` do not match.

Fix:

```env
APP_URL=http://127.0.0.1:8000
```

Then browse exactly:

```text
http://127.0.0.1:8000
```

Clear config:

```bash
php artisan config:clear
```

### QR opens `127.0.0.1` on phone

Cause: QR URL is using local loopback.

Fix:

```env
QR_PUBLIC_URL=http://YOUR_LAPTOP_IPV4:8000
```

Run:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### WhatsApp did not arrive

Check logs:

```text
storage/logs/laravel.log
```

If status is `queued`, Laravel reached Twilio.

If Twilio returns `429` or code `63038`, the Twilio account hit its daily/sandbox limit.

### Vite asset errors

If the browser looks for assets on port `5173`, either run:

```bash
npm run dev
```

or delete:

```text
public/hot
```

then run:

```bash
npm run build
```

### Reverb not updating UI

Make sure all values match:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_KEY=...
VITE_REVERB_APP_KEY=...
```

Then run:

```bash
php artisan reverb:start
```

### Environment file invalid

Laravel `.env` comments must use `#`, not `//`.

Correct:

```env
# this is a comment
```

Incorrect:

```env
// this breaks dotenv
```

## 18. Minimum Setup vs Full Feature Setup

### Minimum Setup

Needed to browse the app and test basic CRUD:

- PHP
- Composer
- Node/npm
- MySQL
- `.env`
- migrations/seeders
- built assets

### Full Feature Setup

Needed to test everything:

- queue worker
- Reverb server
- Gmail OAuth credentials
- Google OAuth credentials
- OCR.space API key
- Twilio WhatsApp credentials
- payment display config
- QR public URL / LAN IP or ngrok

## 19. Files That Should Not Be Committed

These should stay local:

```text
.env
storage/logs/*.log
vendor/
node_modules/
public/hot
```

The existing `.gitignore` already covers the important local files such as `.env`, logs, `vendor`, and `node_modules`.
