<?php
/**
 * Plugin Name: BrndGuru Visual Enhancements
 * Description: Dot grids, glow orbs, photo strips and parallax banners across all pages.
 * Version:     3.2
 * Author:      BrndGuru
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head',   'brndguru_visuals_css', 10 );
add_action( 'wp_footer', 'brndguru_visuals_global_js', 10000 );
add_filter( 'the_content', 'brndguru_visuals_content', 20 );

/* ── CSS utilities ───────────────────────────────────────────── */
function brndguru_visuals_css() {
    ?>
    <style id="brndguru-visuals-css">
    .bv-dot-grid {
        background-color: #0d0d0d;
        background-image: radial-gradient(circle, rgba(255,255,255,0.07) 1px, transparent 1px);
        background-size: 28px 28px;
    }
    .bv-glow-wrap {
        position: relative;
        overflow: hidden;
    }
    .bv-glow-wrap::before {
        content: '';
        position: absolute;
        width: 700px; height: 700px;
        background: radial-gradient(circle, rgba(228,82,43,0.12) 0%, transparent 68%);
        top: -280px; right: -180px;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }
    .bv-geo-line {
        height: 2px;
        background: linear-gradient(90deg, transparent, #e4522b 50%, transparent);
        width: 120px;
        margin: 0 auto 28px;
        border-radius: 2px;
    }
    .bv-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(228,82,43,0.10);
        border: 1px solid rgba(228,82,43,0.28);
        color: #e4522b;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.8px;
        text-transform: uppercase;
        padding: 6px 18px;
        border-radius: 999px;
        margin-bottom: 20px;
    }
    .bv-card {
        overflow: hidden;
        border-radius: 12px;
        position: relative;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        flex: 1 1 220px;
        max-width: 320px;
    }
    .bv-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 24px 64px rgba(228,82,43,0.22);
    }
    .bv-card img {
        width: 100%; height: 260px;
        object-fit: cover; display: block;
        transition: transform 0.45s ease;
    }
    .bv-card:hover img { transform: scale(1.06); }
    .bv-card-label {
        background: #111;
        padding: 14px 20px;
        border-top: 2px solid #e4522b;
    }
    .bv-card-label p {
        color: #fff !important;
        font-size: 13px;
        font-weight: 700;
        margin: 0;
        letter-spacing: 0.5px;
    }
    .bv-banner {
        position: relative;
        min-height: 440px;
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .bv-banner::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(8,8,8,0.88) 0%, rgba(228,82,43,0.15) 100%);
        z-index: 1;
    }
    .bv-banner::after {
        content: '';
        position: absolute; inset: 0;
        background-image: repeating-linear-gradient(
            0deg, rgba(0,0,0,0.06) 0px, rgba(0,0,0,0.06) 1px,
            transparent 1px, transparent 3px
        );
        z-index: 2;
        pointer-events: none;
    }
    .bv-banner-inner {
        position: relative;
        z-index: 3;
        text-align: center;
        padding: 70px 40px;
        max-width: 720px;
    }
    .bv-banner-inner h2 {
        color: #fff !important;
        font-size: clamp(26px, 4vw, 44px) !important;
        font-weight: 800 !important;
        margin: 0 0 16px !important;
        line-height: 1.2 !important;
    }
    .bv-banner-inner p {
        color: rgba(255,255,255,0.80) !important;
        font-size: 17px !important;
        margin: 0 !important;
        line-height: 1.65 !important;
    }
    @media (max-width: 767px) {
        .bv-banner { background-attachment: scroll; min-height: 320px; }
        .bv-glow-wrap::before { display: none; }
    }
    </style>
    <?php
}

/* ── Global glow orbs + dark-section dot grid ────────────────── */
function brndguru_visuals_global_js() {
    ?>
    <style id="brndguru-global-orbs">
    /* ── Animated glow orbs fixed to viewport ── */
    body::before {
        content: '';
        position: fixed;
        top: -200px; left: -200px;
        width: 700px; height: 700px;
        background: radial-gradient(circle, rgba(228,82,43,0.08) 0%, transparent 65%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 1;
        animation: bvPulse 9s ease-in-out infinite alternate;
    }
    body::after {
        content: '';
        position: fixed;
        bottom: -150px; right: -150px;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(228,82,43,0.06) 0%, transparent 65%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 1;
    }
    @keyframes bvPulse {
        0%   { opacity: 0.5; transform: scale(1);    }
        100% { opacity: 1;   transform: scale(1.2); }
    }

    /* ── Alternating section shades — warm orange-tinted dark ── */
    .elementor-top-section:nth-child(odd),
    .elementor-section:nth-child(odd),
    .e-con:nth-child(odd) {
        background-color: #2c1508 !important;
    }
    .elementor-top-section:nth-child(even),
    .elementor-section:nth-child(even),
    .e-con:nth-child(even) {
        background-color: #3a1e0a !important;
    }

    /* ── Dot grid on all sections ── */
    .elementor-section,
    .elementor-top-section,
    .e-con {
        background-image:
            radial-gradient(circle, rgba(255,255,255,0.05) 1px, transparent 1px) !important;
        background-size: 30px 30px !important;
    }

    /* ── Subtle bottom border between sections ── */
    .elementor-section,
    .elementor-top-section {
        border-bottom: 1px solid rgba(255,255,255,0.06) !important;
    }

    /* ── Orange accent on alternating sections left edge ── */
    .elementor-top-section:nth-child(even),
    .elementor-section:nth-child(even) {
        border-left: 3px solid rgba(228,82,43,0.2) !important;
    }
    </style>
    <?php
}

/* ── Per-page photo strip + banner ───────────────────────────── */
function brndguru_visuals_content( $content ) {
    if ( ! is_singular() ) return $content;

    $id = get_the_ID();

    $pages = array(
        12   => array(
            'strip'  => array(
                array( 'url' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80&fit=crop', 'label' => 'Strategy Sessions' ),
                array( 'url' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=800&q=80&fit=crop', 'label' => 'Outbound Execution' ),
                array( 'url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80&fit=crop', 'label' => 'Pipeline Analytics' ),
            ),
            'banner_img' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&q=80&fit=crop',
            'banner_h'   => 'Every engagement starts with your pipeline goal.',
            'banner_p'   => 'We build the infrastructure. You close the deals.',
        ),
        1628 => array(
            'strip'  => array(
                array( 'url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80&fit=crop', 'label' => 'Our Team' ),
                array( 'url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&q=80&fit=crop', 'label' => 'London HQ' ),
                array( 'url' => 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=800&q=80&fit=crop', 'label' => 'Remote First' ),
            ),
            'banner_img' => 'https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=1600&q=80&fit=crop',
            'banner_h'   => 'We are BRND GURU.',
            'banner_p'   => 'A London-based B2B consulting agency obsessed with filling your pipeline.',
        ),
        4325 => array(
            'strip'  => array(
                array( 'url' => 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=800&q=80&fit=crop', 'label' => 'LinkedIn Automation' ),
                array( 'url' => 'https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=800&q=80&fit=crop', 'label' => 'Cold Email' ),
                array( 'url' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80&fit=crop', 'label' => 'AI Agents' ),
                array( 'url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80&fit=crop', 'label' => 'CRM & Automation' ),
            ),
            'banner_img' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&q=80&fit=crop',
            'banner_h'   => 'Every service is built around one outcome.',
            'banner_p'   => 'More qualified conversations with your ideal buyers.',
        ),
        1483 => array(
            'strip'  => array(
                array( 'url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80&fit=crop', 'label' => 'Pipeline Growth' ),
                array( 'url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80&fit=crop', 'label' => 'CRM Results' ),
                array( 'url' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80&fit=crop', 'label' => 'AI Automation' ),
            ),
            'banner_img' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1600&q=80&fit=crop',
            'banner_h'   => 'Results speak louder than promises.',
            'banner_p'   => '200+ B2B clients. £50M+ pipeline generated. 98% retention rate.',
        ),
        23   => array(
            'banner_img' => 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=1600&q=80&fit=crop',
            'banner_h'   => 'Based in London. Working globally.',
            'banner_p'   => 'UK · Europe · North America · Australia',
        ),
        762  => array(
            'banner_img' => 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=1600&q=80&fit=crop',
            'banner_h'   => 'LinkedIn Outreach at Scale.',
            'banner_p'   => 'Personalised. Automated. Booked.',
        ),
        724  => array(
            'banner_img' => 'https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=1600&q=80&fit=crop',
            'banner_h'   => 'Cold Email That Converts.',
            'banner_p'   => 'Built for deliverability and replies.',
        ),
        766  => array(
            'banner_img' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1600&q=80&fit=crop',
            'banner_h'   => 'AI Agents Running 24/7.',
            'banner_p'   => 'Research. Personalise. Follow-up. Automatically.',
        ),
        765  => array(
            'banner_img' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1600&q=80&fit=crop',
            'banner_h'   => 'Your Pipeline. Fully Automated.',
            'banner_p'   => 'GoHighLevel built for how you sell.',
        ),
        2970 => array(
            'banner_img' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1600&q=80&fit=crop',
            'banner_h'   => 'Connect Everything. Automate Everything.',
            'banner_p'   => 'n8n workflows that eliminate manual work.',
        ),
    );

    if ( ! isset( $pages[ $id ] ) ) return $content;

    $v = $pages[ $id ];

    if ( ! empty( $v['strip'] ) ) {
        $strip = brndguru_build_strip( $v['strip'] );
        $pos   = strpos( $content, '</section>' );
        if ( $pos !== false ) {
            $second = strpos( $content, '</section>', $pos + 10 );
            $at     = ( $second !== false ) ? $second + 10 : $pos + 10;
            $content = substr( $content, 0, $at ) . $strip . substr( $content, $at );
        } else {
            $content .= $strip;
        }
    }

    if ( ! empty( $v['banner_img'] ) ) {
        $banner    = brndguru_build_banner( $v['banner_img'], $v['banner_h'], $v['banner_p'] );
        $positions = array();
        $offset    = 0;
        while ( ( $p = strpos( $content, '<section', $offset ) ) !== false ) {
            $positions[] = $p;
            $offset      = $p + 1;
        }
        if ( count( $positions ) >= 2 ) {
            $at      = end( $positions );
            $content = substr( $content, 0, $at ) . $banner . substr( $content, $at );
        } else {
            $content .= $banner;
        }
    }

    return $content;
}

/* ── Helpers ─────────────────────────────────────────────────── */
function brndguru_build_strip( $photos ) {
    $cards = '';
    foreach ( $photos as $p ) {
        $cards .= '<div class="bv-card">'
            . '<img src="' . esc_url( $p['url'] ) . '" alt="' . esc_attr( $p['label'] ) . '" loading="lazy">'
            . '<div class="bv-card-label"><p>' . esc_html( $p['label'] ) . '</p></div>'
            . '</div>';
    }
    return '<div class="bv-dot-grid bv-glow-wrap" style="padding:72px 40px;">'
        . '<div class="bv-geo-line"></div>'
        . '<div style="max-width:1200px;margin:0 auto;display:flex;flex-wrap:wrap;gap:28px;justify-content:center;position:relative;z-index:1;">'
        . $cards
        . '</div></div>';
}

function brndguru_build_banner( $img_url, $heading, $sub ) {
    return '<div class="bv-banner" style="background-image:url(' . esc_url( $img_url ) . ');">'
        . '<div class="bv-banner-inner">'
        . '<div class="bv-badge">&#9670; BRND GURU</div>'
        . '<div class="bv-geo-line"></div>'
        . '<h2>' . esc_html( $heading ) . '</h2>'
        . '<p>' . esc_html( $sub ) . '</p>'
        . '</div></div>';
}
