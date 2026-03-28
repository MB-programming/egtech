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
        $db->prepare("UPDATE `$tbl` SET position=:position,is_active=:is_active,title=:title,slug=:slug,icon=:icon,image=:image,description=:description,features=:features,page_url=:page_url,is_reversed=:is_reversed WHERE id=:id")->execute($data);
        return (int)$data['id'];
    } else {
        unset($data['id']);
        $db->prepare("INSERT INTO `$tbl` (position,is_active,title,slug,icon,image,description,features,page_url,is_reversed) VALUES (:position,:is_active,:title,:slug,:icon,:image,:description,:features,:page_url,:is_reversed)")->execute($data);
        return (int)$db->lastInsertId();
    }
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
    $db  = dgtec_db();
    $cnt = (int)$db->query("SELECT COUNT(*) FROM `site_info`")->fetchColumn();
    if ($cnt === 0) {
        $db->prepare("INSERT INTO `site_info` (phone,email,address,footer_description,site_description,header_logo,footer_logo) VALUES (:phone,:email,:address,:footer_description,:site_description,:header_logo,:footer_logo)")->execute($data);
    } else {
        $db->prepare("UPDATE `site_info` SET phone=:phone,email=:email,address=:address,footer_description=:footer_description,site_description=:site_description,header_logo=:header_logo,footer_logo=:footer_logo ORDER BY id ASC LIMIT 1")->execute($data);
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
        $db->prepare("UPDATE `blog_posts` SET position=:position,is_active=:is_active,title=:title,slug=:slug,category=:category,excerpt=:excerpt,content=:content,image=:image,published_at=:published_at WHERE id=:id")->execute($data);
        return (int)$data['id'];
    } else {
        unset($data['id']);
        $db->prepare("INSERT INTO `blog_posts` (position,is_active,title,slug,category,excerpt,content,image,published_at) VALUES (:position,:is_active,:title,:slug,:category,:excerpt,:content,:image,:published_at)")->execute($data);
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
