# REFA — রেমিট্যান্স ফাইটার্স অ্যাসোসিয়েশন

ফিনল্যান্ডে ২০২৬ সালে গড়া প্রবাসী বাংলাদেশি রেমিট্যান্স কর্মীদের অলাভজনক কমিউনিটি ওয়েবসাইট। সব পাবলিক ও অ্যাডমিন ইন্টারফেস বাংলায় (`lang="bn"`), কনটেন্ট MySQL থেকে আসে।

Core PHP · MySQL (PDO) · Hind Siliguri · সেশন অথ · CSRF · রেট লিমিট

## যা যা আছে

- হোম, আমাদের সম্পর্কে, কান্ট্রি চ্যাপ্টার (তালিকা + বিস্তারিত), সদস্যপদ আবেদন
- সংবাদ ও ইভেন্ট (RSVP), সাফল্যের গল্প, সহায়িকা, যোগাযোগ, স্বেচ্ছাসেবক, অনুদান
- সদস্য লগইন / পাসওয়ার্ড রিসেট / ড্যাশবোর্ড
- অ্যাডমিন প্যানেল: চ্যাপ্টার, সদস্য অনুমোদন, সংবাদ, ইভেন্ট, নেতৃত্ব, গল্প, সহায়িকা, বার্তা, সেটিংস
- চ্যাপ্টার তালিকা ডাটাবেজ-চালিত — নতুন দেশ অ্যাডমিন থেকে যোগ করলে সাইটে দেখাবে

সদস্যপদ ফি-ভিত্তিক নয় (সাধারণ / সম্মানিত)। পরে পেইড টিয়ার যোগ করতে `settings.membership_paid_tiers` ব্যবহার করা যায়।

## হোস্টিং (প্রোডাকশন)

ডাটাবেজ আগেই তৈরি: `refabdco_main` (localhost)। কানেকশন `.env` ও `config/credentials.php`-এ সেট করা আছে।

1. phpMyAdmin-এ **সেই ডাটাবেজ সিলেক্ট করে** `sql/schema.sql` ইমপোর্ট করুন (`CREATE DATABASE` চালাবেন না)।
2. পুরো সাইট `public_html` (বা ডোমেইনের রুট) এ আপলোড করুন।
3. `APP_URL` খালি রাখলে ডোমেইন নিজে থেকে ধরা হবে।

বিস্তারিত: `UPLOAD.txt`

## সেটআপ (XAMPP / Windows)

1. প্রজেক্টটি রাখুন, যেমন `C:\xampp\htdocs\REFA`
2. phpMyAdmin-এ ডাটাবেজ তৈরি করুন (লোকাল):

```sql
CREATE DATABASE refa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. `sql/schema.sql` ইমপোর্ট করুন (টেবিল + নমুনা তথ্য)
4. লোকালে `.env` / `config/credentials.php` আপনার লোকাল ইউজার দিয়ে বদলান।

`APP_URL` খালি রাখলে হোস্ট নিজে থেকে সেট হয়। লোকাল সাবফোল্ডার হলে চাইলে `http://localhost/REFA` দিন।

5. ব্রাউজারে খুলুন: `http://localhost/REFA/`

XAMPP-এ PHP PATH-এ না থাকলে:

```
C:\xampp\php\php.exe -S localhost:8000 -t C:\Users\Nasir\REFA
```

সেক্ষেত্রে `.env`-এ `APP_URL=http://localhost:8000` দিন।

### ডেমো লগইন (ইমপোর্টের পর বদলে ফেলুন)

| ভূমিকা | ইমেইল | পাসওয়ার্ড |
|--------|--------|-----------|
| অ্যাডমিন | `admin@refa.org` | `password` |
| সদস্য | `karim@refa.example` | `password` |

অ্যাডমিন: `/admin/login.php`

## ইমেইল / PHPMailer

ডিফল্ট `MAIL_DRIVER=log` — চিঠি যায় `storage/mail/outbox.log`-এ (লোকাল টেস্টের জন্য)।

আসল SMTP:

```
composer install
```

`.env`:

```
MAIL_DRIVER=smtp
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
SMTP_ENCRYPTION=tls
SMTP_FROM=noreply@yourdomain.org
ADMIN_NOTIFY_EMAIL=admin@yourdomain.org
```

Composer ছাড়া `MAIL_DRIVER=mail` PHP `mail()` ব্যবহার করবে।

## আপলোড ও নিরাপত্তা

- ছবি: `assets/uploads/` (JPG / PNG / WebP, ডিফল্ট সর্বোচ্চ ২ MB)
- ফর্মে CSRF, PDO প্রিপেয়ার্ড স্টেটমেন্ট, `password_hash`
- লগইন ও কন্টাক্ট ফর্মে রেট লিমিট
- `.env`, `/config`, `/sql`, `/storage` ওয়েব থেকে ব্লক (Apache)

সদস্য আবেদন `pending` থাকে। অ্যাডমিন অনুমোদন করলে ইমেইল যায়; পাসওয়ার্ড না থাকলে অস্থায়ী পাসওয়ার্ড তৈরি হয়।

## ইংরেজি পরে যোগ করতে

লেবেল আছে `lang/bn.php`-তে। `lang/en.php` বানিয়ে `$_SESSION['locale']` দিয়ে `lang/load.php` ইতিমধ্যে ফাইল বেছে নেয়। ডাটাবেজের লেখা আলাদা করে অনুবাদ করতে হবে।

## ফোল্ডার

```
admin/          অ্যাডমিন CRUD
member/         সদস্য এরিয়া
includes/       লেআউট, অথ, মেইল, আপলোড
config/         .env লোড, PDO
lang/           বাংলা স্ট্রিং
sql/schema.sql  স্কিমা + সিড
assets/         css, js, images, uploads
```

নতুন কান্ট্রি চ্যাপ্টার শুধু অ্যাডমিন → চ্যাপ্টার থেকে যোগ করুন — কোডে দেশের তালিকা হার্ডকোড নেই।
