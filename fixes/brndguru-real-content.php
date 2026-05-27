<?php
/**
 * Plugin Name: BrndGuru — Real Content Update
 * Description: Replaces all page text with real BRND GURU content — B2B growth & automation consulting agency. AUTO-RUNS on activation.
 * Version: 2.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgrc_run');

add_action('admin_init', function () {
    if (get_option('bgrc_done') !== '2') bgrc_run();
});

function bgrc_replace($page_id, array $map) {
    global $wpdb;
    $raw = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1",
        $page_id
    ));
    if (!$raw) return '(no elementor data found)';

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
            => 'B2B Growth &amp; Automation Consulting — London',

        // Book button
        'Book a Meeting'
            => 'Book a Strategy Call',

        // Stats
        '72'
            => '200+',
        'Hour Prototype Guarantee'
            => 'B2B Clients Grown',
        '100+'
            => '5×',
        'Brands Transformed'
            => 'Average ROI on Outbound',
        '10+'
            => '£50M+',
        'Years of Pixel-Perfect Craft'
            => 'Pipeline Generated for Clients',

        // Services section
        'Services We Offer'
            => 'How We Grow Your Business',

        // Service 1: UI/UX → LinkedIn Automation
        '1.UI\/UX Design'
            => '1. LinkedIn Outreach Automation',
        '1.UI/UX Design'
            => '1. LinkedIn Outreach Automation',
        'Interfaces that delight users and drive conversions. We design with outcomes in mind.'
            => 'Precision outreach at scale using HeyReach &amp; Aimfox. We build personalised sequences that turn cold prospects into booked discovery calls.',

        // Service 2: Brand Design → Cold Email
        '2.Brand Design'
            => '2. Cold Email Infrastructure',
        'Visual identities that command attention and build trust. Logos, style guides, and assets crafted to tell your story.'
            => 'End-to-end cold email systems via ManyReach. Domain setup, warming, deliverability, copywriting, and A/B-tested sequences — all done for you.',

        // Service 3: Webflow → AI Agents
        '3.Webflow Development'
            => '3. AI Agent Development',
        'Websites that load fast, rank higher, and grow with you. No bloated code—just seamless Webflow experiences.'
            => 'Custom AI agents built on n8n that automate lead research, personalisation, follow-up, and reporting — working around the clock without extra headcount.',

        // Service 4: No-Code → GoHighLevel & n8n
        '4.No-Code Development'
            => '4. GoHighLevel CRM &amp; Automation',
        'Launch functional MVPs without engineering headaches. Solutions in weeks, not months.'
            => 'Complete GoHighLevel CRM builds and n8n workflow automation. We connect your entire growth stack so nothing falls through the cracks.',

        // Case studies section
        'Showcase of Selected Work'
            => 'Client Results',

        'FinTech Startup -Stealth Mode'
            => 'SaaS Scale-Up — 3× Pipeline in 60 Days',
        'Simplified IA'         => 'LinkedIn Automation',
        'Data Visualization'    => 'Cold Email',

        'LawLex - Webflow Website'
            => 'Professional Services Firm — 40+ Meetings Booked',
        'CMS'                   => 'HeyReach',
        'Dynamic Filtering'     => 'ManyReach',

        'Greenify - Social Engagement'
            => 'B2B Agency — Full CRM Rebuild',
        'Animation'             => 'n8n Automation',
        'Bold Color Palette'    => 'GHL Pipeline',

        'Quizora - No-Code MVP for EdTech'
            => 'Consultancy — GTM Strategy &amp; Outbound Launch',
        'Gamified'              => 'AI Agents',
        'Stripe Subscription'   => 'Outbound GTM',

        'View All Case Studies'
            => 'View All Results',

        // Testimonials
        'What Clients Say About Us'
            => 'What Our Clients Say',

        'Where Ideas Meet Extraordinary Design'
            => 'The Growth Engine for Ambitious B2B Businesses',

        'We struggled with user drop-offs for months. Web Rocket redesigned our dashboard with intuitive workflows, and our retention skyrocketed by 40% in 30 days. Their team actually listens to users—no'
            => 'BRND GURU transformed our outbound in 30 days. The LinkedIn automation they built books us 20+ qualified calls a month — consistently. Best investment we\'ve made in growth.',

        'Our old branding looked like every other brewery. Web Rocket gave us a bold, hoppy-inspired identity that\'s now on merch, trucks, and even trade shows. Sales jumped 65% post-rebrand—worth every p'
            => 'We had the service but couldn\'t get in front of buyers. BRND GURU built our cold email infrastructure from scratch and within 6 weeks we were having conversations with decision-makers we could never reach before.',

        // Blog section
        'The Studio Journal'
            => 'From the BRND GURU Blog',
    ]);

    // ══════════════════════════════════════════════
    // ABOUT PAGE (ID: 1628)
    // ══════════════════════════════════════════════
    $log['About'] = bgrc_replace(1628, [

        'We Design &amp; Build Digital Experiences That Move the Needle'
            => 'We Help B2B Businesses Build Outbound Engines That Generate Consistent Revenue.',
        'We Design & Build Digital Experiences That Move the Needle'
            => 'We Help B2B Businesses Build Outbound Engines That Generate Consistent Revenue.',

        'How We Work: Painless, Proven, Pixel-Perfect'
            => 'How We Work: Audit, Build, Launch, Scale',

        'Step 1'  => 'Step 1: Strategy &amp; Audit',
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
            => 'B2B Growth Consulting Services',

        'Every service we offer is anchored in strategy. We don\'t execute blindly — we consult first, then build.'
            => 'Every engagement starts with strategy. We audit your current approach, identify the gaps, and build the systems that turn outreach into revenue.',
        "Every service we offer is anchored in strategy. We don't execute blindly — we consult first, then build."
            => "Every engagement starts with strategy. We audit your current approach, identify the gaps, and build the systems that turn outreach into revenue.",

        // Service 1
        '🎨'    => '🔗',
        'Brand Identity &amp; Strategy'     => 'LinkedIn Outreach Automation',
        'Brand Identity & Strategy'         => 'LinkedIn Outreach Automation',
        'We build brand systems that communicate authority and create lasting market recognition. From positioning and messaging to visual identity and brand guidelines.'
            => 'Precision B2B outreach at scale using HeyReach &amp; Aimfox. We build personalised sequences targeting your ideal decision-makers — fully managed and continuously optimised.',

        // Service 2
        '✏️'   => '📧',
        'UI\/UX &amp; Product Design'       => 'Cold Email Infrastructure',
        'UI/UX & Product Design'            => 'Cold Email Infrastructure',
        'Strategic design that removes friction from the user journey. Wireframes, prototypes, and final UI that convert visitors into customers.'
            => 'End-to-end cold email systems via ManyReach. Domain acquisition, technical setup, warming, copywriting, and tested sequences that land in inboxes and generate replies.',

        // Service 3
        'No-Code Development'
            => 'AI Agent Development',
        'Launch production-ready digital products in weeks, not months. We leverage the best no-code platforms to deliver faster and smarter.'
            => 'Custom AI agents built on n8n. Automate prospect research, message personalisation, follow-up sequences, and lead enrichment — 24\/7 with zero manual effort.',

        // Service 4
        '🌐'   => '⚡',
        'Webflow Development'
            => 'GoHighLevel CRM Builds',
        'Bespoke Webflow websites that your marketing team can own and update. Fast, responsive, and built to rank.'
            => 'Full GoHighLevel CRM setup tailored to your sales process. Pipeline architecture, automation workflows, reporting dashboards, and integrations — built to close more deals.',

        // Service 5
        '🛒'   => '🔄',
        'Shopify Growth &amp; Development'  => 'n8n Workflow Automation',
        'Shopify Growth & Development'      => 'n8n Workflow Automation',
        'Full-service Shopify engagements — from store architecture and design to conversion optimisation and growth strategy.'
            => 'Connect every tool in your growth stack with custom n8n automations. Lead enrichment, CRM sync, notifications, cross-platform triggers — your entire operation flowing on autopilot.',

        // Bottom CTA
        'Not Sure Which Service You Need?'
            => 'Not Sure Where to Start?',
        'Book a free 30-minute discovery call. We\'ll diagnose your biggest challenge and recommend the right engagement.'
            => 'Book a free 30-minute strategy call. We\'ll review your current setup and give you a clear plan for generating more revenue from outbound.',
        'Book a Free Discovery Call →'
            => 'Book a Free Strategy Call →',
    ]);

    // ══════════════════════════════════════════════
    // CONTACT PAGE (ID: 23)
    // ══════════════════════════════════════════════
    $log['Contact'] = bgrc_replace(23, [

        "Let's Build Something Awesome!"
            => "Let's Grow Your Business",

        'Got a project that needs pixel-perfect design or bulletproof code? Drop us a line—we reply within 24 hours.'
            => 'Ready to build a smarter outbound system? Tell us about your business — we reply within 24 hours.',

        'Where to Find Us'
            => 'Find Us',

        'Studio HQ'
            => 'Headquarters',
        '123 Design Street, San Francisco (By appointment only)'
            => 'London, United Kingdom (Remote-first)',

        'Remote Teams'
            => 'Working With Clients Globally',
        'We work with clients in 12+ timezones (EST to GMT+5:30).'
            => 'We work with B2B businesses across the UK, Europe, North America &amp; Australia.',
    ]);

    // ── Clear all caches ──────────────────────────
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    if (function_exists('wp_cache_flush')) wp_cache_flush();

    update_option('bgrc_log', $log);
    update_option('bgrc_done', '2');
}

add_action('admin_notices', function () {
    if (get_option('bgrc_done') !== '2') return;
    $log = get_option('bgrc_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px 16px;">
        <h3 style="margin:0 0 8px;">✅ BRND GURU — Content Updated!</h3>
        <ul style="margin:0 0 10px;padding-left:20px;">
            <?php foreach ($log as $page => $result) : ?>
                <li><strong><?php echo esc_html($page); ?>:</strong> <?php echo esc_html($result); ?></li>
            <?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary">🏠 Home</a>
            <a href="<?php echo home_url('/about/'); ?>" target="_blank" class="button">About</a>
            <a href="<?php echo home_url('/services/'); ?>" target="_blank" class="button">Services</a>
            <a href="<?php echo home_url('/contact/'); ?>" target="_blank" class="button">Contact</a>
            &nbsp;— Deactivate + delete this plugin when done.
        </p>
    </div>
    <?php
});
