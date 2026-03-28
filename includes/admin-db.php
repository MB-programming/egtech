<?php
/**
 * DGTEC Admin — SQLite database helper
 */

define('DGTEC_DB_PATH', dirname(__DIR__) . '/data/dgtec.db');

function dgtec_db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;

    $dir = dirname(DGTEC_DB_PATH);
    if (!is_dir($dir)) mkdir($dir, 0750, true);

    $pdo = new PDO('sqlite:' . DGTEC_DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    dgtec_db_init($pdo);
    return $pdo;
}

function dgtec_db_init(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS hero_slides (
            id                INTEGER PRIMARY KEY AUTOINCREMENT,
            position          INTEGER NOT NULL DEFAULT 0,
            is_active         INTEGER NOT NULL DEFAULT 1,
            label             TEXT    NOT NULL DEFAULT '',
            title             TEXT    NOT NULL DEFAULT '',
            highlight_text    TEXT    NOT NULL DEFAULT '',
            highlight_color   TEXT    NOT NULL DEFAULT '',
            description       TEXT    NOT NULL DEFAULT '',
            bg_image          TEXT    NOT NULL DEFAULT '',
            gradient_color1   TEXT    NOT NULL DEFAULT '#183f96',
            gradient_opacity1 REAL    NOT NULL DEFAULT 0.84,
            gradient_color2   TEXT    NOT NULL DEFAULT '#183f96',
            gradient_opacity2 REAL    NOT NULL DEFAULT 0.45,
            btn1_text         TEXT    NOT NULL DEFAULT '',
            btn1_url          TEXT    NOT NULL DEFAULT '',
            btn2_text         TEXT    NOT NULL DEFAULT '',
            btn2_url          TEXT    NOT NULL DEFAULT '',
            created_at        DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $count = (int) $pdo->query("SELECT COUNT(*) FROM hero_slides")->fetchColumn();
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
        INSERT INTO hero_slides
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

/* ---- Query helpers ---- */

function dgtec_slides_active(): array {
    return dgtec_db()
        ->query("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY position ASC")
        ->fetchAll();
}

function dgtec_slides_all(): array {
    return dgtec_db()
        ->query("SELECT * FROM hero_slides ORDER BY position ASC")
        ->fetchAll();
}

function dgtec_slide_get(int $id): array|false {
    $stmt = dgtec_db()->prepare("SELECT * FROM hero_slides WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function dgtec_slide_save(array $data): int {
    $db = dgtec_db();
    if (!empty($data['id'])) {
        $stmt = $db->prepare("
            UPDATE hero_slides SET
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
        return (int) $data['id'];
    } else {
        $stmt = $db->prepare("
            INSERT INTO hero_slides
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
        return (int) $db->lastInsertId();
    }
}

function dgtec_slide_delete(int $id): void {
    $stmt = dgtec_db()->prepare("DELETE FROM hero_slides WHERE id = ?");
    $stmt->execute([$id]);
}

function dgtec_slide_move(int $id, string $dir): void {
    $db  = dgtec_db();
    $all = $db->query("SELECT id, position FROM hero_slides ORDER BY position ASC")->fetchAll();
    $idx = array_search($id, array_column($all, 'id'));
    if ($idx === false) return;

    $swapIdx = ($dir === 'up') ? $idx - 1 : $idx + 1;
    if (!isset($all[$swapIdx])) return;

    $stmt = $db->prepare("UPDATE hero_slides SET position = ? WHERE id = ?");
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
