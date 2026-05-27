<?php
/**
 * Plugin Name: BrndGuru — Services Menu + Portfolio + Service Pages
 * Description: Renames nav menu items, updates Portfolio page, and rewrites all 5 service sub-pages with real BRND GURU content. AUTO-RUNS on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgspu_run');
add_action('admin_init', function () {
    if (get_option('bgspu_done') !== '1') bgspu_run();
});

/* ═══════════════════════════════════════════════════════
   HELPERS
═══════════════════════════════════════════════════════ */
function bgspu_save_page(int $id, string $title, string $slug, string $html): string {
    global $wpdb;

    // Update post
    $wpdb->update($wpdb->posts, [
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $html,
        'post_status'  => 'publish',
        'post_modified'=> current_time('mysql'),
    ], ['ID' => $id]);

    // Clear Elementor — use raw post_content, not Elementor builder
    $wpdb->delete($wpdb->postmeta, ['post_id' => $id, 'meta_key' => '_elementor_data']);
    update_post_meta($id, '_elementor_edit_mode', '');
    update_post_meta($id, '_wp_page_template', 'elementor_full_width');
    delete_post_meta($id, '_elementor_css');
    clean_post_cache($id);
    return "✓ {$title} saved";
}

function bgspu_btn(string $url, string $label, string $bg = '#e4522b'): string {
    return '<a href="' . esc_url($url) . '" target="_blank" rel="noopener"
        style="display:inline-block;background:' . $bg . ';color:#fff;font-weight:700;font-size:16px;
        padding:16px 36px;border-radius:8px;text-decoration:none;letter-spacing:.3px;margin-top:8px;">'
        . esc_html($label) . '</a>';
}

$BOOK = 'https://www.brndgurumedia.com/widget/bookings/brndguru';

/* ═══════════════════════════════════════════════════════
   MAIN
═══════════════════════════════════════════════════════ */
function bgspu_run() {
    global $wpdb, $BOOK;
    $BOOK = 'https://www.brndgurumedia.com/widget/bookings/brndguru';
    $log  = [];

    // ── 1. RENAME NAV MENU ITEMS ──────────────────────────────
    $menu_map = [
        'Brand Design'       => ['label' => 'LinkedIn Automation',   'url' => '/linkedin-outreach-automation/'],
        'UI/UX Design'       => ['label' => 'Cold Email',            'url' => '/cold-email-infrastructure/'],
        'Webflow Development'=> ['label' => 'AI Agents',             'url' => '/ai-agent-development/'],
        'No-Code Development'=> ['label' => 'GoHighLevel CRM',       'url' => '/gohighlevel-crm/'],
        'Shopify Xcelerator' => ['label' => 'n8n Automation',        'url' => '/n8n-automation/'],
    ];

    foreach ($menu_map as $old_title => $new) {
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type='nav_menu_item' AND post_title=%s AND post_status='publish'",
            $old_title
        ));
        foreach ($items as $item) {
            $wpdb->update($wpdb->posts, ['post_title' => $new['label']], ['ID' => $item->ID]);
            update_post_meta($item->ID, '_menu_item_title', $new['label']);
            clean_post_cache($item->ID);
            $log[] = "Nav: '{$old_title}' → '{$new['label']}'";
        }
    }

    // ── 2. PORTFOLIO PAGE (ID: 1483) ─────────────────────────
    $portfolio_html = '
<div style="font-family:inherit;background:#0a0a0a;color:#fff;">

  <!-- Hero -->
  <section style="background:linear-gradient(135deg,#0a0a0a 60%,#1a0a00);text-align:center;padding:100px 40px 80px;">
    <p style="color:#e4522b;font-weight:700;font-size:13px;letter-spacing:2px;text-transform:uppercase;margin:0 0 20px;">CLIENT RESULTS</p>
    <h1 style="color:#fff;font-size:clamp(36px,6vw,64px);font-weight:800;margin:0 0 20px;line-height:1.1;">
      Real Work. Real Results.<br><span style="color:#e4522b;">Real Growth.</span>
    </h1>
    <p style="color:#aaa;font-size:clamp(16px,2vw,20px);max-width:620px;margin:0 auto 40px;line-height:1.6;">
      Every engagement we take on is built around one thing — results. Here\'s a snapshot of what we\'ve delivered for B2B businesses across the UK, Europe, and North America.
    </p>
    <a href="' . $BOOK . '" target="_blank" rel="noopener"
       style="display:inline-block;background:#e4522b;color:#fff;font-weight:700;font-size:16px;padding:16px 36px;border-radius:8px;text-decoration:none;">
      Start Your Project →
    </a>
  </section>

  <!-- Stats Bar -->
  <section style="background:#111;padding:48px 40px;">
    <div style="max-width:900px;margin:0 auto;display:flex;flex-wrap:wrap;justify-content:space-around;gap:32px;text-align:center;">
      <div><div style="font-size:42px;font-weight:800;color:#e4522b;">200+</div><div style="color:#aaa;font-size:14px;margin-top:4px;">B2B Clients Served</div></div>
      <div><div style="font-size:42px;font-weight:800;color:#e4522b;">£50M+</div><div style="color:#aaa;font-size:14px;margin-top:4px;">Pipeline Generated</div></div>
      <div><div style="font-size:42px;font-weight:800;color:#e4522b;">5×</div><div style="color:#aaa;font-size:14px;margin-top:4px;">Average ROI</div></div>
      <div><div style="font-size:42px;font-weight:800;color:#e4522b;">98%</div><div style="color:#aaa;font-size:14px;margin-top:4px;">Client Retention</div></div>
    </div>
  </section>

  <!-- Case Studies Grid -->
  <section style="background:#0a0a0a;padding:80px 40px;">
    <div style="max-width:1200px;margin:0 auto;">
      <h2 style="text-align:center;color:#fff;font-size:clamp(28px,4vw,42px);font-weight:800;margin:0 0 60px;">Featured Engagements</h2>
      <div style="display:flex;flex-wrap:wrap;gap:32px;justify-content:center;">

        <!-- Card 1 -->
        <div style="background:#141414;border-radius:12px;overflow:hidden;flex:1 1 340px;max-width:520px;border:1px solid #222;">
          <div style="background:linear-gradient(135deg,#1a0500,#2d0c00);padding:48px 40px;">
            <span style="background:#e4522b;color:#fff;font-size:11px;font-weight:700;letter-spacing:1.5px;padding:4px 12px;border-radius:20px;">LINKEDIN AUTOMATION</span>
            <h3 style="color:#fff;font-size:26px;font-weight:800;margin:20px 0 10px;">SaaS Scale-Up</h3>
            <p style="color:#aaa;font-size:15px;margin:0;line-height:1.6;">3× Pipeline Growth in 60 Days</p>
          </div>
          <div style="padding:32px 40px;">
            <p style="color:#bbb;font-size:15px;line-height:1.7;margin:0 0 20px;">A UK-based SaaS firm struggling to fill their sales pipeline engaged BRND GURU for a full LinkedIn outreach build. Within 60 days, pipeline tripled using precision HeyReach sequences targeting C-suite buyers.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">HeyReach</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">Aimfox</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">+300% Pipeline</span>
            </div>
          </div>
        </div>

        <!-- Card 2 -->
        <div style="background:#141414;border-radius:12px;overflow:hidden;flex:1 1 340px;max-width:520px;border:1px solid #222;">
          <div style="background:linear-gradient(135deg,#0a0a1a,#0d0d2d);padding:48px 40px;">
            <span style="background:#e4522b;color:#fff;font-size:11px;font-weight:700;letter-spacing:1.5px;padding:4px 12px;border-radius:20px;">COLD EMAIL</span>
            <h3 style="color:#fff;font-size:26px;font-weight:800;margin:20px 0 10px;">Professional Services Firm</h3>
            <p style="color:#aaa;font-size:15px;margin:0;line-height:1.6;">40+ Qualified Meetings in 30 Days</p>
          </div>
          <div style="padding:32px 40px;">
            <p style="color:#bbb;font-size:15px;line-height:1.7;margin:0 0 20px;">A London-based professional services firm needed to reach decision-makers they couldn\'t get in front of. We built end-to-end cold email infrastructure via ManyReach — 40+ meetings booked in the first month.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">ManyReach</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">40+ Meetings</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">Month 1</span>
            </div>
          </div>
        </div>

        <!-- Card 3 -->
        <div style="background:#141414;border-radius:12px;overflow:hidden;flex:1 1 340px;max-width:520px;border:1px solid #222;">
          <div style="background:linear-gradient(135deg,#001a0a,#002d12);padding:48px 40px;">
            <span style="background:#e4522b;color:#fff;font-size:11px;font-weight:700;letter-spacing:1.5px;padding:4px 12px;border-radius:20px;">AI AGENTS</span>
            <h3 style="color:#fff;font-size:26px;font-weight:800;margin:20px 0 10px;">B2B Consultancy</h3>
            <p style="color:#aaa;font-size:15px;margin:0;line-height:1.6;">Full Outbound Automation with n8n AI</p>
          </div>
          <div style="padding:32px 40px;">
            <p style="color:#bbb;font-size:15px;line-height:1.7;margin:0 0 20px;">A fast-growing consultancy wanted to scale outbound without adding headcount. We built custom n8n AI agents that research prospects, personalise messages, and trigger follow-ups automatically — 24/7.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">n8n</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">AI Research</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">Zero Headcount Added</span>
            </div>
          </div>
        </div>

        <!-- Card 4 -->
        <div style="background:#141414;border-radius:12px;overflow:hidden;flex:1 1 340px;max-width:520px;border:1px solid #222;">
          <div style="background:linear-gradient(135deg,#1a1500,#2d2200);padding:48px 40px;">
            <span style="background:#e4522b;color:#fff;font-size:11px;font-weight:700;letter-spacing:1.5px;padding:4px 12px;border-radius:20px;">GOHIGHLEVEL CRM</span>
            <h3 style="color:#fff;font-size:26px;font-weight:800;margin:20px 0 10px;">Marketing Agency</h3>
            <p style="color:#aaa;font-size:15px;margin:0;line-height:1.6;">Full CRM & Pipeline Rebuild</p>
          </div>
          <div style="padding:32px 40px;">
            <p style="color:#bbb;font-size:15px;line-height:1.7;margin:0 0 20px;">A marketing agency operating without a proper CRM was losing deals in the follow-up stage. We rebuilt their entire pipeline on GoHighLevel — automations, workflows, reporting, and integrations all connected.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">GoHighLevel</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">Pipeline Automation</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">0 Lost Leads</span>
            </div>
          </div>
        </div>

        <!-- Card 5 -->
        <div style="background:#141414;border-radius:12px;overflow:hidden;flex:1 1 340px;max-width:520px;border:1px solid #222;">
          <div style="background:linear-gradient(135deg,#0a001a,#150030);padding:48px 40px;">
            <span style="background:#e4522b;color:#fff;font-size:11px;font-weight:700;letter-spacing:1.5px;padding:4px 12px;border-radius:20px;">N8N AUTOMATION</span>
            <h3 style="color:#fff;font-size:26px;font-weight:800;margin:20px 0 10px;">Scale-Up Tech Firm</h3>
            <p style="color:#aaa;font-size:15px;margin:0;line-height:1.6;">Full Stack Automation — 20hrs/week Saved</p>
          </div>
          <div style="padding:32px 40px;">
            <p style="color:#bbb;font-size:15px;line-height:1.7;margin:0 0 20px;">A growing tech firm was spending 20+ hours a week on manual outbound tasks. We connected their entire stack with custom n8n workflows — lead import, enrichment, CRM sync, and Slack notifications all automated.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">n8n</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">20hrs Saved/wk</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">Full Stack</span>
            </div>
          </div>
        </div>

        <!-- Card 6 -->
        <div style="background:#141414;border-radius:12px;overflow:hidden;flex:1 1 340px;max-width:520px;border:1px solid #222;">
          <div style="background:linear-gradient(135deg,#1a0010,#2d0020);padding:48px 40px;">
            <span style="background:#e4522b;color:#fff;font-size:11px;font-weight:700;letter-spacing:1.5px;padding:4px 12px;border-radius:20px;">GTM STRATEGY</span>
            <h3 style="color:#fff;font-size:26px;font-weight:800;margin:20px 0 10px;">Early-Stage Startup</h3>
            <p style="color:#aaa;font-size:15px;margin:0;line-height:1.6;">GTM Built from Zero — First 50 Customers</p>
          </div>
          <div style="padding:32px 40px;">
            <p style="color:#bbb;font-size:15px;line-height:1.7;margin:0 0 20px;">An early-stage B2B startup had a great product but no go-to-market plan. We built their ICP, messaging architecture, outbound channels, and CRM from scratch — helping them land their first 50 customers in 90 days.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">GTM Strategy</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">ICP Build</span>
              <span style="background:#1a1a1a;color:#e4522b;font-size:12px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid #333;">50 Customers / 90 Days</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section style="background:#111;padding:80px 40px;">
    <div style="max-width:900px;margin:0 auto;text-align:center;">
      <h2 style="color:#fff;font-size:clamp(26px,4vw,38px);font-weight:800;margin:0 0 48px;">What Our Clients Say</h2>
      <div style="display:flex;flex-wrap:wrap;gap:28px;justify-content:center;">
        <div style="background:#141414;border-radius:12px;padding:36px;flex:1 1 260px;text-align:left;border:1px solid #222;">
          <p style="color:#e4522b;font-size:20px;margin:0 0 12px;">★★★★★</p>
          <p style="color:#ddd;font-size:15px;line-height:1.7;margin:0 0 20px;">"BRND GURU transformed our outbound in 30 days. 20+ qualified calls booked every month consistently. Best investment we\'ve made in growth."</p>
          <p style="color:#888;font-size:13px;margin:0;"><strong style="color:#fff;">James M.</strong> — CEO, SaaS Scale-Up</p>
        </div>
        <div style="background:#141414;border-radius:12px;padding:36px;flex:1 1 260px;text-align:left;border:1px solid #222;">
          <p style="color:#e4522b;font-size:20px;margin:0 0 12px;">★★★★★</p>
          <p style="color:#ddd;font-size:15px;line-height:1.7;margin:0 0 20px;">"We tried cold email before with zero results. BRND GURU rebuilt everything from scratch — within 6 weeks we were talking to decision-makers we couldn\'t reach before."</p>
          <p style="color:#888;font-size:13px;margin:0;"><strong style="color:#fff;">Sarah C.</strong> — Director, Professional Services</p>
        </div>
        <div style="background:#141414;border-radius:12px;padding:36px;flex:1 1 260px;text-align:left;border:1px solid #222;">
          <p style="color:#e4522b;font-size:20px;margin:0 0 12px;">★★★★★</p>
          <p style="color:#ddd;font-size:15px;line-height:1.7;margin:0 0 20px;">"The GoHighLevel CRM build alone paid for itself in the first month. No more leads falling through the cracks. Incredible team."</p>
          <p style="color:#888;font-size:13px;margin:0;"><strong style="color:#fff;">Marcus T.</strong> — Founder, Marketing Agency</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section style="background:linear-gradient(135deg,#e4522b,#c73d1a);text-align:center;padding:80px 40px;">
    <h2 style="color:#fff;font-size:clamp(28px,4vw,44px);font-weight:800;margin:0 0 16px;">Ready to See Your Name Here?</h2>
    <p style="color:rgba(255,255,255,0.85);font-size:18px;max-width:520px;margin:0 auto 36px;line-height:1.6;">
      Book a free strategy call and let\'s map out exactly how we\'ll grow your pipeline.
    </p>
    <a href="' . $BOOK . '" target="_blank" rel="noopener"
       style="display:inline-block;background:#fff;color:#e4522b;font-weight:800;font-size:16px;padding:16px 40px;border-radius:8px;text-decoration:none;">
      Book a Free Strategy Call →
    </a>
  </section>

</div>';

    $log[] = bgspu_save_page(1483, 'Portfolio', 'portfolio', $portfolio_html);

    // ── 3. SERVICE SUB-PAGES ──────────────────────────────────
    $services = [
        762  => [
            'title' => 'LinkedIn Outreach Automation',
            'slug'  => 'linkedin-outreach-automation',
            'icon'  => '🔗',
            'tag'   => 'OUTBOUND · LINKEDIN',
            'hero'  => 'LinkedIn Outreach Automation That Books Calls While You Sleep.',
            'sub'   => 'We build and manage precision LinkedIn outreach sequences using HeyReach and Aimfox — targeting your ideal buyers, personalising at scale, and filling your calendar with qualified discovery calls.',
            'what'  => [
                'ICP definition and targeting list build',
                'Connection request and message sequence copywriting',
                'HeyReach / Aimfox account setup and management',
                'A/B testing and weekly optimisation',
                'Monthly reporting and pipeline tracking',
            ],
            'why'   => 'LinkedIn is the highest-intent B2B channel available today. Done right, it delivers warm conversations with decision-makers who are already open to your solution. Done wrong, it burns your reputation. We\'ve built hundreds of sequences — we know what works.',
            'result'=> '3× Pipeline Growth in 60 Days for a UK SaaS Firm',
        ],
        724  => [
            'title' => 'Cold Email Infrastructure',
            'slug'  => 'cold-email-infrastructure',
            'icon'  => '📧',
            'tag'   => 'OUTBOUND · EMAIL',
            'hero'  => 'Cold Email That Actually Lands — and Gets Replies.',
            'sub'   => 'We build end-to-end cold email systems via ManyReach. Domain acquisition, warming, technical setup, deliverability management, copywriting, and A/B-tested sequences — all built specifically for your ICP.',
            'what'  => [
                'Domain acquisition and DNS setup (SPF, DKIM, DMARC)',
                'Inbox warming and deliverability management',
                'ManyReach account setup and sequence build',
                'ICP research and prospect list building',
                'Copy and A/B test variants — ongoing optimisation',
                'Weekly performance reporting',
            ],
            'why'   => 'Most cold email fails because of one of three things: deliverability, targeting, or copy. We solve all three. Our infrastructure ensures your emails land in primary inboxes, your lists are laser-targeted, and your copy is built to generate replies from real buyers.',
            'result'=> '40+ Qualified Meetings Booked in Month 1',
        ],
        766  => [
            'title' => 'AI Agent Development',
            'slug'  => 'ai-agent-development',
            'icon'  => '🤖',
            'tag'   => 'AUTOMATION · AI',
            'hero'  => 'AI Agents That Work Your Outbound 24/7 — No Extra Headcount.',
            'sub'   => 'We build custom AI agents on n8n that automate the most time-consuming parts of your outbound: prospect research, message personalisation, follow-up triggers, lead enrichment, and CRM updates.',
            'what'  => [
                'Custom n8n workflow design and build',
                'AI-powered prospect research and enrichment',
                'Automated message personalisation at scale',
                'Follow-up sequence triggers and logic',
                'CRM and tool integrations (GoHighLevel, HubSpot, etc.)',
                'Monitoring, maintenance, and iteration',
            ],
            'why'   => 'Your competitors are still doing outbound manually. AI agents let you run the same volume of personalised outreach with a fraction of the time investment — and they never take a day off. We build agents that fit your exact workflow and scale with you.',
            'result'=> 'Full Outbound Automation Built for a B2B Consultancy',
        ],
        765  => [
            'title' => 'GoHighLevel CRM Builds',
            'slug'  => 'gohighlevel-crm',
            'icon'  => '⚡',
            'tag'   => 'CRM · AUTOMATION',
            'hero'  => 'Your Entire Sales Pipeline — Built, Automated, and Managed on GoHighLevel.',
            'sub'   => 'We design and build complete GoHighLevel CRM environments tailored to your sales process. Pipeline architecture, automated workflows, reporting dashboards, and integrations with your existing tools — all connected.',
            'what'  => [
                'GoHighLevel account setup and configuration',
                'Custom pipeline and stage architecture',
                'Automated follow-up and nurture workflows',
                'Reporting dashboards and KPI tracking',
                'Integration with LinkedIn, email, and other outbound tools',
                'Team training and ongoing support',
            ],
            'why'   => 'A well-built CRM is the difference between a leaky pipeline and a predictable revenue machine. GoHighLevel is the most powerful all-in-one platform for B2B sales and marketing — and we\'ve built dozens of environments that our clients run on every day.',
            'result'=> 'Full CRM Rebuild for a Marketing Agency — Zero Lost Leads',
        ],
        2970 => [
            'title' => 'n8n Workflow Automation',
            'slug'  => 'n8n-automation',
            'icon'  => '🔄',
            'tag'   => 'AUTOMATION · WORKFLOWS',
            'hero'  => 'Connect Your Entire Growth Stack — and Put It on Autopilot.',
            'sub'   => 'We build custom n8n workflows that connect every tool in your outbound and sales stack. Lead enrichment, CRM sync, Slack notifications, cross-platform triggers — your entire operation flowing automatically.',
            'what'  => [
                'Custom n8n workflow architecture and build',
                'Lead import, enrichment, and deduplication flows',
                'CRM sync and pipeline update automation',
                'Slack / email notification triggers',
                'Cross-platform integrations (LinkedIn, email, GHL, HubSpot)',
                'Monitoring, error handling, and maintenance',
            ],
            'why'   => 'Manual data entry and tool-switching kills productivity and creates gaps in your pipeline. n8n lets us connect every platform you use and automate the glue work — so your team focuses on selling, not admin.',
            'result'=> '20+ Hours Per Week Saved for a Scale-Up Tech Firm',
        ],
    ];

    foreach ($services as $id => $s) {
        $what_items = implode('', array_map(fn($item) =>
            '<li style="color:#ccc;font-size:15px;line-height:1.8;padding:8px 0;border-bottom:1px solid #222;">'
            . '<span style="color:#e4522b;margin-right:10px;">→</span>' . esc_html($item) . '</li>',
            $s['what']
        ));

        $html = '
<div style="font-family:inherit;background:#0a0a0a;color:#fff;">

  <!-- Hero -->
  <section style="background:linear-gradient(135deg,#0a0a0a 60%,#1a0800);padding:100px 40px 80px;max-width:900px;margin:0 auto;">
    <p style="color:#e4522b;font-weight:700;font-size:12px;letter-spacing:2.5px;text-transform:uppercase;margin:0 0 20px;">' . esc_html($s['tag']) . '</p>
    <h1 style="color:#fff;font-size:clamp(32px,5vw,56px);font-weight:800;margin:0 0 24px;line-height:1.1;">' . esc_html($s['hero']) . '</h1>
    <p style="color:#aaa;font-size:clamp(16px,2vw,19px);max-width:640px;margin:0 0 36px;line-height:1.7;">' . esc_html($s['sub']) . '</p>
    <a href="' . $BOOK . '" target="_blank" rel="noopener"
       style="display:inline-block;background:#e4522b;color:#fff;font-weight:700;font-size:16px;padding:16px 36px;border-radius:8px;text-decoration:none;">
      Book a Free Strategy Call →
    </a>
  </section>

  <!-- What We Do -->
  <section style="background:#111;padding:80px 40px;">
    <div style="max-width:800px;margin:0 auto;">
      <h2 style="color:#fff;font-size:clamp(24px,3.5vw,36px);font-weight:800;margin:0 0 32px;">What\'s Included</h2>
      <ul style="list-style:none;padding:0;margin:0;">' . $what_items . '</ul>
    </div>
  </section>

  <!-- Why It Works -->
  <section style="background:#0a0a0a;padding:80px 40px;">
    <div style="max-width:800px;margin:0 auto;display:flex;gap:48px;flex-wrap:wrap;align-items:flex-start;">
      <div style="flex:1 1 300px;">
        <p style="color:#e4522b;font-weight:700;font-size:12px;letter-spacing:2px;text-transform:uppercase;margin:0 0 16px;">WHY IT WORKS</p>
        <h2 style="color:#fff;font-size:clamp(22px,3vw,32px);font-weight:800;margin:0 0 20px;">Built for Results, Not Vanity Metrics</h2>
        <p style="color:#aaa;font-size:15px;line-height:1.8;margin:0;">' . esc_html($s['why']) . '</p>
      </div>
      <div style="flex:1 1 240px;background:#141414;border-radius:12px;padding:32px;border:1px solid #222;border-left:4px solid #e4522b;">
        <p style="color:#e4522b;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin:0 0 12px;">CLIENT RESULT</p>
        <p style="color:#fff;font-size:17px;font-weight:700;margin:0;line-height:1.5;">' . esc_html($s['result']) . '</p>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section style="background:#e4522b;text-align:center;padding:72px 40px;">
    <h2 style="color:#fff;font-size:clamp(24px,4vw,38px);font-weight:800;margin:0 0 16px;">Ready to Get Started?</h2>
    <p style="color:rgba(255,255,255,0.88);font-size:17px;max-width:480px;margin:0 auto 32px;line-height:1.6;">
      Book a free 30-minute strategy call. We\'ll audit your current setup and build a plan.
    </p>
    <a href="' . $BOOK . '" target="_blank" rel="noopener"
       style="display:inline-block;background:#fff;color:#e4522b;font-weight:800;font-size:16px;padding:16px 40px;border-radius:8px;text-decoration:none;">
      Book a Free Strategy Call →
    </a>
  </section>

</div>';

        $log[] = bgspu_save_page($id, $s['title'], $s['slug'], $html);
    }

    // ── Clear caches ──────────────────────────────────────────
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    wp_cache_flush();
    flush_rewrite_rules(true);

    update_option('bgspu_log', $log);
    update_option('bgspu_done', '1');
}

add_action('admin_notices', function () {
    if (get_option('bgspu_done') !== '1') return;
    $log = get_option('bgspu_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ BRND GURU — Services Menu + Portfolio + Service Pages Updated!</h3>
        <ul style="margin:0 0 12px;padding-left:20px;">
            <?php foreach ($log as $line) : ?>
                <li><?php echo esc_html($line); ?></li>
            <?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/portfolio/'); ?>" target="_blank" class="button button-primary">Portfolio →</a>
            <a href="<?php echo home_url('/linkedin-outreach-automation/'); ?>" target="_blank" class="button">LinkedIn →</a>
            <a href="<?php echo home_url('/cold-email-infrastructure/'); ?>" target="_blank" class="button">Cold Email →</a>
            <a href="<?php echo home_url('/ai-agent-development/'); ?>" target="_blank" class="button">AI Agents →</a>
            <a href="<?php echo home_url('/gohighlevel-crm/'); ?>" target="_blank" class="button">GHL →</a>
            <a href="<?php echo home_url('/n8n-automation/'); ?>" target="_blank" class="button">n8n →</a>
            &nbsp;— Deactivate + delete when done.
        </p>
    </div>
    <?php
});
