-- REFA (Remittance Fighters Association) database schema
-- Character set: utf8mb4 for Bangla
--
-- Import this file INTO the existing database (phpMyAdmin → database → Import).
-- Do not run CREATE DATABASE — shared hosting already created the empty database.
-- Target database name: refabdco_main
--
-- Demo logins (change immediately after first login):
--   Admin:  admin@refa.org  /  password
--   Member: karim@refa.example  /  password

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS event_rsvps;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS volunteer_interest;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS leadership;
DROP TABLE IF EXISTS members;
DROP TABLE IF EXISTS faqs;
DROP TABLE IF EXISTS content_blocks;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS countries;
DROP TABLE IF EXISTS admins;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(40) NOT NULL DEFAULT 'admin',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE countries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL UNIQUE,
  iso_code CHAR(2) NOT NULL,
  name_bn VARCHAR(120) NOT NULL,
  name_en VARCHAR(120) NOT NULL,
  flag_image VARCHAR(255) DEFAULT NULL,
  coordinator_name VARCHAR(120) DEFAULT NULL,
  coordinator_contact VARCHAR(190) DEFAULT NULL,
  coordinator_email VARCHAR(190) DEFAULT NULL,
  description TEXT,
  activities TEXT,
  is_hq TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE members (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(40) DEFAULT NULL,
  whatsapp VARCHAR(40) DEFAULT NULL,
  country_id INT UNSIGNED DEFAULT NULL,
  profession VARCHAR(160) DEFAULT NULL,
  bio TEXT,
  photo VARCHAR(255) DEFAULT NULL,
  membership_type ENUM('general','honorary') NOT NULL DEFAULT 'general',
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  password_hash VARCHAR(255) DEFAULT NULL,
  joined_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_members_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE leadership (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  role_bn VARCHAR(160) NOT NULL,
  country_id INT UNSIGNED DEFAULT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  bio TEXT,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_leadership_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE news (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(190) NOT NULL UNIQUE,
  body TEXT NOT NULL,
  cover_image VARCHAR(255) DEFAULT NULL,
  category VARCHAR(80) DEFAULT 'সাধারণ',
  published_at DATETIME DEFAULT NULL,
  created_by INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_news_admin FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  event_date DATETIME NOT NULL,
  location VARCHAR(255) DEFAULT NULL,
  is_online TINYINT(1) NOT NULL DEFAULT 0,
  meeting_link VARCHAR(255) DEFAULT NULL,
  cover_image VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event_rsvps (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id INT UNSIGNED NOT NULL,
  member_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rsvp_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  CONSTRAINT fk_rsvp_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testimonials (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_name VARCHAR(120) NOT NULL,
  country_id INT UNSIGNED DEFAULT NULL,
  story TEXT NOT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_testimonial_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE resources (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  country_id INT UNSIGNED DEFAULT NULL,
  category VARCHAR(80) DEFAULT 'সাধারণ',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_resource_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(190) DEFAULT NULL,
  message TEXT NOT NULL,
  country_id INT UNSIGNED DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_contact_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE volunteer_interest (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) DEFAULT NULL,
  country_id INT UNSIGNED DEFAULT NULL,
  interest_area VARCHAR(120) NOT NULL,
  message TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_volunteer_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE content_blocks (
  block_key VARCHAR(100) PRIMARY KEY,
  title VARCHAR(255) DEFAULT NULL,
  body TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faqs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  token_hash VARCHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pr_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  action VARCHAR(40) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_attempt (ip_address, action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: admin (password = "password")
INSERT INTO admins (name, email, password_hash, role) VALUES
('REFA অ্যাডমিন', 'admin@refa.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

INSERT INTO countries (slug, iso_code, name_bn, name_en, coordinator_name, coordinator_contact, coordinator_email, description, activities, is_hq, is_active, sort_order) VALUES
('finland', 'FI', 'ফিনল্যান্ড', 'Finland', 'নাসির উদ্দিন', '+358 40 000 0000', 'finland@refa.org',
 'REFA-এর জন্ম ফিনল্যান্ডে, ২০২৬ সালে। হেলসিঙ্কি সদর দপ্তর থেকে বিশ্বজুড়ে চ্যাপ্টারগুলোকে সংযুক্ত রাখা হয়। এখানে প্রবাসী কর্মী, পরিবার ও সহযোগীরা একসাথে সিদ্ধান্ত নেন।',
 'মাসিক অনলাইন আড্ডা, নতুন সদস্য অভিবাদন, এবং অন্য দেশের চ্যাপ্টারের সাথে সমন্বয়।', 1, 1, 1),
('spain', 'ES', 'স্পেন', 'Spain', 'রফিকুল ইসলাম', '+34 600 000 000', 'spain@refa.org',
 'স্পেনের বিভিন্ন শহরে থাকা বাংলাদেশি রেমিট্যান্স কর্মীরা REFA স্পেন চ্যাপ্টারের মাধ্যমে একে অপরকে কাজে, ভাষায় ও জরুরি সময়ে সাহায্য করেন।',
 'নতুন আগত কর্মীদের তথ্য সহায়তা, সপ্তাহান্তে কমিউনিটি আড্ডা, অসুস্থ সদস্যের পাশে দাঁড়ানো।', 0, 1, 2),
('saudi-arabia', 'SA', 'সৌদি আরব', 'Saudi Arabia', 'আব্দুল করিম', '+966 50 000 0000', 'ksa@refa.org',
 'সৌদি আরবে থাকা বিপুল সংখ্যক বাংলাদেশি কর্মীর জন্য এই চ্যাপ্টার একটি পরিচিত মুখ ও সহযোগিতার জায়গা — একা নন, পাশে কেউ আছে।',
 'হজ/ওমরাহ সফরে সহায়তা সমন্বয়, জরুরি যোগাযোগ চেইন, শুক্রবার আড্ডা।', 0, 1, 3),
('united-kingdom', 'GB', 'যুক্তরাজ্য', 'United Kingdom', 'শাহানা আক্তার', '+44 7700 000000', 'uk@refa.org',
 'যুক্তরাজ্যের প্রবাসী কর্মী ও পরিবার REFA-এর মাধ্যমে নতুন আগতদের পথ দেখান এবং দেশের সাথে সংযোগ রাখেন।',
 'কমিউনিটি ওরিয়েন্টেশন, আইনি তথ্যের উৎস শেয়ার (পরামর্শ নয়), সাংস্কৃতিক সন্ধ্যা।', 0, 1, 4),
('qatar', 'QA', 'কাতার', 'Qatar', 'মাহবুব রহমান', '+974 3000 0000', 'qatar@refa.org',
 'কাতারে কর্মরত ভাইয়েরা কাজের চাপের মাঝেও একে অপরের খোঁজ রাখেন। চ্যাপ্টারটি ছোট, কিন্তু উষ্ণ।',
 'রমজান ইফতার জমায়েত, নতুন ক্যাম্প আগতদের স্বাগত, জরুরি তহবিল আলোচনা।', 0, 1, 5),
('libya', 'LY', 'লিবিয়া', 'Libya', 'জাহাঙ্গীর আলম', '+218 91 000 0000', 'libya@refa.org',
 'লিবিয়ায় পরিস্থিতি কঠিন হতে পারে। এই চ্যাপ্টার নিরাপত্তা তথ্য ভাগাভাগি ও একে অপরকে খোঁজ রাখার নেটওয়ার্ক হিসেবে কাজ করে।',
 'নিরাপত্তা আপডেট শেয়ার, পরিবারের সাথে যোগাযোগে সহায়তা, জরুরি সমন্বয়।', 0, 1, 6),
('kuwait', 'KW', 'কুয়েত', 'Kuwait', 'নাজমা বেগম', '+965 5000 0000', 'kuwait@refa.org',
 'কুয়েত চ্যাপ্টার গৃহকর্মী থেকে শুরু করে নির্মাণ ও সেবা খাতের কর্মীদের একটি সহজ যোগাযোগের জায়গা।',
 'সাপ্তাহিক হোয়াটসঅ্যাপ আড্ডা, নথিপত্র বিষয়ে অভিজ্ঞতা শেয়ার, অসুস্থ সদস্য দেখা।', 0, 1, 7),
('malaysia', 'MY', 'মালয়েশিয়া', 'Malaysia', 'তানভীর হাসান', '+60 10 000 0000', 'malaysia@refa.org',
 'মালয়েশিয়ার কারখানা ও সেবা খাতের বাংলাদেশি কর্মীরা REFA-এর মাধ্যমে একে অপরকে কাজ ও থাকার খোঁজে সাহায্য করেন।',
 'নতুন আগতদের গাইড, ছুটির দিনে খেলাধুলা, দূতাবাস তথ্য আপডেট।', 0, 1, 8),
('portugal', 'PT', 'পর্তুগাল', 'Portugal', 'ফারহানা ইয়াসমিন', '+351 900 000 000', 'portugal@refa.org',
 'পর্তুগালে নতুন প্রবাসীদের সংখ্যা বাড়ছে। চ্যাপ্টারটি ভাষা, কাগজপত্র ও থাকার বিষয়ে অভিজ্ঞতা ভাগাভাগিতে মনোযোগী।',
 'ভাষা শেখার গ্রুপ, কাগজপত্র অভিজ্ঞতা শেয়ার, পারিবারিক পিকনিক।', 0, 1, 9);

-- Seed members (password = "password" for approved demo member)
INSERT INTO members (name, email, phone, whatsapp, country_id, profession, bio, membership_type, status, password_hash, joined_at) VALUES
('আব্দুল করিম', 'karim@refa.example', '+966 50 111 1111', '+966 50 111 1111', 3, 'নির্মাণ কাজ',
 'রিয়াদে কাজ করি। নতুন ভাইয়েরা এলে থাকার খোঁজে সাহায্য করতে পছন্দ করি।', 'general', 'approved',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('মরিয়ম খাতুন', 'mariam@refa.example', '+34 600 111 111', '+34 600 111 111', 2, 'হোটেল ও রেস্তোরাঁ',
 'বার্সেলোনায় আছি। কাজে নতুনদের সাথে কথা বলি, একা লাগলে পাশে থাকি।', 'general', 'approved', NULL, NOW()),
('রহমত উল্লাহ', 'rahmat@refa.example', '+974 3333 1111', '+974 3333 1111', 5, 'লজিস্টিকস',
 'দোহা থেকে। ছোট ছোট সমস্যায় একে অপরকে ফোন করলেই অনেক কিছু সারে।', 'honorary', 'approved', NULL, NOW()),
('সাকিব হোসেন', 'sakib.pending@refa.example', '+60 11 222 3333', '+60 11 222 3333', 8, 'কারখানা',
 'ক্ল্যাং এলাকায় কাজ করি। নেটওয়ার্কে যুক্ত হতে চাই।', 'general', 'pending', NULL, NULL);

INSERT INTO leadership (name, role_bn, country_id, bio, sort_order, is_active) VALUES
('নাসির উদ্দিন', 'সভাপতি', 1, 'ফিনল্যান্ডে বসবাসকারী প্রবাসী। REFA গড়ে তোলার উদ্যোক্তা — কর্মীদের নিজেদের সংগঠন চান, উপরে থেকে চাপিয়ে দেওয়া নয়।', 1, 1),
('শাহানা আক্তার', 'সাধারণ সম্পাদক', 4, 'যুক্তরাজ্য থেকে সমন্বয় করেন। চ্যাপ্টারগুলোর মধ্যে খবর ও সিদ্ধান্ত পৌঁছে দেওয়াই তাঁর কাজ।', 2, 1),
('আব্দুল করিম', 'কোষাধ্যক্ষ', 3, 'স্বচ্ছ হিসাব রাখা এবং জরুরি সহায়তার ছোট তহবিল দেখাশোনা করেন।', 3, 1),
('রফিকুল ইসলাম', 'সদস্য (ইউরোপ সমন্বয়)', 2, 'স্পেনসহ ইউরোপের চ্যাপ্টারগুলোকে সংযুক্ত রাখেন।', 4, 1),
('নাজমা বেগম', 'সদস্য (উপসাগরীয় সমন্বয়)', 7, 'উপসাগরীয় দেশের কর্মীদের বাস্তব সমস্যা তুলে ধরেন পরিষদে।', 5, 1);

INSERT INTO news (title, slug, body, category, published_at, created_by) VALUES
('ফিনল্যান্ডে REFA-এর যাত্রা শুরু', 'refa-finland-launch-2026',
 '২০২৬ সালে হেলসিঙ্কিতে কয়েকজন প্রবাসী রেমিট্যান্স কর্মী বসেছিলেন একটি সাধারণ প্রশ্ন নিয়ে: দেশে টাকা পাঠাই, কিন্তু বিদেশে আমরা নিজেরাই কি একে অপরের পাশে আছি?\n\nসেই আড্ডা থেকেই রেমিট্যান্স ফাইটার্স অ্যাসোসিয়েশন — REFA। কোনো বড় দাতা সংস্থা নয়, কোনো কর্পোরেট ব্যানার নয়। শুধু কর্মীদের নিজেদের নেটওয়ার্ক।\n\nপ্রথম দিন থেকেই লক্ষ্য স্পষ্ট: চ্যাপ্টার থাকবে দেশে দেশে, সিদ্ধান্ত হবে খোলামেলা, আর কেউ যাতে একা না পড়ে।',
 'সংগঠন', DATE_SUB(NOW(), INTERVAL 12 DAY), 1),
('স্পেন চ্যাপ্টারে সহযোগিতা সভা', 'spain-chapter-meetup',
 'বার্সেলোনায় REFA স্পেন চ্যাপ্টারের সদস্যরা মিলিত হয়েছেন। আলোচনার বিষয় ছিল নতুন আগত কর্মীদের প্রাথমিক তথ্য — থাকার খোঁজ, ভাষা, এবং জরুরি ফোন নম্বর।\n\nসভা শেষে সিদ্ধান্ত: প্রতি মাসে একটি খোলা আড্ডা থাকবে, যাতে কেউ নতুন এলে মুখ চিনতে পারে।\n\nযারা স্পেনের অন্য শহরে আছেন, চ্যাপ্টার পাতায় সমন্বয়কের সাথে যোগাযোগ করতে পারেন।',
 'চ্যাপ্টার', DATE_SUB(NOW(), INTERVAL 5 DAY), 1),
('নিরাপদ রেমিট্যান্স নিয়ে সচেতনতা', 'safe-remittance-awareness',
 'অনেকেই তাড়াহুড়োয় অচেনা চ্যানেলে টাকা পাঠান, পরে বিপদে পড়েন। REFA কোনো ব্যাংক নয় — আমরা শুধু একে অপরকে মনে করাই: চেনা পথ ব্যবহার করুন, রসিদ রাখুন, ব্যক্তিগত তথ্য হোয়াটসঅ্যাপে অচেনা কাউকে দেবেন না।\n\nসহায়িকা পাতায় সাধারণ পরামর্শগুলো জমা দেওয়া হচ্ছে। এটা আইনি পরামর্শ নয়, ভাই-বোনদের অভিজ্ঞতা।',
 'সচেতনতা', DATE_SUB(NOW(), INTERVAL 2 DAY), 1);

INSERT INTO events (title, description, event_date, location, is_online, meeting_link) VALUES
('REFA পরিচিতি — অনলাইন আড্ডা',
 'নতুন-পুরনো সদস্যদের পরিচয়, চ্যাপ্টার আপডেট, এবং কীভাবে একে অপরকে সাহায্য করা যায় সেই খোলা আলোচনা। চা-বিস্কুট নিজের বাসায়, কথাবার্তা সবার সাথে।',
 DATE_ADD(NOW(), INTERVAL 14 DAY), 'জুম / অনলাইন', 1, 'https://example.com/refa-meetup'),
('হেলসিঙ্কি সদস্য মিলন',
 'ফিনল্যান্ড সদর দপ্তরের কাছে মুখোমুখি আড্ডা। পরিবারসহ আসা যায়। এজেন্ডা হালকা — পরিচয়, খাবার, আর আগামী কাজের দু-একটা সিদ্ধান্ত।',
 DATE_ADD(NOW(), INTERVAL 28 DAY), 'হেলসিঙ্কি, ফিনল্যান্ড', 0, NULL),
('রমজান ইফতার (কাতার চ্যাপ্টার)',
 'দোহায় কর্মরত সদস্যদের ইফতার জমায়েত। নতুন কেউ এলে যেন একা না থাকেন — এই সহজ উদ্দেশ্য।',
 DATE_ADD(NOW(), INTERVAL 45 DAY), 'দোহা, কাতার', 0, NULL);

INSERT INTO testimonials (member_name, country_id, story, is_featured) VALUES
('জাহিদ হোসেন', 2,
 'স্পেনে নতুন এসেছিলাম, ভাষা জানতাম না। REFA গ্রুপে এক ভাই রাতে ফোন ধরে থাকার ঠিকানা বলে দিলেন। পরের দিন কাজেও সাথে নিয়ে গেলেন। বড় কিছু না — শুধু পাশে থাকা। তাতেই বাঁচা।', 1),
('সালমা আক্তার', 7,
 'কুয়েতে অসুস্থ হয়ে পড়লে সমন্বয়ক নাজমা আপা হাসপাতালের পথ বলে দিলেন, আরেকজন এসে সাথে থাকলেন। পরিবার দেশে — এখানে যে কেউ আছে, সে অনুভূতিটাই বড়।', 1),
('মোস্তাফিজুর রহমান', 8,
 'মালয়েশিয়ায় কাজ বদলের সময় কাগজপত্র নিয়ে গুলিয়ে গিয়েছিলাম। চ্যাপ্টারের তানভীর ভাই নিজের অভিজ্ঞতা শেয়ার করলেন। ভুল পথে হাঁটা থেকে বেঁচেছি।', 1);

INSERT INTO resources (title, body, country_id, category) VALUES
('নিরাপদ রেমিট্যান্স — সাধারণ পরামর্শ',
 '১) চেনা ব্যাংক, এক্সচেঞ্জ হাউস বা বৈধ মোবাইল ব্যাংকিং ব্যবহার করুন।\n২) রসিদ ও ট্রান্সঅ্যাকশন আইডি সংরক্ষণ করুন।\n৩) অচেনা ব্যক্তির অ্যাকাউন্টে পাঠাতে বললে সন্দেহ করুন।\n৪) পাসপোর্ট, পিন বা OTP হোয়াটসঅ্যাপে পাঠাবেন না।\n\nREFA কোনো আর্থিক প্রতিষ্ঠান নয়। এটি সহকর্মীদের অভিজ্ঞতাভিত্তিক সতর্কতা।',
 NULL, 'রেমিট্যান্স'),
('জরুরি যোগাযোগ — দূতাবাস ও হেল্পলাইন',
 'বিপদে প্রথমে স্থানীয় জরুরি নম্বর (পুলিশ/অ্যাম্বুলেন্স), তারপর নিজ দেশের দূতাবাস/হাইকমিশন।\n\nফিনল্যান্ড (সদর): স্থানীয় ১১২।\nস্পেন: ১১২।\nসৌদি আরব: ৯৯৯ / ৯১১।\nযুক্তরাজ্য: ৯৯৯।\nকাতার: ৯৯৯।\n\nদূতাবাসের নম্বর পরিবর্তন হতে পারে — যাওয়ার আগে অফিসিয়াল ওয়েবসাইট দেখুন। চ্যাপ্টার সমন্বয়ক হালনাগাদ জানতে সাহায্য করতে পারেন।',
 NULL, 'জরুরি'),
('কর্মী অধিকার — সাধারণ কথা',
 'প্রতি দেশের শ্রম আইন আলাদা। সাধারণ নীতি: চুক্তি পড়ে সই করুন, পাসপোর্ট নিজের কাছে রাখার চেষ্টা করুন, বেতনের রেকর্ড রাখুন।\n\nএটি আইনি পরামর্শ নয়। সমস্যা হলে দূতাবাস, স্থানীয় শ্রম অফিস বা বিশ্বস্ত আইন সহায়তা কেন্দ্রে যান। REFA অভিজ্ঞতা ভাগাভাগি করে, মামলা লড়ে না।',
 NULL, 'অধিকার'),
('সৌদি আরব — প্রাথমিক তথ্য',
 'নতুন আগতরা ইকামা, কাজের ক্যাম্প নিয়ম এবং বেতন চক্র সম্পর্কে অভিজ্ঞ সদস্যদের সাথে কথা বলতে পারেন। আনুষ্ঠানিক তথ্যের জন্য সৌদি সরকারি পোর্টাল ও বাংলাদেশ দূতাবাস রিয়াদ দেখুন।',
 3, 'দেশভিত্তিক');

INSERT INTO faqs (question, answer, sort_order, is_active) VALUES
('REFA কি রাজনৈতিক সংগঠন?',
 'না। REFA অরাজনৈতিক, অলাভজনক কমিউনিটি নেটওয়ার্ক। লক্ষ্য প্রবাসী রেমিট্যান্স কর্মীদের পারস্পরিক সহযোগিতা।', 1, 1),
('সদস্য হতে কি ফি লাগে?',
 'না, সাধারণ সদস্যপদ ফি-ভিত্তিক নয়। এটা সহযোগিতার নেটওয়ার্ক। স্বেচ্ছায় অনুদান আলাদা — বাধ্যতামূলক নয়।', 2, 1),
('আমার দেশে চ্যাপ্টার নেই, কী করব?',
 'তবুও সদস্য আবেদন করতে পারেন। স্বেচ্ছাসেবক ফর্মে কান্ট্রি সমন্বয়ক হতে আগ্রহ জানালে আমরা যোগাযোগ করব।', 3, 1),
('আমার তথ্য কি গোপন থাকবে?',
 'সদস্য তথ্য অ্যাডমিন প্যানেলে থাকে, প্রকাশ্যে ফোন নম্বর দেখানো হয় না। সমন্বয়কের যোগাযোগ চ্যাপ্টার পাতায় থাকে তাঁর সম্মতিতে।', 4, 1);

INSERT INTO content_blocks (block_key, title, body) VALUES
('home_who', 'আমরা কারা?',
 'রেমিট্যান্স ফাইটার্স অ্যাসোসিয়েশন (REFA) ফিনল্যান্ডে ২০২৬ সালে গড়া একটি অলাভজনক কমিউনিটি সংগঠন। এটা প্রবাসী বাংলাদেশি রেমিট্যান্স কর্মীদের নিজেদের জায়গা — একে অপরকে খোঁজ রাখা, কাজে সাহায্য করা, বিপদে পাশে দাঁড়ানো।\n\nআমরা প্রতিষ্ঠান হিসেবে মানুষকে “উপকার” করি না; আমরা সহকর্মী হিসেবে একে অপরের সাথে থাকি। স্পেন থেকে সৌদি আরব, যুক্তরাজ্য থেকে মালয়েশিয়া — যেখানেই থাকুন, আপনি একা নন।'),
('about_history', 'ইতিহাস',
 '২০২৬ সালে ফিনল্যান্ডের হেলসিঙ্কিতে একদল প্রবাসী বসে ভাবলেন: রেমিট্যান্স দেশের অর্থনীতির রক্ত, কিন্তু যে মানুষগুলো সেই টাকা পাঠান তাঁরা বিদেশে প্রায়ই একা, তথ্যহীন, সাহায্যহীন।\n\nREFA সেই ফাঁক ভরাবার চেষ্টা। কোনো বড় অনুদানের অপেক্ষা না করেই চ্যাপ্টার খোলা শুরু — যেখানে কর্মী আছেন, সেখানেই নেটওয়ার্ক। আজ ফিনল্যান্ড সদর দপ্তর; কাল নতুন দেশ যোগ হবে, কারণ তালিকা ডাটাবেজে — পাথরে খোদাই নয়।'),
('about_mission', 'লক্ষ্য',
 'প্রবাসী বাংলাদেশি রেমিট্যান্স কর্মীদের মধ্যে আস্থা, সহযোগিতা ও ব্যবহারিক সহায়তার একটি বৈশ্বিক নেটওয়ার্ক গড়ে তোলা — যাতে কেউ চাকরি, থাকার জায়গা, অসুস্থতা বা নিরাপত্তার সংকটে একা না পড়েন।'),
('about_vision', 'দৃষ্টিভঙ্গি',
 'এমন একটি কমিউনিটি যেখানে দেশে দেশে প্রবাসীরা একে অপরকে চেনেন, বিশ্বাস করেন এবং সাহায্য করেন — কর্পোরেট ব্যানার ছাড়া, রাজনৈতিক ব্যানার ছাড়া, শুধু মানুষ হিসেবে।'),
('donate_info', 'অনুদান তথ্য',
 'REFA-এর অনুদান স্বেচ্ছায়। টাকা যায় জরুরি সহায়তা, ছোট জমায়েতের খরচ এবং যোগাযোগ খরচে। বাধ্যতামূলক চাঁদা নেই।\n\nব্যাংক: (আপডেট করা হবে)\nহিসাবের নাম: Remittance Fighters Association\nIBAN / হিসাব নম্বর: অ্যাডমিন প্যানেল থেকে যোগ করুন\n\nগেটওয়ে পরে যোগ করা যাবে। এখন অনুদান দিতে যোগাযোগ ফর্ম ব্যবহার করুন, বিষয় লিখুন “অনুদান”।');

INSERT INTO settings (setting_key, setting_value) VALUES
('hq_name', 'রেমিট্যান্স ফাইটার্স অ্যাসোসিয়েশন (REFA)'),
('hq_address', 'হেলসিঙ্কি, ফিনল্যান্ড'),
('hq_email', 'info@refa.org'),
('hq_phone', '+358 40 000 0000'),
('admin_email', 'admin@refa.org'),
('map_embed', 'https://maps.google.com/maps?q=Helsinki%2C%20Finland&t=&z=6&ie=UTF8&iwloc=&output=embed'),
('facebook_url', 'https://facebook.com'),
('whatsapp_url', 'https://wa.me/358400000000'),
('donate_enabled', '1'),
('membership_paid_tiers', '0');
