<?php
/**
 * Blog post preview for drafts — admin-only, token-protected.
 * URL: blog-preview.php?id=N&token=XXXXX
 */
session_name('dgtec_admin_sess');
ini_set('session.cookie_httponly', '1');
session_start();

require_once 'includes/admin-db.php';

/* Verify admin session */
if (empty($_SESSION['dgtec_admin_auth'])) {
    http_response_code(403);
    die('Access denied. <a href="securelog/">Admin login required.</a>');
}

$id    = (int)($_GET['id'] ?? 0);
$token = trim($_GET['token'] ?? '');

if (!$id || !$token) { http_response_code(404); die('Not found.'); }

/* Validate token stored in session */
$validToken = $_SESSION['blog_preview_tokens'][$id] ?? '';
if (!$validToken || !hash_equals($validToken, $token)) {
    http_response_code(403);
    die('Invalid or expired preview token. <a href="securelog/blog-form.php?id=' . $id . '">Go back to editor.</a>');
}

/* Load post regardless of is_active */
$db   = dgtec_db();
$stmt = $db->prepare("SELECT * FROM blog_posts WHERE id=?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) { http_response_code(404); die('Post not found.'); }

$seo_page_key = '_preview'; /* no real SEO for previews */
$page_title   = htmlspecialchars($post['title']) . ' [PREVIEW]';

include 'includes/header.php';
?>

<!-- Preview Banner -->
<div style="background:#f59e0b;color:#fff;text-align:center;padding:10px 20px;font-size:14px;font-weight:700;position:sticky;top:0;z-index:999">
  <i class="fas fa-eye"></i> PREVIEW MODE — This post is not yet published.
  <a href="securelog/blog-form.php?id=<?= $post['id'] ?>" style="color:#fff;text-decoration:underline;margin-left:12px">← Back to Editor</a>
</div>

<?php
/* Re-use the blog-post.php rendering logic inline */
$tags = array_filter(array_map('trim', explode(',', $post['tags'] ?? '')));

/* include blog-post template — simplified inline render */
?>
<section class="blog-hero" style="padding:60px 0 40px;background:var(--light-gray)">
  <div class="container" style="max-width:860px">
    <?php if ($post['category']): ?>
    <span class="section-label" style="display:inline-block;margin-bottom:12px"><?= htmlspecialchars($post['category']) ?></span>
    <?php endif; ?>
    <h1 style="font-size:clamp(26px,4vw,42px);font-weight:800;color:var(--primary);line-height:1.25;margin-bottom:16px"><?= htmlspecialchars($post['title']) ?></h1>
    <p style="font-size:17px;color:var(--gray);line-height:1.7;margin-bottom:20px"><?= htmlspecialchars($post['excerpt']) ?></p>
    <div style="font-size:13px;color:var(--gray)">
      <?= $post['published_at'] ? date('d F Y', strtotime($post['published_at'])) : 'Not published yet' ?>
    </div>
  </div>
</section>

<?php if ($post['image']): ?>
<div style="max-width:860px;margin:0 auto;padding:0 20px">
  <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
       style="width:100%;border-radius:14px;margin-top:-20px;box-shadow:var(--shadow)" loading="lazy" />
</div>
<?php endif; ?>

<section style="padding:48px 0 80px">
  <div class="container" style="max-width:860px">
    <div class="blog-content" style="font-size:16px;line-height:1.85;color:var(--dark)">
      <?= $post['content'] ?>
    </div>

    <?php if ($tags): ?>
    <div style="margin-top:32px;padding-top:24px;border-top:1px solid var(--border);display:flex;flex-wrap:wrap;gap:8px;align-items:center">
      <span style="font-size:13px;color:var(--gray);font-weight:600">Tags:</span>
      <?php foreach ($tags as $tag): ?>
      <span style="padding:4px 12px;background:var(--light-gray);border-radius:20px;font-size:13px;color:var(--gray)"><?= htmlspecialchars($tag) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
