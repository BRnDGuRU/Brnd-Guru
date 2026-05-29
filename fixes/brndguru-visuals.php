<?php
/**
 * Plugin Name: BrndGuru — Visual Enhancements
 * Description: Adds stock photos, background patterns, and visual depth to all BRND GURU pages. AUTO-RUNS on activation.
 * Version: 2.1
 */
if (!defined('ABSPATH')) exit;

// ── Inject CSS globally ───────────────────────────────────────
add_action('wp_head', function () { ?>
<style id="bg-visuals-css">
/* ── Hide accidental empty strips ── */
.bg-dot-grid:empty,.bg-photo-full:empty,section:empty { display:none !important; }
.bg-dot-grid { margin-top:0; }

/* ─────────────────────────────────────────
   BACKGROUND PATTERN UTILITIES
───────────────────────────────────────── */

/* Fine dot grid */
.bg-dot-grid {
    background-color: #0d0d0d;
    background-image: radial-gradient(circle, rgba(255,255,255,0.07) 1px, transparent 1px);
    background-size: 28px 28px;
}

/* Cross-hatch grid */
.bg-grid-lines {
    background-image:
        linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
    background-size: 60px 60px;
}

/* Diagonal stripe */
.bg-stripe {
    background-image: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 8px,
        rgba(228,82,43,0.04) 8px,
        rgba(228,82,43,0.04) 16px
    );
}

/* Noise texture overlay */
.bg-noise::after {
    content:'';
    position:absolute;
    inset:0;
    background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
    pointer-events:none;
    z-index:0;
}

/* ─────────────────────────────────────────
   GLOW & ORBS
───────────────────────────────────────── */

/* Orange glow top-right */
.bg-glow-blob { position:relative; overflow:hidden; }
.bg-glow-blob::before {
    content:'';
    position:absolute;
    width:700px; height:700px;
    background:radial-gradient(circle, rgba(228,82,43,0.13) 0%, transparent 68%);
    top:-280px; right:-180px;
    border-radius:50%;
    pointer-events:none;
    z-index:0;
}

/* Dual orb: orange left + faint blue-grey right */
.bg-dual-orb { position:relative; overflow:hidden; }
.bg-dual-orb::before {
    content:'';
    position:absolute;
    width:500px; height:500px;
    background:radial-gradient(circle, rgba(228,82,43,0.10) 0%, transparent 70%);
    bottom:-150px; left:-80px;
    border-radius:50%;
    pointer-events:none;
    z-index:0;
}
.bg-dual-orb::after {
    content:'';
    position:absolute;
    width:400px; height:400px;
    background:radial-gradient(circle, rgba(80,80,120,0.08) 0%, transparent 70%);
    top:-100px; right:-60px;
    border-radius:50%;
    pointer-events:none;
    z-index:0;
}

/* ─────────────────────────────────────────
   DECORATIVE LINES & BORDERS
───────────────────────────────────────── */
.bg-geo-line {
    height:3px;
    background:linear-gradient(90deg, #e4522b, transparent);
    width:80px;
    margin:0 0 24px;
    border-radius:2px;
}
.bg-geo-line-center {
    height:2px;
    background:linear-gradient(90deg, transparent, #e4522b 50%, transparent);
    width:120px;
    margin:0 auto 28px;
    border-radius:2px;
}
.bg-corner-accent {
    position:relative;
}
.bg-corner-accent::before {
    content:'';
    position:absolute;
    top:0; left:0;
    width:40px; height:40px;
    border-top:2px solid #e4522b;
    border-left:2px solid #e4522b;
    pointer-events:none;
}

/* ─────────────────────────────────────────
   PHOTO CARDS
───────────────────────────────────────── */
.bg-photo-card {
    overflow:hidden;
    border-radius:12px;
    transition:transform .25s ease, box-shadow .25s ease;
    position:relative;
}
.bg-photo-card::before {
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.7));
    z-index:1;
    pointer-events:none;
}
.bg-photo-card:hover {
    transform:translateY(-5px);
    box-shadow:0 24px 64px rgba(228,82,43,0.22);
}
.bg-photo-card img {
    width:100%;
    height:260px;
    object-fit:cover;
    display:block;
    transition:transform .45s ease;
}
.bg-photo-card:hover img { transform:scale(1.06); }

/* ─────────────────────────────────────────
   FULL-BLEED PHOTO BANNER
───────────────────────────────────────── */
.bg-photo-full {
    position:relative;
    min-height:440px;
    background-size:cover;
    background-position:center;
    background-attachment:fixed;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}
.bg-photo-full::before {
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(135deg, rgba(8,8,8,0.88) 0%, rgba(228,82,43,0.15) 100%);
    z-index:1;
}
/* subtle scanline overlay */
.bg-photo-full::after {
    content:'';
    position:absolute;
    inset:0;
    background-image:repeating-linear-gradient(
        0deg, rgba(0,0,0,0.06) 0px, rgba(0,0,0,0.06) 1px, transparent 1px, transparent 3px
    );
    z-index:2;
    pointer-events:none;
}
.bg-photo-full .inner {
    position:relative;
    z-index:3;
    text-align:center;
    padding:70px 40px;
    max-width:720px;
}

/* ─────────────────────────────────────────
   MISC
───────────────────────────────────────── */
.bg-float-badge {
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:rgba(228,82,43,0.10);
    border:1px solid rgba(228,82,43,0.28);
    color:#e4522b;
    font-size:11px;
    font-weight:700;
    letter-spacing:1.8px;
    text-transform:uppercase;
    padding:6px 18px;
    border-radius:999px;
    margin-bottom:20px;
}
.bg-stat-big {
    font-size:clamp(48px,8vw,80px);
    font-weight:900;
    color:#e4522b;
    line-height:1;
    font-style:italic;
}
.bg-wave-divider { line-height:0; overflow:hidden; }
.bg-wave-divider svg { display:block; }

@media (max-width:767px) {
    .bg-photo-full { background-attachment:scroll; min-height:320px; }
    .bg-photo-grid { flex-direction:column !important; }
    .bg-glow-blob::before, .bg-dual-orb::before, .bg-dual-orb::after { display:none; }
}
</style>
<?php });

// ── Global background graphic pass ───────────────────────────
// Injects dot grid + glow orbs onto the page via wp_footer (pure CSS, no DOM mutation)
add_action('wp_footer', function () {
    if (is_admin()) return;
    ?>
<style id="bg-global-graphics">
/* ── Dot grid on every dark Elementor section ── */
.elementor-section[data-settings*='"background_color":"#0d0d0d"'],
.elementor-section[data-settings*='"background_color":"#0a0a0a"'],
.elementor-section[data-settings*='"background_color":"#111111"'],
.elementor-section[data-settings*='"background_color":"#121212"'],
.elementor-top-section {
    background-image:
        radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px) !important;
    background-size: 32px 32px !important;
    background-blend-mode: normal;
}

/* ── Subtle top-left corner glow on body ── */
body::before {
    content:'';
    position:fixed;
    top:-200px; left:-200px;
    width:700px; height:700px;
    background:radial-gradient(circle, rgba(228,82,43,0.06) 0%, transparent 65%);
    border-radius:50%;
    pointer-events:none;
    z-index:0;
    animation: bgGlowPulse 8s ease-in-out infinite alternate;
}
@keyframes bgGlowPulse {
    0%   { opacity: 0.6; transform: scale(1); }
    100% { opacity: 1;   transform: scale(1.15); }
}

/* ── Bottom-right accent orb ── */
body::after {
    content:'';
    position:fixed;
    bottom:-150px; right:-150px;
    width:500px; height:500px;
    background:radial-gradient(circle, rgba(228,82,43,0.05) 0%, transparent 65%);
    border-radius:50%;
    pointer-events:none;
    z-index:0;
}

/* ── Orange top-edge line ── */
.elementor-first-section,
.elementor-top-section:first-of-type,
.elementor-section:first-child {
    border-top: 2px solid rgba(228,82,43,0.3) !important;
}

/* ── Thin orange left-border accent on every section ── */
.elementor-section:nth-child(even) {
    border-left: 2px solid rgba(228,82,43,0.08) !important;
}
</style>
<script id="bg-global-graphics-js">
(function(){
  /* Apply dot-grid class to all dark-background Elementor sections */
  function applyGraphics(){
    var secs = document.querySelectorAll(
      '.elementor-section, .elementor-top-section, .e-con, [data-elementor-type]'
    );
    for(var i=0;i<secs.length;i++){
      var el = secs[i];
      var bg = getComputedStyle(el).backgroundColor;
      var m = bg.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
      if(!m) continue;
      var r=+m[1],g=+m[2],b=+m[3];
      var lum=(0.2126*r+0.7152*g+0.0722*b)/255;
      if(lum<0.10){ /* very dark sections get dot grid */
        el.style.backgroundImage='radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px)';
        el.style.backgroundSize='32px 32px';
      }
    }
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',applyGraphics);
  else applyGraphics();
  setTimeout(applyGraphics,800);
})();
</script>
<?php }, 20);

// ── Inject visuals into page content ─────────────────────────
// Priority 20 = runs AFTER Elementor renders (priority 10), so we see full HTML
add_filter('the_content', function ($content) {
    // Only run on singular pages, not archives/loops
    if (!is_singular()) return $content;
    $id = get_the_ID();

    // Photos chosen per page context
    $visuals = [

        /* ── HOME ────────────────────────────────── */
        12 => [
            'after_hero' => bg_photo_strip([
                ['url' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80&fit=crop', 'label' => 'Strategy Sessions'],
                ['url' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=800&q=80&fit=crop', 'label' => 'Outbound Execution'],
                ['url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80&fit=crop', 'label' => 'Pipeline Analytics'],
            ]),
            'mid_banner' => bg_photo_full(
                'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&q=80&fit=crop',
                'Every engagement starts with your pipeline goal.',
                'We build the infrastructure. You close the deals.'
            ),
        ],

        /* ── ABOUT ───────────────────────────────── */
        1628 => [
            'after_hero' => bg_photo_strip([
                ['url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80&fit=crop', 'label' => 'Our Team'],
                ['url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&q=80&fit=crop', 'label' => 'London HQ'],
                ['url' => 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=800&q=80&fit=crop', 'label' => 'Remote First'],
            ]),
            'mid_banner' => bg_photo_full(
                'https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=1600&q=80&fit=crop',
                'We are BRND GURU.',
                'A London-based B2B consulting agency obsessed with one thing — filling your pipeline.'
            ),
        ],

        /* ── SERVICES ────────────────────────────── */
        4325 => [
            'after_hero' => bg_photo_strip([
                ['url' => 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=800&q=80&fit=crop', 'label' => 'LinkedIn Automation'],
                ['url' => 'https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=800&q=80&fit=crop', 'label' => 'Cold Email'],
                ['url' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80&fit=crop', 'label' => 'AI Agents'],
                ['url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80&fit=crop', 'label' => 'CRM & Automation'],
            ]),
            'mid_banner' => bg_photo_full(
                'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&q=80&fit=crop',
                'Every service is built around one outcome.',
                'More qualified conversations with your ideal buyers.'
            ),
        ],

        /* ── PORTFOLIO ───────────────────────────── */
        1483 => [
            'after_hero' => bg_photo_strip([
                ['url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80&fit=crop', 'label' => 'Pipeline Growth'],
                ['url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80&fit=crop', 'label' => 'CRM Results'],
                ['url' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80&fit=crop', 'label' => 'AI Automation'],
            ]),
            'mid_banner' => bg_photo_full(
                'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1600&q=80&fit=crop',
                'Results speak louder than promises.',
                '200+ B2B clients. £50M+ pipeline generated. 98% retention rate.'
            ),
        ],

        /* ── CONTACT ─────────────────────────────── */
        23 => [
            'mid_banner' => bg_photo_full(
                'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=1600&q=80&fit=crop',
                'Based in London. Working globally.',
                'UK · Europe · North America · Australia'
            ),
        ],

        /* ── SERVICE SUB-PAGES ───────────────────── */
        762  => ['mid_banner' => bg_photo_full('https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=1600&q=80&fit=crop', 'LinkedIn Outreach at Scale.', 'Personalised. Automated. Booked.')],
        724  => ['mid_banner' => bg_photo_full('https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=1600&q=80&fit=crop', 'Cold Email That Converts.', 'Infrastructure built for deliverability and replies.')],
        766  => ['mid_banner' => bg_photo_full('https://images.unsplash.com/photo-1518770660439-4636190af475?w=1600&q=80&fit=crop', 'AI Agents Running 24/7.', 'Research. Personalise. Follow-up. Automatically.')],
        765  => ['mid_banner' => bg_photo_full('https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1600&q=80&fit=crop', 'Your Pipeline. Fully Automated.', 'GoHighLevel built for how you sell.')],
        2970 => ['mid_banner' => bg_photo_full('https://images.unsplash.com/photo-1518770660439-4636190af475?w=1600&q=80&fit=crop', 'Connect Everything. Automate Everything.', 'n8n workflows that eliminate manual work.')],
    ];

    if (!isset($visuals[$id])) return $content;

    $v = $visuals[$id];

    // ── Photo strip: inject after the SECOND </section> so it goes below the hero ──
    if (!empty($v['after_hero'])) {
        $first  = strpos($content, '</section>');
        if ($first !== false) {
            $second = strpos($content, '</section>', $first + 10);
            $insert_at = ($second !== false) ? $second + 10 : $first + 10;
            $content = substr($content, 0, $insert_at) . $v['after_hero'] . substr($content, $insert_at);
        } else {
            // No section tags — append at end
            $content .= $v['after_hero'];
        }
    }

    // ── Full-bleed banner: insert before the LAST <section> (before CTA) ──
    if (!empty($v['mid_banner'])) {
        // Find second-to-last <section> occurrence for a mid-page position
        $positions = [];
        $offset = 0;
        while (($pos = strpos($content, '<section', $offset)) !== false) {
            $positions[] = $pos;
            $offset = $pos + 1;
        }
        // Insert before the last section
        if (count($positions) >= 2) {
            $insert_at = end($positions);
            $content = substr($content, 0, $insert_at) . $v['mid_banner'] . substr($content, $insert_at);
        } else {
            $content .= $v['mid_banner'];
        }
    }

    return $content;
}, 20); /* priority 20 — after Elementor renders at 10 */

/* ─── Helper: 3-4 photo strip ─────────────────────────────── */
function bg_photo_strip(array $photos): string {
    $cards = '';
    foreach ($photos as $p) {
        $cards .= '
        <div class="bg-photo-card" style="flex:1 1 220px;max-width:320px;">
            <img src="' . esc_url($p['url']) . '" alt="' . esc_attr($p['label']) . '" loading="lazy">
            <div style="background:#111;padding:14px 20px;border-top:2px solid #e4522b;position:relative;z-index:2;">
                <div class="bg-geo-line" style="width:32px;height:2px;margin:0 0 6px;"></div>
                <p style="color:#fff;font-size:13px;font-weight:700;margin:0;letter-spacing:.5px;">' . esc_html($p['label']) . '</p>
            </div>
        </div>';
    }
    return '
<div class="bg-dot-grid bg-glow-blob" style="background-color:#0d0d0d;padding:72px 40px;position:relative;overflow:hidden;">
    <div class="bg-geo-line-center"></div>
    <div style="max-width:1200px;margin:0 auto;display:flex;flex-wrap:wrap;gap:28px;justify-content:center;position:relative;z-index:1;">
        ' . $cards . '
    </div>
</div>';
}

/* ─── Helper: full-bleed parallax photo banner ───────────────*/
function bg_photo_full(string $img_url, string $heading, string $sub): string {
    return '
<div class="bg-photo-full bg-noise" style="background-image:url(' . esc_url($img_url) . ');">
    <div class="inner">
        <div class="bg-float-badge">&#9670; BRND GURU</div>
        <div class="bg-geo-line-center"></div>
        <h2 style="color:#fff;font-size:clamp(26px,4vw,44px);font-weight:800;margin:0 0 16px;line-height:1.2;">'
        . esc_html($heading) . '</h2>
        <p style="color:rgba(255,255,255,0.80);font-size:17px;margin:0;line-height:1.65;">'
        . esc_html($sub) . '</p>
    </div>
</div>';
}
