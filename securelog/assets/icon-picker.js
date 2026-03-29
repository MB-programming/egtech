/* ============================================================
   Icon Picker — FontAwesome 6 Free (Solid + Regular + Brands)
   ============================================================ */
(function (global) {

var FA_ICONS = [
  /* ---- Business & Finance ---- */
  {c:'fas fa-briefcase',       n:'briefcase'},
  {c:'fas fa-building',        n:'building'},
  {c:'fas fa-building-columns',n:'bank'},
  {c:'fas fa-chart-line',      n:'chart-line'},
  {c:'fas fa-chart-bar',       n:'chart-bar'},
  {c:'fas fa-chart-pie',       n:'chart-pie'},
  {c:'fas fa-coins',           n:'coins'},
  {c:'fas fa-dollar-sign',     n:'dollar'},
  {c:'fas fa-file-invoice',    n:'invoice'},
  {c:'fas fa-handshake',       n:'handshake'},
  {c:'fas fa-money-bill',      n:'money'},
  {c:'fas fa-piggy-bank',      n:'piggy-bank'},
  {c:'fas fa-receipt',         n:'receipt'},
  {c:'fas fa-sack-dollar',     n:'sack-dollar'},
  {c:'fas fa-scale-balanced',  n:'scale'},
  {c:'fas fa-store',           n:'store'},
  {c:'fas fa-tag',             n:'tag'},
  {c:'fas fa-tags',            n:'tags'},
  {c:'fas fa-truck',           n:'truck'},
  {c:'fas fa-warehouse',       n:'warehouse'},
  {c:'fas fa-percent',         n:'percent'},

  /* ---- Technology & Digital ---- */
  {c:'fas fa-code',            n:'code'},
  {c:'fas fa-laptop-code',     n:'laptop-code'},
  {c:'fas fa-microchip',       n:'microchip'},
  {c:'fas fa-database',        n:'database'},
  {c:'fas fa-server',          n:'server'},
  {c:'fas fa-cloud',           n:'cloud'},
  {c:'fas fa-cloud-arrow-up',  n:'cloud-upload'},
  {c:'fas fa-cloud-arrow-down',n:'cloud-download'},
  {c:'fas fa-wifi',            n:'wifi'},
  {c:'fas fa-network-wired',   n:'network'},
  {c:'fas fa-robot',           n:'robot'},
  {c:'fas fa-brain',           n:'brain'},
  {c:'fas fa-cpu',             n:'cpu'},
  {c:'fas fa-desktop',         n:'desktop'},
  {c:'fas fa-laptop',          n:'laptop'},
  {c:'fas fa-mobile-screen',   n:'mobile'},
  {c:'fas fa-tablet-screen-button', n:'tablet'},
  {c:'fas fa-hard-drive',      n:'hard-drive'},
  {c:'fas fa-terminal',        n:'terminal'},
  {c:'fas fa-bugs',            n:'bugs'},
  {c:'fas fa-sitemap',         n:'sitemap'},
  {c:'fas fa-qrcode',          n:'qrcode'},
  {c:'fas fa-barcode',         n:'barcode'},

  /* ---- AI & Automation ---- */
  {c:'fas fa-wand-magic-sparkles', n:'magic'},
  {c:'fas fa-bolt',            n:'bolt'},
  {c:'fas fa-bolt-lightning',  n:'lightning'},
  {c:'fas fa-gears',           n:'gears'},
  {c:'fas fa-gear',            n:'gear'},
  {c:'fas fa-cogs',            n:'cogs'},
  {c:'fas fa-cog',             n:'cog'},
  {c:'fas fa-sliders',         n:'sliders'},
  {c:'fas fa-shuffle',         n:'shuffle'},
  {c:'fas fa-rotate',          n:'rotate'},
  {c:'fas fa-infinity',        n:'infinity'},
  {c:'fas fa-diagram-project', n:'diagram'},
  {c:'fas fa-timeline',        n:'timeline'},

  /* ---- Data & Analytics ---- */
  {c:'fas fa-magnifying-glass-chart', n:'analytics'},
  {c:'fas fa-table',           n:'table'},
  {c:'fas fa-table-columns',   n:'table-columns'},
  {c:'fas fa-filter',          n:'filter'},
  {c:'fas fa-funnel',          n:'funnel'},
  {c:'fas fa-list',            n:'list'},
  {c:'fas fa-square-poll-vertical', n:'poll'},
  {c:'fas fa-file-csv',        n:'csv'},
  {c:'fas fa-file-excel',      n:'excel'},

  /* ---- People & Team ---- */
  {c:'fas fa-user',            n:'user'},
  {c:'fas fa-users',           n:'users'},
  {c:'fas fa-user-tie',        n:'user-tie'},
  {c:'fas fa-user-graduate',   n:'graduate'},
  {c:'fas fa-user-check',      n:'user-check'},
  {c:'fas fa-user-plus',       n:'user-plus'},
  {c:'fas fa-person',          n:'person'},
  {c:'fas fa-people-group',    n:'people-group'},
  {c:'fas fa-people-roof',     n:'people-roof'},
  {c:'fas fa-id-badge',        n:'id-badge'},
  {c:'fas fa-id-card',         n:'id-card'},
  {c:'fas fa-address-card',    n:'address-card'},

  /* ---- Communication ---- */
  {c:'fas fa-envelope',        n:'envelope'},
  {c:'fas fa-envelope-open',   n:'envelope-open'},
  {c:'fas fa-comment',         n:'comment'},
  {c:'fas fa-comments',        n:'comments'},
  {c:'fas fa-message',         n:'message'},
  {c:'fas fa-phone',           n:'phone'},
  {c:'fas fa-phone-volume',    n:'phone-volume'},
  {c:'fas fa-headset',         n:'headset'},
  {c:'fas fa-bell',            n:'bell'},
  {c:'fas fa-bullhorn',        n:'bullhorn'},
  {c:'fas fa-rss',             n:'rss'},
  {c:'fas fa-paper-plane',     n:'paper-plane'},
  {c:'fas fa-inbox',           n:'inbox'},
  {c:'fas fa-at',              n:'at'},

  /* ---- Navigation & Location ---- */
  {c:'fas fa-location-dot',    n:'location'},
  {c:'fas fa-map',             n:'map'},
  {c:'fas fa-map-pin',         n:'map-pin'},
  {c:'fas fa-globe',           n:'globe'},
  {c:'fas fa-earth-americas',  n:'earth'},
  {c:'fas fa-compass',         n:'compass'},
  {c:'fas fa-route',           n:'route'},
  {c:'fas fa-road',            n:'road'},

  /* ---- Security ---- */
  {c:'fas fa-shield',          n:'shield'},
  {c:'fas fa-shield-halved',   n:'shield-halved'},
  {c:'fas fa-lock',            n:'lock'},
  {c:'fas fa-lock-open',       n:'lock-open'},
  {c:'fas fa-key',             n:'key'},
  {c:'fas fa-user-shield',     n:'user-shield'},
  {c:'fas fa-fingerprint',     n:'fingerprint'},
  {c:'fas fa-eye',             n:'eye'},
  {c:'fas fa-eye-slash',       n:'eye-slash'},
  {c:'fas fa-bug',             n:'bug'},

  /* ---- Education & Knowledge ---- */
  {c:'fas fa-graduation-cap',  n:'graduation'},
  {c:'fas fa-book',            n:'book'},
  {c:'fas fa-book-open',       n:'book-open'},
  {c:'fas fa-chalkboard',      n:'chalkboard'},
  {c:'fas fa-lightbulb',       n:'lightbulb'},
  {c:'fas fa-atom',            n:'atom'},
  {c:'fas fa-flask',           n:'flask'},
  {c:'fas fa-microscope',      n:'microscope'},
  {c:'fas fa-pen',             n:'pen'},
  {c:'fas fa-pen-to-square',   n:'edit'},
  {c:'fas fa-pencil',          n:'pencil'},
  {c:'fas fa-school',          n:'school'},
  {c:'fas fa-certificate',     n:'certificate'},

  /* ---- Awards & Recognition ---- */
  {c:'fas fa-award',           n:'award'},
  {c:'fas fa-medal',           n:'medal'},
  {c:'fas fa-trophy',          n:'trophy'},
  {c:'fas fa-star',            n:'star'},
  {c:'fas fa-crown',           n:'crown'},
  {c:'fas fa-ribbon',          n:'ribbon'},
  {c:'fas fa-flag',            n:'flag'},
  {c:'fas fa-fire',            n:'fire'},
  {c:'fas fa-fire-flame-curved', n:'flame'},
  {c:'fas fa-gem',             n:'gem'},
  {c:'fas fa-heart',           n:'heart'},
  {c:'fas fa-thumbs-up',       n:'thumbs-up'},

  /* ---- Tools & Operations ---- */
  {c:'fas fa-wrench',          n:'wrench'},
  {c:'fas fa-screwdriver-wrench', n:'tools'},
  {c:'fas fa-hammer',          n:'hammer'},
  {c:'fas fa-toolbox',         n:'toolbox'},
  {c:'fas fa-ruler',           n:'ruler'},
  {c:'fas fa-ruler-combined',  n:'ruler-combined'},
  {c:'fas fa-tape',            n:'tape'},
  {c:'fas fa-scissors',        n:'scissors'},
  {c:'fas fa-puzzle-piece',    n:'puzzle'},
  {c:'fas fa-cubes',           n:'cubes'},
  {c:'fas fa-cube',            n:'cube'},
  {c:'fas fa-layer-group',     n:'layers'},
  {c:'fas fa-object-group',    n:'object-group'},

  /* ---- Documents & Files ---- */
  {c:'fas fa-file',            n:'file'},
  {c:'fas fa-file-lines',      n:'file-text'},
  {c:'fas fa-file-code',       n:'file-code'},
  {c:'fas fa-file-pdf',        n:'file-pdf'},
  {c:'fas fa-file-image',      n:'file-image'},
  {c:'fas fa-folder',          n:'folder'},
  {c:'fas fa-folder-open',     n:'folder-open'},
  {c:'fas fa-copy',            n:'copy'},
  {c:'fas fa-clipboard',       n:'clipboard'},
  {c:'fas fa-paste',           n:'paste'},
  {c:'fas fa-paperclip',       n:'paperclip'},
  {c:'fas fa-note-sticky',     n:'note'},

  /* ---- Media & Design ---- */
  {c:'fas fa-image',           n:'image'},
  {c:'fas fa-images',          n:'images'},
  {c:'fas fa-video',           n:'video'},
  {c:'fas fa-film',            n:'film'},
  {c:'fas fa-music',           n:'music'},
  {c:'fas fa-play',            n:'play'},
  {c:'fas fa-camera',          n:'camera'},
  {c:'fas fa-palette',         n:'palette'},
  {c:'fas fa-paint-roller',    n:'paint-roller'},
  {c:'fas fa-paintbrush',      n:'paintbrush'},
  {c:'fas fa-vector-square',   n:'vector'},
  {c:'fas fa-wand-sparkles',   n:'wand'},
  {c:'fas fa-crop',            n:'crop'},

  /* ---- Healthcare ---- */
  {c:'fas fa-heart-pulse',     n:'heartbeat'},
  {c:'fas fa-stethoscope',     n:'stethoscope'},
  {c:'fas fa-hospital',        n:'hospital'},
  {c:'fas fa-pills',           n:'pills'},
  {c:'fas fa-syringe',         n:'syringe'},
  {c:'fas fa-user-doctor',     n:'doctor'},
  {c:'fas fa-kit-medical',     n:'first-aid'},
  {c:'fas fa-teeth',           n:'teeth'},
  {c:'fas fa-eye-dropper',     n:'eye-dropper'},

  /* ---- Nature & Environment ---- */
  {c:'fas fa-leaf',            n:'leaf'},
  {c:'fas fa-seedling',        n:'seedling'},
  {c:'fas fa-tree',            n:'tree'},
  {c:'fas fa-sun',             n:'sun'},
  {c:'fas fa-moon',            n:'moon'},
  {c:'fas fa-cloud-sun',       n:'cloud-sun'},
  {c:'fas fa-snowflake',       n:'snowflake'},
  {c:'fas fa-water',           n:'water'},
  {c:'fas fa-wind',            n:'wind'},
  {c:'fas fa-recycle',         n:'recycle'},
  {c:'fas fa-solar-panel',     n:'solar-panel'},

  /* ---- Transport ---- */
  {c:'fas fa-car',             n:'car'},
  {c:'fas fa-plane',           n:'plane'},
  {c:'fas fa-ship',            n:'ship'},
  {c:'fas fa-train',           n:'train'},
  {c:'fas fa-bicycle',         n:'bicycle'},
  {c:'fas fa-rocket',          n:'rocket'},
  {c:'fas fa-helicopter',      n:'helicopter'},

  /* ---- UI / Actions ---- */
  {c:'fas fa-check',           n:'check'},
  {c:'fas fa-check-circle',    n:'check-circle'},
  {c:'fas fa-xmark',           n:'xmark'},
  {c:'fas fa-circle-xmark',    n:'circle-xmark'},
  {c:'fas fa-plus',            n:'plus'},
  {c:'fas fa-minus',           n:'minus'},
  {c:'fas fa-arrow-right',     n:'arrow-right'},
  {c:'fas fa-arrow-up',        n:'arrow-up'},
  {c:'fas fa-arrow-down',      n:'arrow-down'},
  {c:'fas fa-arrow-left',      n:'arrow-left'},
  {c:'fas fa-rotate-left',     n:'undo'},
  {c:'fas fa-rotate-right',    n:'redo'},
  {c:'fas fa-search',          n:'search'},
  {c:'fas fa-magnifying-glass',n:'magnify'},
  {c:'fas fa-bars',            n:'bars'},
  {c:'fas fa-ellipsis',        n:'ellipsis'},
  {c:'fas fa-ellipsis-vertical',n:'ellipsis-v'},
  {c:'fas fa-sort',            n:'sort'},
  {c:'fas fa-upload',          n:'upload'},
  {c:'fas fa-download',        n:'download'},
  {c:'fas fa-share',           n:'share'},
  {c:'fas fa-link',            n:'link'},
  {c:'fas fa-external-link',   n:'external-link'},
  {c:'fas fa-expand',          n:'expand'},
  {c:'fas fa-compress',        n:'compress'},
  {c:'fas fa-home',            n:'home'},
  {c:'fas fa-house',           n:'house'},
  {c:'fas fa-info',            n:'info'},
  {c:'fas fa-info-circle',     n:'info-circle'},
  {c:'fas fa-question',        n:'question'},
  {c:'fas fa-circle-question', n:'question-circle'},
  {c:'fas fa-exclamation',     n:'exclamation'},
  {c:'fas fa-triangle-exclamation', n:'warning'},
  {c:'fas fa-trash',           n:'trash'},
  {c:'fas fa-trash-can',       n:'trash-can'},
  {c:'fas fa-floppy-disk',     n:'save'},
  {c:'fas fa-print',           n:'print'},
  {c:'fas fa-power-off',       n:'power'},
  {c:'fas fa-right-from-bracket', n:'logout'},
  {c:'fas fa-right-to-bracket', n:'login'},
  {c:'fas fa-calendar',        n:'calendar'},
  {c:'fas fa-calendar-days',   n:'calendar-days'},
  {c:'fas fa-clock',           n:'clock'},
  {c:'fas fa-stopwatch',       n:'stopwatch'},
  {c:'fas fa-hourglass',       n:'hourglass'},
  {c:'fas fa-bell-slash',      n:'bell-slash'},
  {c:'fas fa-ban',             n:'ban'},
  {c:'fas fa-circle',          n:'circle'},
  {c:'fas fa-square',          n:'square'},

  /* ---- Social & Brands ---- */
  {c:'fab fa-twitter',         n:'twitter'},
  {c:'fab fa-x-twitter',       n:'x-twitter'},
  {c:'fab fa-facebook',        n:'facebook'},
  {c:'fab fa-instagram',       n:'instagram'},
  {c:'fab fa-linkedin',        n:'linkedin'},
  {c:'fab fa-youtube',         n:'youtube'},
  {c:'fab fa-tiktok',          n:'tiktok'},
  {c:'fab fa-whatsapp',        n:'whatsapp'},
  {c:'fab fa-telegram',        n:'telegram'},
  {c:'fab fa-snapchat',        n:'snapchat'},
  {c:'fab fa-github',          n:'github'},
  {c:'fab fa-gitlab',          n:'gitlab'},
  {c:'fab fa-slack',           n:'slack'},
  {c:'fab fa-google',          n:'google'},
  {c:'fab fa-apple',           n:'apple'},
  {c:'fab fa-microsoft',       n:'microsoft'},
  {c:'fab fa-aws',             n:'aws'},
  {c:'fab fa-docker',          n:'docker'},
  {c:'fab fa-python',          n:'python'},
  {c:'fab fa-js',              n:'javascript'},
  {c:'fab fa-react',           n:'react'},
  {c:'fab fa-node',            n:'nodejs'},
  {c:'fab fa-php',             n:'php'},
  {c:'fab fa-wordpress',       n:'wordpress'},
  {c:'fab fa-shopify',         n:'shopify'},
  {c:'fab fa-stripe',          n:'stripe'},
  {c:'fab fa-paypal',          n:'paypal'},
  {c:'fab fa-android',         n:'android'},
  {c:'fab fa-app-store',       n:'app-store'},
  {c:'fab fa-google-play',     n:'google-play'},
];

/* ---- State ---- */
var _pickerTarget = null;   // { input, preview }
var _rendered     = false;

/* ---- Build DOM once ---- */
function _buildModal() {
  if (document.getElementById('_iconPickerOverlay')) return;

  var overlay = document.createElement('div');
  overlay.id        = '_iconPickerOverlay';
  overlay.className = 'icon-picker-overlay';
  overlay.innerHTML =
    '<div class="icon-picker-modal">' +
      '<div class="icon-picker-head">' +
        '<h3><i class="fas fa-icons" style="color:#03869e;margin-right:6px"></i> اختر أيقونة</h3>' +
        '<input type="text" class="icon-picker-search" id="_iconPickerSearch" placeholder="ابحث عن أيقونة..." />' +
        '<span class="icon-picker-count" id="_iconPickerCount"></span>' +
        '<button class="icon-picker-close" onclick="window.closeIconPicker()"><i class="fas fa-xmark"></i></button>' +
      '</div>' +
      '<div class="icon-picker-body">' +
        '<div class="icon-picker-grid" id="_iconPickerGrid"></div>' +
      '</div>' +
    '</div>';

  document.body.appendChild(overlay);

  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) window.closeIconPicker();
  });

  document.getElementById('_iconPickerSearch').addEventListener('input', function () {
    _renderIcons(this.value.trim().toLowerCase());
  });

  _renderIcons('');
}

function _renderIcons(q) {
  var grid  = document.getElementById('_iconPickerGrid');
  var count = document.getElementById('_iconPickerCount');
  var cur   = _pickerTarget ? _pickerTarget.input.value.trim() : '';

  var list = q
    ? FA_ICONS.filter(function (ic) { return ic.n.indexOf(q) !== -1 || ic.c.indexOf(q) !== -1; })
    : FA_ICONS;

  count.textContent = list.length + ' أيقونة';

  if (!list.length) {
    grid.innerHTML = '<div class="icon-picker-empty"><i class="fas fa-magnifying-glass" style="font-size:28px;color:#d1d5db;display:block;margin-bottom:8px"></i>لا توجد نتائج</div>';
    return;
  }

  grid.innerHTML = list.map(function (ic) {
    return '<div class="icon-picker-item' + (ic.c === cur ? ' selected' : '') + '" ' +
           'data-class="' + ic.c + '" title="' + ic.c + '" onclick="window._pickIcon(\'' + ic.c + '\')">' +
           '<i class="' + ic.c + '"></i>' +
           '<span>' + ic.n + '</span>' +
           '</div>';
  }).join('');
}

/* ---- Public API ---- */
global.showIconPicker = function (triggerBtn) {
  var wrap  = triggerBtn.closest('.icon-field-wrap');
  var input = wrap ? wrap.querySelector('input[type="text"]') : null;
  var prev  = wrap ? wrap.querySelector('.icon-field-preview') : null;

  if (!input) return;
  _pickerTarget = { input: input, preview: prev };

  _buildModal();
  document.getElementById('_iconPickerSearch').value = '';
  _renderIcons('');

  document.getElementById('_iconPickerOverlay').classList.add('open');
  setTimeout(function () {
    document.getElementById('_iconPickerSearch').focus();
    /* scroll to selected */
    var sel = document.querySelector('.icon-picker-item.selected');
    if (sel) sel.scrollIntoView({ block: 'center' });
  }, 80);
};

global._pickIcon = function (cls) {
  if (!_pickerTarget) return;
  _pickerTarget.input.value = cls;
  if (_pickerTarget.preview) {
    _pickerTarget.preview.className = 'icon-field-preview ' + cls;
  }
  /* fire oninput so any existing preview handlers run */
  _pickerTarget.input.dispatchEvent(new Event('input'));
  global.closeIconPicker();
};

global.closeIconPicker = function () {
  var ov = document.getElementById('_iconPickerOverlay');
  if (ov) ov.classList.remove('open');
};

/* ESC to close */
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') global.closeIconPicker();
});

}(window));
