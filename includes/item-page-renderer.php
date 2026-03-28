<?php
/**
 * DGTEC — Renders structured page_content JSON for solution/service detail pages.
 * Called from each service/solution .php file when page_content JSON exists.
 */

function dgtec_render_item_overview(array $h): string {
    if (empty($h)) return '';

    $sub   = htmlspecialchars($h['sub_label']   ?? '');
    $title = nl2br(htmlspecialchars($h['title'] ?? ''));
    $desc  = htmlspecialchars($h['description'] ?? '');
    $ctaText = htmlspecialchars($h['cta_text']  ?? '');
    $ctaUrl  = htmlspecialchars($h['cta_url']   ?? 'contact.php');
    $image   = htmlspecialchars($h['image']     ?? '');

    $bulletHtml = '';
    foreach (($h['bullets'] ?? []) as $b) {
        $b = trim($b);
        if ($b !== '') {
            $bulletHtml .= '<li><i class="fas fa-check-circle"></i> ' . htmlspecialchars($b) . '</li>';
        }
    }

    $subHtml  = $sub        ? '<span class="section-label">' . $sub . '</span>' : '';
    $listHtml = $bulletHtml ? '<ul class="feature-list">' . $bulletHtml . '</ul>' : '';
    $btnHtml  = $ctaText    ? '<a href="' . $ctaUrl . '" class="btn btn-primary">' . $ctaText . ' <i class="fas fa-arrow-right"></i></a>' : '';
    $imgHtml  = $image      ? '<img src="' . $image . '" alt="" loading="lazy" />' : '';

    return '
    <section class="inner-overview">
      <div class="container">
        <div class="inner-overview-grid">
          <div class="inner-overview-text">
            ' . $subHtml . '
            <h2 class="section-title">' . $title . '</h2>
            ' . ($desc ? '<p class="section-desc">' . $desc . '</p>' : '') . '
            ' . $listHtml . '
            ' . $btnHtml . '
          </div>
          <div class="inner-overview-image">' . $imgHtml . '</div>
        </div>
      </div>
    </section>';
}

function dgtec_render_item_stats(array $stats): string {
    $items = '';
    foreach ($stats as $s) {
        $v = trim($s['value'] ?? '');
        $l = trim($s['label'] ?? '');
        if ($v === '' && $l === '') continue;
        $items .= '<div class="inner-highlight-item"><div class="num">'
            . htmlspecialchars($v) . '</div><p>' . htmlspecialchars($l) . '</p></div>';
    }
    if (!$items) return '';
    return '
    <section class="inner-highlights">
      <div class="container">
        <div class="inner-highlights-grid">' . $items . '</div>
      </div>
    </section>';
}

function dgtec_render_item_features(array $features): string {
    $cards = '';
    $autoNum = 0;
    foreach ($features as $f) {
        if (empty($f['title'])) continue;
        $autoNum++;
        $icon  = htmlspecialchars($f['icon']        ?? 'fas fa-star');
        $title = htmlspecialchars($f['title']        ?? '');
        $desc  = htmlspecialchars($f['description']  ?? '');
        $cards .= '
        <div class="inner-feature-card">
          <div class="inner-feature-icon"><i class="' . $icon . '"></i></div>
          <h4>' . $title . '</h4>
          ' . ($desc ? '<p>' . $desc . '</p>' : '') . '
        </div>';
    }
    if (!$cards) return '';
    return '
    <section class="inner-features">
      <div class="container">
        <div class="inner-features-header">
          <span class="section-label">What\'s Included</span>
          <h2 class="section-title">Key Features</h2>
        </div>
        <div class="inner-features-grid">' . $cards . '</div>
      </div>
    </section>';
}
