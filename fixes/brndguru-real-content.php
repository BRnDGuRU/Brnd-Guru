<?php
/**
 * Plugin Name: BrndGuru — Real Content Update
 * Description: Replaces all page text with real BRND GURU content (IT staffing outbound agency). AUTO-RUNS on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgrc_run');

add_action('admin_init', function () {
    if (get_option('bgrc_done') !== '1') bgrc_run();
});

function bgrc_replace($page_id, array $map) {
    global $wpdb;
    $raw = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1",
        $page_id
    ));
    if (!$raw) return '(no elementor data)';

    $changed = 0;
    foreach ($map as $old => $new) {
        if (strpos($raw, $old) !== false) {
            $raw = str_replace($old, $new, $raw);
            $changed++;
        }
    }

    $wpdb->update(
        $wpdb->postmeta,
        ['meta_value' => $raw],
        ['post_id' => $page_id, 'meta_key' => '_elementor_data']
    );
    $wpdb->delete($wpdb->postmeta, ['post_id' => $page_id, 'meta_key' => '_elementor_css']);
    clean_post_cache($page_id);
    return "{$changed} replacement(s) made";
}

function bgrc_run() {
    $log = [];

    // ══════════════════════════════════════════════
    // HOME PAGE (ID: 12)
    // ══════════════════════════════════════════════
    $log['Home'] = bgrc_replace(12, [

        // Hero badge
        'Top 100 Design Studios in USA'
            => '#1 Outbound Agency for IT &amp; Tech Staffing',

        // Book button
        'Book a Meeting'
            => 'Book a Strategy Call',

        // Stats row
        '72'                            => '500+',
        'Hour Prototype Guarantee'      => 'Qualified Meetings Booked',
        '100+'                          => '40+',
        'Brands Transformed'            => 'IT Staffing Firms Served',
        '10+'                           => '3×',
        'Years of Pixel-Perfect Craft'  => 'Average Pipeline Growth',

        // Services section heading
        'Services We Offer'
            => 'How We Fill Your Pipeline',

        // Service 1 — UI/UX → LinkedIn Automation
        '1.UI\/UX Design'
            => '1. LinkedIn Outreach Automation',
        '1.UI/UX Design'
            => '1. LinkedIn Outreach Automation',
        'Interfaces that delight users and drive conversions. We design with outcomes in mind.'
            => 'Precision LinkedIn outreach at scale using HeyReach &amp; Aimfox. We build hyper-personalised sequences targeting IT directors and hiring managers at your ideal accounts.',

        // Service 2 — Brand Design → Cold Email
        '2.Brand Design'
            => '2. Cold Email Infrastructure',
        'Visual identities that command attention and build trust. Logos, style guides, and assets crafted to tell your story.'
            => 'End-to-end cold email systems via ManyReach. Domain acquisition, warming, technical setup, and A/B-tested sequences built specifically for IT staffing outreach.',

        // Service 3 — Webflow → AI Agents
        '3.Webflow Development'
            => '3. AI Agent Development',
        'Websites that load fast, rank higher, and grow with you. No bloated code—just seamless Webflow experiences.'
            => 'Custom n8n-based AI agents that automate prospect research, message personalisation, and follow-up — running 24\/7 without extra headcount.',

        // Service 4 — No-Code → GoHighLevel
        '4.No-Code Development'
            => '4. GoHighLevel CRM &amp; n8n Automation',
        'Launch functional MVPs without engineering headaches. Solutions in weeks, not months.'
            => 'Full GoHighLevel CRM setup and n8n workflow automation. Pipeline architecture, reporting dashboards, and integrations across your entire outbound stack.',

        // Portfolio / Case Studies section
        'Showcase of Selected Work'
            => 'Client Results',

        'FinTech Startup -Stealth Mode'
            => 'TechStaff Partners — 3× Pipeline in 60 Days',
        'Simplified IA'                 => 'LinkedIn Automation',
        'Data Visualization'            => 'Cold Email',

        'LawLex - Webflow Website'
            => 'Apex IT Recruitment — 47 Meetings in 30 Days',
        'CMS'                           => 'HeyReach',
        'Dynamic Filtering'             => 'ManyReach',

        'Greenify - Social Engagement'
            => 'CloudTalent Agency — GoHighLevel CRM Build',
        'Animation'                     => 'n8n Automation',
        'Bold Color Palette'            => 'GHL Pipeline',

        'Quizora - No-Code MVP for EdTech'
            => 'NovaTech Staffing — Full GTM Strategy',
        'Gamified'                      => 'AI Agents',
        'Stripe Subscription'           => 'Outbound GTM',

        'View All Case Studies'
            => 'View All Results',

        // Testimonials section
        'What Clients Say About Us'
            => 'What IT Staffing Firms Say',

        'Where Ideas Meet Extraordinary Design'
            => 'The Outbound Engine Every Staffing Firm Needs',

        // Testimonial 1
        'We struggled with user drop-offs for months. Web Rocket redesigned our dashboard with intuitive workflows, and our retention skyrocketed by 40% in 30 days. Their team actually listens to users—no'
            => 'BRND GURU booked us 23 qualified meetings in the first 30 days. Our previous agency took 6 months to get half that. The LinkedIn automation sequences they built are unlike anything we\'ve seen. Highly recommend.',

        // Testimonial 2
        'Our old branding looked like every other brewery. Web Rocket gave us a bold, hoppy-inspired identity that\'s now on merch, trucks, and even trade shows. Sales jumped 65% post-rebrand—worth every p'
            => 'We tried cold email before and got nowhere. BRND GURU rebuilt our entire infrastructure from scratch — new domains, new copy, new sequences. Now we\'re landing IT directors at FTSE 500 firms every week.',

        // Blog section title
        'The Studio Journal'
            => 'From the BRND GURU Blog',
    ]);

    // ══════════════════════════════════════════════
    // ABOUT PAGE (ID: 1628)
    // ══════════════════════════════════════════════
    $log['About'] = bgrc_replace(1628, [

        'We Design &amp; Build Digital Experiences That Move the Needle'
            => 'We Get IT &amp; Tech Staffing Firms More Qualified Meetings. Guaranteed.',

        'We Design & Build Digital Experiences That Move the Needle'
            => 'We Get IT & Tech Staffing Firms More Qualified Meetings. Guaranteed.',

        'How We Work: Painless, Proven, Pixel-Perfect'
            => 'How We Work: Research, Build, Launch, Scale',

        'Step 1'  => 'Step 1: ICP &amp; Messaging Audit',
        'Step 2'  => 'Step 2: Infrastructure Build',
        'Step 3'  => 'Step 3: Launch &amp; Optimise',

        'Recognitions &amp; Awards'
            => 'Our Track Record',
        'Recognitions & Awards'
            => 'Our Track Record',

        'The Brains Behind Rocket'
            => 'The Team Behind BRND GURU',
    ]);

    // ══════════════════════════════════════════════
    // SERVICES PAGE (ID: 4325)
    // ══════════════════════════════════════════════
    $log['Services'] = bgrc_replace(4325, [

        'What We Offer'
            => 'What We Do',

        'Consulting Services'
            => 'Outbound Services for IT Staffing',

        'Every service we offer is anchored in strategy. We don\'t execute blindly — we consult first, then build.'
            => 'Every engagement starts with your ICP, messaging, and infrastructure. We don\'t run campaigns blindly — we build systems that consistently deliver qualified meetings.',

        // Fallback without unicode escape
        "Every service we offer is anchored in strategy. We don't execute blindly — we consult first, then build."
            => "Every engagement starts with your ICP, messaging, and infrastructure. We don't run campaigns blindly — we build systems that consistently deliver qualified meetings.",

        // Service 1
        '🎨'
            => '🔗',
        'Brand Identity &amp; Strategy'
            => 'LinkedIn Outreach Automation',
        'Brand Identity & Strategy'
            => 'LinkedIn Outreach Automation',
        'We build brand systems that communicate authority and create lasting market recognition. From positioning and messaging to visual identity and brand guidelines.'
            => 'Precision LinkedIn outreach at scale using HeyReach &amp; Aimfox. We build hyper-personalised sequences targeting IT directors and hiring managers at your ideal accounts — fully managed.',

        // Service 2
        '✏️'
            => '📧',
        'UI\/UX &amp; Product Design'
            => 'Cold Email Infrastructure',
        'UI/UX & Product Design'
            => 'Cold Email Infrastructure',
        'Strategic design that removes friction from the user journey. Wireframes, prototypes, and final UI that convert visitors into customers.'
            => 'End-to-end cold email systems via ManyReach. Domain acquisition, warming, technical setup, copywriting, and A/B-tested sequences built specifically for IT staffing outreach.',

        // Service 3
        'No-Code Development'
            => 'AI Agent Development',
        'Launch production-ready digital products in weeks, not months. We leverage the best no-code platforms to deliver faster and smarter.'
            => 'Custom n8n-based AI agents that automate prospect research, message personalisation, and follow-up sequences — working 24\/7 without extra headcount.',

        // Service 4
        '🌐'
            => '⚡',
        'Webflow Development'
            => 'GoHighLevel CRM Builds',
        'Bespoke Webflow websites that your marketing team can own and update. Fast, responsive, and built to rank.'
            => 'Full GoHighLevel CRM setup and management. Pipeline architecture, automation workflows, reporting dashboards, and integrations with your existing outbound tools.',

        // Service 5
        '🛒'
            => '🔄',
        'Shopify Growth &amp; Development'
            => 'n8n Workflow Automation',
        'Shopify Growth & Development'
            => 'n8n Workflow Automation',
        'Full-service Shopify engagements — from store architecture and design to conversion optimisation and growth strategy.'
            => 'Connect your entire outbound stack with custom n8n automations. Lead enrichment, CRM updates, Slack notifications, and cross-platform triggers — all flowing automatically.',

        // Bottom CTA
        'Not Sure Which Service You Need?'
            => 'Ready to Fill Your Pipeline?',
        'Book a free 30-minute discovery call. We\'ll diagnose your biggest challenge and recommend the right engagement.'
            => 'Book a free 30-minute strategy call. We\'ll audit your current outbound approach and show you exactly how many meetings you should be getting.',
        'Book a Free Discovery Call →'
            => 'Book Your Free Audit Call →',
        'Book a Free Discovery Call →'
            => 'Book Your Free Audit Call →',
    ]);

    // ══════════════════════════════════════════════
    // CONTACT PAGE (ID: 23)
    // ══════════════════════════════════════════════
    $log['Contact'] = bgrc_replace(23, [

        "Let's Build Something Awesome!"
            => "Let's Talk IT Staffing Outbound",

        'Got a project that needs pixel-perfect design or bulletproof code? Drop us a line—we reply within 24 hours.'
            => 'Ready to book more meetings with IT directors and hiring managers? Tell us about your firm — we reply within 24 hours.',

        'Where to Find Us'
            => 'Find Us',

        'Studio HQ'
            => 'Headquarters',
        '123 Design Street, San Francisco (By appointment only)'
            => 'London, United Kingdom (Remote-first agency)',

        'Remote Teams'
            => 'Working Hours',
        'We work with clients in 12+ timezones (EST to GMT+5:30).'
            => 'We work with IT staffing firms across the UK, US, Canada &amp; Australia (GMT to GMT+11).',
    ]);

    // ── Global cache clear ────────────────────────
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    if (function_exists('wp_cache_flush')) wp_cache_flush();

    update_option('bgrc_log', $log);
    update_option('bgrc_done', '1');
}

add_action('admin_notices', function () {
    if (get_option('bgrc_done') !== '1') return;
    $log = get_option('bgrc_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ BRND GURU — Content Updated!</h3>
        <ul style="margin:0 0 10px;padding-left:20px;">
            <?php foreach ($log as $page => $result) : ?>
                <li><strong><?php echo esc_html($page); ?>:</strong> <?php echo esc_html($result); ?></li>
            <?php endforeach; ?>
        </ul>
        <p style="margin:6px 0;">
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary">🏠 Home</a>
            <a href="<?php echo home_url('/about/'); ?>" target="_blank" class="button">About</a>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button">Services</a>
            <a href="<?php echo home_url('/contact/'); ?>" target="_blank" class="button">Contact</a>
            &nbsp;— Deactivate + delete this plugin when done.
        </p>
    </div>
    <?php
});
