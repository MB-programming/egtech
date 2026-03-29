<?php
/**
 * DGTEC Admin — MySQL database helper
 */

function dgtec_db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;

    $dsn = 'mysql:host=localhost;dbname=u186120816_egtech;charset=utf8mb4';
    $pdo = new PDO($dsn, 'u186120816_egtech', 'Mina&Egtech2030', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    dgtec_db_init($pdo);
    return $pdo;
}

function dgtec_db_init(PDO $pdo): void {
    /* ---- hero_slides ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `hero_slides` (
            `id`                INT AUTO_INCREMENT PRIMARY KEY,
            `position`          INT NOT NULL DEFAULT 0,
            `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
            `label`             VARCHAR(255) NOT NULL DEFAULT '',
            `title`             TEXT NOT NULL,
            `highlight_text`    VARCHAR(255) NOT NULL DEFAULT '',
            `highlight_color`   VARCHAR(7) NOT NULL DEFAULT '',
            `description`       TEXT NOT NULL,
            `bg_image`          VARCHAR(500) NOT NULL DEFAULT '',
            `gradient_color1`   VARCHAR(7) NOT NULL DEFAULT '#183f96',
            `gradient_opacity1` DECIMAL(3,2) NOT NULL DEFAULT 0.84,
            `gradient_color2`   VARCHAR(7) NOT NULL DEFAULT '#183f96',
            `gradient_opacity2` DECIMAL(3,2) NOT NULL DEFAULT 0.45,
            `btn1_text`         VARCHAR(100) NOT NULL DEFAULT '',
            `btn1_url`          VARCHAR(500) NOT NULL DEFAULT '',
            `btn2_text`         VARCHAR(100) NOT NULL DEFAULT '',
            `btn2_url`          VARCHAR(500) NOT NULL DEFAULT '',
            `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- admin_users ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admin_users` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `username`      VARCHAR(50) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `display_name`  VARCHAR(100) NOT NULL DEFAULT '',
            `last_login`    DATETIME NULL,
            `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- contacts: add is_read if missing ---- */
    $cols = $pdo->query("SHOW COLUMNS FROM `contacts` LIKE 'is_read'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE `contacts` ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0");
    }

    /* ---- services ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `services` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `position`    INT NOT NULL DEFAULT 0,
            `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
            `title`       VARCHAR(255) NOT NULL DEFAULT '',
            `slug`        VARCHAR(255) NOT NULL DEFAULT '',
            `icon`        VARCHAR(100) NOT NULL DEFAULT '',
            `image`       VARCHAR(500) NOT NULL DEFAULT '',
            `description` TEXT NOT NULL,
            `features`    TEXT NOT NULL DEFAULT '',
            `page_url`    VARCHAR(500) NOT NULL DEFAULT '',
            `is_reversed` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- solutions ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `solutions` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `position`    INT NOT NULL DEFAULT 0,
            `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
            `title`       VARCHAR(255) NOT NULL DEFAULT '',
            `slug`        VARCHAR(255) NOT NULL DEFAULT '',
            `icon`        VARCHAR(100) NOT NULL DEFAULT '',
            `image`       VARCHAR(500) NOT NULL DEFAULT '',
            `description` TEXT NOT NULL,
            `features`    TEXT NOT NULL DEFAULT '',
            `page_url`    VARCHAR(500) NOT NULL DEFAULT '',
            `is_reversed` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- Seed admin user ---- */
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `admin_users` WHERE `username` = ?");
    $stmt->execute(['minaboules']);
    if ((int)$stmt->fetchColumn() === 0) {
        $hash = password_hash('Mina&egy2030#', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO `admin_users` (username, password_hash, display_name) VALUES (?, ?, ?)")
            ->execute(['minaboules', $hash, 'Mina Boules']);
    }

    /* ---- Seed default slides if table is empty ---- */
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `hero_slides`")->fetchColumn();
    if ($count === 0) {
        dgtec_seed_slides($pdo);
    }

    /* ---- Seed default services ---- */
    $svcCount = (int)$pdo->query("SELECT COUNT(*) FROM `services`")->fetchColumn();
    if ($svcCount === 0) {
        dgtec_seed_services($pdo);
    }

    /* ---- Seed default solutions ---- */
    $solCount = (int)$pdo->query("SELECT COUNT(*) FROM `solutions`")->fetchColumn();
    if ($solCount === 0) {
        dgtec_seed_solutions($pdo);
    }

    /* ---- partners ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `partners` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `position`    INT NOT NULL DEFAULT 0,
            `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
            `name`        VARCHAR(255) NOT NULL DEFAULT '',
            `logo`        VARCHAR(500) NOT NULL DEFAULT '',
            `website_url` VARCHAR(500) NOT NULL DEFAULT '',
            `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- client_reviews ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `client_reviews` (
            `id`        INT AUTO_INCREMENT PRIMARY KEY,
            `position`  INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `name`      VARCHAR(255) NOT NULL DEFAULT '',
            `job_title` VARCHAR(255) NOT NULL DEFAULT '',
            `review`    TEXT NOT NULL,
            `stars`     TINYINT(1) NOT NULL DEFAULT 5,
            `image`     VARCHAR(500) NOT NULL DEFAULT '',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- site_info ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `site_info` (
            `id`                INT AUTO_INCREMENT PRIMARY KEY,
            `phone`             VARCHAR(100) NOT NULL DEFAULT '',
            `email`             VARCHAR(150) NOT NULL DEFAULT '',
            `address`           TEXT NOT NULL,
            `footer_description` TEXT NOT NULL,
            `site_description`  TEXT NOT NULL,
            `header_logo`       VARCHAR(500) NOT NULL DEFAULT '',
            `footer_logo`       VARCHAR(500) NOT NULL DEFAULT '',
            `updated_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- blog_posts ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `blog_posts` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `position`     INT NOT NULL DEFAULT 0,
            `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
            `title`        VARCHAR(255) NOT NULL DEFAULT '',
            `slug`         VARCHAR(255) NOT NULL DEFAULT '',
            `category`     VARCHAR(100) NOT NULL DEFAULT '',
            `excerpt`      TEXT NOT NULL,
            `content`      LONGTEXT NOT NULL,
            `image`        VARCHAR(500) NOT NULL DEFAULT '',
            `published_at` DATE NULL,
            `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- Seed partners ---- */
    $partnerCount = (int)$pdo->query("SELECT COUNT(*) FROM `partners`")->fetchColumn();
    if ($partnerCount === 0) {
        dgtec_seed_partners($pdo);
    }

    /* ---- Seed client reviews ---- */
    $reviewCount = (int)$pdo->query("SELECT COUNT(*) FROM `client_reviews`")->fetchColumn();
    if ($reviewCount === 0) {
        dgtec_seed_reviews($pdo);
    }

    /* ---- Seed site info ---- */
    $infoCount = (int)$pdo->query("SELECT COUNT(*) FROM `site_info`")->fetchColumn();
    if ($infoCount === 0) {
        dgtec_seed_site_info($pdo);
    }

    /* ---- Seed blog posts ---- */
    $blogCount = (int)$pdo->query("SELECT COUNT(*) FROM `blog_posts`")->fetchColumn();
    if ($blogCount === 0) {
        dgtec_seed_blogs($pdo);
    }

    /* ---- site_info column migrations ---- */
    $siCols = array_column($pdo->query("SHOW COLUMNS FROM `site_info`")->fetchAll(), 'Field');
    $siAdd  = [
        'google_analytics'       => "VARCHAR(100) NOT NULL DEFAULT ''",
        'favicon'                => "VARCHAR(500) NOT NULL DEFAULT ''",
        'global_head_code'       => "TEXT NOT NULL",
        'global_body_code'       => "TEXT NOT NULL",
        'header_nav_json'        => "LONGTEXT NOT NULL",
        'footer_nav_json'        => "LONGTEXT NOT NULL",
        'home_content_json'      => "LONGTEXT NOT NULL",
        'home_process_json'      => "LONGTEXT NOT NULL",
        'home_achievements_json' => "LONGTEXT NOT NULL",
        'about_content_json'     => "LONGTEXT NOT NULL",
    ];
    foreach ($siAdd as $col => $def) {
        if (!in_array($col, $siCols, true)) {
            $pdo->exec("ALTER TABLE `site_info` ADD COLUMN `$col` $def");
        }
    }

    /* ---- services / solutions: add page_content if missing ---- */
    foreach (['services', 'solutions'] as $_tbl) {
        $cols = array_column($pdo->query("SHOW COLUMNS FROM `$_tbl`")->fetchAll(), 'Field');
        if (!in_array('page_content', $cols, true)) {
            $pdo->exec("ALTER TABLE `$_tbl` ADD COLUMN `page_content` LONGTEXT NOT NULL");
        }
    }

    /* ---- blog_posts: add tags if missing ---- */
    $bpCols = array_column($pdo->query("SHOW COLUMNS FROM `blog_posts`")->fetchAll(), 'Field');
    if (!in_array('tags', $bpCols, true)) {
        $pdo->exec("ALTER TABLE `blog_posts` ADD COLUMN `tags` TEXT NOT NULL");
    }

    /* ---- social_links ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `social_links` (
            `id`        INT AUTO_INCREMENT PRIMARY KEY,
            `position`  INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `platform`  VARCHAR(50)  NOT NULL DEFAULT '',
            `icon`      VARCHAR(100) NOT NULL DEFAULT '',
            `url`       VARCHAR(500) NOT NULL DEFAULT '',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- pages ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `pages` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `position`   INT NOT NULL DEFAULT 0,
            `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
            `title`      VARCHAR(255) NOT NULL DEFAULT '',
            `slug`       VARCHAR(255) NOT NULL DEFAULT '',
            `content`    LONGTEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- Seed social links ---- */
    $slCount = (int)$pdo->query("SELECT COUNT(*) FROM `social_links`")->fetchColumn();
    if ($slCount === 0) {
        $stmt = $pdo->prepare("INSERT INTO `social_links` (position,platform,icon,url) VALUES (?,?,?,?)");
        $stmt->execute([1,'LinkedIn',  'fab fa-linkedin-in', '#']);
        $stmt->execute([2,'Twitter/X', 'fab fa-x-twitter',  '#']);
        $stmt->execute([3,'Facebook',  'fab fa-facebook-f', '#']);
        $stmt->execute([4,'Instagram', 'fab fa-instagram',  '#']);
    }

    /* ---- admin_roles ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admin_roles` (
            `id`               INT AUTO_INCREMENT PRIMARY KEY,
            `name`             VARCHAR(50)  NOT NULL UNIQUE DEFAULT '',
            `display_name`     VARCHAR(100) NOT NULL DEFAULT '',
            `permissions_json` TEXT NOT NULL,
            `is_system`        TINYINT(1) NOT NULL DEFAULT 0,
            `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- admin_users: add role_id + is_active if missing ---- */
    $auCols = array_column($pdo->query("SHOW COLUMNS FROM `admin_users`")->fetchAll(), 'Field');
    if (!in_array('role_id', $auCols))   $pdo->exec("ALTER TABLE `admin_users` ADD COLUMN `role_id` INT NULL DEFAULT NULL");
    if (!in_array('is_active', $auCols)) $pdo->exec("ALTER TABLE `admin_users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1");

    /* ---- Seed system roles ---- */
    $roleCount = (int)$pdo->query("SELECT COUNT(*) FROM `admin_roles`")->fetchColumn();
    if ($roleCount === 0) {
        $allPerms = json_encode(array_keys([
            'slides'=>1,'services'=>1,'solutions'=>1,'partners'=>1,'blog'=>1,'pages'=>1,
            'careers'=>1,'career_applications'=>1,'social_media'=>1,'submissions'=>1,
            'site_info'=>1,'home_content'=>1,'about_content'=>1,'seo'=>1,'nav_menus'=>1,'users'=>1,
        ]));
        $rs = $pdo->prepare("INSERT INTO `admin_roles` (`name`,`display_name`,`permissions_json`,`is_system`) VALUES (?,?,?,1)");
        $rs->execute(['admin',      'Administrator', $allPerms]);
        $rs->execute(['seo',        'SEO Manager',   json_encode(['seo'])]);
        $rs->execute(['data_entry', 'Data Entry',    json_encode(['slides','services','solutions','partners','blog','pages','social_media'])]);
        $rs->execute(['hr',         'HR Manager',    json_encode(['careers','career_applications'])]);

        /* Assign existing users to admin role */
        $adminRoleId = (int)$pdo->query("SELECT id FROM admin_roles WHERE name='admin' LIMIT 1")->fetchColumn();
        if ($adminRoleId) {
            $pdo->prepare("UPDATE `admin_users` SET `role_id`=? WHERE `role_id` IS NULL")
                ->execute([$adminRoleId]);
        }
    }

    /* ---- seo_pages ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `seo_pages` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `page_key`     VARCHAR(150) NOT NULL UNIQUE,
            `meta_title`   VARCHAR(255) NOT NULL DEFAULT '',
            `meta_desc`    VARCHAR(500) NOT NULL DEFAULT '',
            `og_title`     VARCHAR(255) NOT NULL DEFAULT '',
            `og_desc`      VARCHAR(500) NOT NULL DEFAULT '',
            `og_image`     VARCHAR(500) NOT NULL DEFAULT '',
            `canonical`    VARCHAR(500) NOT NULL DEFAULT '',
            `robots`       VARCHAR(100) NOT NULL DEFAULT 'index, follow',
            `schema_json`  TEXT NOT NULL,
            `head_code`    TEXT NOT NULL,
            `body_code`    TEXT NOT NULL,
            `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- careers ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `careers` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `position`     INT NOT NULL DEFAULT 0,
            `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
            `title`        VARCHAR(255) NOT NULL DEFAULT '',
            `slug`         VARCHAR(255) NOT NULL UNIQUE DEFAULT '',
            `department`   VARCHAR(100) NOT NULL DEFAULT '',
            `location`     VARCHAR(100) NOT NULL DEFAULT '',
            `job_type`     VARCHAR(50)  NOT NULL DEFAULT 'Full-time',
            `description`  LONGTEXT NOT NULL,
            `requirements` LONGTEXT NOT NULL,
            `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- career_form_fields ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `career_form_fields` (
            `id`           INT AUTO_INCREMENT PRIMARY KEY,
            `career_id`    INT NOT NULL,
            `field_key`    VARCHAR(100) NOT NULL DEFAULT '',
            `field_type`   VARCHAR(50)  NOT NULL DEFAULT 'text',
            `label`        VARCHAR(255) NOT NULL DEFAULT '',
            `placeholder`  VARCHAR(255) NOT NULL DEFAULT '',
            `required`     TINYINT(1)   NOT NULL DEFAULT 0,
            `options_json` TEXT NOT NULL,
            `sort_order`   INT NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    /* ---- career_applications ---- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `career_applications` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `career_id`  INT NOT NULL,
            `data_json`  LONGTEXT NOT NULL,
            `files_json` LONGTEXT NOT NULL,
            `status`     VARCHAR(50) NOT NULL DEFAULT 'new',
            `ip`         VARCHAR(45) NOT NULL DEFAULT '',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

/* ============================================================
   CAREERS
   ============================================================ */
function dgtec_careers_all(): array {
    return dgtec_db()->query("SELECT * FROM `careers` ORDER BY `position` ASC, `id` DESC")->fetchAll();
}

function dgtec_careers_active(): array {
    return dgtec_db()->query("SELECT * FROM `careers` WHERE `is_active`=1 ORDER BY `position` ASC, `id` DESC")->fetchAll();
}

function dgtec_career_get(int $id): ?array {
    $r = dgtec_db()->prepare("SELECT * FROM `careers` WHERE `id`=?");
    $r->execute([$id]);
    return $r->fetch() ?: null;
}

function dgtec_career_get_by_slug(string $slug): ?array {
    $r = dgtec_db()->prepare("SELECT * FROM `careers` WHERE `slug`=? AND `is_active`=1");
    $r->execute([$slug]);
    return $r->fetch() ?: null;
}

function dgtec_career_save(array $d): int {
    $db = dgtec_db();
    if (!empty($d['id'])) {
        $db->prepare("UPDATE `careers` SET `title`=?,`slug`=?,`department`=?,`location`=?,`job_type`=?,
            `description`=?,`requirements`=?,`is_active`=?,`position`=? WHERE `id`=?")
           ->execute([$d['title'],$d['slug'],$d['department'],$d['location'],$d['job_type'],
                      $d['description'],$d['requirements'],$d['is_active'],$d['position'],$d['id']]);
        return (int)$d['id'];
    }
    $db->prepare("INSERT INTO `careers` (`title`,`slug`,`department`,`location`,`job_type`,`description`,`requirements`,`is_active`,`position`)
        VALUES (?,?,?,?,?,?,?,?,?)")
       ->execute([$d['title'],$d['slug'],$d['department'],$d['location'],$d['job_type'],
                  $d['description'],$d['requirements'],$d['is_active'],$d['position']]);
    return (int)$db->lastInsertId();
}

function dgtec_career_delete(int $id): void {
    $db = dgtec_db();
    $db->prepare("DELETE FROM `career_form_fields` WHERE `career_id`=?")->execute([$id]);
    $db->prepare("DELETE FROM `career_applications`  WHERE `career_id`=?")->execute([$id]);
    $db->prepare("DELETE FROM `careers` WHERE `id`=?")->execute([$id]);
}

/* ---- Form fields ---- */
function dgtec_career_fields(int $career_id): array {
    $r = dgtec_db()->prepare("SELECT * FROM `career_form_fields` WHERE `career_id`=? ORDER BY `sort_order` ASC, `id` ASC");
    $r->execute([$career_id]);
    return $r->fetchAll();
}

function dgtec_career_fields_save(int $career_id, array $fields): void {
    $db = dgtec_db();
    $db->prepare("DELETE FROM `career_form_fields` WHERE `career_id`=?")->execute([$career_id]);
    $stmt = $db->prepare("INSERT INTO `career_form_fields`
        (`career_id`,`field_key`,`field_type`,`label`,`placeholder`,`required`,`options_json`,`sort_order`)
        VALUES (?,?,?,?,?,?,?,?)");
    foreach ($fields as $i => $f) {
        $key = preg_replace('/[^a-z0-9_]/', '_', strtolower($f['label'] ?? 'field'));
        $stmt->execute([
            $career_id,
            $f['field_key'] ?: $key . '_' . ($i + 1),
            $f['field_type'] ?? 'text',
            $f['label']      ?? '',
            $f['placeholder'] ?? '',
            !empty($f['required']) ? 1 : 0,
            is_array($f['options'] ?? null) ? json_encode($f['options']) : ($f['options_json'] ?? '[]'),
            $i,
        ]);
    }
}

/* ---- Applications ---- */
function dgtec_career_apply(int $career_id, array $data, array $files, string $ip): int {
    $db = dgtec_db();
    $db->prepare("INSERT INTO `career_applications` (`career_id`,`data_json`,`files_json`,`ip`) VALUES (?,?,?,?)")
       ->execute([$career_id, json_encode($data, JSON_UNESCAPED_UNICODE), json_encode($files, JSON_UNESCAPED_UNICODE), $ip]);
    return (int)$db->lastInsertId();
}

function dgtec_career_applications(int $career_id): array {
    $r = dgtec_db()->prepare("SELECT a.*, c.title as career_title FROM `career_applications` a
        LEFT JOIN `careers` c ON c.id=a.career_id WHERE a.`career_id`=? ORDER BY a.`created_at` DESC");
    $r->execute([$career_id]);
    return $r->fetchAll();
}

function dgtec_career_applications_all(): array {
    return dgtec_db()->query("SELECT a.*, c.title as career_title FROM `career_applications` a
        LEFT JOIN `careers` c ON c.id=a.career_id ORDER BY a.`created_at` DESC")->fetchAll();
}

function dgtec_career_application_get(int $id): ?array {
    $r = dgtec_db()->prepare("SELECT a.*, c.title as career_title FROM `career_applications` a
        LEFT JOIN `careers` c ON c.id=a.career_id WHERE a.`id`=?");
    $r->execute([$id]);
    return $r->fetch() ?: null;
}

function dgtec_career_application_status(int $id, string $status): void {
    dgtec_db()->prepare("UPDATE `career_applications` SET `status`=? WHERE `id`=?")->execute([$status, $id]);
}

function dgtec_career_applications_count(): int {
    return (int)dgtec_db()->query("SELECT COUNT(*) FROM `career_applications` WHERE `status`='new'")->fetchColumn();
}

/* ============================================================
   USER ROLES & PERMISSIONS
   ============================================================ */
function dgtec_all_permissions(): array {
    return [
        'slides'               => 'Hero Slides',
        'services'             => 'Services',
        'solutions'            => 'Solutions',
        'partners'             => 'Partners & Reviews',
        'blog'                 => 'Blog Posts',
        'pages'                => 'Pages',
        'careers'              => 'Careers',
        'career_applications'  => 'Career Applications',
        'social_media'         => 'Social Media',
        'submissions'          => 'Submissions',
        'site_info'            => 'Site Info',
        'home_content'         => 'Home Content',
        'about_content'        => 'About Content',
        'seo'                  => 'SEO Settings',
        'nav_menus'            => 'Nav Menus',
        'users'                => 'User Management',
    ];
}

function dgtec_roles_all(): array {
    return dgtec_db()->query("SELECT * FROM `admin_roles` ORDER BY `id` ASC")->fetchAll();
}

function dgtec_role_get(int $id): ?array {
    $r = dgtec_db()->prepare("SELECT * FROM `admin_roles` WHERE `id`=?");
    $r->execute([$id]);
    return $r->fetch() ?: null;
}

function dgtec_role_save(array $d): int {
    $db   = dgtec_db();
    $perms = json_encode(array_values(array_filter($d['permissions'] ?? [])));
    if (!empty($d['id'])) {
        $db->prepare("UPDATE `admin_roles` SET `display_name`=?,`permissions_json`=? WHERE `id`=? AND `is_system`=0")
           ->execute([$d['display_name'], $perms, $d['id']]);
        return (int)$d['id'];
    }
    $name = preg_replace('/[^a-z0-9_]/', '_', strtolower($d['display_name'] ?? 'role'));
    $db->prepare("INSERT INTO `admin_roles` (`name`,`display_name`,`permissions_json`) VALUES (?,?,?)")
       ->execute([$name . '_' . time(), $d['display_name'], $perms]);
    return (int)$db->lastInsertId();
}

function dgtec_role_delete(int $id): void {
    /* Unset role from all users that use it */
    dgtec_db()->prepare("UPDATE `admin_users` SET `role_id`=NULL WHERE `role_id`=?")->execute([$id]);
    dgtec_db()->prepare("DELETE FROM `admin_roles` WHERE `id`=? AND `is_system`=0")->execute([$id]);
}

/* ---- Users ---- */
function dgtec_users_all(): array {
    return dgtec_db()->query("SELECT u.*, r.display_name as role_name, r.name as role_slug
        FROM `admin_users` u LEFT JOIN `admin_roles` r ON r.id=u.role_id ORDER BY u.id ASC")->fetchAll();
}

function dgtec_user_get(int $id): ?array {
    $r = dgtec_db()->prepare("SELECT u.*, r.display_name as role_name, r.permissions_json
        FROM `admin_users` u LEFT JOIN `admin_roles` r ON r.id=u.role_id WHERE u.id=?");
    $r->execute([$id]);
    return $r->fetch() ?: null;
}

function dgtec_user_save(array $d): int {
    $db = dgtec_db();
    if (!empty($d['id'])) {
        $sets = ['display_name=?', 'role_id=?', 'is_active=?'];
        $vals = [$d['display_name'], $d['role_id'] ?: null, $d['is_active'] ? 1 : 0];
        if (!empty($d['password'])) {
            $sets[] = 'password_hash=?';
            $vals[] = password_hash($d['password'], PASSWORD_BCRYPT);
        }
        $vals[] = $d['id'];
        $db->prepare("UPDATE `admin_users` SET " . implode(',', $sets) . " WHERE `id`=?")->execute($vals);
        return (int)$d['id'];
    }
    $hash = password_hash($d['password'], PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO `admin_users` (`username`,`password_hash`,`display_name`,`role_id`,`is_active`) VALUES (?,?,?,?,?)")
       ->execute([$d['username'], $hash, $d['display_name'], $d['role_id'] ?: null, $d['is_active'] ? 1 : 0]);
    return (int)$db->lastInsertId();
}

function dgtec_user_delete(int $id, bool $withContent = false): void {
    /* Never delete the last admin */
    $adminRoleId = (int)dgtec_db()->query("SELECT id FROM admin_roles WHERE name='admin' LIMIT 1")->fetchColumn();
    $adminCount  = (int)dgtec_db()->prepare("SELECT COUNT(*) FROM admin_users WHERE role_id=? AND id!=? AND is_active=1")->execute([$adminRoleId,$id]) ? 0 : 0;
    $stmt = dgtec_db()->prepare("SELECT COUNT(*) FROM admin_users WHERE role_id=? AND id!=? AND is_active=1");
    $stmt->execute([$adminRoleId, $id]);
    if ((int)$stmt->fetchColumn() === 0 && $adminRoleId) {
        /* check if user being deleted is an admin */
        $uRole = (int)(dgtec_db()->prepare("SELECT role_id FROM admin_users WHERE id=?")->execute([$id]) ? 0 : 0);
        $s = dgtec_db()->prepare("SELECT role_id FROM admin_users WHERE id=?");
        $s->execute([$id]);
        $uRole = (int)($s->fetchColumn() ?: 0);
        if ($uRole === $adminRoleId) return; /* protect last admin */
    }
    dgtec_db()->prepare("DELETE FROM `admin_users` WHERE `id`=?")->execute([$id]);
}

function dgtec_user_get_by_username(string $username): ?array {
    $r = dgtec_db()->prepare("SELECT u.*, r.name as role_slug, r.permissions_json
        FROM `admin_users` u LEFT JOIN `admin_roles` r ON r.id=u.role_id WHERE u.username=? LIMIT 1");
    $r->execute([$username]);
    return $r->fetch() ?: null;
}

function dgtec_seed_slides(PDO $pdo): void {
    $slides = [
        [
            'position'          => 1,
            'label'             => 'Saudi Vision 2030 Aligned',
            'title'             => "Technological\ntransformation in",
            'highlight_text'    => 'The Kingdom',
            'highlight_color'   => '',
            'description'       => 'We deliver advanced integrated solutions — from Technical Recruitment and Outsourcing to AI-driven Digital Transformation.',
            'bg_image'          => 'assets/images/hero-slider.webp',
            'gradient_color1'   => '#183f96',
            'gradient_opacity1' => 0.84,
            'gradient_color2'   => '#183f96',
            'gradient_opacity2' => 0.45,
            'btn1_text'         => 'Get Started',
            'btn1_url'          => 'contact.php',
            'btn2_text'         => 'About Us',
            'btn2_url'          => 'about.php',
        ],
        [
            'position'          => 2,
            'label'             => 'Expert Talent Acquisition',
            'title'             => "The Right Talent,\nRight Now, Right",
            'highlight_text'    => 'Here',
            'highlight_color'   => '',
            'description'       => 'Hire top-tier technical, managerial and engineering professionals to drive innovation and business success across The Kingdom.',
            'bg_image'          => 'assets/images/hero-bg.png',
            'gradient_color1'   => '#033250',
            'gradient_opacity1' => 0.88,
            'gradient_color2'   => '#036478',
            'gradient_opacity2' => 0.50,
            'btn1_text'         => 'Hire Now',
            'btn1_url'          => 'contact.php',
            'btn2_text'         => 'Our Services',
            'btn2_url'          => 'services.php',
        ],
        [
            'position'          => 3,
            'label'             => 'AI-Powered Digital Growth',
            'title'             => "Automate, Scale,\nand Lead in the",
            'highlight_text'    => 'Digital Era',
            'highlight_color'   => '',
            'description'       => 'Transform your enterprise with AI-driven automation, smart digital workflows and cutting-edge technology solutions built for tomorrow.',
            'bg_image'          => 'assets/images/hero-slider.webp',
            'gradient_color1'   => '#03869e',
            'gradient_opacity1' => 0.86,
            'gradient_color2'   => '#183f96',
            'gradient_opacity2' => 0.90,
            'btn1_text'         => 'Transform Now',
            'btn1_url'          => 'contact.php',
            'btn2_text'         => 'Our Solutions',
            'btn2_url'          => 'solutions.php',
        ],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO `hero_slides`
            (position, label, title, highlight_text, highlight_color, description,
             bg_image, gradient_color1, gradient_opacity1, gradient_color2, gradient_opacity2,
             btn1_text, btn1_url, btn2_text, btn2_url)
        VALUES
            (:position, :label, :title, :highlight_text, :highlight_color, :description,
             :bg_image, :gradient_color1, :gradient_opacity1, :gradient_color2, :gradient_opacity2,
             :btn1_text, :btn1_url, :btn2_text, :btn2_url)
    ");

    foreach ($slides as $slide) {
        $stmt->execute($slide);
    }
}

/* ---- Slide query helpers ---- */

function dgtec_slides_active(): array {
    return dgtec_db()
        ->query("SELECT * FROM `hero_slides` WHERE `is_active` = 1 ORDER BY `position` ASC")
        ->fetchAll();
}

function dgtec_slides_all(): array {
    return dgtec_db()
        ->query("SELECT * FROM `hero_slides` ORDER BY `position` ASC")
        ->fetchAll();
}

function dgtec_slide_get(int $id): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `hero_slides` WHERE `id` = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function dgtec_slide_save(array $data): int {
    $db = dgtec_db();
    if (!empty($data['id'])) {
        $stmt = $db->prepare("
            UPDATE `hero_slides` SET
                position=:position, is_active=:is_active, label=:label, title=:title,
                highlight_text=:highlight_text, highlight_color=:highlight_color,
                description=:description, bg_image=:bg_image,
                gradient_color1=:gradient_color1, gradient_opacity1=:gradient_opacity1,
                gradient_color2=:gradient_color2, gradient_opacity2=:gradient_opacity2,
                btn1_text=:btn1_text, btn1_url=:btn1_url,
                btn2_text=:btn2_text, btn2_url=:btn2_url
            WHERE id=:id
        ");
        $stmt->execute($data);
        return (int)$data['id'];
    } else {
        $stmt = $db->prepare("
            INSERT INTO `hero_slides`
                (position, is_active, label, title, highlight_text, highlight_color,
                 description, bg_image, gradient_color1, gradient_opacity1,
                 gradient_color2, gradient_opacity2,
                 btn1_text, btn1_url, btn2_text, btn2_url)
            VALUES
                (:position, :is_active, :label, :title, :highlight_text, :highlight_color,
                 :description, :bg_image, :gradient_color1, :gradient_opacity1,
                 :gradient_color2, :gradient_opacity2,
                 :btn1_text, :btn1_url, :btn2_text, :btn2_url)
        ");
        unset($data['id']); /* id not in INSERT — extra named keys cause PDO error */
        $stmt->execute($data);
        return (int)$db->lastInsertId();
    }
}

function dgtec_slide_delete(int $id): void {
    $stmt = dgtec_db()->prepare("DELETE FROM `hero_slides` WHERE `id` = ?");
    $stmt->execute([$id]);
}

function dgtec_slide_move(int $id, string $dir): void {
    $db  = dgtec_db();
    $all = $db->query("SELECT `id`, `position` FROM `hero_slides` ORDER BY `position` ASC")->fetchAll();
    $idx = array_search($id, array_column($all, 'id'));
    if ($idx === false) return;

    $swapIdx = ($dir === 'up') ? $idx - 1 : $idx + 1;
    if (!isset($all[$swapIdx])) return;

    $stmt = $db->prepare("UPDATE `hero_slides` SET `position` = ? WHERE `id` = ?");
    $stmt->execute([$all[$swapIdx]['position'], $id]);
    $stmt->execute([$all[$idx]['position'], $all[$swapIdx]['id']]);
}

/* ---- Submission query helpers ---- */

function dgtec_submissions_all(): array {
    return dgtec_db()
        ->query("SELECT * FROM `contacts` ORDER BY `created_at` DESC")
        ->fetchAll();
}

function dgtec_submission_get(int $id): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `contacts` WHERE `id` = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function dgtec_submission_mark_read(int $id, int $val): void {
    $stmt = dgtec_db()->prepare("UPDATE `contacts` SET `is_read` = ? WHERE `id` = ?");
    $stmt->execute([$val, $id]);
}

function dgtec_submission_delete(int $id): void {
    $stmt = dgtec_db()->prepare("DELETE FROM `contacts` WHERE `id` = ?");
    $stmt->execute([$id]);
}

function dgtec_submissions_unread_count(): int {
    return (int)dgtec_db()
        ->query("SELECT COUNT(*) FROM `contacts` WHERE `is_read` = 0")
        ->fetchColumn();
}

/* ---- Seed functions for services & solutions ---- */

function dgtec_seed_services(PDO $pdo): void {
    $items = [
        ['position'=>1,'title'=>'Expert Technical Recruitment','slug'=>'service-recruitment','icon'=>'fas fa-users','image'=>'assets/images/team.png','description'=>'Hire top-tier technical, managerial and engineering talent fast. Our AI-powered screening, vast talent network and 72-hour shortlist SLA ensures you never lose a great candidate to a slower competitor.','features'=>'72h Shortlist SLA|AI-Powered Screening|Permanent & Contract|Saudisation Compliance|90-Day Guarantee','page_url'=>'service-recruitment.php','is_reversed'=>0],
        ['position'=>2,'title'=>'Scalable Outsourcing Solutions','slug'=>'service-outsourcing','icon'=>'fas fa-people-group','image'=>'assets/images/our-soul.webp','description'=>'Access fully managed, skilled resources at up to 55% less than traditional employment. Flexible monthly contracts, zero overhead, full Saudi labour law compliance and rapid scale-up capability.','features'=>'Up to 55% Cost Reduction|Fully Managed Teams|Flexible Contracts|Legal Compliance|KPI Reporting','page_url'=>'service-outsourcing.php','is_reversed'=>1],
        ['position'=>3,'title'=>'Enterprise Digital Transformation','slug'=>'service-digital-transformation','icon'=>'fas fa-microchip','image'=>'assets/images/hero-bg.png','description'=>'Powered by Zenoo and Newgen — we take your organisation from strategy to go-live. Legacy modernisation, cloud adoption, AI integration and change management — all under one accountable partner.','features'=>'Digital Strategy & Roadmap|Cloud Adoption|AI & Automation|Change Management|Vision 2030 Aligned','page_url'=>'service-digital-transformation.php','is_reversed'=>0],
        ['position'=>4,'title'=>'Tech Squad-as-a-Service','slug'=>'service-tech-squad','icon'=>'fas fa-code','image'=>'assets/images/hero-slider.webp','description'=>'A fully assembled, cross-functional engineering team deployed in 48 hours — complete with a dedicated Project Manager, agile delivery methodology and flexible monthly scaling. Ship faster without the overhead.','features'=>'48h Deployment|Full-Stack Capabilities|Dedicated PM Included|Agile / Scrum|Elastic Scaling','page_url'=>'service-tech-squad.php','is_reversed'=>1],
        ['position'=>5,'title'=>'Data Handling Solutions','slug'=>'service-data-handling','icon'=>'fas fa-database','image'=>'assets/images/process-road.webp','description'=>'From raw data collection to AI-ready pipelines — DGTEC manages the full data lifecycle. Clean, governed, secure data that powers smarter decisions, better analytics and faster machine learning.','features'=>'Data Collection & Ingestion|Cleansing & Enrichment|Governance Framework|BI & Analytics|PDPL Compliant','page_url'=>'service-data-handling.php','is_reversed'=>0],
    ];
    $stmt = $pdo->prepare("INSERT INTO `services` (position,title,slug,icon,image,description,features,page_url,is_reversed) VALUES (:position,:title,:slug,:icon,:image,:description,:features,:page_url,:is_reversed)");
    foreach ($items as $item) { $stmt->execute($item); }
}

function dgtec_seed_solutions(PDO $pdo): void {
    $items = [
        ['position'=>1,'title'=>'Digital Onboarding & Compliance','slug'=>'solution-digital-onboarding','icon'=>'fas fa-id-card-clip','image'=>'assets/images/our-soul.webp','description'=>'Replace slow, paper-heavy onboarding with intelligent digital workflows. From KYC and identity verification to compliance monitoring and audit trails — fully automated, fully compliant.','features'=>'KYC & Identity Verification|Digital Document Management|Compliance Monitoring|Smart Workflow Builder|Full Audit Trail','page_url'=>'solution-digital-onboarding.php','is_reversed'=>0],
        ['position'=>2,'title'=>'Enterprise Content & Process Automation','slug'=>'solution-enterprise-automation','icon'=>'fas fa-robot','image'=>'assets/images/process-road.webp','description'=>'Powered by Newgen — a global BPM and ECM leader — automate complex cross-department processes, manage enterprise content and unlock real-time process intelligence at scale.','features'=>'Business Process Automation|Intelligent Document Capture|Low-Code Workflow Designer|Enterprise Content Management|Process Analytics','page_url'=>'solution-enterprise-automation.php','is_reversed'=>1],
        ['position'=>3,'title'=>'Tea Boy – Smart Internal Operations Automation','slug'=>'solution-tea-boy','icon'=>'fas fa-mug-hot','image'=>'assets/images/team.png','description'=>"DGTEC's proprietary AI-powered platform transforms how organisations manage day-to-day internal operations — from facilities and IT requests to asset tracking and smart scheduling.",'features'=>'Smart Request Management|Intelligent Scheduling|Internal Service Desk|Asset Tracking|Mobile Employee App','page_url'=>'solution-tea-boy.php','is_reversed'=>0],
    ];
    $stmt = $pdo->prepare("INSERT INTO `solutions` (position,title,slug,icon,image,description,features,page_url,is_reversed) VALUES (:position,:title,:slug,:icon,:image,:description,:features,:page_url,:is_reversed)");
    foreach ($items as $item) { $stmt->execute($item); }
}

/* ---- Generic list/get/save/delete/move for services & solutions ---- */

function _dgtec_tbl(string $type): string {
    return $type === 'service' ? 'services' : 'solutions';
}

function dgtec_items_all(string $type): array {
    return dgtec_db()->query("SELECT * FROM `"._dgtec_tbl($type)."` ORDER BY `position` ASC")->fetchAll();
}

function dgtec_items_active(string $type): array {
    return dgtec_db()->query("SELECT * FROM `"._dgtec_tbl($type)."` WHERE `is_active`=1 ORDER BY `position` ASC")->fetchAll();
}

function dgtec_item_get(string $type, int $id): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `"._dgtec_tbl($type)."` WHERE `id`=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function dgtec_item_save(string $type, array $data): int {
    $db  = dgtec_db();
    $tbl = _dgtec_tbl($type);
    if (!empty($data['id'])) {
        $db->prepare("UPDATE `$tbl` SET position=:position,is_active=:is_active,title=:title,slug=:slug,icon=:icon,image=:image,description=:description,features=:features,page_url=:page_url,is_reversed=:is_reversed,page_content=:page_content WHERE id=:id")->execute($data);
        return (int)$data['id'];
    } else {
        unset($data['id']);
        $db->prepare("INSERT INTO `$tbl` (position,is_active,title,slug,icon,image,description,features,page_url,is_reversed,page_content) VALUES (:position,:is_active,:title,:slug,:icon,:image,:description,:features,:page_url,:is_reversed,:page_content)")->execute($data);
        return (int)$db->lastInsertId();
    }
}

function dgtec_item_get_by_page_url(string $type, string $pageUrl): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `"._dgtec_tbl($type)."` WHERE `page_url`=? AND `is_active`=1 LIMIT 1");
    $stmt->execute([$pageUrl]);
    return $stmt->fetch();
}

function dgtec_item_get_by_slug(string $type, string $slug): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `"._dgtec_tbl($type)."` WHERE `slug`=? AND `is_active`=1 LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function dgtec_item_delete(string $type, int $id): void {
    dgtec_db()->prepare("DELETE FROM `"._dgtec_tbl($type)."` WHERE `id`=?")->execute([$id]);
}

function dgtec_item_move(string $type, int $id, string $dir): void {
    $db  = dgtec_db();
    $tbl = _dgtec_tbl($type);
    $all = $db->query("SELECT `id`,`position` FROM `$tbl` ORDER BY `position` ASC")->fetchAll();
    $idx = array_search($id, array_column($all, 'id'));
    if ($idx === false) return;
    $swapIdx = ($dir === 'up') ? $idx - 1 : $idx + 1;
    if (!isset($all[$swapIdx])) return;
    $stmt = $db->prepare("UPDATE `$tbl` SET `position`=? WHERE `id`=?");
    $stmt->execute([$all[$swapIdx]['position'], $id]);
    $stmt->execute([$all[$idx]['position'], $all[$swapIdx]['id']]);
}

/* ---- Utility ---- */

function hex_rgba(string $hex, float $alpha): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "rgba($r,$g,$b,$alpha)";
}

/* ================================================================
   PARTNERS
   ================================================================ */

function dgtec_seed_partners(PDO $pdo): void {
    $items = [
        ['position'=>1,'name'=>'Partner 1','logo'=>'assets/images/partner-1.jpg','website_url'=>''],
        ['position'=>2,'name'=>'Partner 2','logo'=>'assets/images/partner-2.jpg','website_url'=>''],
        ['position'=>3,'name'=>'Partner 3','logo'=>'assets/images/partner-3.jpg','website_url'=>''],
        ['position'=>4,'name'=>'Partner 4','logo'=>'assets/images/partner-4.jpg','website_url'=>''],
        ['position'=>5,'name'=>'Partner 5','logo'=>'assets/images/partner-5.jpg','website_url'=>''],
        ['position'=>6,'name'=>'Brand Partner','logo'=>'assets/images/brand-one.webp','website_url'=>''],
    ];
    $stmt = $pdo->prepare("INSERT INTO `partners` (position,name,logo,website_url) VALUES (:position,:name,:logo,:website_url)");
    foreach ($items as $item) { $stmt->execute($item); }
}

function dgtec_partners_all(): array {
    return dgtec_db()->query("SELECT * FROM `partners` ORDER BY `position` ASC")->fetchAll();
}

function dgtec_partners_active(): array {
    return dgtec_db()->query("SELECT * FROM `partners` WHERE `is_active`=1 ORDER BY `position` ASC")->fetchAll();
}

function dgtec_partner_get(int $id): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `partners` WHERE `id`=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function dgtec_partner_save(array $data): int {
    $db = dgtec_db();
    if (!empty($data['id'])) {
        $db->prepare("UPDATE `partners` SET position=:position,is_active=:is_active,name=:name,logo=:logo,website_url=:website_url WHERE id=:id")->execute($data);
        return (int)$data['id'];
    } else {
        unset($data['id']);
        $db->prepare("INSERT INTO `partners` (position,is_active,name,logo,website_url) VALUES (:position,:is_active,:name,:logo,:website_url)")->execute($data);
        return (int)$db->lastInsertId();
    }
}

function dgtec_partner_delete(int $id): void {
    dgtec_db()->prepare("DELETE FROM `partners` WHERE `id`=?")->execute([$id]);
}

function dgtec_partner_move(int $id, string $dir): void {
    $db  = dgtec_db();
    $all = $db->query("SELECT `id`,`position` FROM `partners` ORDER BY `position` ASC")->fetchAll();
    $idx = array_search($id, array_column($all, 'id'));
    if ($idx === false) return;
    $swapIdx = ($dir === 'up') ? $idx - 1 : $idx + 1;
    if (!isset($all[$swapIdx])) return;
    $stmt = $db->prepare("UPDATE `partners` SET `position`=? WHERE `id`=?");
    $stmt->execute([$all[$swapIdx]['position'], $id]);
    $stmt->execute([$all[$idx]['position'], $all[$swapIdx]['id']]);
}

/* ================================================================
   CLIENT REVIEWS
   ================================================================ */

function dgtec_seed_reviews(PDO $pdo): void {
    $items = [
        ['position'=>1,'name'=>'Ahmed Al-Rashidi','job_title'=>'HR Director, National Tech Co.','review'=>'DGTEC transformed our HR and recruitment process entirely. Their team understood our needs from day one and delivered a talent pipeline that exceeded every expectation. We now operate with a level of efficiency we didn\'t think was possible.','stars'=>5,'image'=>''],
        ['position'=>2,'name'=>'Sara Al-Otaibi','job_title'=>'CEO, GreenPath Solutions KSA','review'=>'The digital transformation roadmap DGTEC delivered was exactly what our organisation needed. Their expertise in process automation and AI integration helped us cut operational costs significantly and accelerate our Vision 2030 alignment.','stars'=>5,'image'=>''],
        ['position'=>3,'name'=>'Khalid Mansour','job_title'=>'CTO, Horizons Digital Group','review'=>'We engaged DGTEC\'s Tech Squad-as-a-Service and the results were outstanding. A dedicated, highly skilled team deployed within days — no overhead, no delays. Their agile approach made scaling our product development seamless and cost-effective.','stars'=>5,'image'=>''],
    ];
    $stmt = $pdo->prepare("INSERT INTO `client_reviews` (position,name,job_title,review,stars,image) VALUES (:position,:name,:job_title,:review,:stars,:image)");
    foreach ($items as $item) { $stmt->execute($item); }
}

function dgtec_reviews_all(): array {
    return dgtec_db()->query("SELECT * FROM `client_reviews` ORDER BY `position` ASC")->fetchAll();
}

function dgtec_reviews_active(): array {
    return dgtec_db()->query("SELECT * FROM `client_reviews` WHERE `is_active`=1 ORDER BY `position` ASC")->fetchAll();
}

function dgtec_review_get(int $id): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `client_reviews` WHERE `id`=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function dgtec_review_save(array $data): int {
    $db = dgtec_db();
    if (!empty($data['id'])) {
        $db->prepare("UPDATE `client_reviews` SET position=:position,is_active=:is_active,name=:name,job_title=:job_title,review=:review,stars=:stars,image=:image WHERE id=:id")->execute($data);
        return (int)$data['id'];
    } else {
        unset($data['id']);
        $db->prepare("INSERT INTO `client_reviews` (position,is_active,name,job_title,review,stars,image) VALUES (:position,:is_active,:name,:job_title,:review,:stars,:image)")->execute($data);
        return (int)$db->lastInsertId();
    }
}

function dgtec_review_delete(int $id): void {
    dgtec_db()->prepare("DELETE FROM `client_reviews` WHERE `id`=?")->execute([$id]);
}

function dgtec_review_move(int $id, string $dir): void {
    $db  = dgtec_db();
    $all = $db->query("SELECT `id`,`position` FROM `client_reviews` ORDER BY `position` ASC")->fetchAll();
    $idx = array_search($id, array_column($all, 'id'));
    if ($idx === false) return;
    $swapIdx = ($dir === 'up') ? $idx - 1 : $idx + 1;
    if (!isset($all[$swapIdx])) return;
    $stmt = $db->prepare("UPDATE `client_reviews` SET `position`=? WHERE `id`=?");
    $stmt->execute([$all[$swapIdx]['position'], $id]);
    $stmt->execute([$all[$idx]['position'], $all[$swapIdx]['id']]);
}

/* ================================================================
   SITE INFO
   ================================================================ */

function dgtec_seed_site_info(PDO $pdo): void {
    $pdo->prepare("
        INSERT INTO `site_info` (phone,email,address,footer_description,site_description,header_logo,footer_logo)
        VALUES (:phone,:email,:address,:footer_description,:site_description,:header_logo,:footer_logo)
    ")->execute([
        'phone'               => '+966 11 000 0000',
        'email'               => 'info@dgtec.com.sa',
        'address'             => 'Riyadh, Saudi Arabia',
        'footer_description'  => 'We believe technology has the power to do amazing things. DGTEC delivers advanced integrated solutions that transform businesses across the Kingdom.',
        'site_description'    => 'DGTEC delivers advanced Technical Recruitment, Scalable Outsourcing, AI automation and Digital Transformation solutions in Saudi Arabia.',
        'header_logo'         => 'assets/images/logo.webp',
        'footer_logo'         => 'assets/images/logo.webp',
    ]);
}

function dgtec_site_info(): array {
    static $info;
    if ($info) return $info;
    $info = dgtec_db()->query("SELECT * FROM `site_info` ORDER BY `id` ASC LIMIT 1")->fetch();
    if (!$info) {
        $info = [
            'id'=>0,'phone'=>'+966 11 000 0000','email'=>'info@dgtec.com.sa',
            'address'=>'Riyadh, Saudi Arabia',
            'footer_description'=>'We believe technology has the power to do amazing things. DGTEC delivers advanced integrated solutions that transform businesses across the Kingdom.',
            'site_description'=>'DGTEC delivers advanced integrated solutions in Saudi Arabia.',
            'header_logo'=>'assets/images/logo.webp','footer_logo'=>'assets/images/logo.webp',
        ];
    }
    return $info;
}

function dgtec_site_info_save(array $data): void {
    $db          = dgtec_db();
    $allowedCols = [
        'phone', 'email', 'address', 'footer_description', 'site_description',
        'header_logo', 'footer_logo', 'google_analytics', 'favicon',
        'global_head_code', 'global_body_code', 'header_nav_json', 'footer_nav_json',
        'home_content_json', 'home_process_json', 'home_achievements_json', 'about_content_json',
    ];
    $filtered = array_filter($data, fn($k) => in_array($k, $allowedCols, true), ARRAY_FILTER_USE_KEY);
    if (empty($filtered)) return;

    $cnt = (int)$db->query("SELECT COUNT(*) FROM `site_info`")->fetchColumn();
    if ($cnt === 0) {
        $cols = implode(',', array_keys($filtered));
        $phs  = implode(',', array_map(fn($k) => ":$k", array_keys($filtered)));
        $db->prepare("INSERT INTO `site_info` ($cols) VALUES ($phs)")->execute($filtered);
    } else {
        $sets = implode(',', array_map(fn($k) => "$k=:$k", array_keys($filtered)));
        $db->prepare("UPDATE `site_info` SET $sets ORDER BY id ASC LIMIT 1")->execute($filtered);
    }
}

/* ================================================================
   BLOG POSTS
   ================================================================ */

function dgtec_seed_blogs(PDO $pdo): void {
    $posts = [
        [
            'position'=>1,'title'=>'How AI is Reshaping Technical Recruitment in Saudi Arabia',
            'slug'=>'how-ai-is-reshaping-technical-recruitment',
            'category'=>'AI & Recruitment',
            'excerpt'=>'Artificial intelligence is transforming how organisations identify, screen and onboard technical talent — cutting time-to-hire while improving candidate quality and cultural fit.',
            'content'=>'<p>The Kingdom of Saudi Arabia is experiencing an unprecedented demand for technical talent. As Vision 2030 accelerates the pace of digital transformation across both the government and private sectors, organisations are under pressure to attract, screen and onboard skilled professionals faster than ever before.</p><p>Artificial intelligence is emerging as the critical enabler — not to replace human recruiters, but to give them superpowers. From intelligent candidate sourcing to automated skills assessment and predictive retention modelling, AI is fundamentally changing what\'s possible in talent acquisition.</p><h2>The Talent Gap Challenge in KSA</h2><p>Saudi Arabia\'s Vision 2030 has created simultaneous demand across multiple sectors — technology, healthcare, finance, energy and logistics. The challenge isn\'t just finding candidates; it\'s finding the right candidates quickly, reliably and at scale.</p>',
            'image'=>'assets/images/hero-slider.webp',
            'published_at'=>'2026-03-10',
        ],
        [
            'position'=>2,'title'=>'The Future of Enterprise Digital Transformation in Vision 2030',
            'slug'=>'future-enterprise-digital-transformation-vision-2030',
            'category'=>'Digital Transformation',
            'excerpt'=>'Saudi Arabia\'s Vision 2030 is driving a wave of enterprise digitisation. We explore the key pillars, challenges and opportunities for businesses navigating this shift.',
            'content'=>'<p>Saudi Arabia\'s Vision 2030 is one of the most ambitious national transformation programmes in the world. At its core is a fundamental shift in the country\'s economic model — away from oil dependency and towards a diversified, technology-driven knowledge economy.</p><p>For enterprises operating in the Kingdom, this shift is both an enormous opportunity and a serious challenge. The organisations that successfully navigate digital transformation will be the ones that thrive in the new economy. Those that resist will find themselves left behind.</p>',
            'image'=>'assets/images/our-soul.webp',
            'published_at'=>'2026-02-28',
        ],
        [
            'position'=>3,'title'=>'Why Outsourcing IT Operations Can Save Your Business Up to 55%',
            'slug'=>'outsourcing-it-operations-save-55-percent',
            'category'=>'Outsourcing',
            'excerpt'=>'Smart outsourcing isn\'t just about cost — it\'s about agility. Discover how companies are leveraging managed outsourcing models to scale without overhead risk.',
            'content'=>'<p>In today\'s fast-moving business environment, organisations are under constant pressure to do more with less. Operational efficiency is no longer a nice-to-have — it\'s a competitive necessity.</p><p>One of the most powerful levers available to any organisation is the strategic outsourcing of IT operations. When done right, outsourcing can deliver cost savings of up to 55% compared to traditional in-house models, while simultaneously improving service quality, agility and scalability.</p>',
            'image'=>'assets/images/process-road.webp',
            'published_at'=>'2026-02-14',
        ],
        [
            'position'=>4,'title'=>'Tea Boy: Redefining Internal Operations with Smart Automation',
            'slug'=>'tea-boy-smart-internal-operations-automation',
            'category'=>'Smart Automation',
            'excerpt'=>'Meet Tea Boy — DGTEC\'s AI-powered internal operations platform that eliminates manual overhead and brings enterprise-grade service management to everyday workplace tasks.',
            'content'=>'<p>Every organisation, regardless of size or sector, deals with the friction of day-to-day internal operations. Facility requests, IT support tickets, asset management, meeting room bookings, visitor management — the list goes on. These are the invisible costs that drain productivity and frustrate employees.</p><p>Tea Boy, DGTEC\'s proprietary AI-powered platform, was built to solve this problem. By automating the full lifecycle of internal service requests, Tea Boy transforms operational friction into seamless, digital-first experiences.</p>',
            'image'=>'assets/images/team.png',
            'published_at'=>'2026-01-30',
        ],
        [
            'position'=>5,'title'=>'Data Governance in the Age of AI: What Saudi Enterprises Need to Know',
            'slug'=>'data-governance-age-of-ai-saudi-enterprises',
            'category'=>'Data & AI',
            'excerpt'=>'As AI adoption accelerates, data governance becomes the backbone of every successful enterprise strategy. We break down what matters most for KSA organisations.',
            'content'=>'<p>The rise of artificial intelligence has fundamentally changed the strategic importance of data. Data is no longer just a byproduct of operations — it\'s a critical business asset that can drive competitive advantage, improve decision-making and power the next generation of AI applications.</p><p>But for data to deliver value, it must be governed. Poor data quality, inconsistent definitions, compliance gaps and security vulnerabilities don\'t just create operational problems — they actively undermine AI investments and expose organisations to significant risk.</p>',
            'image'=>'assets/images/hero-bg.png',
            'published_at'=>'2026-01-12',
        ],
        [
            'position'=>6,'title'=>'Tech Squad-as-a-Service: The Agile Alternative to Traditional IT Hiring',
            'slug'=>'tech-squad-as-a-service-agile-alternative',
            'category'=>'Tech Squad',
            'excerpt'=>'The "build vs. buy" debate is evolving. Squad-as-a-Service offers a third path — on-demand technical teams that deliver at speed without the cost and risk of permanent hires.',
            'content'=>'<p>The traditional approach to building technical capability — recruit, hire, onboard, retain — is increasingly misaligned with the pace of modern business. Projects move faster. Technology evolves faster. Market windows open and close in months, not years.</p><p>This mismatch between hiring timelines and business needs has created a growing demand for a new model: Squad-as-a-Service. At DGTEC, our Tech Squad-as-a-Service offering represents a fundamentally different approach to technical capability — one designed for the agile, fast-moving business environment of today.</p>',
            'image'=>'assets/images/contact-us.webp',
            'published_at'=>'2025-12-20',
        ],
    ];
    $stmt = $pdo->prepare("INSERT INTO `blog_posts` (position,title,slug,category,excerpt,content,image,published_at) VALUES (:position,:title,:slug,:category,:excerpt,:content,:image,:published_at)");
    foreach ($posts as $post) { $stmt->execute($post); }
}

function dgtec_blogs_all(): array {
    return dgtec_db()->query("SELECT * FROM `blog_posts` ORDER BY `position` ASC")->fetchAll();
}

function dgtec_blogs_active(): array {
    return dgtec_db()->query("SELECT * FROM `blog_posts` WHERE `is_active`=1 ORDER BY `published_at` DESC, `position` ASC")->fetchAll();
}

function dgtec_blog_get(int $id): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `blog_posts` WHERE `id`=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function dgtec_blog_get_by_slug(string $slug): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `blog_posts` WHERE `slug`=? AND `is_active`=1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function dgtec_blog_save(array $data): int {
    $db = dgtec_db();
    if (!empty($data['id'])) {
        $db->prepare("UPDATE `blog_posts` SET position=:position,is_active=:is_active,title=:title,slug=:slug,category=:category,tags=:tags,excerpt=:excerpt,content=:content,image=:image,published_at=:published_at WHERE id=:id")->execute($data);
        return (int)$data['id'];
    } else {
        unset($data['id']);
        $db->prepare("INSERT INTO `blog_posts` (position,is_active,title,slug,category,tags,excerpt,content,image,published_at) VALUES (:position,:is_active,:title,:slug,:category,:tags,:excerpt,:content,:image,:published_at)")->execute($data);
        return (int)$db->lastInsertId();
    }
}

function dgtec_blog_delete(int $id): void {
    dgtec_db()->prepare("DELETE FROM `blog_posts` WHERE `id`=?")->execute([$id]);
}

function dgtec_blog_move(int $id, string $dir): void {
    $db  = dgtec_db();
    $all = $db->query("SELECT `id`,`position` FROM `blog_posts` ORDER BY `position` ASC")->fetchAll();
    $idx = array_search($id, array_column($all, 'id'));
    if ($idx === false) return;
    $swapIdx = ($dir === 'up') ? $idx - 1 : $idx + 1;
    if (!isset($all[$swapIdx])) return;
    $stmt = $db->prepare("UPDATE `blog_posts` SET `position`=? WHERE `id`=?");
    $stmt->execute([$all[$swapIdx]['position'], $id]);
    $stmt->execute([$all[$idx]['position'], $all[$swapIdx]['id']]);
}

/* ================================================================
   SEO PAGES
   ================================================================ */

function dgtec_seo_get(string $pageKey): array {
    $stmt = dgtec_db()->prepare("SELECT * FROM `seo_pages` WHERE `page_key`=? LIMIT 1");
    $stmt->execute([$pageKey]);
    $row = $stmt->fetch();
    if (!$row) {
        return [
            'id'=>0,'page_key'=>$pageKey,'meta_title'=>'','meta_desc'=>'',
            'og_title'=>'','og_desc'=>'','og_image'=>'','canonical'=>'',
            'robots'=>'index, follow','schema_json'=>'','head_code'=>'','body_code'=>'',
        ];
    }
    return $row;
}

function dgtec_seo_save(string $pageKey, array $data): void {
    $db  = dgtec_db();
    $row = $db->prepare("SELECT `id` FROM `seo_pages` WHERE `page_key`=?")->execute([$pageKey]) ? null : null;
    $stmt = $db->prepare("SELECT `id` FROM `seo_pages` WHERE `page_key`=?");
    $stmt->execute([$pageKey]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        $db->prepare("UPDATE `seo_pages` SET meta_title=:meta_title,meta_desc=:meta_desc,og_title=:og_title,og_desc=:og_desc,og_image=:og_image,canonical=:canonical,robots=:robots,schema_json=:schema_json,head_code=:head_code,body_code=:body_code WHERE page_key=:page_key")
           ->execute(array_merge($data, ['page_key' => $pageKey]));
    } else {
        $db->prepare("INSERT INTO `seo_pages` (page_key,meta_title,meta_desc,og_title,og_desc,og_image,canonical,robots,schema_json,head_code,body_code) VALUES (:page_key,:meta_title,:meta_desc,:og_title,:og_desc,:og_image,:canonical,:robots,:schema_json,:head_code,:body_code)")
           ->execute(array_merge($data, ['page_key' => $pageKey]));
    }
}

function dgtec_seo_all(): array {
    return dgtec_db()->query("SELECT * FROM `seo_pages` ORDER BY `page_key` ASC")->fetchAll();
}

/* ================================================================
   NAVIGATION (stored as JSON in site_info)
   ================================================================ */

function dgtec_default_header_nav(): array {
    return [
        ['id'=>1,'label'=>'Home','url'=>'index.php','type'=>'link','children'=>[],'target'=>'_self'],
        ['id'=>2,'label'=>'About','url'=>'about.php','type'=>'link','children'=>[],'target'=>'_self'],
        ['id'=>3,'label'=>'Our Solutions','url'=>'solutions.php','type'=>'solutions_auto','children'=>[],'target'=>'_self'],
        ['id'=>4,'label'=>'Our Services','url'=>'services.php','type'=>'services_auto','children'=>[],'target'=>'_self'],
        ['id'=>5,'label'=>'Blogs','url'=>'blog.php','type'=>'link','children'=>[],'target'=>'_self'],
        ['id'=>6,'label'=>'Contact','url'=>'contact.php','type'=>'link','children'=>[],'target'=>'_self'],
    ];
}

function dgtec_default_footer_nav(): array {
    return [
        ['title'=>'Our Solutions','links'=>[
            ['label'=>'Digital Onboarding','url'=>'solution-digital-onboarding.php'],
            ['label'=>'Process Automation','url'=>'solution-enterprise-automation.php'],
            ['label'=>'Internal Operations','url'=>'solution-tea-boy.php'],
        ]],
        ['title'=>'Our Services','links'=>[
            ['label'=>'Expert Tech Recruitment','url'=>'service-recruitment.php'],
            ['label'=>'Scalable Outsourcing','url'=>'service-outsourcing.php'],
            ['label'=>'Digital Transformation','url'=>'service-digital-transformation.php'],
            ['label'=>'Tech Squad-as-a-Service','url'=>'service-tech-squad.php'],
            ['label'=>'Data Handling Solutions','url'=>'service-data-handling.php'],
        ]],
    ];
}

function dgtec_header_nav(): array {
    $info = dgtec_site_info();
    if (!empty($info['header_nav_json'])) {
        $nav = json_decode($info['header_nav_json'], true);
        if (is_array($nav) && count($nav) > 0) return $nav;
    }
    return dgtec_default_header_nav();
}

function dgtec_footer_nav(): array {
    $info = dgtec_site_info();
    if (!empty($info['footer_nav_json'])) {
        $nav = json_decode($info['footer_nav_json'], true);
        if (is_array($nav) && count($nav) > 0) return $nav;
    }
    return dgtec_default_footer_nav();
}

/* ================================================================
   SOCIAL LINKS
   ================================================================ */

function dgtec_social_links_all(): array {
    return dgtec_db()->query("SELECT * FROM `social_links` ORDER BY `position` ASC")->fetchAll();
}

function dgtec_social_links_active(): array {
    return dgtec_db()->query("SELECT * FROM `social_links` WHERE `is_active`=1 ORDER BY `position` ASC")->fetchAll();
}

function dgtec_social_link_get(int $id): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `social_links` WHERE `id`=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function dgtec_social_link_save(array $data): int {
    $db = dgtec_db();
    if (!empty($data['id'])) {
        $db->prepare("UPDATE `social_links` SET position=:position,is_active=:is_active,platform=:platform,icon=:icon,url=:url WHERE id=:id")->execute($data);
        return (int)$data['id'];
    } else {
        unset($data['id']);
        $db->prepare("INSERT INTO `social_links` (position,is_active,platform,icon,url) VALUES (:position,:is_active,:platform,:icon,:url)")->execute($data);
        return (int)$db->lastInsertId();
    }
}

function dgtec_social_link_delete(int $id): void {
    dgtec_db()->prepare("DELETE FROM `social_links` WHERE `id`=?")->execute([$id]);
}

function dgtec_social_link_move(int $id, string $dir): void {
    $db  = dgtec_db();
    $all = $db->query("SELECT `id`,`position` FROM `social_links` ORDER BY `position` ASC")->fetchAll();
    $idx = array_search($id, array_column($all, 'id'));
    if ($idx === false) return;
    $swapIdx = ($dir === 'up') ? $idx - 1 : $idx + 1;
    if (!isset($all[$swapIdx])) return;
    $stmt = $db->prepare("UPDATE `social_links` SET `position`=? WHERE `id`=?");
    $stmt->execute([$all[$swapIdx]['position'], $id]);
    $stmt->execute([$all[$idx]['position'], $all[$swapIdx]['id']]);
}

/* ================================================================
   CUSTOM PAGES
   ================================================================ */

function dgtec_pages_all(): array {
    return dgtec_db()->query("SELECT * FROM `pages` ORDER BY `position` ASC")->fetchAll();
}

function dgtec_page_get(int $id): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `pages` WHERE `id`=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function dgtec_page_get_by_slug(string $slug): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM `pages` WHERE `slug`=? AND `is_active`=1 LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function dgtec_page_save(array $data): int {
    $db = dgtec_db();
    if (!empty($data['id'])) {
        $db->prepare("UPDATE `pages` SET position=:position,is_active=:is_active,title=:title,slug=:slug,content=:content WHERE id=:id")->execute($data);
        return (int)$data['id'];
    } else {
        unset($data['id']);
        $db->prepare("INSERT INTO `pages` (position,is_active,title,slug,content) VALUES (:position,:is_active,:title,:slug,:content)")->execute($data);
        return (int)$db->lastInsertId();
    }
}

function dgtec_page_delete(int $id): void {
    dgtec_db()->prepare("DELETE FROM `pages` WHERE `id`=?")->execute([$id]);
}

/* ================================================================
   HOME PAGE CONTENT HELPERS
   ================================================================ */

function _dgtec_default_home_content(): array {
    return [
        'services_label'         => 'What We Do',
        'services_title'         => 'Explore Our Services',
        'services_desc'          => 'A full spectrum of technology and talent services to power your business in a rapidly evolving market.',
        'services_count'         => 5,
        'solutions_label'        => 'Our Solutions',
        'solutions_title'        => 'Our Solutions',
        'solutions_desc'         => 'A comprehensive suite of intelligent AI and automation services designed to enhance efficiency, unlock data-driven insights, and streamline business operations.',
        'solutions_count'        => 3,
        'process_label'          => 'How We Work',
        'process_title'          => 'Our Business Process Road',
        'process_desc'           => 'From the first conversation to long-term digital success — here\'s the clear, proven path we walk with every client to deliver measurable results.',
        'achievements_label'     => 'Our Track Record',
        'achievements_title'     => 'Our Achievements',
        'achievements_desc'      => 'Numbers that reflect the trust our clients place in us and the results we consistently deliver across The Kingdom and beyond.',
        'partners_label'         => 'Our Partners',
        'partners_title'         => 'Trusted By Leading Brands',
        'testimonials_label'     => 'Client Feedback',
        'testimonials_title'     => 'Our Clients Says',
        'testimonials_desc'      => 'Real results, real relationships. Here\'s what our clients say about working with DGTEC.',
        'contact_label'          => 'Get In Touch',
        'contact_title'          => "Let's Start Your\nDigital Journey",
        'contact_desc'           => 'Ready to transform your business? Fill in the form and our team will get back to you within 24 hours.',
        'contact_form_title'     => 'Send Us a Message',
        'contact_form_subtitle'  => 'We\'ll respond within 24 hours.',
    ];
}

function dgtec_home_content(): array {
    $info = dgtec_site_info();
    $defaults = _dgtec_default_home_content();
    if (!empty($info['home_content_json'])) {
        $data = json_decode($info['home_content_json'], true);
        if (is_array($data)) return array_merge($defaults, $data);
    }
    return $defaults;
}

function _dgtec_default_process(): array {
    return [
        ['icon'=>'fas fa-magnifying-glass-chart','title'=>'Discovery & Assessment','desc'=>'We start by deeply understanding your business goals, current challenges, and technology landscape to build a clear picture of your needs.'],
        ['icon'=>'fas fa-pencil-ruler','title'=>'Strategy & Design','desc'=>'Our experts craft a tailored digital roadmap and solution architecture that aligns with your Vision 2030 objectives and growth ambitions.'],
        ['icon'=>'fas fa-robot','title'=>'Build & Automate','desc'=>'We implement smart automation, AI-driven workflows and enterprise systems — deploying efficiently with minimal disruption to daily operations.'],
        ['icon'=>'fas fa-chart-line','title'=>'Scale & Grow','desc'=>'We stay with you post-launch — monitoring, optimising and scaling your digital capabilities for sustainable long-term success.'],
    ];
}

function dgtec_home_process(): array {
    $info = dgtec_site_info();
    if (!empty($info['home_process_json'])) {
        $data = json_decode($info['home_process_json'], true);
        if (is_array($data) && count($data) > 0) return $data;
    }
    return _dgtec_default_process();
}

function _dgtec_default_achievements(): array {
    return [
        ['icon'=>'fas fa-check-circle','number'=>'250','suffix'=>'+','label'=>'Completed Tasks'],
        ['icon'=>'fas fa-folder-open','number'=>'120','suffix'=>'+','label'=>'Successful Projects'],
        ['icon'=>'fas fa-rocket','number'=>'85','suffix'=>'+','label'=>'Delivered Projects'],
        ['icon'=>'fas fa-handshake','number'=>'60','suffix'=>'+','label'=>'Happy Clients'],
    ];
}

function dgtec_home_achievements(): array {
    $info = dgtec_site_info();
    if (!empty($info['home_achievements_json'])) {
        $data = json_decode($info['home_achievements_json'], true);
        if (is_array($data) && count($data) > 0) return $data;
    }
    return _dgtec_default_achievements();
}

function _dgtec_default_about(): array {
    return [
        'hero_title'   => 'About',
        'intro_label'  => 'Who we are?',
        'intro_title'  => 'We are DGTEC',
        'intro_desc1'  => 'A leading integrated solutions company delivering advanced Technical recruitment and outsourcing services, Squad-as-a-Service, AI and digital transformation, in addition to Data Solutions.',
        'intro_desc2'  => 'With a strong presence in both government and private sectors, we empower our clients to achieve measurable results — not just promises. Our +6 years of experience have enabled us to build a reputation built on trust, innovation, and excellence.',
        'badge_number' => '6+',
        'badge_text'   => 'Years of Experience',
        'intro_image'  => 'assets/images/our-soul.webp',
        'why_label'    => 'Why Choose Us',
        'why_title'    => 'Why Choose DGTEC?',
        'why_cards'    => [
            ['icon'=>'fas fa-award','text'=>'Our +6 years of experience enabled us to deliver results not just promises.'],
            ['icon'=>'fas fa-flag','text'=>'Full compliance with Saudi Kingdom Vision 2030.'],
            ['icon'=>'fas fa-puzzle-piece','text'=>'Provide our clients with tailored solutions, not just ready ones.'],
            ['icon'=>'fas fa-globe','text'=>'Deep Saudi / GCC market knowledge and expertise.'],
            ['icon'=>'fas fa-cubes','text'=>'Provide Integrated Services: Recruitment, Squad-as-a-Service, Automation & Digital Transformation.'],
            ['icon'=>'fas fa-handshake','text'=>'Partnership with global platforms like Zenoo, Newgen and others.'],
        ],
        'cta_title'     => 'Ready to Transform Your Business?',
        'cta_desc'      => 'Let us show you how DGTEC can drive real results for your organization.',
        'cta_btn1_text' => 'Free Consultation',
        'cta_btn1_url'  => 'contact.php',
        'cta_btn2_text' => 'Our Services',
        'cta_btn2_url'  => 'index.php#services',
    ];
}

function dgtec_about_content(): array {
    $info = dgtec_site_info();
    $defaults = _dgtec_default_about();
    if (!empty($info['about_content_json'])) {
        $data = json_decode($info['about_content_json'], true);
        if (is_array($data)) {
            $merged = array_merge($defaults, $data);
            if (empty($merged['why_cards'])) $merged['why_cards'] = $defaults['why_cards'];
            return $merged;
        }
    }
    return $defaults;
}
