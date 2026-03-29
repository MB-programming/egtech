<?php
require_once 'includes/admin-db.php';

$seo_page_key = 'careers';
$page_title   = 'Careers – DGTEC';
$page_desc    = 'Explore open positions at DGTEC and join our team of professionals driving digital transformation.';

$jobs = dgtec_careers_active();

/* group by department */
$byDept = [];
foreach ($jobs as $j) {
    $byDept[$j['department'] ?: 'General'][] = $j;
}

include 'includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" style="background:linear-gradient(135deg,var(--primary) 0%,#0d6eaa 100%);padding:80px 0;text-align:center;color:#fff">
  <div class="container">
    <span class="section-label" style="color:rgba(255,255,255,.8);background:rgba(255,255,255,.15);padding:4px 14px;border-radius:20px;font-size:13px;font-weight:600">Join Our Team</span>
    <h1 style="font-size:clamp(32px,5vw,52px);font-weight:800;margin:16px 0 16px;line-height:1.2">Shape the Future With Us</h1>
    <p style="font-size:18px;opacity:.85;max-width:560px;margin:0 auto">We're always looking for talented people to help us deliver technology solutions that make a real difference.</p>
  </div>
</section>

<!-- Jobs Section -->
<section style="padding:80px 0;background:var(--light-gray)">
  <div class="container">

    <?php if (empty($jobs)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--gray)">
      <i class="fas fa-briefcase" style="font-size:48px;display:block;margin-bottom:16px;color:#d1d5db"></i>
      <h3 style="font-size:22px;margin-bottom:8px;color:var(--dark)">No open positions right now</h3>
      <p>Check back soon or send us your CV at <a href="mailto:<?= htmlspecialchars($_site['email'] ?? '') ?>" style="color:var(--btn)"><?= htmlspecialchars($_site['email'] ?? 'careers@dgtec.sa') ?></a></p>
    </div>

    <?php else: ?>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:40px">
      <div>
        <h2 style="font-size:28px;font-weight:800;color:var(--primary)"><?= count($jobs) ?> Open Position<?= count($jobs) !== 1 ? 's' : '' ?></h2>
        <p style="color:var(--gray);margin-top:4px">Find your next opportunity at DGTEC</p>
      </div>
      <!-- quick search -->
      <input type="text" id="jobSearch" placeholder="Search jobs…"
             oninput="filterJobs(this.value)"
             style="padding:10px 16px;border:1.5px solid #d0d7e3;border-radius:8px;font-size:14px;outline:none;min-width:220px" />
    </div>

    <?php foreach ($byDept as $dept => $dJobs): ?>
    <div class="dept-group" data-dept="<?= htmlspecialchars($dept) ?>" style="margin-bottom:40px">
      <h3 style="font-size:16px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #e5e9f0">
        <i class="fas fa-folder-open" style="color:var(--btn);margin-right:6px"></i><?= htmlspecialchars($dept) ?>
      </h3>
      <?php foreach ($dJobs as $job): ?>
      <a href="career-detail.php?slug=<?= urlencode($job['slug']) ?>"
         class="job-card" data-title="<?= htmlspecialchars(strtolower($job['title'])) ?>"
         data-dept="<?= htmlspecialchars(strtolower($dept)) ?>"
         style="display:flex;align-items:center;justify-content:space-between;gap:16px;background:#fff;border:1.5px solid #e5e9f0;border-radius:12px;padding:20px 24px;margin-bottom:12px;text-decoration:none;color:inherit;transition:.2s;flex-wrap:wrap">
        <div style="flex:1;min-width:200px">
          <h4 style="font-size:17px;font-weight:700;color:var(--primary);margin-bottom:6px"><?= htmlspecialchars($job['title']) ?></h4>
          <div style="display:flex;flex-wrap:wrap;gap:10px">
            <?php if ($job['location']): ?>
            <span style="font-size:13px;color:var(--gray)"><i class="fas fa-location-dot" style="color:var(--btn);margin-right:4px"></i><?= htmlspecialchars($job['location']) ?></span>
            <?php endif; ?>
            <span style="font-size:13px;color:var(--gray)"><i class="fas fa-clock" style="color:var(--btn);margin-right:4px"></i><?= htmlspecialchars($job['job_type']) ?></span>
          </div>
        </div>
        <span style="white-space:nowrap;padding:8px 20px;background:var(--btn);color:#fff;border-radius:8px;font-size:13px;font-weight:600;flex-shrink:0">
          Apply Now <i class="fas fa-arrow-right" style="margin-left:4px"></i>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <div id="noResults" style="display:none;text-align:center;padding:40px 0;color:var(--gray)">
      <i class="fas fa-magnifying-glass" style="font-size:32px;display:block;margin-bottom:10px;color:#d1d5db"></i>
      No jobs match your search.
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- CTA -->
<section style="padding:70px 0;background:#fff;text-align:center">
  <div class="container" style="max-width:600px">
    <i class="fas fa-envelope" style="font-size:36px;color:var(--btn);margin-bottom:16px;display:block"></i>
    <h2 style="font-size:26px;font-weight:800;color:var(--primary);margin-bottom:12px">Don't see the right role?</h2>
    <p style="color:var(--gray);margin-bottom:24px">Send your CV to <strong><?= htmlspecialchars($_site['email'] ?? 'careers@dgtec.sa') ?></strong> and we'll keep it on file.</p>
    <a href="contact.php" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Contact Us</a>
  </div>
</section>

<style>
.job-card:hover { border-color:var(--btn)!important; box-shadow:0 4px 20px rgba(3,134,158,.12); transform:translateY(-2px); }
</style>
<script>
function filterJobs(q) {
  q = q.toLowerCase().trim();
  var cards   = document.querySelectorAll('.job-card');
  var groups  = document.querySelectorAll('.dept-group');
  var noRes   = document.getElementById('noResults');
  var anyVis  = false;
  cards.forEach(function(c){
    var match = !q || c.dataset.title.indexOf(q) !== -1 || c.dataset.dept.indexOf(q) !== -1;
    c.style.display = match ? '' : 'none';
    if (match) anyVis = true;
  });
  groups.forEach(function(g){
    var vis = [...g.querySelectorAll('.job-card')].some(function(c){ return c.style.display !== 'none'; });
    g.style.display = vis ? '' : 'none';
  });
  if (noRes) noRes.style.display = anyVis ? 'none' : '';
}
</script>

<?php include 'includes/footer.php'; ?>
