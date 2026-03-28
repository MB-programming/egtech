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
