<?php
/**
 * Plugin Name: BrndGuru — About, Home & Blog Redesign
 * Description: Dark theme with visuals for About Us, Home, and Blog pages.
 * Version: 2.0
 */
if (!defined('ABSPATH')) exit;

/* ══════════════════════════════════════════════════════════
   GLOBAL CSS — dark-force on Home & About + Blog styling
══════════════════════════════════════════════════════════ */
add_action('wp_head', function () { ?>
<style id="bg-ahb-css">

/* ── Force dark bg on Home & About Elementor sections ── */
body.page-id-12 .elementor-section,
body.page-id-12 .elementor-top-section,
body.page-id-1628 .elementor-section,
body.page-id-1628 .elementor-top-section {
    background-color: #0d0d0d !important;
}
body.page-id-12,
body.page-id-1628 {
    background: #0d0d0d !important;
}
/* White text on dark sections */
body.page-id-12 .elementor-widget-heading .elementor-heading-title,
body.page-id-1628 .elementor-widget-heading .elementor-heading-title {
    color: #ffffff !important;
}
body.page-id-12 .elementor-widget-text-editor,
body.page-id-1628 .elementor-widget-text-editor {
    color: #aaaaaa !important;
}

/* ══════════════════════════════
   BLOG PAGE FULL REDESIGN
══════════════════════════════ */
body.blog,
body.archive,
body.home.blog {
    background: #0d0d0d !important;
}

/* Blog hero section (injected above posts) */
.bg-blog-hero {
    background: linear-gradient(135deg, #0d0d0d 60%, #1a0800 100%);
    padding: 96px 40px 80px;
    position: relative;
    overflow: hidden;
    text-align: center;
}
.bg-blog-hero::before {
    content: '';
    position: absolute;
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(228,82,43,0.1) 0%, transparent 70%);
    top: -200px; right: -100px;
    border-radius: 50%;
    pointer-events: none;
}

/* Post cards grid */
.bg-blog-grid {
    max-width: 1160px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 28px;
    padding: 0 40px 80px;
}
@media (max-width: 768px) { .bg-blog-grid { grid-template-columns: 1fr; padding: 0 20px 60px; } }

/* Individual post card */
.bg-post-card {
    background: #141414;
    border: 1px solid #1e1e1e;
    border-top: 3px solid #e4522b;
    border-radius: 14px;
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease;
    display: flex;
    flex-direction: column;
}
.bg-post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(228,82,43,0.15);
}
.bg-post-card .card-img {
    width: 100%; height: 200px;
    object-fit: cover;
    display: block;
    transition: transform .4s ease;
}
.bg-post-card:hover .card-img { transform: scale(1.04); }
.bg-post-card .card-img-placeholder {
    width: 100%; height: 200px;
    background: linear-gradient(135deg, #1a1a1a, #111);
    display: flex; align-items: center; justify-content: center;
    font-size: 48px; opacity: .3;
}
.bg-post-card .card-body {
    padding: 24px 28px 28px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.bg-post-card .card-cat {
    display: inline-block;
    background: rgba(228,82,43,0.1);
    color: #e4522b;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 999px;
    border: 1px solid rgba(228,82,43,0.2);
    margin-bottom: 14px;
    text-decoration: none;
}
.bg-post-card .card-title {
    color: #ffffff !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1.35 !important;
    margin: 0 0 12px !important;
    text-decoration: none !important;
}
.bg-post-card .card-title a {
    color: #ffffff !important;
    text-decoration: none !important;
}
.bg-post-card .card-title a:hover { color: #e4522b !important; }
.bg-post-card .card-excerpt {
    color: #888;
    font-size: 14px;
    line-height: 1.75;
    margin: 0 0 20px;
    flex: 1;
}
.bg-post-card .card-meta {
    font-size: 12px;
    color: #555;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.bg-post-card .card-read-more {
    display: inline-block;
    background: #e4522b;
    color: #fff !important;
    font-weight: 700;
    font-size: 13px;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none !important;
    transition: opacity .2s;
    align-self: flex-start;
}
.bg-post-card .card-read-more:hover { opacity: .85; color: #fff !important; }

/* Pagination */
.bg-blog-pagination {
    text-align: center;
    padding: 20px 40px 80px;
}
.bg-blog-pagination .page-numbers {
    display: inline-block;
    background: #141414;
    border: 1px solid #222;
    color: #fff !important;
    padding: 10px 16px;
    margin: 3px;
    border-radius: 8px;
    text-decoration: none !important;
    font-weight: 600;
    transition: all .2s;
}
.bg-blog-pagination .page-numbers.current,
.bg-blog-pagination .page-numbers:hover {
    background: #e4522b !important;
    border-color: #e4522b !important;
    color: #fff !important;
}

/* Hide default WP blog markup so our grid takes over */
body.blog .site-main > article,
body.blog .posts-navigation,
body.blog #primary > article {
    display: none !important;
}
/* Also hide default post loop if theme outputs it */
body.blog .ast-article-post,
body.blog .ast-grid-post { display: none !important; }

</style>
<?php }, 10);

/* ══════════════════════════════════════════════════════════
   HOME PAGE — inject stats + process sections
══════════════════════════════════════════════════════════ */
add_filter('the_content', function ($content) {
    if (!is_page(12)) return $content;

    $stats = '
<section style="background:#111;padding:52px 40px;border-top:3px solid #e4522b;border-bottom:1px solid #1e1e1e;">
  <div style="max-width:1000px;margin:0 auto;display:flex;flex-wrap:wrap;justify-content:space-around;gap:32px;text-align:center;">
    <div><div style="font-size:52px;font-weight:900;color:#e4522b;line-height:1;">200+</div><div style="color:#666;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-top:8px;">B2B Clients</div></div>
    <div><div style="font-size:52px;font-weight:900;color:#e4522b;line-height:1;">£50M+</div><div style="color:#666;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-top:8px;">Pipeline Generated</div></div>
    <div><div style="font-size:52px;font-weight:900;color:#e4522b;line-height:1;">98%</div><div style="color:#666;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-top:8px;">Client Retention</div></div>
    <div><div style="font-size:52px;font-weight:900;color:#e4522b;line-height:1;">3x</div><div style="color:#666;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-top:8px;">Avg. Pipeline Growth</div></div>
  </div>
</section>';

    $services_grid = '
<section style="background:#0d0d0d;padding:96px 40px;background-image:radial-gradient(circle,#1a1a1a 1px,transparent 1px);background-size:28px 28px;">
  <div style="max-width:1160px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:60px;">
      <div style="width:60px;height:3px;background:linear-gradient(90deg,#e4522b,#ff7a57);border-radius:2px;margin:0 auto 20px;"></div>
      <h2 style="color:#fff;font-size:clamp(26px,4vw,42px);font-weight:800;margin:0 0 14px;background:linear-gradient(135deg,#fff 50%,#e4522b);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">What We Build For You</h2>
      <p style="color:#888;font-size:17px;max-width:520px;margin:0 auto;line-height:1.7;">Five outbound services. One goal — a full pipeline.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
      <a href="/services/linkedin-outreach-automation/" style="background:#141414;border:1px solid #1e1e1e;border-top:3px solid #e4522b;border-radius:14px;padding:28px 24px;text-decoration:none;transition:all .25s;display:block;">
        <div style="width:44px;height:44px;background:rgba(228,82,43,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="2" y="2" width="20" height="20" rx="4" stroke="#e4522b" stroke-width="2"/><path d="M8 11h8M8 15h5" stroke="#e4522b" stroke-width="1.5" stroke-linecap="round"/><circle cx="8" cy="7.5" r="1.5" fill="#e4522b"/></svg>
        </div>
        <h3 style="color:#fff;font-size:15px;font-weight:700;margin:0 0 8px;">LinkedIn Automation</h3>
        <p style="color:#666;font-size:13px;margin:0;line-height:1.6;">Personalised outreach at scale.</p>
      </a>
      <a href="/services/cold-email-infrastructure/" style="background:#141414;border:1px solid #1e1e1e;border-top:3px solid #e4522b;border-radius:14px;padding:28px 24px;text-decoration:none;transition:all .25s;display:block;">
        <div style="width:44px;height:44px;background:rgba(228,82,43,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="16" rx="3" stroke="#e4522b" stroke-width="2"/><path d="M2 7l10 7 10-7" stroke="#e4522b" stroke-width="1.5"/></svg>
        </div>
        <h3 style="color:#fff;font-size:15px;font-weight:700;margin:0 0 8px;">Cold Email</h3>
        <p style="color:#666;font-size:13px;margin:0;line-height:1.6;">Land in inboxes. Get replies.</p>
      </a>
      <a href="/services/ai-agent-development/" style="background:#141414;border:1px solid #1e1e1e;border-top:3px solid #e4522b;border-radius:14px;padding:28px 24px;text-decoration:none;transition:all .25s;display:block;">
        <div style="width:44px;height:44px;background:rgba(228,82,43,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="#e4522b" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#e4522b" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <h3 style="color:#fff;font-size:15px;font-weight:700;margin:0 0 8px;">AI Agents</h3>
        <p style="color:#666;font-size:13px;margin:0;line-height:1.6;">Research. Personalise. Follow up.</p>
      </a>
      <a href="/services/gohighlevel-crm/" style="background:#141414;border:1px solid #1e1e1e;border-top:3px solid #e4522b;border-radius:14px;padding:28px 24px;text-decoration:none;transition:all .25s;display:block;">
        <div style="width:44px;height:44px;background:rgba(228,82,43,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M3 3h18v4H3zM3 10h11v4H3zM3 17h7v4H3z" stroke="#e4522b" stroke-width="2" stroke-linejoin="round"/></svg>
        </div>
        <h3 style="color:#fff;font-size:15px;font-weight:700;margin:0 0 8px;">GoHighLevel CRM</h3>
        <p style="color:#666;font-size:13px;margin:0;line-height:1.6;">Pipeline automation that scales.</p>
      </a>
      <a href="/services/n8n-workflow-automation/" style="background:#141414;border:1px solid #1e1e1e;border-top:3px solid #e4522b;border-radius:14px;padding:28px 24px;text-decoration:none;transition:all .25s;display:block;">
        <div style="width:44px;height:44px;background:rgba(228,82,43,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="5" cy="12" r="2" stroke="#e4522b" stroke-width="2"/><circle cx="19" cy="5" r="2" stroke="#e4522b" stroke-width="2"/><circle cx="19" cy="19" r="2" stroke="#e4522b" stroke-width="2"/><path d="M7 12h4l4-5M11 12l4 5" stroke="#e4522b" stroke-width="1.5" stroke-linecap="round"/></svg>
        </div>
        <h3 style="color:#fff;font-size:15px;font-weight:700;margin:0 0 8px;">n8n Automation</h3>
        <p style="color:#666;font-size:13px;margin:0;line-height:1.6;">Connect everything. Automate it all.</p>
      </a>
    </div>
    <div style="text-align:center;margin-top:44px;">
      <a href="/services/" style="display:inline-block;background:#e4522b;color:#fff;font-weight:700;font-size:15px;padding:14px 36px;border-radius:8px;text-decoration:none;">View All Services →</a>
    </div>
  </div>
</section>';

    $photo_strip = '
<section style="background:#0a0a0a;padding:72px 40px;">
  <div style="max-width:1200px;margin:0 auto;display:flex;flex-wrap:wrap;gap:24px;justify-content:center;">
    <div style="flex:1 1 260px;max-width:340px;border-radius:12px;overflow:hidden;background:#141414;border:1px solid #1e1e1e;transition:transform .25s;">
      <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=700&q=80&fit=crop" alt="Strategy Sessions" loading="lazy" style="width:100%;height:220px;object-fit:cover;display:block;">
      <div style="padding:16px 20px;border-top:2px solid #e4522b;"><p style="color:#fff;font-size:13px;font-weight:700;margin:0;letter-spacing:.5px;">Strategy Sessions</p></div>
    </div>
    <div style="flex:1 1 260px;max-width:340px;border-radius:12px;overflow:hidden;background:#141414;border:1px solid #1e1e1e;">
      <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?w=700&q=80&fit=crop" alt="Outbound Execution" loading="lazy" style="width:100%;height:220px;object-fit:cover;display:block;">
      <div style="padding:16px 20px;border-top:2px solid #e4522b;"><p style="color:#fff;font-size:13px;font-weight:700;margin:0;letter-spacing:.5px;">Outbound Execution</p></div>
    </div>
    <div style="flex:1 1 260px;max-width:340px;border-radius:12px;overflow:hidden;background:#141414;border:1px solid #1e1e1e;">
      <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=700&q=80&fit=crop" alt="Pipeline Analytics" loading="lazy" style="width:100%;height:220px;object-fit:cover;display:block;">
      <div style="padding:16px 20px;border-top:2px solid #e4522b;"><p style="color:#fff;font-size:13px;font-weight:700;margin:0;letter-spacing:.5px;">Pipeline Analytics</p></div>
    </div>
  </div>
</section>';

    $mid_banner = '
<section style="position:relative;min-height:420px;background-image:url(https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&q=80&fit=crop);background-size:cover;background-position:center;background-attachment:fixed;display:flex;align-items:center;justify-content:center;">
  <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,10,10,0.88),rgba(228,82,43,0.2));"></div>
  <div style="position:relative;z-index:1;text-align:center;padding:60px 40px;max-width:680px;">
    <div style="width:60px;height:3px;background:#e4522b;border-radius:2px;margin:0 auto 24px;"></div>
    <h2 style="color:#fff;font-size:clamp(26px,4vw,44px);font-weight:800;margin:0 0 16px;line-height:1.2;">Ready to Fill Your Pipeline?</h2>
    <p style="color:rgba(255,255,255,0.82);font-size:17px;line-height:1.6;margin:0 0 36px;">Book a free strategy call. We will audit your current outbound and show you exactly where the revenue is being left on the table.</p>
    <a href="/contact/" style="display:inline-block;background:#e4522b;color:#fff;font-weight:700;font-size:16px;padding:16px 40px;border-radius:8px;text-decoration:none;">Book Free Strategy Call →</a>
  </div>
</section>';

    // NOTE: photo_strip is handled by visuals plugin — not duplicated here
    $content = $content . $stats . $services_grid . $mid_banner;
    return $content;
}, 20);

/* ══════════════════════════════════════════════════════════
   ABOUT US PAGE — inject stats + values + team vision
══════════════════════════════════════════════════════════ */
add_filter('the_content', function ($content) {
    if (!is_page(1628)) return $content;

    $stats = '
<section style="background:#111;padding:52px 40px;border-top:3px solid #e4522b;border-bottom:1px solid #1e1e1e;">
  <div style="max-width:1000px;margin:0 auto;display:flex;flex-wrap:wrap;justify-content:space-around;gap:32px;text-align:center;">
    <div><div style="font-size:52px;font-weight:900;color:#e4522b;line-height:1;">2019</div><div style="color:#666;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-top:8px;">Founded</div></div>
    <div><div style="font-size:52px;font-weight:900;color:#e4522b;line-height:1;">15+</div><div style="color:#666;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-top:8px;">Team Members</div></div>
    <div><div style="font-size:52px;font-weight:900;color:#e4522b;line-height:1;">200+</div><div style="color:#666;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-top:8px;">Clients Served</div></div>
    <div><div style="font-size:52px;font-weight:900;color:#e4522b;line-height:1;">5</div><div style="color:#666;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-top:8px;">Countries</div></div>
  </div>
</section>';

    $values = '
<section style="background:#0d0d0d;padding:96px 40px;background-image:radial-gradient(circle,#1a1a1a 1px,transparent 1px);background-size:28px 28px;">
  <div style="max-width:1100px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:60px;">
      <div style="width:60px;height:3px;background:linear-gradient(90deg,#e4522b,#ff7a57);border-radius:2px;margin:0 auto 20px;"></div>
      <h2 style="color:#fff;font-size:clamp(26px,4vw,42px);font-weight:800;margin:0 0 14px;">What Drives Us</h2>
      <p style="color:#888;font-size:17px;max-width:520px;margin:0 auto;line-height:1.7;">Three principles we do not compromise on.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:28px;">
      <div style="background:#141414;border:1px solid #1e1e1e;border-top:3px solid #e4522b;border-radius:14px;padding:36px 32px;position:relative;overflow:hidden;">
        <div style="font-size:48px;font-weight:900;color:rgba(228,82,43,0.08);position:absolute;top:16px;right:20px;line-height:1;">01</div>
        <h3 style="color:#fff;font-size:20px;font-weight:700;margin:0 0 14px;">Results, Not Reports</h3>
        <p style="color:#888;font-size:15px;line-height:1.75;margin:0;">We measure ourselves by pipeline generated, meetings booked, and clients retained — not decks delivered or hours logged.</p>
      </div>
      <div style="background:#141414;border:1px solid #1e1e1e;border-top:3px solid #e4522b;border-radius:14px;padding:36px 32px;position:relative;overflow:hidden;">
        <div style="font-size:48px;font-weight:900;color:rgba(228,82,43,0.08);position:absolute;top:16px;right:20px;line-height:1;">02</div>
        <h3 style="color:#fff;font-size:20px;font-weight:700;margin:0 0 14px;">Radical Transparency</h3>
        <p style="color:#888;font-size:15px;line-height:1.75;margin:0;">You always know what we are doing, why we are doing it, and what the numbers say. No fluff. No hiding behind jargon.</p>
      </div>
      <div style="background:#141414;border:1px solid #1e1e1e;border-top:3px solid #e4522b;border-radius:14px;padding:36px 32px;position:relative;overflow:hidden;">
        <div style="font-size:48px;font-weight:900;color:rgba(228,82,43,0.08);position:absolute;top:16px;right:20px;line-height:1;">03</div>
        <h3 style="color:#fff;font-size:20px;font-weight:700;margin:0 0 14px;">Built to Scale</h3>
        <p style="color:#888;font-size:15px;line-height:1.75;margin:0;">Every system we build is designed to grow with you. Start with one channel. Add more as your pipeline expands.</p>
      </div>
    </div>
  </div>
</section>';

    $photo_strip = '
<section style="background:#0a0a0a;padding:72px 40px;">
  <div style="max-width:1200px;margin:0 auto;display:flex;flex-wrap:wrap;gap:24px;justify-content:center;">
    <div style="flex:1 1 260px;max-width:340px;border-radius:12px;overflow:hidden;border:1px solid #1e1e1e;">
      <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=700&q=80&fit=crop" alt="Our Team" loading="lazy" style="width:100%;height:220px;object-fit:cover;display:block;">
      <div style="background:#141414;padding:16px 20px;border-top:2px solid #e4522b;"><p style="color:#fff;font-size:13px;font-weight:700;margin:0;">Our Team</p></div>
    </div>
    <div style="flex:1 1 260px;max-width:340px;border-radius:12px;overflow:hidden;border:1px solid #1e1e1e;">
      <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=700&q=80&fit=crop" alt="London HQ" loading="lazy" style="width:100%;height:220px;object-fit:cover;display:block;">
      <div style="background:#141414;padding:16px 20px;border-top:2px solid #e4522b;"><p style="color:#fff;font-size:13px;font-weight:700;margin:0;">London HQ</p></div>
    </div>
    <div style="flex:1 1 260px;max-width:340px;border-radius:12px;overflow:hidden;border:1px solid #1e1e1e;">
      <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=700&q=80&fit=crop" alt="Client Work" loading="lazy" style="width:100%;height:220px;object-fit:cover;display:block;">
      <div style="background:#141414;padding:16px 20px;border-top:2px solid #e4522b;"><p style="color:#fff;font-size:13px;font-weight:700;margin:0;">Client Work</p></div>
    </div>
  </div>
</section>';

    $mid_banner = '
<section style="position:relative;min-height:400px;background-image:url(https://images.unsplash.com/photo-1557200134-90327ee9fafa?w=1600&q=80&fit=crop);background-size:cover;background-position:center;background-attachment:fixed;display:flex;align-items:center;justify-content:center;">
  <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,10,10,0.88),rgba(228,82,43,0.2));"></div>
  <div style="position:relative;z-index:1;text-align:center;padding:60px 40px;max-width:680px;">
    <div style="width:60px;height:3px;background:#e4522b;border-radius:2px;margin:0 auto 24px;"></div>
    <h2 style="color:#fff;font-size:clamp(26px,4vw,44px);font-weight:800;margin:0 0 16px;">We are BRND GURU.</h2>
    <p style="color:rgba(255,255,255,0.82);font-size:17px;line-height:1.6;margin:0 0 36px;">A London-based B2B consulting agency obsessed with one thing — filling your pipeline.</p>
    <a href="/portfolio/" style="display:inline-block;background:#e4522b;color:#fff;font-weight:700;font-size:16px;padding:16px 40px;border-radius:8px;text-decoration:none;">See Our Results →</a>
  </div>
</section>';

    // NOTE: photo_strip is handled by visuals plugin — not duplicated here
    $content = $content . $stats . $values . $mid_banner;
    return $content;
}, 20);

/* ══════════════════════════════════════════════════════════
   BLOG PAGE — custom dark post grid
══════════════════════════════════════════════════════════ */
add_action('loop_start', function ($query) {
    if (!$query->is_main_query() || !$query->is_home()) return;
    echo '
<div class="bg-blog-hero">
  <div style="position:relative;z-index:1;max-width:700px;margin:0 auto;">
    <p style="color:#e4522b;font-weight:700;font-size:12px;letter-spacing:2.5px;text-transform:uppercase;margin:0 0 16px;">INSIGHTS & UPDATES</p>
    <h1 style="color:#fff;font-size:clamp(36px,6vw,62px);font-weight:800;margin:0 0 20px;line-height:1.1;">The BRND GURU <span style="color:#e4522b;">Blog</span></h1>
    <p style="color:#888;font-size:clamp(15px,2vw,18px);line-height:1.7;margin:0;max-width:540px;margin:0 auto;">B2B outbound strategy, automation insights, and growth playbooks from our consulting team.</p>
  </div>
</div>
<div class="bg-blog-grid">';
});

add_action('loop_end', function ($query) {
    if (!$query->is_main_query() || !$query->is_home()) return;
    echo '</div>';
    // Pagination
    $pagination = get_the_posts_pagination([
        'mid_size'  => 2,
        'prev_text' => '← Prev',
        'next_text' => 'Next →',
    ]);
    if ($pagination) {
        echo '<div class="bg-blog-pagination">' . $pagination . '</div>';
    }
});

add_action('the_post', function () {
    if (!is_home()) return;
    global $post;

    $thumb = has_post_thumbnail($post->ID)
        ? '<img class="card-img" src="' . esc_url(get_the_post_thumbnail_url($post->ID, 'medium_large')) . '" alt="' . esc_attr(get_the_title()) . '">'
        : '<div class="card-img-placeholder">✍</div>';

    $cats = get_the_category($post->ID);
    $cat_html = '';
    if ($cats) {
        $cat_html = '<a href="' . esc_url(get_category_link($cats[0]->term_id)) . '" class="card-cat">' . esc_html($cats[0]->name) . '</a>';
    }

    $excerpt = wp_trim_words(get_the_excerpt(), 22, '...');
    $date = get_the_date('M j, Y', $post->ID);

    echo '
<article class="bg-post-card">
  <a href="' . esc_url(get_permalink()) . '">' . $thumb . '</a>
  <div class="card-body">
    ' . $cat_html . '
    <h2 class="card-title"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h2>
    <p class="card-excerpt">' . esc_html($excerpt) . '</p>
    <div class="card-meta"><span>' . esc_html($date) . '</span></div>
    <a href="' . esc_url(get_permalink()) . '" class="card-read-more">Read Article →</a>
  </div>
</article>';
});
