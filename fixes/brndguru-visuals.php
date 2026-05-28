<?php
/**
 * Plugin Name: BrndGuru — Visual Enhancements
 * Description: Adds stock photos, background patterns, and visual depth to all BRND GURU pages. AUTO-RUNS on activation.
 * Version: 2.0
 */
if (!defined('ABSPATH')) exit;

// ── Inject CSS globally ───────────────────────────────────────
add_action('wp_head', function () { ?>
<style id="bg-visuals-css">
/* ── Hide any accidental empty strip sections ── */
.bg-dot-grid:empty,
.bg-photo-full:empty,
section:empty,
div[style*="background"]:empty { display: none !important; }
/* Prevent photo strip from sticking to top of page */
.bg-dot-grid { margin-top: 0; }

/* ── Dot grid pattern helper ── */
.bg-dot-grid {
    background-image: radial-gradient(circle, #333 1px, transparent 1px);
    background-size: 28px 28px;
}
/* ── Subtle noise texture ── */
.bg-noise {
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
}
/* ── Orange glow blob ── */
.bg-glow-blob::before {
    content: '';
    position: absolute;
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(228,82,43,0.12) 0%, transparent 70%);
    top: -200px; right: -100px;
    border-radius: 50%;
    pointer-events: none;
}
/* ── Section divider wave ── */
.bg-wave-divider {
    line-height: 0;
    overflow: hidden;
}
.bg-wave-divider svg { display: block; }

/* ── Photo strip hover ── */
.bg-photo-card {
    overflow: hidden;
    border-radius: 12px;
    transition: transform .25s ease, box-shadow .25s ease;
}
.bg-photo-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 60px rgba(228,82,43,0.2);
}
.bg-photo-card img {
    width: 100%;
    height: 260px;
    object-fit: cover;
    display: block;
    transition: transform .4s ease;
}
.bg-photo-card:hover img { transform: scale(1.04); }

/* ── Floating badge ── */
.bg-float-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(228,82,43,0.12);
    border: 1px solid rgba(228,82,43,0.3);
    color: #e4522b;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 6px 16px;
    border-radius: 999px;
    margin-bottom: 20px;
}

/* ── Full-bleed photo section ── */
.bg-photo-full {
    position: relative;
    min-height: 420px;
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-photo-full::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(10,10,10,0.82), rgba(228,82,43,0.18));
}
.bg-photo-full .inner {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 60px 40px;
    max-width: 700px;
}

/* ── Geometric accent lines ── */
.bg-geo-line {
    height: 3px;
    background: linear-gradient(90deg, #e4522b, transparent);
    width: 80px;
    margin: 0 0 24px;
    border-radius: 2px;
}

/* ── Number highlight ── */
.bg-stat-big {
    font-size: clamp(48px, 8vw, 80px);
    font-weight: 900;
    color: #e4522b;
    line-height: 1;
    font-style: italic;
}

/* Mobile ── */
@media (max-width: 767px) {
    .bg-photo-full { background-attachment: scroll; min-height: 300px; }
    .bg-photo-grid { flex-direction: column !important; }
}
</style>
<?php });

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
            <div style="background:#141414;padding:14px 20px;border-top:2px solid #e4522b;">
                <p style="color:#fff;font-size:13px;font-weight:700;margin:0;letter-spacing:.5px;">' . esc_html($p['label']) . '</p>
            </div>
        </div>';
    }
    return '
<div class="bg-dot-grid" style="background-color:#0d0d0d;padding:64px 40px;position:relative;">
    <div style="max-width:1200px;margin:0 auto;display:flex;flex-wrap:wrap;gap:24px;justify-content:center;">
        ' . $cards . '
    </div>
</div>';
}

/* ─── Helper: full-bleed parallax photo banner ───────────────*/
function bg_photo_full(string $img_url, string $heading, string $sub): string {
    return '
<div class="bg-photo-full" style="background-image:url(' . esc_url($img_url) . ');">
    <div class="inner">
        <div class="bg-geo-line" style="margin:0 auto 20px;"></div>
        <h2 style="color:#fff;font-size:clamp(24px,4vw,42px);font-weight:800;margin:0 0 14px;line-height:1.2;">'
        . esc_html($heading) . '</h2>
        <p style="color:rgba(255,255,255,0.82);font-size:17px;margin:0;line-height:1.6;">'
        . esc_html($sub) . '</p>
    </div>
</div>';
}
