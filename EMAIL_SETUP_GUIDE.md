# Email Setup Guide

## Gmail SMTP Configuration

Gmail requires **App Passwords** for SMTP authentication. You cannot use your regular Gmail password.

### Step 1: Enable 2-Step Verification

1. Go to your Google Account: https://myaccount.google.com/
2. Click on **Security** in the left sidebar
3. Under "Signing in to Google", find **2-Step Verification**
4. Follow the steps to enable it (if not already enabled)

### Step 2: Generate App Password

1. Go back to **Security** settings
2. Under "Signing in to Google", find **App passwords**
3. You may need to sign in again
4. Select **Mail** as the app type
5. Select **Other (Custom name)** as the device type
6. Enter a name like "Laravel App" or "Timetable System"
7. Click **Generate**
8. Copy the 16-character password (it will look like: `abcd efgh ijkl mnop`)

### Step 3: Update .env File

Update your `.env` file with the App Password:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=technestagency@gmail.com
MAIL_PASSWORD=your-16-character-app-password-here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=technestagency@gmail.com
MAIL_FROM_NAME="أكاديمية ترتيل"
```

**Important:** 
- Remove spaces from the App Password when pasting it
- The `MAIL_PASSWORD` should be the 16-character App Password, NOT your regular Gmail password

### Step 4: Clear Config Cache

After updating `.env`, run:

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 5: Test Again

Visit: `http://127.0.0.1:8000/admin/test-email`

---

## Alternative: Use Mailtrap (For Testing/Development)

If you want to test emails without configuring Gmail, you can use Mailtrap:

1. Sign up at https://mailtrap.io/ (free tier available)
2. Create an inbox
3. Get your SMTP credentials from Mailtrap
4. Update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=test@example.com
MAIL_FROM_NAME="أكاديمية ترتيل"
```

Emails sent will appear in your Mailtrap inbox instead of being actually sent.

---

## Alternative: Use Other SMTP Services

You can also use:
- **SendGrid** (free tier: 100 emails/day)
- **Mailgun** (free tier: 5,000 emails/month)
- **Amazon SES** (very cheap, pay-as-you-go)
- **Postmark** (free tier: 100 emails/month)

Each service will provide their own SMTP credentials.

---

## Troubleshooting

### Error: "Username and Password not accepted"
- Make sure you're using an **App Password**, not your regular password
- Ensure 2-Step Verification is enabled
- Check that the App Password doesn't have spaces
- Try regenerating the App Password

### Error: "Connection timeout"
- Check your firewall settings
- Verify `MAIL_PORT=587` (or try `465` with `MAIL_ENCRYPTION=ssl`)
- Check if your hosting provider blocks SMTP ports

### Emails not sending
- Check Laravel logs: `storage/logs/laravel.log`
- Verify support email is set in Settings page
- Make sure timetable entries have `notification_minutes` set
- Check that the scheduler is running: `php artisan schedule:work` (for testing)

---

## Production Recommendations

For production, consider:
1. Using a dedicated email service (SendGrid, Mailgun, etc.)
2. Setting up proper SPF/DKIM records for your domain
3. Using a dedicated email address (not a personal Gmail)
4. Monitoring email delivery rates
5. Setting up email queues for better performance

