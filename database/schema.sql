-- CAAP database schema — clean install for cPanel (no test data)
-- Import into your EMPTY cPanel database via phpMyAdmin (select the database first, then Import).
-- No CREATE DATABASE / USE lines on purpose: shared hosting creates the database for you.

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    category ENUM('blog', 'impact_story') NOT NULL DEFAULT 'blog',
    body TEXT NOT NULL,
    cover_image VARCHAR(255),
    author_id INT,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE grants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    funder VARCHAR(150) NOT NULL,
    scope ENUM('local', 'international') NOT NULL,
    amount_range VARCHAR(100),
    deadline DATE NOT NULL,
    requirements TEXT,
    source_url VARCHAR(255),
    status ENUM('identified', 'in_progress', 'submitted', 'awarded', 'rejected', 'closed')
        NOT NULL DEFAULT 'identified',
    added_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    type ENUM('individual', 'foundation', 'corporate', 'government', 'partner_org') NOT NULL,
    contact_person VARCHAR(120),
    email VARCHAR(150),
    phone VARCHAR(30),
    country VARCHAR(80),
    relationship_status ENUM('prospect', 'engaged', 'active', 'lapsed') NOT NULL DEFAULT 'prospect',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE proposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grant_id INT,
    donor_id INT,
    title VARCHAR(200) NOT NULL,
    amount_requested DECIMAL(12,2),
    status ENUM('concept_note', 'drafting', 'submitted', 'awarded', 'rejected')
        NOT NULL DEFAULT 'concept_note',
    submitted_at DATE,
    follow_up_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grant_id) REFERENCES grants(id) ON DELETE SET NULL,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE meetups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    meetup_date DATE NOT NULL,
    location VARCHAR(200),
    is_virtual BOOLEAN NOT NULL DEFAULT FALSE,
    partner_group VARCHAR(150),
    coordinator_name VARCHAR(120),
    coordinator_contact VARCHAR(120),
    description TEXT,
    status ENUM('planned', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'planned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_grants_deadline ON grants (deadline);
CREATE INDEX idx_grants_status ON grants (status);
CREATE INDEX idx_posts_status_published ON posts (status, published_at);
CREATE INDEX idx_proposals_status ON proposals (status);
