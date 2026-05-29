<?php
/**
 * Plugin Name: BrndGuru — Visual Enhancements
 * Description: Dot grids, glow orbs, photo strips, parallax banners on all BRND GURU pages.
 * Version: 2.2
 */
if (!defined('ABSPATH')) exit;

/* ── Global CSS ──────────────────────────────────────────────── */
add_action('wp_head', function () { ?>
<style id="bg-visuals-css">
.bg-dot-grid{background-color:#0d0d0d;background-image:radial-gradient(circle,rgba(255,255,255,.07) 1px,transparent 1px);background-size:28px 28px;}
.bg-grid-lines{background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:60px 60px;}
.bg-stripe{background-image:repeating-linear-gradient(45deg,transparent,transparent 8px,rgba(228,82,43,.04) 8px,rgba(228,82,43,.04) 16px);}
.bg-glow-blob{position:relative;overflow:hidden;}
.bg-glow-blob::before{content:'';position:absolute;width:700px;height:700px;background:radial-gradient(circle,rgba(228,82,43,.13) 0%,transparent 68%);top:-280px;right:-180px;border-radius:50%;pointer-events:none;z-index:0;}
.bg-dual-orb{position:relative;overflow:hidden;}
.bg-dual-orb::before{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(228,82,43,.10) 0%,transparent 70%);bottom:-150px;left:-80px;border-radius:50%;pointer-events:none;z-index:0;}
.bg-dual-orb::after{content:'';position:absolute;width:400px;height:400px;background:radial-gradient(circle,rgba(80,80,120,.08) 0%,transparent 70%);top:-100px;right:-60px;border-radius:50%;pointer-events:none;z-index:0;}
.bg-geo-line{height:3px;background:linear-gradient(90deg,#e4522b,transparent);width:80px;margin:0 0 24px;border-radius:2px;}
.bg-geo-line-center{height:2px;background:linear-gradient(90deg,transparent,#e4522b 50%,transparent);width:120px;margin:0 auto 28px;border-radius:2px;}
.bg-float-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(228,82,43,.10);border:1px solid rgba(228,82,43,.28);color:#e4522b;font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;padding:6px 18px;border-radius:999px;margin-bottom:20px;}
.bg-photo-card{overflow:hidden;border-radius:12px;transition:transform .25s ease,box-shadow .25s ease;position:relative;}
.bg-photo-card::before{content:'';position:absolute;inset:0;background:linear-gradient(to bottom,transparent 50%,rgba(0,0,0,.7));z-index:1;pointer-events:none;}
.bg-photo-card:hover{transform:translateY(-5px);box-shadow:0 24px 64px rgba(228,82,43,.22);}
.bg-photo-card img{width:100%;height:260px;object-fit:cover;display:block;transition:transform .45s ease;}
.bg-photo-card:hover img{transform:scale(1.06);}
.bg-photo-full{position:relative;min-height:440px;background-size:cover;background-position:center;background-attachment:fixed;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.bg-photo-full::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(8,8,8,.88) 0%,rgba(228,82,43,.15) 100%);z-index:1;}
.bg-photo-full::after{content:'';position:absolute;inset:0;background-image:repeating-linear-gradient(0deg,rgba(0,0,0,.06) 0px,rgba(0,0,0,.06) 1px,transparent 1px,transparent 3px);z-index:2;pointer-events:none;}
.bg-photo-full .inner{position:relative;z-index:3;text-align:center;padding:70px 40px;max-width:720px;}
.bg-stat-big{font-size:clamp(48px,8vw,80px);font-weight:900;color:#e4522b;line-height:1;font-style:italic;}
@media(max-width:767px){.bg-photo-full{background-attachment:scroll;min-height:320px;}.bg-glow-blob::before,.bg-dual-orb::before,.bg-dual-orb::after{display:none;}}
</style>
<?php }, 5);

/* ── Global dark-section dot grid + glow orbs via JS ─────────── */
add_action('wp_footer', function () {
    if (is_admin()) return; ?>
<style id="bg-global-orbs">
body::before{content:'';position:fixed;top:-200px;left:-200px;width:700px;height:700px;background:radial-gradient(circle,rgba(228,82,43,.06) 0%,transparent 65%);border-radius:50%;pointer-events:none;z-index:0;animation:bgPulse 9s ease-in-out infinite alternate;}
body::after{content:'';position:fixed;bottom:-150px;right:-150px;width:500px;height:500px;background:radial-gradient(circle,rgba(228,82,43,.04) 0%,transparent 65%);border-radius:50%;pointer-events:none;z-index:0;}
@keyframes bgPulse{0%{opacity:.6;transform:scale(1);}100%{opacity:1;transform:scale(1.15);}}
</style>
<script id="bg-global-js">
(function(){
  function applyDotGrid(){
    document.querySelectorAll('.elementor-section,.elementor-top-section,.e-con').forEach(function(s){
      var bg=getComputedStyle(s).backgroundColor;
      var m=bg.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
      if(!m)return;
      var l=(0.2126*+m[1]+0.7152*+m[2]+0.0722*+m[3])/255;
      if(l<0.10){
        s.style.backgroundImage='radial-gradient(circle,rgba(255,255,255,0.055) 1px,transparent 1px)';
        s.style.backgroundSize='32px 32px';
      }
    });
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',applyDotGrid);
  else applyDotGrid();
  setTimeout(applyDotGrid,900);
})();
</script>
<?php }, 20);

/* ── Per-page photo strip + banner injection ─────────────────── */
add_filter('the_content', function ($content) {
    if (!is_singular()) return $content;
    $id = get_the_ID();

    $visuals = [
        12   => ['strip' => [['url'=>'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80&fit=crop','label'=>'Strategy Sessions'],['url'=>'https://images.unsplash.com/photo-1551434678-e076c223a692?w=800&q=80&fit=crop','label'=>'Outbound Execution'],['url'=>'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80&fit=crop','label'=>'Pipeline Analytics']],'banner_img'=>'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&q=80&fit=crop','banner_h'=>'Every engagement starts with your pipeline goal.','banner_p'=>'We build the infrastructure. You close the deals.'],
        1628 => ['strip' => [['url'=>'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80&fit=crop','label'=>'Our Team'],['url'=>'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&q=80&fit=crop','label'=>'London HQ'],['url'=>'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=800&q=80&fit=crop','label'=>'Remote First']],'banner_img'=>'https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=1600&q=80&fit=crop','banner_h'=>'We are BRND GURU.','banner_p'=>'A London-based B2B consulting agency obsessed with filling your pipeline.'],
        4325 => ['strip' => [['url'=>'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=800&q=80&fit=crop','label'=>'LinkedIn Automation'],['url'=>'https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=800&q=80&fit=crop','label'=>'Cold Email'],['url'=>'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80&fit=crop','label'=>'AI Agents'],['url'=>'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80&fit=crop','label'=>'CRM & Automation']],'banner_img'=>'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&q=80&fit=crop','banner_h'=>'Every service is built around one outcome.','banner_p'=>'More qualified conversations with your ideal buyers.'],
        1483 => ['strip' => [['url'=>'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80&fit=crop','label'=>'Pipeline Growth'],['url'=>'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80&fit=crop','label'=>'CRM Results'],['url'=>'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80&fit=crop','label'=>'AI Automation']],'banner_img'=>'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1600&q=80&fit=crop','banner_h'=>'Results speak louder than promises.','banner_p'=>'200+ B2B clients. £50M+ pipeline generated. 98% retention rate.'],
        23   => ['banner_img'=>'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=1600&q=80&fit=crop','banner_h'=>'Based in London. Working globally.','banner_p'=>'UK · Europe · North America · Australia'],
        762  => ['banner_img'=>'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=1600&q=80&fit=crop','banner_h'=>'LinkedIn Outreach at Scale.','banner_p'=>'Personalised. Automated. Booked.'],
        724  => ['banner_img'=>'https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=1600&q=80&fit=crop','banner_h'=>'Cold Email That Converts.','banner_p'=>'Built for deliverability and replies.'],
        766  => ['banner_img'=>'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1600&q=80&fit=crop','banner_h'=>'AI Agents Running 24/7.','banner_p'=>'Research. Personalise. Follow-up. Automatically.'],
        765  => ['banner_img'=>'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1600&q=80&fit=crop','banner_h'=>'Your Pipeline. Fully Automated.','banner_p'=>'GoHighLevel built for how you sell.'],
        2970 => ['banner_img'=>'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1600&q=80&fit=crop','banner_h'=>'Connect Everything. Automate Everything.','banner_p'=>'n8n workflows that eliminate manual work.'],
    ];

    if (!isset($visuals[$id])) return $content;
    $v = $visuals[$id];

    if (!empty($v['strip'])) {
        $strip = bg_photo_strip($v['strip']);
        $pos   = strpos($content, '</section>');
        if ($pos !== false) {
            $second = strpos($content, '</section>', $pos + 10);
            $at = ($second !== false) ? $second + 10 : $pos + 10;
            $content = substr($content, 0, $at) . $strip . substr($content, $at);
        } else {
            $content .= $strip;
        }
    }

    if (!empty($v['banner_img'])) {
        $banner = bg_photo_full($v['banner_img'], $v['banner_h'], $v['banner_p']);
        $positions = [];
        $off = 0;
        while (($p = strpos($content, '<section', $off)) !== false) { $positions[] = $p; $off = $p + 1; }
        if (count($positions) >= 2) {
            $at = end($positions);
            $content = substr($content, 0, $at) . $banner . substr($content, $at);
        } else {
            $content .= $banner;
        }
    }

    return $content;
}, 20);

/* ── Helpers ─────────────────────────────────────────────────── */
if (!function_exists('bg_photo_strip')) :
function bg_photo_strip(array $photos): string {
    $cards = '';
    foreach ($photos as $p) {
        $cards .= '<div class="bg-photo-card" style="flex:1 1 220px;max-width:320px;">
            <img src="'.esc_url($p['url']).'" alt="'.esc_attr($p['label']).'" loading="lazy">
            <div style="background:#111;padding:14px 20px;border-top:2px solid #e4522b;position:relative;z-index:2;">
                <div class="bg-geo-line" style="width:32px;height:2px;margin:0 0 6px;"></div>
                <p style="color:#fff;font-size:13px;font-weight:700;margin:0;letter-spacing:.5px;">'.esc_html($p['label']).'</p>
            </div></div>';
    }
    return '<div class="bg-dot-grid bg-glow-blob" style="background-color:#0d0d0d;padding:72px 40px;position:relative;overflow:hidden;">
    <div class="bg-geo-line-center"></div>
    <div style="max-width:1200px;margin:0 auto;display:flex;flex-wrap:wrap;gap:28px;justify-content:center;position:relative;z-index:1;">'.$cards.'</div></div>';
}
endif;

if (!function_exists('bg_photo_full')) :
function bg_photo_full(string $img_url, string $heading, string $sub): string {
    return '<div class="bg-photo-full" style="background-image:url('.esc_url($img_url).');">
    <div class="inner">
        <div class="bg-float-badge">&#9670; BRND GURU</div>
        <div class="bg-geo-line-center"></div>
        <h2 style="color:#fff;font-size:clamp(26px,4vw,44px);font-weight:800;margin:0 0 16px;line-height:1.2;">'.esc_html($heading).'</h2>
        <p style="color:rgba(255,255,255,.80);font-size:17px;margin:0;line-height:1.65;">'.esc_html($sub).'</p>
    </div></div>';
}
endif;
