<?php
/**
 * Plugin Name: BrndGuru — Find & Replace Text
 * Description: Searches raw Elementor JSON for specific strings and replaces them. Auto-runs on activation.
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'bgft_run');
add_action('admin_init', function () {
    if (get_option('bgft_done') !== '1') bgft_run();
});

function bgft_run() {
    global $wpdb;

    $pages = [12 => 'Home', 1628 => 'About', 4325 => 'Services', 23 => 'Contact'];
    $log   = [];

    // Strings to find and replace — covering ALL possible storage formats
    $replacements = [
        // ── Main hero heading (widget key unknown — brute force) ──
        'Where Stunning Design Meets Flawless Functionality'
            => 'We Build Outbound Systems That Generate Revenue.',

        // ── Hero subheading ──
        'We craft high-converting websites, apps, and brands for startups, agencies, and businesses that refuse to settle for good enough.'
            => 'BRND GURU is a London-based B2B consulting agency. We design and deploy LinkedIn automation, cold email infrastructure, AI agents, and CRM systems that consistently fill your pipeline.',

        // ── About page hero ──
        'We Design & Build Digital Experiences That Move the Needle'
            => 'We Help B2B Businesses Build Outbound Engines That Generate Consistent Revenue.',
        'We Design &amp; Build Digital Experiences That Move the Needle'
            => 'We Help B2B Businesses Build Outbound Engines That Generate Consistent Revenue.',

        // ── About process ──
        'How We Work: Painless, Proven, Pixel-Perfect'
            => 'How We Work: Audit, Build, Launch, Scale',
        'Step 1' => 'Step 1: Strategy &amp; Audit',
        'Step 2' => 'Step 2: Infrastructure Build',
        'Step 3' => 'Step 3: Launch &amp; Optimise',
        'Recognitions & Awards'    => 'Our Track Record',
        'Recognitions &amp; Awards' => 'Our Track Record',
        'The Brains Behind Rocket' => 'The Team Behind BRND GURU',

        // ── Home — badge & button ──
        'Top 100 Design Studios in USA' => 'B2B Growth &amp; Automation Consulting — London',
        'Book a Meeting'                => 'Book a Strategy Call',

        // ── Home — stats ──
        '"72"'           => '"200+"',
        '"100+"'         => '"40+"',
        '"10+"'          => '"3×"',
        'Hour Prototype Guarantee'    => 'B2B Clients Grown',
        'Brands Transformed'          => 'Average ROI on Outbound',
        'Years of Pixel-Perfect Craft'=> 'Pipeline Generated',

        // ── Home — services section ──
        'Services We Offer'  => 'How We Grow Your Business',

        '1.UI\/UX Design'    => '1. LinkedIn Outreach Automation',
        '1.UI/UX Design'     => '1. LinkedIn Outreach Automation',
        'Interfaces that delight users and drive conversions. We design with outcomes in mind.'
            => 'Precision outreach at scale using HeyReach &amp; Aimfox. We build personalised sequences that turn cold prospects into booked calls.',

        '2.Brand Design'     => '2. Cold Email Infrastructure',
        'Visual identities that command attention and build trust. Logos, style guides, and assets crafted to tell your story.'
            => 'End-to-end cold email systems via ManyReach. Domain setup, warming, deliverability, copywriting, and A\/B-tested sequences — all done for you.',

        '3.Webflow Development' => '3. AI Agent Development',
        'Websites that load fast, rank higher, and grow with you. No bloated code—just seamless Webflow experiences.'
            => 'Custom AI agents built on n8n that automate lead research, personalisation, follow-up, and reporting — running 24\/7.',

        '4.No-Code Development' => '4. GoHighLevel CRM &amp; Automation',
        'Launch functional MVPs without engineering headaches. Solutions in weeks, not months.'
            => 'Complete GoHighLevel CRM builds and n8n workflow automation. We connect your entire growth stack so nothing falls through the cracks.',

        // ── Home — case studies ──
        'Showcase of Selected Work'       => 'Client Results',
        'FinTech Startup -Stealth Mode'   => 'SaaS Scale-Up — 3× Pipeline in 60 Days',
        'Simplified IA'                   => 'LinkedIn Automation',
        'Data Visualization'              => 'Cold Email',
        'LawLex - Webflow Website'        => 'Professional Services — 40+ Meetings Booked',
        'Dynamic Filtering'               => 'ManyReach',
        'Greenify - Social Engagement'    => 'B2B Agency — Full CRM Rebuild',
        'Animation'                       => 'n8n Automation',
        'Bold Color Palette'              => 'GHL Pipeline',
        'Quizora - No-Code MVP for EdTech'=> 'Consultancy — GTM Strategy &amp; Outbound Launch',
        'Gamified'                        => 'AI Agents',
        'Stripe Subscription'             => 'Outbound GTM',
        'View All Case Studies'           => 'View All Results',

        // ── Home — testimonials ──
        'What Clients Say About Us'        => 'What Our Clients Say',
        'Where Ideas Meet Extraordinary Design'
            => 'The Growth Engine for Ambitious B2B Businesses',

        // ── Home — blog ──
        'The Studio Journal' => 'From the BRND GURU Blog',

        // ── Services page ──
        'What We Offer'      => 'What We Do',
        'Consulting Services' => 'B2B Growth Consulting Services',
        "Every service we offer is anchored in strategy. We don't execute blindly — we consult first, then build."
            => "Every engagement starts with strategy. We audit your current approach, identify the gaps, and build systems that turn outreach into revenue.",
        'Brand Identity &amp; Strategy' => 'LinkedIn Outreach Automation',
        'Brand Identity & Strategy'     => 'LinkedIn Outreach Automation',
        'We build brand systems that communicate authority and create lasting market recognition. From positioning and messaging to visual identity and brand guidelines.'
            => 'Precision B2B outreach at scale using HeyReach &amp; Aimfox. We build personalised sequences targeting your ideal decision-makers — fully managed.',
        'UI\/UX &amp; Product Design'   => 'Cold Email Infrastructure',
        'UI/UX & Product Design'        => 'Cold Email Infrastructure',
        'Strategic design that removes friction from the user journey. Wireframes, prototypes, and final UI that convert visitors into customers.'
            => 'End-to-end cold email systems via ManyReach. Domain acquisition, technical setup, warming, copywriting, and tested sequences that land in inboxes.',
        'Webflow Development'           => 'GoHighLevel CRM Builds',
        'Bespoke Webflow websites that your marketing team can own and update. Fast, responsive, and built to rank.'
            => 'Full GoHighLevel CRM setup tailored to your sales process. Pipeline architecture, automation workflows, reporting dashboards, and integrations.',
        'Shopify Growth &amp; Development' => 'n8n Workflow Automation',
        'Shopify Growth & Development'     => 'n8n Workflow Automation',
        'Full-service Shopify engagements — from store architecture and design to conversion optimisation and growth strategy.'
            => 'Connect every tool in your growth stack with custom n8n automations. Lead enrichment, CRM sync, notifications — your entire operation on autopilot.',
        'Not Sure Which Service You Need?' => 'Not Sure Where to Start?',
        'Book a Free Discovery Call →'    => 'Book a Free Strategy Call →',

        // ── Contact page ──
        "Let's Build Something Awesome!" => "Let's Grow Your Business",
        'Got a project that needs pixel-perfect design or bulletproof code? Drop us a line—we reply within 24 hours.'
            => 'Ready to build a smarter outbound system? Tell us about your business — we reply within 24 hours.',
        'Where to Find Us'               => 'Find Us',
        'Studio HQ'                      => 'Headquarters',
        '123 Design Street, San Francisco (By appointment only)' => 'London, United Kingdom (Remote-first)',
        'Remote Teams'                   => 'Working With Clients Globally',
        'We work with clients in 12+ timezones (EST to GMT+5:30).'
            => 'We work with B2B businesses across the UK, Europe, North America &amp; Australia.',
    ];

    foreach ($pages as $id => $name) {
        // ── Try _elementor_data ──────────────────────────────
        $raw = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta}
             WHERE post_id=%d AND meta_key='_elementor_data' LIMIT 1", $id
        ));

        $elem_hits = 0;
        if ($raw) {
            foreach ($replacements as $old => $new) {
                if (strpos($raw, $old) !== false) {
                    $raw = str_replace($old, $new, $raw);
                    $elem_hits++;
                }
            }
            $wpdb->update($wpdb->postmeta,
                ['meta_value' => $raw],
                ['post_id' => $id, 'meta_key' => '_elementor_data']
            );
            $wpdb->delete($wpdb->postmeta, ['post_id' => $id, 'meta_key' => '_elementor_css']);
        }

        // ── Also try post_content (some headings render there) ──
        $post = get_post($id);
        $content_hits = 0;
        if ($post && $post->post_content) {
            $new_content = $post->post_content;
            foreach ($replacements as $old => $new) {
                if (strpos($new_content, $old) !== false) {
                    $new_content = str_replace($old, $new, $new_content);
                    $content_hits++;
                }
            }
            if ($content_hits > 0) {
                $wpdb->update($wpdb->posts,
                    ['post_content' => $new_content, 'post_modified' => current_time('mysql')],
                    ['ID' => $id]
                );
            }
        }

        clean_post_cache($id);
        $log[] = "{$name} (ID:{$id}): elementor={$elem_hits} hits, post_content={$content_hits} hits";
    }

    // ── Also scan ALL post meta for the heading (in case it's a global widget or template) ──
    $heading_old = 'Where Stunning Design Meets Flawless Functionality';
    $heading_new = 'We Build Outbound Systems That Generate Revenue.';
    $found = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_id, meta_key FROM {$wpdb->postmeta}
         WHERE meta_value LIKE %s LIMIT 20",
        '%' . $wpdb->esc_like($heading_old) . '%'
    ));
    foreach ($found as $row) {
        $val = get_metadata_by_mid('post', $row->meta_id);
        if ($val && isset($val->meta_value)) {
            $new_val = str_replace($heading_old, $heading_new, $val->meta_value);
            $wpdb->update($wpdb->postmeta, ['meta_value' => $new_val], ['meta_id' => $row->meta_id]);
            $log[] = "Found heading in post_id:{$row->post_id} key:{$row->meta_key} — replaced";
        }
    }

    // Also scan posts table
    $found_posts = $wpdb->get_results($wpdb->prepare(
        "SELECT ID, post_type FROM {$wpdb->posts}
         WHERE post_content LIKE %s AND post_status != 'auto-draft' LIMIT 10",
        '%' . $wpdb->esc_like($heading_old) . '%'
    ));
    foreach ($found_posts as $p) {
        $post_raw = $wpdb->get_var("SELECT post_content FROM {$wpdb->posts} WHERE ID={$p->ID}");
        $new_raw = str_replace($heading_old, $heading_new, $post_raw);
        $wpdb->update($wpdb->posts, ['post_content' => $new_raw], ['ID' => $p->ID]);
        clean_post_cache($p->ID);
        $log[] = "Found heading in posts table ID:{$p->ID} type:{$p->post_type} — replaced";
    }

    // ── Sub-heading scan across all meta ──
    $sub_old = 'We craft high-converting websites, apps, and brands for startups, agencies, and businesses that refuse to settle for good enough.';
    $sub_new = 'BRND GURU is a London-based B2B consulting agency. We design and deploy LinkedIn automation, cold email infrastructure, AI agents, and CRM systems that fill your pipeline.';

    $found_sub = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_id, meta_key FROM {$wpdb->postmeta}
         WHERE meta_value LIKE %s LIMIT 20",
        '%' . $wpdb->esc_like('We craft high-converting websites') . '%'
    ));
    foreach ($found_sub as $row) {
        $val = get_metadata_by_mid('post', $row->meta_id);
        if ($val && isset($val->meta_value)) {
            $new_val = str_replace($sub_old, $sub_new, $val->meta_value);
            $wpdb->update($wpdb->postmeta, ['meta_value' => $new_val], ['meta_id' => $row->meta_id]);
            $log[] = "Found subheading in post_id:{$row->post_id} key:{$row->meta_key} — replaced";
        }
    }

    // ── Global cache flush ──────────────────────────
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }
    if (class_exists('Swift_Performance')) {
        do_action('swift_performance_clear_all_cache');
    }
    wp_cache_flush();
    flush_rewrite_rules(true);

    update_option('bgft_log', $log);
    update_option('bgft_done', '1');
}

add_action('admin_notices', function () {
    if (get_option('bgft_done') !== '1') return;
    $log = get_option('bgft_log', []);
    ?>
    <div class="notice notice-success is-dismissible" style="padding:14px;">
        <h3 style="margin:0 0 8px;">✅ BRND GURU — Content Search &amp; Replace Complete</h3>
        <ul style="margin:0 0 10px;padding-left:20px;font-family:monospace;font-size:12px;">
            <?php foreach ($log as $line) : ?>
                <li><?php echo esc_html($line); ?></li>
            <?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="button button-primary">View Site →</a>
            &nbsp; Deactivate + delete this plugin when done.
        </p>
    </div>
    <?php
});
