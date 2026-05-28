<?php
/**
 * Plugin Name: BrndGuru — Careers Center + Services Visuals
 * Description: Centers Careers hero, adds rich visuals to Services page. AUTO-RUNS on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgcsu_run');
add_action('admin_init', function () {
    if (get_option('bgcsu_done') !== '1') bgcsu_run();
});

function bgcsu_run() {
    global $wpdb;
    $log = [];

    // ── FIX 1: Center-align Careers hero ──────────────────────────
    $careers_id = 21;
    $content = $wpdb->get_var($wpdb->prepare(
        "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d", $careers_id
    ));

    if ($content) {
        // Center the inner hero div
        $content = str_replace(
            'max-width: 800px; margin: 0 auto; position: relative; z-index: 1;">',
            'max-width: 800px; margin: 0 auto; position: relative; z-index: 1; text-align: center;">',
            $content
        );
        // Center the sub-paragraph
        $content = str_replace(
            'max-width: 580px; margin: 0 0 40px;',
            'max-width: 580px; margin: 0 auto 40px;',
            $content
        );
        $wpdb->update($wpdb->posts,
            ['post_content' => $content, 'post_modified' => current_time('mysql')],
            ['ID' => $careers_id]
        );
        clean_post_cache($careers_id);
        $log[] = "✓ Careers hero centered";
    }

    // Clear caches
    if (class_exists('\Elementor\Plugin')) \Elementor\Plugin::$instance->files_manager->clear_cache();
    foreach ([WP_CONTENT_DIR.'/cache/swift-performance/', WP_CONTENT_DIR.'/cache/swift-performance-lite/'] as $dir) {
        if (is_dir($dir)) { $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST); foreach ($it as $f) { $f->isDir() ? @rmdir($f) : @unlink($f); } }
    }
    if (class_exists('Swift_Performance')) do_action('swift_performance_clear_all_cache');
    wp_cache_flush();

    update_option('bgcsu_log', $log);
    update_option('bgcsu_done', '1');
}

// ── Services page visual injection via the_content ──────────────
add_filter('the_content', function ($content) {
    if (!is_page(4325)) return $content;

    $stats_bar = '
<div style="background:#111;padding:52px 40px;border-top:3px solid #e4522b;border-bottom:1px solid #1e1e1e;">
  <div style="max-width:1000px;margin:0 auto;display:flex;flex-wrap:wrap;justify-content:space-around;gap:32px;text-align:center;">
    <div><div style="font-size:48px;font-weight:900;color:#e4522b;line-height:1;">200+</div><div style="color:#888;font-size:13px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;margin-top:8px;">B2B Clients</div></div>
    <div><div style="font-size:48px;font-weight:900;color:#e4522b;line-height:1;">£50M+</div><div style="color:#888;font-size:13px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;margin-top:8px;">Pipeline Generated</div></div>
    <div><div style="font-size:48px;font-weight:900;color:#e4522b;line-height:1;">98%</div><div style="color:#888;font-size:13px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;margin-top:8px;">Client Retention</div></div>
    <div><div style="font-size:48px;font-weight:900;color:#e4522b;line-height:1;">5</div><div style="color:#888;font-size:13px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;margin-top:8px;">Core Services</div></div>
  </div>
</div>';

    $photo_strip = '
<div style="background:#0d0d0d;padding:72px 40px;background-image:radial-gradient(circle,#1e1e1e 1px,transparent 1px);background-size:28px 28px;">
  <div style="max-width:1200px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:52px;">
      <div style="width:60px;height:3px;background:#e4522b;border-radius:2px;margin:0 auto 20px;"></div>
      <h2 style="color:#fff;font-size:clamp(24px,3.5vw,38px);font-weight:800;margin:0 0 12px;">Every Service. One Goal.</h2>
      <p style="color:#888;font-size:16px;margin:0;">More qualified conversations with your ideal buyers.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;">
      <div style="border-radius:12px;overflow:hidden;background:#141414;border:1px solid #222;transition:transform .2s;">
        <img src="https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=600&q=80&fit=crop" alt="LinkedIn Automation" loading="lazy" style="width:100%;height:200px;object-fit:cover;display:block;">
        <div style="padding:20px 24px;border-top:3px solid #e4522b;">
          <h3 style="color:#fff;font-size:16px;font-weight:700;margin:0 0 6px;">LinkedIn Automation</h3>
          <p style="color:#888;font-size:13px;margin:0;line-height:1.6;">Personalised outreach at scale. Hundreds of qualified conversations per month.</p>
        </div>
      </div>
      <div style="border-radius:12px;overflow:hidden;background:#141414;border:1px solid #222;">
        <img src="https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=600&q=80&fit=crop" alt="Cold Email" loading="lazy" style="width:100%;height:200px;object-fit:cover;display:block;">
        <div style="padding:20px 24px;border-top:3px solid #e4522b;">
          <h3 style="color:#fff;font-size:16px;font-weight:700;margin:0 0 6px;">Cold Email Infrastructure</h3>
          <p style="color:#888;font-size:13px;margin:0;line-height:1.6;">Deliverability-first email systems built to land in the inbox and get replies.</p>
        </div>
      </div>
      <div style="border-radius:12px;overflow:hidden;background:#141414;border:1px solid #222;">
        <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&q=80&fit=crop" alt="AI Agents" loading="lazy" style="width:100%;height:200px;object-fit:cover;display:block;">
        <div style="padding:20px 24px;border-top:3px solid #e4522b;">
          <h3 style="color:#fff;font-size:16px;font-weight:700;margin:0 0 6px;">AI Agent Development</h3>
          <p style="color:#888;font-size:13px;margin:0;line-height:1.6;">Custom AI agents that research, personalise, and follow up — automatically.</p>
        </div>
      </div>
      <div style="border-radius:12px;overflow:hidden;background:#141414;border:1px solid #222;">
        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&q=80&fit=crop" alt="GoHighLevel CRM" loading="lazy" style="width:100%;height:200px;object-fit:cover;display:block;">
        <div style="padding:20px 24px;border-top:3px solid #e4522b;">
          <h3 style="color:#fff;font-size:16px;font-weight:700;margin:0 0 6px;">GoHighLevel CRM</h3>
          <p style="color:#888;font-size:13px;margin:0;line-height:1.6;">Full pipeline automation built inside GoHighLevel — tailored to how you sell.</p>
        </div>
      </div>
      <div style="border-radius:12px;overflow:hidden;background:#141414;border:1px solid #222;">
        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=80&fit=crop" alt="n8n Automation" loading="lazy" style="width:100%;height:200px;object-fit:cover;display:block;">
        <div style="padding:20px 24px;border-top:3px solid #e4522b;">
          <h3 style="color:#fff;font-size:16px;font-weight:700;margin:0 0 6px;">n8n Workflow Automation</h3>
          <p style="color:#888;font-size:13px;margin:0;line-height:1.6;">Connect your entire stack. Eliminate manual work with n8n workflows.</p>
        </div>
      </div>
    </div>
  </div>
</div>';

    $mid_banner = '
<div style="position:relative;min-height:420px;background-image:url(https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&q=80&fit=crop);background-size:cover;background-position:center;background-attachment:fixed;display:flex;align-items:center;justify-content:center;">
  <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,10,10,0.88),rgba(228,82,43,0.2));"></div>
  <div style="position:relative;z-index:1;text-align:center;padding:60px 40px;max-width:700px;">
    <div style="width:60px;height:3px;background:#e4522b;border-radius:2px;margin:0 auto 24px;"></div>
    <h2 style="color:#fff;font-size:clamp(26px,4vw,44px);font-weight:800;margin:0 0 16px;line-height:1.2;">Every engagement starts with your pipeline goal.</h2>
    <p style="color:rgba(255,255,255,0.82);font-size:17px;margin:0 0 36px;line-height:1.6;">We build the infrastructure. You close the deals.</p>
    <a href="/contact/" style="display:inline-block;background:#e4522b;color:#fff;font-weight:700;font-size:16px;padding:16px 40px;border-radius:8px;text-decoration:none;">Book a Strategy Call →</a>
  </div>
</div>';

    // Inject stats bar at the very start of content
    $content = $stats_bar . $content;

    // Inject photo strip after content, before last section
    $positions = [];
    $offset = 0;
    while (($pos = strpos($content, '<section', $offset)) !== false) {
        $positions[] = $pos;
        $offset = $pos + 1;
    }
    if (count($positions) >= 2) {
        $insert_at = end($positions);
        $content = substr($content, 0, $insert_at) . $mid_banner . substr($content, $insert_at);
    } else {
        $content .= $mid_banner;
    }

    $content .= $photo_strip;

    return $content;
}, 20);

add_action('admin_notices', function () {
    if (get_option('bgcsu_done') !== '1') return;
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ Careers + Services Upgraded</h3>
        <ul style="margin:0 0 10px;padding-left:20px;">
            <?php foreach (get_option('bgcsu_log', []) as $l): ?><li><?php echo esc_html($l); ?></li><?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/careers/'); ?>" target="_blank" class="button button-primary">Careers →</a>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button">Services →</a>
        </p>
    </div>
    <?php
});
