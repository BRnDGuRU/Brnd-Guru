<?php
/**
 * Plugin Name: BrndGuru — Careers + Contact Redesign
 * Description: Rebuilds Careers and Contact pages to match the Portfolio dark design. AUTO-RUNS on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgcc2_run');
add_action('admin_init', function () {
    if (get_option('bgcc2_done') !== '1') bgcc2_run();
});

// Kill the strip on portfolio + all rebuilt pages via CSS
add_action('wp_head', function () { ?>
<style id="bg-strip-kill">
/* Kill any rogue empty strip at top of pages */
.entry-content > div:first-child:empty,
.entry-content > section:first-child:empty,
.bg-dot-grid:empty { display:none !important; height:0 !important; }
/* Ensure rebuilt page wrappers sit flush */
.entry-content > div[style]:first-child { margin-top: 0 !important; }
</style>
<?php }, 1);

function bgcc2_save(int $id, string $title, string $slug, string $html): string {
    global $wpdb;
    $wpdb->update($wpdb->posts, [
        'post_title'    => $title,
        'post_name'     => $slug,
        'post_content'  => $html,
        'post_status'   => 'publish',
        'post_modified' => current_time('mysql'),
    ], ['ID' => $id]);
    $wpdb->delete($wpdb->postmeta, ['post_id' => $id, 'meta_key' => '_elementor_data']);
    update_post_meta($id, '_elementor_edit_mode', '');
    update_post_meta($id, '_wp_page_template', 'elementor_full_width');
    delete_post_meta($id, '_elementor_css');
    clean_post_cache($id);
    return "✓ {$title} rebuilt";
}

function bgcc2_run() {
    $BOOK = 'https://www.brndgurumedia.com/widget/bookings/brndguru';
    $log  = [];

    /* ═══════════════════════════════════════════════════════
       CAREERS PAGE (ID: 21)
    ═══════════════════════════════════════════════════════ */
    $careers = '
<div style="font-family:inherit;background:#0d0d0d;color:#fff;">

  <!-- Hero -->
  <section style="background:linear-gradient(135deg,#0d0d0d 55%,#1a0800 100%);padding:100px 40px 80px;position:relative;overflow:hidden;">
    <div style="position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(228,82,43,0.1) 0%,transparent 70%);top:-100px;right:-50px;border-radius:50%;pointer-events:none;"></div>
    <div style="max-width:800px;margin:0 auto;position:relative;z-index:1;">
      <p style="color:#e4522b;font-weight:700;font-size:12px;letter-spacing:2.5px;text-transform:uppercase;margin:0 0 20px;">JOIN THE TEAM</p>
      <h1 style="color:#fff;font-size:clamp(38px,6vw,66px);font-weight:800;margin:0 0 24px;line-height:1.1;">Build the Future of<br><span style="color:#e4522b;">B2B Outbound.</span></h1>
      <p style="color:#aaa;font-size:clamp(16px,2vw,19px);max-width:580px;margin:0 0 40px;line-height:1.7;">
        We are a small, ambitious team building outbound engines for B2B businesses worldwide. If you are obsessed with results, hate busywork, and want to grow fast — you will fit right in.
      </p>
      <a href="#open-roles" style="display:inline-block;background:#e4522b;color:#fff;font-weight:700;font-size:16px;padding:16px 36px;border-radius:8px;text-decoration:none;">See Open Roles ↓</a>
    </div>
  </section>

  <!-- Stats -->
  <section style="background:#111;padding:52px 40px;border-top:1px solid #1e1e1e;border-bottom:1px solid #1e1e1e;">
    <div style="max-width:900px;margin:0 auto;display:flex;flex-wrap:wrap;justify-content:space-around;gap:32px;text-align:center;">
      <div><div style="font-size:44px;font-weight:900;color:#e4522b;line-height:1;">100%</div><div style="color:#888;font-size:13px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;margin-top:8px;">Remote First</div></div>
      <div><div style="font-size:44px;font-weight:900;color:#e4522b;line-height:1;">15+</div><div style="color:#888;font-size:13px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;margin-top:8px;">Team Members</div></div>
      <div><div style="font-size:44px;font-weight:900;color:#e4522b;line-height:1;">5</div><div style="color:#888;font-size:13px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;margin-top:8px;">Countries</div></div>
      <div><div style="font-size:44px;font-weight:900;color:#e4522b;line-height:1;">Fast</div><div style="color:#888;font-size:13px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;margin-top:8px;">Career Growth</div></div>
    </div>
  </section>

  <!-- Why Join -->
  <section style="background:#0d0d0d;padding:80px 40px;">
    <div style="max-width:1100px;margin:0 auto;">
      <div style="margin-bottom:52px;">
        <div style="width:60px;height:3px;background:#e4522b;border-radius:2px;margin-bottom:20px;"></div>
        <h2 style="color:#fff;font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 12px;">Why Work at BRND GURU?</h2>
        <p style="color:#aaa;font-size:16px;max-width:540px;margin:0;line-height:1.7;">We move fast, reward performance, and give everyone room to own their work.</p>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px;">
        <div style="background:#141414;border:1px solid #222;border-top:3px solid #e4522b;border-radius:12px;padding:32px;">
          <div style="font-size:28px;margin-bottom:14px;">🌍</div>
          <h3 style="color:#fff;font-size:18px;font-weight:700;margin:0 0 10px;">Remote First</h3>
          <p style="color:#aaa;font-size:14px;line-height:1.7;margin:0;">Work from anywhere. We hire for skills, not geography. Async by default, with regular team calls.</p>
        </div>
        <div style="background:#141414;border:1px solid #222;border-top:3px solid #e4522b;border-radius:12px;padding:32px;">
          <div style="font-size:28px;margin-bottom:14px;">📈</div>
          <h3 style="color:#fff;font-size:18px;font-weight:700;margin:0 0 10px;">Real Ownership</h3>
          <p style="color:#aaa;font-size:14px;line-height:1.7;margin:0;">You own your work end-to-end. No micromanagement. If you deliver, you grow — in responsibility and compensation.</p>
        </div>
        <div style="background:#141414;border:1px solid #222;border-top:3px solid #e4522b;border-radius:12px;padding:32px;">
          <div style="font-size:28px;margin-bottom:14px;">🚀</div>
          <h3 style="color:#fff;font-size:18px;font-weight:700;margin:0 0 10px;">Fast Growth</h3>
          <p style="color:#aaa;font-size:14px;line-height:1.7;margin:0;">Small team, big impact. You will build skills in outbound, automation, and B2B strategy that take years to learn elsewhere.</p>
        </div>
        <div style="background:#141414;border:1px solid #222;border-top:3px solid #e4522b;border-radius:12px;padding:32px;">
          <div style="font-size:28px;margin-bottom:14px;">🛠️</div>
          <h3 style="color:#fff;font-size:18px;font-weight:700;margin:0 0 10px;">Best-in-Class Tools</h3>
          <p style="color:#aaa;font-size:14px;line-height:1.7;margin:0;">Work with HeyReach, Aimfox, ManyReach, n8n, GoHighLevel, and the latest AI tools — before anyone else does.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Open Roles -->
  <section id="open-roles" style="background:#111;padding:80px 40px;">
    <div style="max-width:860px;margin:0 auto;">
      <div style="margin-bottom:52px;">
        <div style="width:60px;height:3px;background:#e4522b;border-radius:2px;margin-bottom:20px;"></div>
        <h2 style="color:#fff;font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 12px;">Open Roles</h2>
        <p style="color:#aaa;font-size:16px;margin:0;">All roles are remote. We hire globally.</p>
      </div>
      <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Role 1 -->
        <div style="background:#141414;border:1px solid #222;border-radius:12px;padding:28px 32px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;transition:border-color .2s;" onmouseover="this.style.borderColor=\'#e4522b\'" onmouseout="this.style.borderColor=\'#222\'">
          <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
              <h3 style="color:#fff;font-size:17px;font-weight:700;margin:0;">LinkedIn Outreach Specialist</h3>
              <span style="background:rgba(228,82,43,0.1);color:#e4522b;font-size:11px;font-weight:700;letter-spacing:1px;padding:4px 10px;border-radius:999px;border:1px solid rgba(228,82,43,0.2);text-transform:uppercase;">Full-time</span>
            </div>
            <p style="color:#888;font-size:14px;margin:0;">Remote · Outbound Team · HeyReach / Aimfox</p>
          </div>
          <a href="' . $BOOK . '" target="_blank" style="background:#e4522b;color:#fff;font-weight:700;font-size:14px;padding:12px 24px;border-radius:8px;text-decoration:none;white-space:nowrap;">Apply Now →</a>
        </div>

        <!-- Role 2 -->
        <div style="background:#141414;border:1px solid #222;border-radius:12px;padding:28px 32px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;" onmouseover="this.style.borderColor=\'#e4522b\'" onmouseout="this.style.borderColor=\'#222\'">
          <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
              <h3 style="color:#fff;font-size:17px;font-weight:700;margin:0;">Cold Email Copywriter</h3>
              <span style="background:rgba(228,82,43,0.1);color:#e4522b;font-size:11px;font-weight:700;letter-spacing:1px;padding:4px 10px;border-radius:999px;border:1px solid rgba(228,82,43,0.2);text-transform:uppercase;">Full-time</span>
            </div>
            <p style="color:#888;font-size:14px;margin:0;">Remote · Email Team · ManyReach</p>
          </div>
          <a href="' . $BOOK . '" target="_blank" style="background:#e4522b;color:#fff;font-weight:700;font-size:14px;padding:12px 24px;border-radius:8px;text-decoration:none;white-space:nowrap;">Apply Now →</a>
        </div>

        <!-- Role 3 -->
        <div style="background:#141414;border:1px solid #222;border-radius:12px;padding:28px 32px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;" onmouseover="this.style.borderColor=\'#e4522b\'" onmouseout="this.style.borderColor=\'#222\'">
          <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
              <h3 style="color:#fff;font-size:17px;font-weight:700;margin:0;">n8n / AI Automation Engineer</h3>
              <span style="background:rgba(228,82,43,0.1);color:#e4522b;font-size:11px;font-weight:700;letter-spacing:1px;padding:4px 10px;border-radius:999px;border:1px solid rgba(228,82,43,0.2);text-transform:uppercase;">Full-time</span>
            </div>
            <p style="color:#888;font-size:14px;margin:0;">Remote · Automation Team · n8n / AI</p>
          </div>
          <a href="' . $BOOK . '" target="_blank" style="background:#e4522b;color:#fff;font-weight:700;font-size:14px;padding:12px 24px;border-radius:8px;text-decoration:none;white-space:nowrap;">Apply Now →</a>
        </div>

        <!-- Role 4 -->
        <div style="background:#141414;border:1px solid #222;border-radius:12px;padding:28px 32px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;" onmouseover="this.style.borderColor=\'#e4522b\'" onmouseout="this.style.borderColor=\'#222\'">
          <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
              <h3 style="color:#fff;font-size:17px;font-weight:700;margin:0;">GoHighLevel CRM Specialist</h3>
              <span style="background:rgba(228,82,43,0.1);color:#e4522b;font-size:11px;font-weight:700;letter-spacing:1px;padding:4px 10px;border-radius:999px;border:1px solid rgba(228,82,43,0.2);text-transform:uppercase;">Full-time</span>
            </div>
            <p style="color:#888;font-size:14px;margin:0;">Remote · CRM Team · GoHighLevel</p>
          </div>
          <a href="' . $BOOK . '" target="_blank" style="background:#e4522b;color:#fff;font-weight:700;font-size:14px;padding:12px 24px;border-radius:8px;text-decoration:none;white-space:nowrap;">Apply Now →</a>
        </div>

        <!-- Role 5 -->
        <div style="background:#141414;border:1px solid #222;border-radius:12px;padding:28px 32px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;" onmouseover="this.style.borderColor=\'#e4522b\'" onmouseout="this.style.borderColor=\'#222\'">
          <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
              <h3 style="color:#fff;font-size:17px;font-weight:700;margin:0;">B2B Account Manager</h3>
              <span style="background:rgba(228,82,43,0.1);color:#e4522b;font-size:11px;font-weight:700;letter-spacing:1px;padding:4px 10px;border-radius:999px;border:1px solid rgba(228,82,43,0.2);text-transform:uppercase;">Full-time</span>
            </div>
            <p style="color:#888;font-size:14px;margin:0;">Remote · Client Success · B2B Strategy</p>
          </div>
          <a href="' . $BOOK . '" target="_blank" style="background:#e4522b;color:#fff;font-weight:700;font-size:14px;padding:12px 24px;border-radius:8px;text-decoration:none;white-space:nowrap;">Apply Now →</a>
        </div>

      </div>
    </div>
  </section>

  <!-- Speculative CTA -->
  <section style="background:linear-gradient(135deg,#e4522b,#c03b1e);padding:80px 40px;text-align:center;position:relative;overflow:hidden;">
    <div style="position:relative;z-index:1;max-width:600px;margin:0 auto;">
      <h2 style="color:#fff;font-size:clamp(26px,4vw,40px);font-weight:800;margin:0 0 16px;">Don\'t See Your Role?</h2>
      <p style="color:rgba(255,255,255,0.88);font-size:17px;line-height:1.7;margin:0 0 36px;">
        We are always open to exceptional people. Send us a note — tell us what you do and how you can help BRND GURU grow.
      </p>
      <a href="mailto:hello@brndguru.com" style="display:inline-block;background:#fff;color:#e4522b;font-weight:800;font-size:16px;padding:16px 40px;border-radius:8px;text-decoration:none;">Send a Speculative Application →</a>
    </div>
  </section>

</div>';

    $log[] = bgcc2_save(21, 'Careers', 'careers', $careers);

    /* ═══════════════════════════════════════════════════════
       CONTACT PAGE (ID: 23)
    ═══════════════════════════════════════════════════════ */
    $contact = '
<div style="font-family:inherit;background:#0d0d0d;color:#fff;">

  <!-- Hero -->
  <section style="background:linear-gradient(135deg,#0d0d0d 55%,#1a0800 100%);padding:100px 40px 80px;position:relative;overflow:hidden;">
    <div style="position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(228,82,43,0.1) 0%,transparent 70%);top:-100px;right:-50px;border-radius:50%;pointer-events:none;"></div>
    <div style="max-width:800px;margin:0 auto;position:relative;z-index:1;text-align:center;">
      <p style="color:#e4522b;font-weight:700;font-size:12px;letter-spacing:2.5px;text-transform:uppercase;margin:0 0 20px;">GET IN TOUCH</p>
      <h1 style="color:#fff;font-size:clamp(36px,6vw,62px);font-weight:800;margin:0 0 24px;line-height:1.1;">Let\'s Grow<br><span style="color:#e4522b;">Your Business.</span></h1>
      <p style="color:#aaa;font-size:clamp(16px,2vw,19px);max-width:560px;margin:0 auto 40px;line-height:1.7;">
        Ready to build a smarter outbound system? Tell us about your business — we reply within 24 hours.
      </p>
      <a href="' . $BOOK . '" target="_blank" style="display:inline-block;background:#e4522b;color:#fff;font-weight:700;font-size:16px;padding:16px 36px;border-radius:8px;text-decoration:none;">Book a Free Strategy Call →</a>
    </div>
  </section>

  <!-- Contact Info Cards -->
  <section style="background:#111;padding:72px 40px;border-top:1px solid #1e1e1e;">
    <div style="max-width:1000px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px;">

      <div style="background:#141414;border:1px solid #222;border-top:3px solid #e4522b;border-radius:12px;padding:36px 28px;text-align:center;">
        <div style="font-size:32px;margin-bottom:16px;">📍</div>
        <h3 style="color:#fff;font-size:17px;font-weight:700;margin:0 0 10px;">Headquarters</h3>
        <p style="color:#aaa;font-size:14px;line-height:1.7;margin:0;">London, United Kingdom<br><span style="color:#666;font-size:13px;">Remote-first agency</span></p>
      </div>

      <div style="background:#141414;border:1px solid #222;border-top:3px solid #e4522b;border-radius:12px;padding:36px 28px;text-align:center;">
        <div style="font-size:32px;margin-bottom:16px;">📧</div>
        <h3 style="color:#fff;font-size:17px;font-weight:700;margin:0 0 10px;">Email Us</h3>
        <p style="color:#aaa;font-size:14px;line-height:1.7;margin:0;"><a href="mailto:hello@brndguru.com" style="color:#e4522b;text-decoration:none;">hello@brndguru.com</a><br><span style="color:#666;font-size:13px;">We reply within 24 hours</span></p>
      </div>

      <div style="background:#141414;border:1px solid #222;border-top:3px solid #e4522b;border-radius:12px;padding:36px 28px;text-align:center;">
        <div style="font-size:32px;margin-bottom:16px;">📅</div>
        <h3 style="color:#fff;font-size:17px;font-weight:700;margin:0 0 10px;">Book a Call</h3>
        <p style="color:#aaa;font-size:14px;line-height:1.7;margin:0 0 16px;">Free 30-minute strategy session — no commitment.</p>
        <a href="' . $BOOK . '" target="_blank" style="display:inline-block;background:#e4522b;color:#fff;font-weight:700;font-size:13px;padding:10px 20px;border-radius:6px;text-decoration:none;">Schedule Now →</a>
      </div>

      <div style="background:#141414;border:1px solid #222;border-top:3px solid #e4522b;border-radius:12px;padding:36px 28px;text-align:center;">
        <div style="font-size:32px;margin-bottom:16px;">🌍</div>
        <h3 style="color:#fff;font-size:17px;font-weight:700;margin:0 0 10px;">Working Hours</h3>
        <p style="color:#aaa;font-size:14px;line-height:1.7;margin:0;">UK · Europe · North America · Australia<br><span style="color:#666;font-size:13px;">GMT to GMT+11</span></p>
      </div>

    </div>
  </section>

  <!-- Contact Form Section -->
  <section style="background:#0d0d0d;padding:80px 40px;">
    <div style="max-width:720px;margin:0 auto;">
      <div style="margin-bottom:48px;text-align:center;">
        <div style="width:60px;height:3px;background:#e4522b;border-radius:2px;margin:0 auto 20px;"></div>
        <h2 style="color:#fff;font-size:clamp(24px,4vw,38px);font-weight:800;margin:0 0 12px;">Send Us a Message</h2>
        <p style="color:#aaa;font-size:16px;margin:0;line-height:1.7;">Tell us about your business and what you are trying to achieve. We will get back to you within 24 hours.</p>
      </div>
      <div style="background:#141414;border:1px solid #222;border-radius:16px;padding:48px 40px;">
        [wpforms id="1" title="false"]
      </div>
    </div>
  </section>

  <!-- Social / Follow -->
  <section style="background:#111;padding:60px 40px;text-align:center;border-top:1px solid #1e1e1e;">
    <div style="max-width:600px;margin:0 auto;">
      <h3 style="color:#fff;font-size:22px;font-weight:700;margin:0 0 8px;">Follow Us</h3>
      <p style="color:#888;font-size:14px;margin:0 0 28px;">Stay updated with the latest in B2B outbound and automation.</p>
      <div style="display:flex;justify-content:center;gap:16px;flex-wrap:wrap;">
        <a href="https://www.linkedin.com/company/brndguru/" target="_blank" rel="noopener"
           style="display:inline-flex;align-items:center;gap:8px;background:#141414;color:#fff;font-weight:600;font-size:14px;padding:12px 24px;border-radius:8px;text-decoration:none;border:1px solid #333;transition:border-color .2s;"
           onmouseover="this.style.borderColor=\'#e4522b\'" onmouseout="this.style.borderColor=\'#333\'">
          <svg width="18" height="18" fill="#e4522b" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
          LinkedIn
        </a>
        <a href="https://twitter.com/brndguru" target="_blank" rel="noopener"
           style="display:inline-flex;align-items:center;gap:8px;background:#141414;color:#fff;font-weight:600;font-size:14px;padding:12px 24px;border-radius:8px;text-decoration:none;border:1px solid #333;"
           onmouseover="this.style.borderColor=\'#e4522b\'" onmouseout="this.style.borderColor=\'#333\'">
          <svg width="18" height="18" fill="#e4522b" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          X (Twitter)
        </a>
        <a href="https://instagram.com/brndguru" target="_blank" rel="noopener"
           style="display:inline-flex;align-items:center;gap:8px;background:#141414;color:#fff;font-weight:600;font-size:14px;padding:12px 24px;border-radius:8px;text-decoration:none;border:1px solid #333;"
           onmouseover="this.style.borderColor=\'#e4522b\'" onmouseout="this.style.borderColor=\'#333\'">
          <svg width="18" height="18" fill="#e4522b" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
          Instagram
        </a>
      </div>
    </div>
  </section>

</div>';

    $log[] = bgcc2_save(23, 'Contact', 'contact', $contact);

    // Clear all caches
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    foreach ([WP_CONTENT_DIR.'/cache/swift-performance/', WP_CONTENT_DIR.'/cache/swift-performance-lite/'] as $dir) {
        if (is_dir($dir)) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($it as $f) { $f->isDir() ? rmdir($f) : unlink($f); }
        }
    }
    if (class_exists('Swift_Performance')) do_action('swift_performance_clear_all_cache');
    wp_cache_flush();
    flush_rewrite_rules(true);

    update_option('bgcc2_log', $log);
    update_option('bgcc2_done', '1');
}

add_action('admin_notices', function () {
    if (get_option('bgcc2_done') !== '1') return;
    $log = get_option('bgcc2_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ Careers + Contact rebuilt to match Portfolio design!</h3>
        <ul style="margin:0 0 10px;padding-left:20px;">
            <?php foreach ($log as $l): ?><li><?php echo esc_html($l); ?></li><?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/careers/'); ?>" target="_blank" class="button button-primary">Careers →</a>
            <a href="<?php echo home_url('/contact/'); ?>" target="_blank" class="button">Contact →</a>
            &nbsp; Deactivate + delete when done.
        </p>
    </div>
    <?php
});
