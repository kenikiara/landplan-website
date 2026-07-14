-- ============================================================
--  LANDPLAN.CO.KE  —  Database schema (MySQL 5.7+ / MariaDB 10.2+)
--  Import this in cPanel → phpMyAdmin AFTER creating the database.
--  Then import seed.sql for the default admin + starter content.
--  Charset utf8mb4 for full Unicode (names, symbols, KSh, etc.)
-- ============================================================
SET NAMES utf8mb4;
SET time_zone = '+03:00';   -- East Africa Time

-- ---------- Admin / staff logins ----------
CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,          -- password_hash(), never plain text
  role          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  last_login    DATETIME NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Global site settings (phone, email, socials, SEO, hero) ----------
CREATE TABLE settings (
  setting_key   VARCHAR(80) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Editable content pages (About, guides, Terms, Privacy, etc.) ----------
CREATE TABLE pages (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  slug             VARCHAR(160) NOT NULL UNIQUE,
  title            VARCHAR(200) NOT NULL,
  body             MEDIUMTEXT,
  meta_title       VARCHAR(200),
  meta_description VARCHAR(320),
  status           ENUM('published','draft') NOT NULL DEFAULT 'published',
  updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Land for sale ----------
CREATE TABLE land_listings (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  title            VARCHAR(200) NOT NULL,
  slug             VARCHAR(200) NOT NULL UNIQUE,
  location         VARCHAR(160) NOT NULL,        -- e.g. "Kitengela, Kajiado"
  size             VARCHAR(80),                  -- e.g. "1/8 Acre Plot"
  price            DECIMAL(14,2),                -- KSh
  category         ENUM('Residential','Commercial','Agricultural','Mixed Use') NOT NULL DEFAULT 'Residential',
  title_status     VARCHAR(120) DEFAULT 'Ready Title Deed',
  description      MEDIUMTEXT,
  features         TEXT,                          -- one feature per line
  map_embed        TEXT,                          -- optional Google Maps iframe/URL
  cover_image      VARCHAR(255),
  featured         TINYINT(1) NOT NULL DEFAULT 0,
  status           ENUM('published','draft','sold') NOT NULL DEFAULT 'published',
  meta_title       VARCHAR(200),
  meta_description VARCHAR(320),
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (status), INDEX (category), INDEX (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE land_images (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NOT NULL,
  path       VARCHAR(255) NOT NULL,
  sort       INT NOT NULL DEFAULT 0,
  FOREIGN KEY (listing_id) REFERENCES land_listings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Houses for sale ----------
CREATE TABLE houses (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  title            VARCHAR(200) NOT NULL,
  slug             VARCHAR(200) NOT NULL UNIQUE,
  location         VARCHAR(160) NOT NULL,
  bedrooms         INT,
  bathrooms        INT,
  size             VARCHAR(80),                   -- plinth / plot size
  price            DECIMAL(14,2),
  description      MEDIUMTEXT,
  features         TEXT,
  cover_image      VARCHAR(255),
  featured         TINYINT(1) NOT NULL DEFAULT 0,
  status           ENUM('published','draft','sold') NOT NULL DEFAULT 'published',
  meta_title       VARCHAR(200),
  meta_description VARCHAR(320),
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (status), INDEX (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE house_images (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  house_id  INT NOT NULL,
  path      VARCHAR(255) NOT NULL,
  sort      INT NOT NULL DEFAULT 0,
  FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Projects ----------
CREATE TABLE projects (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  title            VARCHAR(200) NOT NULL,
  slug             VARCHAR(200) NOT NULL UNIQUE,
  location         VARCHAR(160),
  description      MEDIUMTEXT,
  cover_image      VARCHAR(255),
  status           ENUM('ongoing','completed') NOT NULL DEFAULT 'completed',
  featured         TINYINT(1) NOT NULL DEFAULT 0,
  meta_title       VARCHAR(200),
  meta_description VARCHAR(320),
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE project_images (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  project_id  INT NOT NULL,
  path        VARCHAR(255) NOT NULL,
  sort        INT NOT NULL DEFAULT 0,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Services (What We Do) ----------
CREATE TABLE services (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(160) NOT NULL,
  slug        VARCHAR(160) NOT NULL UNIQUE,
  icon        VARCHAR(80),                       -- icon key
  excerpt     VARCHAR(320),
  body        MEDIUMTEXT,
  cover_image VARCHAR(255),
  sort        INT NOT NULL DEFAULT 0,
  status      ENUM('published','draft') NOT NULL DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Blog / articles (SEO) ----------
CREATE TABLE articles (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  title            VARCHAR(220) NOT NULL,
  slug             VARCHAR(220) NOT NULL UNIQUE,
  excerpt          VARCHAR(400),
  body             MEDIUMTEXT,
  cover_image      VARCHAR(255),
  category         VARCHAR(120),
  author_id        INT,
  status           ENUM('published','draft') NOT NULL DEFAULT 'draft',
  meta_title       VARCHAR(200),
  meta_description VARCHAR(320),
  published_at     DATETIME,
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (status), INDEX (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- FAQs ----------
CREATE TABLE faqs (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(300) NOT NULL,
  answer   TEXT NOT NULL,
  category VARCHAR(120) DEFAULT 'General',
  sort     INT NOT NULL DEFAULT 0,
  status   ENUM('published','draft') NOT NULL DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Testimonials ----------
CREATE TABLE testimonials (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  name     VARCHAR(120) NOT NULL,
  location VARCHAR(120),
  quote    TEXT NOT NULL,
  rating   TINYINT NOT NULL DEFAULT 5,
  photo    VARCHAR(255),
  sort     INT NOT NULL DEFAULT 0,
  status   ENUM('published','draft') NOT NULL DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Clients (portal accounts + CRM records) ----------
CREATE TABLE clients (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(160) NOT NULL,
  email         VARCHAR(160) UNIQUE,
  phone         VARCHAR(40),
  password_hash VARCHAR(255) NULL,               -- NULL = CRM record only (no portal login yet)
  notes         TEXT,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Leads / enquiries (from site forms) ----------
CREATE TABLE leads (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(160) NOT NULL,
  email      VARCHAR(160),
  phone      VARCHAR(40),
  interest   VARCHAR(120),                        -- "Buying Land", "Building", etc.
  message    TEXT,
  source     VARCHAR(120),                        -- which page/form
  item_type  VARCHAR(20) NULL,                    -- 'land' | 'house' | 'project'
  item_id    INT NULL,                            -- optional: enquiry on a specific item
  client_id  INT NULL,                            -- set when converted to a client
  assigned_to INT NULL,                           -- staff user handling it
  status     ENUM('new','contacted','qualified','won','lost') NOT NULL DEFAULT 'new',
  admin_notes TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id)  REFERENCES clients(id) ON DELETE SET NULL,
  FOREIGN KEY (assigned_to) REFERENCES users(id)  ON DELETE SET NULL,
  INDEX (status), INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Saved / wishlist (client portal) ----------
CREATE TABLE saved_listings (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  client_id  INT NOT NULL,
  item_type  ENUM('land','house','project') NOT NULL,
  item_id    INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_save (client_id, item_type, item_id),
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Documents shared with a client (title deeds, agreements) ----------
CREATE TABLE client_documents (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  client_id   INT NOT NULL,
  title       VARCHAR(200) NOT NULL,
  path        VARCHAR(255) NOT NULL,
  uploaded_by INT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id)   REFERENCES clients(id) ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by) REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Appointments / site visits ----------
CREATE TABLE appointments (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  client_id   INT NULL,
  lead_id     INT NULL,
  name        VARCHAR(160) NOT NULL,
  phone       VARCHAR(40),
  when_at     DATETIME NOT NULL,
  location    VARCHAR(200),
  notes       TEXT,
  status      ENUM('scheduled','done','cancelled') NOT NULL DEFAULT 'scheduled',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
  FOREIGN KEY (lead_id)   REFERENCES leads(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Activity log (audit trail) ----------
CREATE TABLE activity_log (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NULL,
  action     VARCHAR(80) NOT NULL,               -- 'login','create','update','delete', etc.
  entity     VARCHAR(60),
  entity_id  INT NULL,
  detail     VARCHAR(255),
  ip         VARCHAR(45),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
