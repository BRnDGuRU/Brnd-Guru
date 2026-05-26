<?php
/**
 * Plugin Name: BrndGuru Consulting Rewrite v11
 * Description: Rebuilds Home, About, Services, Contact as a bold creative consulting agency. DELETE after running.
 * Version: 11.1
 */
if (!defined('ABSPATH')) exit;

add_filter('plugin_row_meta', function($links, $file) {
    if (strpos($file, 'brndguru-fixes') !== false) {
        $nonce = wp_create_nonce('bg11_run');
        $links[] = '<a href="' . admin_url('?bg11_run=1&bg11_nonce=' . $nonce) . '" style="font-weight:700;color:#ff6600;font-size:14px;">▶ REBUILD SITE AS CONSULTING AGENCY</a>';
    }
    return $links;
}, 10, 2);

add_action('admin_init', function() {
    if (!isset($_GET['bg11_run'])) return;
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    if (!wp_verify_nonce($_GET['bg11_nonce'] ?? '', 'bg11_run')) wp_die('Invalid nonce.');

    echo '<pre style="font-family:monospace;padding:20px;background:#0d1117;color:#58d68d;font-size:13px;line-height:1.7;">';
    echo "BrndGuru — Consulting Agency Rewrite v11\n" . str_repeat('=', 60) . "\n\n";

    bg11_home();
    bg11_about();
    bg11_services();
    bg11_contact();
    bg11_service_pages();
    bg11_flush();

    echo str_repeat('=', 60) . "\n";
    echo '<span style="color:#ff6600;font-size:15px;">✓ DONE. Your site now looks like a consulting agency.</span>' . "\n";
    echo '<span style="color:#fff">Deactivate + delete this plugin now.</span>' . "\n";
    echo '</pre>';
    exit;
});

function bg11_ok($m)   { echo '  <span style="color:#2ecc71">✓ ' . esc_html($m) . '</span>' . "\n"; }
function bg11_info($m) { echo '  ' . esc_html($m) . "\n"; }
function bg11_head($m) { echo '<span style="color:#ff6600">── ' . esc_html($m) . ' ──</span>' . "\n"; }

$BOOK = 'https://www.brndgurumedia.com/widget/bookings/brndguru';

// ── Helpers ───────────────────────────────────────────────────
function bg11_save($id, array $elements, string $title = '', string $slug = '') {
    $json = wp_json_encode($elements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    update_post_meta($id, '_elementor_data', wp_slash($json));
    update_post_meta($id, '_elementor_edit_mode', 'builder');
    update_post_meta($id, '_wp_page_template', 'elementor_canvas');
    delete_post_meta($id, '_elementor_css');
    $args = ['ID' => $id, 'post_status' => 'publish', 'post_content' => ''];
    if ($title) $args['post_title'] = $title;
    if ($slug)  $args['post_name']  = $slug;
    wp_update_post($args);
}

function bg11_container(string $id, array $settings, array $children): array {
    return ['id' => $id, 'elType' => 'container', 'settings' => $settings, 'elements' => $children];
}
function bg11_heading(string $id, string $text, string $tag='h2', string $color='#ffffff', int $size=42, string $align='left', string $weight='700'): array {
    return ['id'=>$id,'elType'=>'widget','widgetType'=>'heading','settings'=>[
        'title'=>$text,'header_size'=>$tag,'title_color'=>$color,'align'=>$align,
        'typography_font_size'=>['unit'=>'px','size'=>$size,'sizes'=>[]],
        'typography_font_weight'=>$weight,
    ]];
}
function bg11_text(string $id, string $html, string $align='left'): array {
    return ['id'=>$id,'elType'=>'widget','widgetType'=>'text-editor','settings'=>['editor'=>$html,'text_align'=>$align]];
}
function bg11_btn(string $id, string $text, string $url, string $bg='#ff6600', string $color='#000000', string $align='left', string $size='lg'): array {
    return ['id'=>$id,'elType'=>'widget','widgetType'=>'button','settings'=>[
        'text'=>$text,'link'=>['url'=>$url,'is_external'=>'on','nofollow'=>''],
        'background_color'=>$bg,'button_text_color'=>$color,'size'=>$size,'align'=>$align,
        'border_radius'=>['unit'=>'px','top'=>'6','right'=>'6','bottom'=>'6','left'=>'6','isLinked'=>true],
        'typography_font_weight'=>'700',
    ]];
}
function bg11_divider(string $id, string $color='#333333', int $gap=0): array {
    return ['id'=>$id,'elType'=>'widget','widgetType'=>'divider','settings'=>[
        'color'=>['color'=>$color],'gap'=>['unit'=>'px','size'=>$gap],
    ]];
}
function bg11_spacer(string $id, int $h=40): array {
    return ['id'=>$id,'elType'=>'widget','widgetType'=>'spacer','settings'=>['space'=>['unit'=>'px','size'=>$h]]];
}
function bg11_section(string $bg, array $children, int $pt=80, int $pb=80): array {
    static $i = 0; $i++;
    return bg11_container('sec-'.$i, [
        'flex_direction'=>'column','content_width'=>'full',
        'background_background'=>'classic','background_color'=>$bg,
        'padding'=>['unit'=>'px','top'=>(string)$pt,'right'=>'40','bottom'=>(string)$pb,'left'=>'40','isLinked'=>false],
    ], $children);
}
function bg11_inner(array $children, int $maxW=1200, string $dir='row', string $gap='40'): array {
    static $i = 0; $i++;
    return bg11_container('inner-'.$i, [
        'flex_direction'=>$dir,'content_width'=>'boxed',
        'boxed_width'=>['unit'=>'px','size'=>$maxW,'sizes'=>[]],
        'gap'=>['unit'=>'px','size'=>(int)$gap],
        'flex_wrap'=>'wrap','align_items'=>'flex-start',
    ], $children);
}
function bg11_card(string $icon, string $title, string $desc, string $url=''): array {
    static $i=0; $i++;
    $link_html = $url ? '<a href="'.$url.'" style="display:inline-flex;align-items:center;gap:6px;color:#ff6600;font-size:13px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;text-decoration:none;margin-top:20px;border-bottom:1px solid rgba(255,102,0,0.3);padding-bottom:2px;">Explore →</a>' : '';
    return bg11_container('card-'.$i, [
        'flex_direction'=>'column',
        'background_background'=>'classic','background_color'=>'#111111',
        'border_radius'=>['unit'=>'px','top'=>'16','right'=>'16','bottom'=>'16','left'=>'16','isLinked'=>true],
        'border_top_width'=>['unit'=>'px','top'=>'3','right'=>'0','bottom'=>'0','left'=>'0','isLinked'=>false],
        'border_color'=>'#ff6600',
        'padding'=>['unit'=>'px','top'=>'40','right'=>'36','bottom'=>'40','left'=>'36','isLinked'=>false],
        'flex'=>'1 1 280px','width'=>['unit'=>'%','size'=>30],
    ],[
        bg11_text('ci-'.$i, '<div style="font-size:40px;margin-bottom:20px;filter:drop-shadow(0 0 12px rgba(255,102,0,0.3));">'.$icon.'</div>'),
        bg11_text('ct-'.$i, '<h3 style="color:#ffffff;font-size:21px;font-weight:800;margin:0;letter-spacing:-.3px;">'.$title.'</h3>'),
        bg11_text('cd-'.$i, '<p style="color:#777;font-size:15px;line-height:1.75;margin:14px 0 0;">'.$desc.'</p>'.$link_html),
    ]);
}
function bg11_stat(string $num, string $label): array {
    static $i=0; $i++;
    return bg11_container('stat-'.$i,[
        'flex_direction'=>'column','align_items'=>'center','text_align'=>'center',
        'flex'=>'1 1 180px',
    ],[
        bg11_heading('sn-'.$i,$num,'h3','#ff6600',52,'center','800'),
        bg11_text('sl-'.$i,'<p style="color:#aaa;font-size:15px;letter-spacing:.5px;text-transform:uppercase;margin:8px 0 0;">'.$label.'</p>','center'),
    ]);
}
function bg11_step(int $num, string $title, string $desc): array {
    static $i=0; $i++;
    return bg11_container('step-'.$i,[
        'flex_direction'=>'column','flex'=>'1 1 220px',
        'padding'=>['unit'=>'px','top'=>'0','right'=>'24','bottom'=>'0','left'=>'0','isLinked'=>false],
    ],[
        bg11_text('stepn-'.$i,'<div style="font-size:48px;font-weight:800;color:#ff6600;opacity:.4;line-height:1;">'.(str_pad($num,2,'0',STR_PAD_LEFT)).'</div>'),
        bg11_heading('stept-'.$i,$title,'h4','#ffffff',20,'left','700'),
        bg11_text('stepd-'.$i,'<p style="color:#888;font-size:14px;line-height:1.7;margin:10px 0 0;">'.$desc.'</p>'),
    ]);
}

// ═══════════════════════════════════════════════════════════
// HOME PAGE (ID: 12)
// ═══════════════════════════════════════════════════════════
function bg11_home() {
    global $BOOK;
    bg11_head('HOME PAGE');

    $el = [];

    // ── HERO ─────────────────────────────────────────────────
    $el[] = bg11_container('hero', [
        'flex_direction'=>'column','content_width'=>'full',
        'background_background'=>'gradient',
        'background_gradient_type'=>'linear',
        'background_gradient_angle'=>['unit'=>'deg','size'=>135],
        'background_gradient_stops'=>[
            ['color'=>'#0a0a0a','stop'=>['unit'=>'%','size'=>0]],
            ['color'=>'#1a0800','stop'=>['unit'=>'%','size'=>100]],
        ],
        'padding'=>['unit'=>'px','top'=>'160','right'=>'40','bottom'=>'140','left'=>'40','isLinked'=>false],
    ],[
        bg11_inner([
            bg11_container('hero-left',[
                'flex_direction'=>'column','width'=>['unit'=>'%','size'=>65],'flex'=>'1 1 400px',
            ],[
                bg11_text('hero-tag','<div style="display:inline-flex;align-items:center;gap:10px;background:rgba(255,102,0,0.12);border:1px solid rgba(255,102,0,0.3);border-radius:100px;padding:8px 20px;margin-bottom:4px;">
                    <span style="width:8px;height:8px;background:#ff6600;border-radius:50%;display:inline-block;box-shadow:0 0 8px #ff6600;"></span>
                    <span style="color:#ff6600;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Brand &amp; Digital Consulting Agency</span>
                </div>'),
                bg11_spacer('hs1',28),
                bg11_text('hero-h1','<h1 style="color:#ffffff;font-size:clamp(44px,6vw,76px);font-weight:900;line-height:1.05;margin:0;letter-spacing:-2px;">We Build<br><span style="color:#ff6600;-webkit-text-stroke:0px;text-shadow:0 0 60px rgba(255,102,0,0.4);">Brands</span> That<br>Dominate.</h1>'),
                bg11_spacer('hs2',28),
                bg11_text('hero-sub','<p style="color:#aaaaaa;font-size:19px;line-height:1.75;max-width:540px;border-left:3px solid #ff6600;padding-left:20px;">BrndGuru transforms ambitious companies into category-defining brands. Strategy, design, and digital — all under one roof.</p>'),
                bg11_spacer('hs3',44),
                bg11_container('hero-btns',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>16],'flex_wrap'=>'wrap'],[
                    bg11_btn('hero-btn1','Book a Strategy Call →',$BOOK,'#ff6600','#ffffff','left','lg'),
                    bg11_btn('hero-btn2','See Our Work',get_home_url().'/portfolio/','transparent','#ffffff','left','lg'),
                ]),
                bg11_spacer('hs4',64),
                bg11_text('hero-trust','<div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                    <span style="color:#444;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;">Trusted in</span>
                    <span style="background:#1a1a1a;color:#aaa;font-size:13px;font-weight:600;padding:6px 14px;border-radius:100px;border:1px solid #2a2a2a;">E-Commerce</span>
                    <span style="background:#1a1a1a;color:#aaa;font-size:13px;font-weight:600;padding:6px 14px;border-radius:100px;border:1px solid #2a2a2a;">SaaS</span>
                    <span style="background:#1a1a1a;color:#aaa;font-size:13px;font-weight:600;padding:6px 14px;border-radius:100px;border:1px solid #2a2a2a;">Retail</span>
                    <span style="background:#1a1a1a;color:#aaa;font-size:13px;font-weight:600;padding:6px 14px;border-radius:100px;border:1px solid #2a2a2a;">Fintech</span>
                    <span style="background:#1a1a1a;color:#aaa;font-size:13px;font-weight:600;padding:6px 14px;border-radius:100px;border:1px solid #2a2a2a;">Healthcare</span>
                </div>'),
            ]),
        ], 1200, 'row', '60'),
    ]);

    // ── STATS BAR ─────────────────────────────────────────────
    $el[] = bg11_container('stats-bar',[
        'flex_direction'=>'row','content_width'=>'full',
        'background_background'=>'classic','background_color'=>'#0f0f0f',
        'padding'=>['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'0','left'=>'0','isLinked'=>false],
        'border_top_width'=>['unit'=>'px','top'=>'1','right'=>'0','bottom'=>'1','left'=>'0','isLinked'=>false],
        'border_color'=>'#1e1e1e',
    ],[
        bg11_text('stats-inner','<div style="display:flex;flex-wrap:wrap;max-width:1200px;margin:0 auto;width:100%;">
            <div style="flex:1 1 200px;text-align:center;padding:48px 20px;border-right:1px solid #1e1e1e;">
                <div style="font-size:52px;font-weight:900;color:#ff6600;line-height:1;letter-spacing:-2px;">150<span style="font-size:32px;">+</span></div>
                <div style="color:#555;font-size:12px;letter-spacing:2px;text-transform:uppercase;margin-top:10px;">Projects Delivered</div>
            </div>
            <div style="flex:1 1 200px;text-align:center;padding:48px 20px;border-right:1px solid #1e1e1e;">
                <div style="font-size:52px;font-weight:900;color:#ff6600;line-height:1;letter-spacing:-2px;">8<span style="font-size:32px;">+</span></div>
                <div style="color:#555;font-size:12px;letter-spacing:2px;text-transform:uppercase;margin-top:10px;">Years of Expertise</div>
            </div>
            <div style="flex:1 1 200px;text-align:center;padding:48px 20px;border-right:1px solid #1e1e1e;">
                <div style="font-size:52px;font-weight:900;color:#ff6600;line-height:1;letter-spacing:-2px;">40<span style="font-size:32px;">+</span></div>
                <div style="color:#555;font-size:12px;letter-spacing:2px;text-transform:uppercase;margin-top:10px;">Brand Identities Built</div>
            </div>
            <div style="flex:1 1 200px;text-align:center;padding:48px 20px;">
                <div style="font-size:52px;font-weight:900;color:#ff6600;line-height:1;letter-spacing:-2px;">98<span style="font-size:32px;">%</span></div>
                <div style="color:#555;font-size:12px;letter-spacing:2px;text-transform:uppercase;margin-top:10px;">Client Retention Rate</div>
            </div>
        </div>'),
    ]);

    // ── SERVICES ──────────────────────────────────────────────
    $home = get_home_url();
    $el[] = bg11_section('#0a0a0a',[
        bg11_inner([
            bg11_text('svc-tag','<span style="display:inline-block;background:rgba(255,102,0,0.1);border:1px solid rgba(255,102,0,0.25);color:#ff6600;font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;padding:6px 16px;border-radius:100px;">What We Do</span>'),
        ], 1200, 'column', '0'),
        bg11_spacer('svc-sp1',16),
        bg11_inner([
            bg11_heading('svc-h','Our Consulting Services','h2','#ffffff',44,'left','700'),
            bg11_text('svc-sub','<p style="color:#888;font-size:17px;line-height:1.6;max-width:480px;margin-left:auto;">From brand strategy to digital execution — we cover every touchpoint that matters.</p>'),
        ], 1200, 'row', '40'),
        bg11_spacer('svc-sp2',48),
        bg11_inner([
            bg11_card('🎨','Brand Identity & Strategy','We create brand systems that communicate authority, build trust, and drive recognition across every channel.',$home.'/brand-design/'),
            bg11_card('✏️','UI/UX & Product Design','User-centred design that removes friction and guides prospects from first visit to signed contract.',$home.'/ui-ux-design/'),
            bg11_card('⚡','No-Code Development','Production-ready websites and apps built without legacy code — faster delivery, lower costs, higher ROI.',$home.'/no-code-development/'),
            bg11_card('🌐','Webflow Development','Pixel-perfect, CMS-powered Webflow sites that your team can manage without touching a line of code.',$home.'/webflow-development/'),
            bg11_card('🛒','Shopify Growth','End-to-end Shopify strategy and development for brands ready to scale their e-commerce revenue.',$home.'/shopify-xcelerator/'),
        ], 1200, 'row', '24'),
        bg11_spacer('svc-sp3',48),
        bg11_inner([
            bg11_btn('svc-cta','View All Services →',$home.'/services/','#ff6600','#000000','center','lg'),
        ], 1200, 'column', '0'),
    ], 100, 100);

    // ── WHY BRNDGURU ──────────────────────────────────────────
    $el[] = bg11_section('#141414',[
        bg11_inner([
            bg11_container('why-left',['flex_direction'=>'column','width'=>['unit'=>'%','size'=>48],'flex'=>'1 1 360px'],[
                bg11_text('why-tag','<span style="display:inline-block;background:rgba(255,102,0,0.1);border:1px solid rgba(255,102,0,0.25);color:#ff6600;font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;padding:6px 16px;border-radius:100px;">Why BrndGuru</span>'),
                bg11_spacer('why-sp1',16),
                bg11_heading('why-h','We Don\'t Just Design. We Consult.','h2','#ffffff',40,'left','700'),
                bg11_spacer('why-sp2',20),
                bg11_text('why-p','<p style="color:#999;font-size:17px;line-height:1.7;">Most agencies hand you deliverables. We hand you competitive advantages. Every project starts with understanding your market, your competitors, and your growth objectives — then we execute with precision.</p>'),
                bg11_spacer('why-sp3',32),
                bg11_btn('why-btn','Schedule a Consultation',$BOOK,'#ff6600','#000000','left','lg'),
            ]),
            bg11_container('why-right',['flex_direction'=>'column','width'=>['unit'=>'%','size'=>48],'flex'=>'1 1 360px','gap'=>['unit'=>'px','size'=>'24']],[
                bg11_container('why-r1',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'20'],'background_color'=>'#1a1a1a','border_radius'=>['unit'=>'px','top'=>'10','right'=>'10','bottom'=>'10','left'=>'10','isLinked'=>true],'padding'=>['unit'=>'px','top'=>'28','right'=>'28','bottom'=>'28','left'=>'28','isLinked'=>false]],[
                    bg11_text('ic1','<div style="font-size:32px;flex-shrink:0;">🎯</div>'),
                    bg11_container('ic1t',['flex_direction'=>'column'],[
                        bg11_heading('ic1h','Strategy First','h4','#ffffff',18,'left','700'),
                        bg11_text('ic1d','<p style="color:#888;font-size:14px;line-height:1.6;margin:6px 0 0;">Every decision is rooted in market data, competitor analysis, and your specific growth goals.</p>'),
                    ]),
                ]),
                bg11_container('why-r2',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'20'],'background_color'=>'#1a1a1a','border_radius'=>['unit'=>'px','top'=>'10','right'=>'10','bottom'=>'10','left'=>'10','isLinked'=>true],'padding'=>['unit'=>'px','top'=>'28','right'=>'28','bottom'=>'28','left'=>'28','isLinked'=>false]],[
                    bg11_text('ic2','<div style="font-size:32px;flex-shrink:0;">⚙️</div>'),
                    bg11_container('ic2t',['flex_direction'=>'column'],[
                        bg11_heading('ic2h','End-to-End Execution','h4','#ffffff',18,'left','700'),
                        bg11_text('ic2d','<p style="color:#888;font-size:14px;line-height:1.6;margin:6px 0 0;">From initial audit to final launch — one team, one vision, zero handoff chaos.</p>'),
                    ]),
                ]),
                bg11_container('why-r3',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'20'],'background_color'=>'#1a1a1a','border_radius'=>['unit'=>'px','top'=>'10','right'=>'10','bottom'=>'10','left'=>'10','isLinked'=>true],'padding'=>['unit'=>'px','top'=>'28','right'=>'28','bottom'=>'28','left'=>'28','isLinked'=>false]],[
                    bg11_text('ic3','<div style="font-size:32px;flex-shrink:0;">📈</div>'),
                    bg11_container('ic3t',['flex_direction'=>'column'],[
                        bg11_heading('ic3h','Measurable Results','h4','#ffffff',18,'left','700'),
                        bg11_text('ic3d','<p style="color:#888;font-size:14px;line-height:1.6;margin:6px 0 0;">We set KPIs at the start and report against them. Accountability is non-negotiable.</p>'),
                    ]),
                ]),
                bg11_container('why-r4',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'20'],'background_color'=>'#1a1a1a','border_radius'=>['unit'=>'px','top'=>'10','right'=>'10','bottom'=>'10','left'=>'10','isLinked'=>true],'padding'=>['unit'=>'px','top'=>'28','right'=>'28','bottom'=>'28','left'=>'28','isLinked'=>false]],[
                    bg11_text('ic4','<div style="font-size:32px;flex-shrink:0;">🤝</div>'),
                    bg11_container('ic4t',['flex_direction'=>'column'],[
                        bg11_heading('ic4h','Long-Term Partnership','h4','#ffffff',18,'left','700'),
                        bg11_text('ic4d','<p style="color:#888;font-size:14px;line-height:1.6;margin:6px 0 0;">98% of our clients return for follow-on engagements. We build relationships, not just brands.</p>'),
                    ]),
                ]),
            ]),
        ], 1200, 'row', '80'),
    ], 100, 100);

    // ── PROCESS ───────────────────────────────────────────────
    $el[] = bg11_section('#0d0d0d',[
        bg11_inner([
            bg11_text('proc-tag','<div style="text-align:center;"><span style="display:inline-block;background:rgba(255,102,0,0.1);border:1px solid rgba(255,102,0,0.25);color:#ff6600;font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;padding:6px 16px;border-radius:100px;">How We Work</span></div>'),
        ],1200,'column','0'),
        bg11_spacer('proc-sp1',16),
        bg11_inner([
            bg11_heading('proc-h','Our Consulting Process','h2','#ffffff',44,'center','700'),
        ],1200,'column','0'),
        bg11_spacer('proc-sp2',16),
        bg11_inner([
            bg11_text('proc-sub','<p style="color:#888;font-size:17px;line-height:1.6;max-width:560px;margin:0 auto;text-align:center;">A proven four-phase framework that delivers clarity, momentum, and results on every engagement.</p>'),
        ],1200,'column','0'),
        bg11_spacer('proc-sp3',56),
        bg11_inner([
            bg11_step(1,'Discovery & Audit','We analyse your current brand position, competitive landscape, and growth opportunities through structured workshops and market research.'),
            bg11_step(2,'Strategy & Positioning','We develop your brand strategy, messaging framework, and digital roadmap — a clear blueprint aligned with your business objectives.'),
            bg11_step(3,'Design & Build','Our creative and technical teams execute with precision — brand identity, website, and digital assets built to the highest standard.'),
            bg11_step(4,'Launch & Optimise','We manage launch, measure performance against agreed KPIs, and continuously optimise to maximise your return on investment.'),
        ],1200,'row','32'),
    ],100,100);

    // ── CTA BANNER ────────────────────────────────────────────
    $el[] = bg11_container('cta-banner',[
        'flex_direction'=>'column','content_width'=>'full',
        'background_background'=>'gradient',
        'background_gradient_type'=>'linear',
        'background_gradient_angle'=>['unit'=>'deg','size'=>135],
        'background_gradient_stops'=>[
            ['color'=>'#cc4400','stop'=>['unit'=>'%','size'=>0]],
            ['color'=>'#ff6600','stop'=>['unit'=>'%','size'=>50]],
            ['color'=>'#ff8800','stop'=>['unit'=>'%','size'=>100]],
        ],
        'padding'=>['unit'=>'px','top'=>'110','right'=>'40','bottom'=>'110','left'=>'40','isLinked'=>false],
        'text_align'=>'center',
    ],[
        bg11_text('cta-label','<div style="text-align:center;margin-bottom:24px;"><span style="background:rgba(0,0,0,0.2);color:#fff;font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;padding:6px 16px;border-radius:100px;">Free Strategy Call</span></div>'),
        bg11_text('cta-h','<h2 style="color:#ffffff;font-size:clamp(36px,5vw,62px);font-weight:900;line-height:1.05;margin:0;letter-spacing:-1.5px;text-shadow:0 4px 24px rgba(0,0,0,0.2);">Ready to Build a Brand<br>That Leads Your Market?</h2>'),
        bg11_spacer('cta-sp1',24),
        bg11_text('cta-sub','<p style="color:rgba(255,255,255,0.85);font-size:19px;line-height:1.6;max-width:560px;margin:0 auto;">30 minutes. No pitch. Just an honest conversation about your biggest growth opportunity.</p>','center'),
        bg11_spacer('cta-sp2',44),
        bg11_container('cta-btns',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'16'],'justify_content'=>'center','flex_wrap'=>'wrap'],[
            bg11_btn('cta-btn1','Book Your Free Strategy Call →',$BOOK,'#000000','#ffffff','center','lg'),
            bg11_btn('cta-btn2','View Our Work',get_home_url().'/portfolio/','rgba(255,255,255,0.15)','#ffffff','center','lg'),
        ]),
        bg11_spacer('cta-sp3',32),
        bg11_text('cta-note','<p style="color:rgba(255,255,255,0.5);font-size:13px;text-align:center;">No commitment required · Response within 24 hours · 100% confidential</p>','center'),
    ]);

    bg11_save(12, $el, 'Home');
    bg11_ok('Home page rebuilt (5 sections)');
    echo "\n";
}

// ═══════════════════════════════════════════════════════════
// ABOUT PAGE (ID: 1628)
// ═══════════════════════════════════════════════════════════
function bg11_about() {
    global $BOOK;
    bg11_head('ABOUT PAGE');

    $el = [];

    // Hero
    $el[] = bg11_container('ab-hero',[
        'flex_direction'=>'column','content_width'=>'full',
        'background_background'=>'classic','background_color'=>'#0d0d0d',
        'padding'=>['unit'=>'px','top'=>'120','right'=>'40','bottom'=>'100','left'=>'40','isLinked'=>false],
    ],[
        bg11_inner([
            bg11_container('ab-hero-content',['flex_direction'=>'column','width'=>['unit'=>'%','size'=>65],'flex'=>'1 1 400px'],[
                bg11_text('ab-tag','<span style="color:#ff6600;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">About BrndGuru</span>'),
                bg11_spacer('ab-sp1',16),
                bg11_heading('ab-h1','The Consulting Team Behind Your Next Big Brand.','h1','#ffffff',52,'left','800'),
                bg11_spacer('ab-sp2',24),
                bg11_text('ab-sub','<p style="color:#aaa;font-size:19px;line-height:1.7;">We are a brand and digital consulting agency built for founders, marketers, and leaders who are serious about growth. No fluff. No generic templates. Just sharp strategy and precise execution.</p>'),
                bg11_spacer('ab-sp3',36),
                bg11_btn('ab-cta','Work With Us',$BOOK,'#ff6600','#000000','left','lg'),
            ]),
        ],1200,'row','0'),
    ]);

    // Mission
    $el[] = bg11_section('#111111',[
        bg11_inner([
            bg11_container('ms-left',['flex_direction'=>'column','flex'=>'1 1 340px','width'=>['unit'=>'%','size'=>40]],[
                bg11_text('ms-tag','<span style="color:#ff6600;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Our Mission</span>'),
                bg11_spacer('ms-sp1',16),
                bg11_heading('ms-h','We Exist to Make Great Brands Impossible to Ignore.','h2','#ffffff',38,'left','700'),
            ]),
            bg11_container('ms-right',['flex_direction'=>'column','flex'=>'1 1 340px','width'=>['unit'=>'%','size'=>55],'padding'=>['unit'=>'px','top'=>'8','right'=>'0','bottom'=>'0','left'=>'0','isLinked'=>false]],[
                bg11_text('ms-p1','<p style="color:#aaa;font-size:17px;line-height:1.8;">The market is noisy. Attention is scarce. And most brands look, sound, and feel identical to their competitors. BrndGuru was founded to solve that problem.</p>'),
                bg11_spacer('ms-sp2',20),
                bg11_text('ms-p2','<p style="color:#aaa;font-size:17px;line-height:1.8;">We combine rigorous strategic thinking with exceptional creative and technical execution to build brands that earn attention, command premium pricing, and retain customers for life.</p>'),
                bg11_spacer('ms-sp3',20),
                bg11_text('ms-p3','<p style="color:#aaa;font-size:17px;line-height:1.8;">Our clients are not looking for another vendor. They are looking for a partner who is as invested in their success as they are. That is who we are.</p>'),
            ]),
        ],1200,'row','80'),
    ], 100, 100);

    // Values
    $el[] = bg11_section('#0a0a0a',[
        bg11_inner([
            bg11_text('val-tag','<div style="text-align:center;"><span style="color:#ff6600;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Our Values</span></div>'),
        ],1200,'column','0'),
        bg11_spacer('val-sp0',16),
        bg11_inner([
            bg11_heading('val-h','What We Stand For','h2','#ffffff',44,'center','700'),
        ],1200,'column','0'),
        bg11_spacer('val-sp1',48),
        bg11_inner([
            bg11_card('🔍','Clarity Over Complexity','We cut through confusion to give you a clear strategy, clear deliverables, and a clear path to results.'),
            bg11_card('💡','Insight-Driven Decisions','Every recommendation is backed by research, data, and honest expertise — not guesswork or trends.'),
            bg11_card('🏆','Excellence Without Compromise','We hold ourselves to exceptional standards because your brand deserves nothing less than the best.'),
            bg11_card('🤝','Partnership Over Transactions','We build long-term relationships. Your wins are our wins. Your challenges become ours to solve.'),
        ],1200,'row','24'),
    ],100,100);

    // How we work
    $el[] = bg11_section('#141414',[
        bg11_inner([
            bg11_container('hw-left',['flex_direction'=>'column','flex'=>'1 1 340px','width'=>['unit'=>'%','size'=>45]],[
                bg11_text('hw-tag','<span style="color:#ff6600;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">How We Work</span>'),
                bg11_spacer('hw-sp1',16),
                bg11_heading('hw-h','Embedded Partners, Not Outside Vendors.','h2','#ffffff',38,'left','700'),
                bg11_spacer('hw-sp2',20),
                bg11_text('hw-p','<p style="color:#aaa;font-size:17px;line-height:1.8;">When you engage BrndGuru, you get a dedicated consulting team that integrates with your business. We attend your strategy sessions, challenge your assumptions, and operate with full accountability to your outcomes.</p>'),
                bg11_spacer('hw-sp3',32),
                bg11_btn('hw-btn','Start a Conversation',$BOOK,'#ff6600','#000000','left','lg'),
            ]),
            bg11_container('hw-right',['flex_direction'=>'column','flex'=>'1 1 340px','width'=>['unit'=>'%','size'=>50],'gap'=>['unit'=>'px','size'=>'4']],[
                bg11_container('hw-item1',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'20'],'padding'=>['unit'=>'px','top'=>'20','right'=>'0','bottom'=>'20','left'=>'0','isLinked'=>false],'border_bottom_width'=>['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'1','left'=>'0','isLinked'=>false],'border_color'=>'#222'],[
                    bg11_text('hwi1n','<span style="font-size:22px;font-weight:800;color:#ff6600;min-width:32px;">01</span>'),
                    bg11_container('hwi1t',['flex_direction'=>'column'],[
                        bg11_heading('hwi1h','Dedicated Account Lead','h4','#ffffff',17,'left','600'),
                        bg11_text('hwi1d','<p style="color:#777;font-size:14px;line-height:1.6;margin:6px 0 0;">One senior consultant owns your engagement from kickoff to delivery.</p>'),
                    ]),
                ]),
                bg11_container('hw-item2',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'20'],'padding'=>['unit'=>'px','top'=>'20','right'=>'0','bottom'=>'20','left'=>'0','isLinked'=>false],'border_bottom_width'=>['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'1','left'=>'0','isLinked'=>false],'border_color'=>'#222'],[
                    bg11_text('hwi2n','<span style="font-size:22px;font-weight:800;color:#ff6600;min-width:32px;">02</span>'),
                    bg11_container('hwi2t',['flex_direction'=>'column'],[
                        bg11_heading('hwi2h','Weekly Progress Reviews','h4','#ffffff',17,'left','600'),
                        bg11_text('hwi2d','<p style="color:#777;font-size:14px;line-height:1.6;margin:6px 0 0;">Regular check-ins ensure alignment and keep projects on track and on budget.</p>'),
                    ]),
                ]),
                bg11_container('hw-item3',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'20'],'padding'=>['unit'=>'px','top'=>'20','right'=>'0','bottom'=>'20','left'=>'0','isLinked'=>false],'border_bottom_width'=>['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'1','left'=>'0','isLinked'=>false],'border_color'=>'#222'],[
                    bg11_text('hwi3n','<span style="font-size:22px;font-weight:800;color:#ff6600;min-width:32px;">03</span>'),
                    bg11_container('hwi3t',['flex_direction'=>'column'],[
                        bg11_heading('hwi3h','Transparent Reporting','h4','#ffffff',17,'left','600'),
                        bg11_text('hwi3d','<p style="color:#777;font-size:14px;line-height:1.6;margin:6px 0 0;">You always know what we\'re working on, why, and what it means for your goals.</p>'),
                    ]),
                ]),
                bg11_container('hw-item4',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'20'],'padding'=>['unit'=>'px','top'=>'20','right'=>'0','bottom'=>'20','left'=>'0','isLinked'=>false]],[
                    bg11_text('hwi4n','<span style="font-size:22px;font-weight:800;color:#ff6600;min-width:32px;">04</span>'),
                    bg11_container('hwi4t',['flex_direction'=>'column'],[
                        bg11_heading('hwi4h','Post-Launch Support','h4','#ffffff',17,'left','600'),
                        bg11_text('hwi4d','<p style="color:#777;font-size:14px;line-height:1.6;margin:6px 0 0;">Delivery isn\'t the end. We stay engaged to optimise performance and capture new opportunities.</p>'),
                    ]),
                ]),
            ]),
        ],1200,'row','80'),
    ],100,100);

    // CTA
    $el[] = bg11_container('ab-cta-wrap',[
        'flex_direction'=>'column','content_width'=>'full',
        'background_background'=>'classic','background_color'=>'#ff6600',
        'padding'=>['unit'=>'px','top'=>'90','right'=>'40','bottom'=>'90','left'=>'40','isLinked'=>false],
        'text_align'=>'center',
    ],[
        bg11_heading('ab-cta-h','Let\'s Build Something Exceptional.','h2','#000000',44,'center','800'),
        bg11_spacer('ab-cta-sp',20),
        bg11_text('ab-cta-sub','<p style="color:#333;font-size:18px;max-width:520px;margin:0 auto;line-height:1.6;">Tell us about your brand challenge. We\'ll respond within one business day with a clear plan of attack.</p>','center'),
        bg11_spacer('ab-cta-sp2',36),
        bg11_btn('ab-cta-btn','Book a Free Consultation →',$BOOK,'#000000','#ffffff','center','lg'),
    ]);

    bg11_save(1628, $el, 'About');
    bg11_ok('About page rebuilt (5 sections)');
    echo "\n";
}

// ═══════════════════════════════════════════════════════════
// SERVICES PAGE (ID: 4325)
// ═══════════════════════════════════════════════════════════
function bg11_services() {
    global $BOOK;
    bg11_head('SERVICES PAGE');

    $home = get_home_url();
    $services = [
        ['🎨','Brand Identity & Strategy','We build brand systems that communicate authority and create lasting market recognition. From positioning and messaging to visual identity and brand guidelines.',$home.'/brand-design/'],
        ['✏️','UI/UX & Product Design','Strategic design that removes friction from the user journey. Wireframes, prototypes, and final UI that convert visitors into customers.',$home.'/ui-ux-design/'],
        ['⚡','No-Code Development','Launch production-ready digital products in weeks, not months. We leverage the best no-code platforms to deliver faster and smarter.',$home.'/no-code-development/'],
        ['🌐','Webflow Development','Bespoke Webflow websites that your marketing team can own and update. Fast, responsive, and built to rank.',$home.'/webflow-development/'],
        ['🛒','Shopify Growth & Development','Full-service Shopify engagements — from store architecture and design to conversion optimisation and growth strategy.',$home.'/shopify-xcelerator/'],
    ];

    $el = [];

    $el[] = bg11_container('sv-hero',[
        'flex_direction'=>'column','content_width'=>'full',
        'background_background'=>'classic','background_color'=>'#0d0d0d',
        'padding'=>['unit'=>'px','top'=>'120','right'=>'40','bottom'=>'80','left'=>'40','isLinked'=>false],
        'text_align'=>'center',
    ],[
        bg11_text('sv-tag','<div style="text-align:center;"><span style="color:#ff6600;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">What We Offer</span></div>'),
        bg11_spacer('sv-sp1',16),
        bg11_heading('sv-h','Consulting Services','h1','#ffffff',58,'center','800'),
        bg11_spacer('sv-sp2',20),
        bg11_text('sv-sub','<p style="color:#aaa;font-size:20px;max-width:640px;margin:0 auto;line-height:1.7;">Every service we offer is anchored in strategy. We don\'t execute blindly — we consult first, then build.</p>','center'),
        bg11_spacer('sv-sp3',40),
        bg11_btn('sv-cta','Book a Strategy Call →',$BOOK,'#ff6600','#000000','center','lg'),
    ]);

    // Service rows
    foreach ($services as $i => $s) {
        $bg = $i % 2 === 0 ? '#0a0a0a' : '#111111';
        $el[] = bg11_section($bg,[
            bg11_inner([
                bg11_container('svr-icon-'.$i,['flex_direction'=>'column','flex'=>'0 0 auto','align_items'=>'center','justify_content'=>'center',
                    'background_background'=>'classic','background_color'=>'#141414',
                    'border_radius'=>['unit'=>'px','top'=>'16','right'=>'16','bottom'=>'16','left'=>'16','isLinked'=>true],
                    'padding'=>['unit'=>'px','top'=>'32','right'=>'32','bottom'=>'32','left'=>'32','isLinked'=>false],
                    'width'=>['unit'=>'px','size'=>120],
                ],[
                    bg11_text('svr-i-'.$i,'<div style="font-size:52px;text-align:center;">'.$s[0].'</div>'),
                ]),
                bg11_container('svr-content-'.$i,['flex_direction'=>'column','flex'=>'1 1 400px'],[
                    bg11_heading('svr-h-'.$i, $s[1],'h2','#ffffff',34,'left','700'),
                    bg11_spacer('svr-sp-'.$i,16),
                    bg11_text('svr-d-'.$i,'<p style="color:#aaa;font-size:17px;line-height:1.8;">'.$s[2].'</p>'),
                    bg11_spacer('svr-sp2-'.$i,24),
                    bg11_btn('svr-btn-'.$i,'Explore This Service →',$s[3],'#ff6600','#000000','left','md'),
                ]),
            ],1200,'row','48'),
        ],72,72);
    }

    // Bottom CTA
    $el[] = bg11_container('sv-bottom-cta',[
        'flex_direction'=>'column','content_width'=>'full',
        'background_background'=>'classic','background_color'=>'#ff6600',
        'padding'=>['unit'=>'px','top'=>'90','right'=>'40','bottom'=>'90','left'=>'40','isLinked'=>false],
        'text_align'=>'center',
    ],[
        bg11_heading('sv-cta-h','Not Sure Which Service You Need?','h2','#000000',44,'center','800'),
        bg11_spacer('sv-cta-sp',20),
        bg11_text('sv-cta-sub','<p style="color:#333;font-size:18px;max-width:520px;margin:0 auto;line-height:1.6;">Book a free 30-minute discovery call. We\'ll diagnose your biggest challenge and recommend the right engagement.</p>','center'),
        bg11_spacer('sv-cta-sp2',36),
        bg11_btn('sv-cta-btn','Book a Free Discovery Call →',$BOOK,'#000000','#ffffff','center','lg'),
    ]);

    bg11_save(4325, $el, 'Services');
    bg11_ok('Services page rebuilt (hero + 5 service rows + CTA)');
    echo "\n";
}

// ═══════════════════════════════════════════════════════════
// CONTACT PAGE (ID: 23)
// ═══════════════════════════════════════════════════════════
function bg11_contact() {
    global $BOOK;
    bg11_head('CONTACT PAGE');

    $el = [];

    $el[] = bg11_container('ct-hero',[
        'flex_direction'=>'column','content_width'=>'full',
        'background_background'=>'classic','background_color'=>'#0d0d0d',
        'padding'=>['unit'=>'px','top'=>'120','right'=>'40','bottom'=>'100','left'=>'40','isLinked'=>false],
    ],[
        bg11_inner([
            bg11_container('ct-left',['flex_direction'=>'column','flex'=>'1 1 380px','width'=>['unit'=>'%','size'=>48]],[
                bg11_text('ct-tag','<span style="color:#ff6600;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Get In Touch</span>'),
                bg11_spacer('ct-sp1',16),
                bg11_heading('ct-h','Let\'s Talk About Your Brand.','h1','#ffffff',48,'left','800'),
                bg11_spacer('ct-sp2',24),
                bg11_text('ct-p','<p style="color:#aaa;font-size:17px;line-height:1.8;">Whether you\'re starting from scratch or need to reposition an existing brand, we\'d love to hear about your challenge. Book a call and let\'s explore what\'s possible.</p>'),
                bg11_spacer('ct-sp3',40),
                bg11_container('ct-info',['flex_direction'=>'column','gap'=>['unit'=>'px','size'=>'24']],[
                    bg11_container('ct-i1',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'16'],'align_items'=>'center'],[
                        bg11_text('ct-i1i','<span style="font-size:22px;">📧</span>'),
                        bg11_container('ct-i1t',['flex_direction'=>'column'],[
                            bg11_text('ct-i1l','<span style="color:#666;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Email</span>'),
                            bg11_text('ct-i1v','<a href="mailto:hello@brndguru.com" style="color:#fff;font-size:16px;font-weight:600;text-decoration:none;">hello@brndguru.com</a>'),
                        ]),
                    ]),
                    bg11_container('ct-i2',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'16'],'align_items'=>'center'],[
                        bg11_text('ct-i2i','<span style="font-size:22px;">📅</span>'),
                        bg11_container('ct-i2t',['flex_direction'=>'column'],[
                            bg11_text('ct-i2l','<span style="color:#666;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Book a Call</span>'),
                            bg11_text('ct-i2v','<a href="'.$BOOK.'" target="_blank" style="color:#ff6600;font-size:16px;font-weight:600;text-decoration:none;">Schedule via Calendly →</a>'),
                        ]),
                    ]),
                    bg11_container('ct-i3',['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'16'],'align_items'=>'center'],[
                        bg11_text('ct-i3i','<span style="font-size:22px;">⏱️</span>'),
                        bg11_container('ct-i3t',['flex_direction'=>'column'],[
                            bg11_text('ct-i3l','<span style="color:#666;font-size:12px;text-transform:uppercase;letter-spacing:1px;">Response Time</span>'),
                            bg11_text('ct-i3v','<span style="color:#fff;font-size:16px;font-weight:600;">Within 1 business day</span>'),
                        ]),
                    ]),
                ]),
            ]),
            bg11_container('ct-right',['flex_direction'=>'column','flex'=>'1 1 380px','width'=>['unit'=>'%','size'=>48],
                'background_background'=>'classic','background_color'=>'#141414',
                'border_radius'=>['unit'=>'px','top'=>'16','right'=>'16','bottom'=>'16','left'=>'16','isLinked'=>true],
                'padding'=>['unit'=>'px','top'=>'48','right'=>'48','bottom'=>'48','left'=>'48','isLinked'=>false],
            ],[
                bg11_heading('ct-r-h','Book a Free Strategy Call','h3','#ffffff',26,'center','700'),
                bg11_spacer('ct-r-sp1',16),
                bg11_text('ct-r-p','<p style="color:#888;font-size:15px;line-height:1.7;text-align:center;margin:0 0 32px;">30 minutes. No pitch. Just an honest conversation about your brand and where you want to take it.</p>','center'),
                bg11_btn('ct-r-btn1','Schedule Your Call Now →',$BOOK,'#ff6600','#000000','center','lg'),
                bg11_spacer('ct-r-sp2',24),
                bg11_text('ct-r-inc','<div style="text-align:center;color:#666;font-size:14px;line-height:1.8;">
                    ✓ &nbsp;Brand audit overview<br>
                    ✓ &nbsp;Competitor landscape review<br>
                    ✓ &nbsp;Growth opportunity identification<br>
                    ✓ &nbsp;Recommended engagement path<br>
                    ✓ &nbsp;100% free, no obligation
                </div>','center'),
            ]),
        ],1200,'row','60'),
    ]);

    $el[] = bg11_container('ct-faq',[
        'flex_direction'=>'column','content_width'=>'full',
        'background_background'=>'classic','background_color'=>'#0a0a0a',
        'padding'=>['unit'=>'px','top'=>'90','right'=>'40','bottom'=>'90','left'=>'40','isLinked'=>false],
    ],[
        bg11_inner([
            bg11_heading('faq-h','Common Questions','h2','#ffffff',40,'center','700'),
        ],1200,'column','0'),
        bg11_spacer('faq-sp0',48),
        bg11_inner([
            bg11_container('faq-col1',['flex_direction'=>'column','flex'=>'1 1 420px','gap'=>['unit'=>'px','size'=>'32']],[
                bg11_container('faq-1',['flex_direction'=>'column','background_color'=>'#141414','border_radius'=>['unit'=>'px','top'=>'10','right'=>'10','bottom'=>'10','left'=>'10','isLinked'=>true],'padding'=>['unit'=>'px','top'=>'28','right'=>'28','bottom'=>'28','left'=>'28','isLinked'=>false]],[
                    bg11_heading('faq-1h','How long does a typical engagement take?','h4','#ffffff',17,'left','700'),
                    bg11_text('faq-1d','<p style="color:#888;font-size:14px;line-height:1.7;margin:12px 0 0;">Brand identity projects typically take 4–8 weeks. Website builds range from 3–10 weeks depending on scope. We\'ll give you a precise timeline in your first consultation.</p>'),
                ]),
                bg11_container('faq-2',['flex_direction'=>'column','background_color'=>'#141414','border_radius'=>['unit'=>'px','top'=>'10','right'=>'10','bottom'=>'10','left'=>'10','isLinked'=>true],'padding'=>['unit'=>'px','top'=>'28','right'=>'28','bottom'=>'28','left'=>'28','isLinked'=>false]],[
                    bg11_heading('faq-2h','Do you work with early-stage startups?','h4','#ffffff',17,'left','700'),
                    bg11_text('faq-2d','<p style="color:#888;font-size:14px;line-height:1.7;margin:12px 0 0;">Yes. We work with companies at every stage — from pre-revenue startups building their first brand to established businesses repositioning for growth.</p>'),
                ]),
            ]),
            bg11_container('faq-col2',['flex_direction'=>'column','flex'=>'1 1 420px','gap'=>['unit'=>'px','size'=>'32']],[
                bg11_container('faq-3',['flex_direction'=>'column','background_color'=>'#141414','border_radius'=>['unit'=>'px','top'=>'10','right'=>'10','bottom'=>'10','left'=>'10','isLinked'=>true],'padding'=>['unit'=>'px','top'=>'28','right'=>'28','bottom'=>'28','left'=>'28','isLinked'=>false]],[
                    bg11_heading('faq-3h','What industries do you specialise in?','h4','#ffffff',17,'left','700'),
                    bg11_text('faq-3d','<p style="color:#888;font-size:14px;line-height:1.7;margin:12px 0 0;">We have deep experience in e-commerce, SaaS, professional services, fintech, and consumer brands. Our strategic frameworks adapt to any industry.</p>'),
                ]),
                bg11_container('faq-4',['flex_direction'=>'column','background_color'=>'#141414','border_radius'=>['unit'=>'px','top'=>'10','right'=>'10','bottom'=>'10','left'=>'10','isLinked'=>true],'padding'=>['unit'=>'px','top'=>'28','right'=>'28','bottom'=>'28','left'=>'28','isLinked'=>false]],[
                    bg11_heading('faq-4h','What happens after the strategy call?','h4','#ffffff',17,'left','700'),
                    bg11_text('faq-4d','<p style="color:#888;font-size:14px;line-height:1.7;margin:12px 0 0;">If there\'s a fit, we\'ll send a tailored proposal within 48 hours outlining scope, timeline, and investment. No pressure, no hard sell.</p>'),
                ]),
            ]),
        ],1200,'row','24'),
    ]);

    bg11_save(23, $el, 'Contact');
    bg11_ok('Contact page rebuilt');
    echo "\n";
}

// ═══════════════════════════════════════════════════════════
// INDIVIDUAL SERVICE PAGES
// ═══════════════════════════════════════════════════════════
function bg11_service_pages() {
    global $BOOK;
    bg11_head('INDIVIDUAL SERVICE PAGES');

    $pages = [
        762  => ['Brand Design','🎨','Brand Identity & Strategy','We transform how the market perceives your business.',
            'A strong brand is not a logo. It is a system of signals — visual, verbal, and emotional — that tells your market exactly who you are, why you\'re different, and why they should choose you.',
            ['Brand Audit & Competitive Analysis','Positioning & Messaging Strategy','Logo & Visual Identity System','Brand Guidelines Document','Tone of Voice & Messaging Framework','Marketing Collateral Design'],
        ],
        724  => ['UI/UX Design','✏️','UI/UX & Product Design','Design that converts visitors into customers.',
            'We design digital experiences that are not just beautiful — they are engineered to guide users toward action. Every element earns its place by serving your conversion goals.',
            ['UX Research & User Journey Mapping','Wireframing & Prototyping','UI Design & Design Systems','Usability Testing','Conversion Rate Optimisation','Handoff to Development'],
        ],
        766  => ['No-Code Development','⚡','No-Code Development','Launch faster. Cost less. Scale smarter.',
            'Traditional development is slow and expensive. No-code lets us build production-grade products in weeks, not months — without sacrificing quality, performance, or scalability.',
            ['Platform Selection & Architecture','Database & Workflow Design','Responsive Frontend Build','CMS & Content Setup','Integrations & Automations','Launch & Ongoing Support'],
        ],
        765  => ['Webflow Development','🌐','Webflow Development','The last website you\'ll ever need to rebuild.',
            'Webflow combines the flexibility of custom code with the simplicity of a CMS your team can actually use. We build fast, beautiful, SEO-optimised Webflow sites built to grow with your business.',
            ['Custom Webflow Design','CMS Architecture','Responsive & Accessible Build','SEO & Performance Optimisation','Animations & Interactions','Team Training & Handoff'],
        ],
        2970 => ['Shopify Xcelerator','🛒','Shopify Growth & Development','E-commerce built to convert and scale.',
            'Your Shopify store should be your highest-performing sales asset. We combine conversion-focused design with technical excellence to build stores that grow revenue month after month.',
            ['Store Audit & Strategy','Custom Theme Design & Development','Product & Collection Architecture','Checkout Optimisation','App Integration & Automation','Growth Strategy & Analytics'],
        ],
    ];

    foreach ($pages as $pid => $p) {
        [$slug, $icon, $title, $tagline, $desc, $deliverables] = $p;
        $el = [];

        // Hero
        $el[] = bg11_container('sp-hero-'.$pid,[
            'flex_direction'=>'column','content_width'=>'full',
            'background_background'=>'classic','background_color'=>'#0d0d0d',
            'padding'=>['unit'=>'px','top'=>'120','right'=>'40','bottom'=>'100','left'=>'40','isLinked'=>false],
        ],[
            bg11_inner([
                bg11_container('sp-hero-c-'.$pid,['flex_direction'=>'column','flex'=>'1 1 400px','width'=>['unit'=>'%','size'=>65]],[
                    bg11_text('sp-icon-'.$pid,'<div style="font-size:56px;margin-bottom:20px;">'.$icon.'</div>'),
                    bg11_text('sp-tag-'.$pid,'<span style="color:#ff6600;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">BrndGuru Consulting</span>'),
                    bg11_spacer('sp-sp1-'.$pid,12),
                    bg11_heading('sp-h-'.$pid,$title,'h1','#ffffff',52,'left','800'),
                    bg11_spacer('sp-sp2-'.$pid,12),
                    bg11_heading('sp-tl-'.$pid,$tagline,'h2','#ff6600',24,'left','500'),
                    bg11_spacer('sp-sp3-'.$pid,24),
                    bg11_text('sp-desc-'.$pid,'<p style="color:#aaa;font-size:18px;line-height:1.8;">'.$desc.'</p>'),
                    bg11_spacer('sp-sp4-'.$pid,36),
                    bg11_container('sp-btns-'.$pid,['flex_direction'=>'row','gap'=>['unit'=>'px','size'=>'16'],'flex_wrap'=>'wrap'],[
                        bg11_btn('sp-btn1-'.$pid,'Start This Project',$BOOK,'#ff6600','#000000','left','lg'),
                        bg11_btn('sp-btn2-'.$pid,'View All Services',get_home_url().'/services/','transparent','#ffffff','left','lg'),
                    ]),
                ]),
            ],1200,'row','0'),
        ]);

        // Deliverables
        $items = '';
        foreach ($deliverables as $d) {
            $items .= '<div style="display:flex;align-items:center;gap:12px;padding:14px 0;border-bottom:1px solid #1e1e1e;">
                <span style="color:#ff6600;font-size:18px;flex-shrink:0;">✓</span>
                <span style="color:#ddd;font-size:16px;">'.$d.'</span>
            </div>';
        }

        $el[] = bg11_section('#111111',[
            bg11_inner([
                bg11_container('sp-del-left-'.$pid,['flex_direction'=>'column','flex'=>'1 1 340px','width'=>['unit'=>'%','size'=>45]],[
                    bg11_text('sp-del-tag-'.$pid,'<span style="color:#ff6600;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">What\'s Included</span>'),
                    bg11_spacer('sp-del-sp1-'.$pid,16),
                    bg11_heading('sp-del-h-'.$pid,'Everything You Need to Succeed.','h2','#ffffff',36,'left','700'),
                    bg11_spacer('sp-del-sp2-'.$pid,20),
                    bg11_text('sp-del-p-'.$pid,'<p style="color:#888;font-size:16px;line-height:1.7;">Our '.strtolower($title).' engagement is comprehensive. You get a full consulting team working toward one goal: measurable results for your business.</p>'),
                    bg11_spacer('sp-del-sp3-'.$pid,32),
                    bg11_btn('sp-del-btn-'.$pid,'Discuss Your Project →',$BOOK,'#ff6600','#000000','left','lg'),
                ]),
                bg11_container('sp-del-right-'.$pid,['flex_direction'=>'column','flex'=>'1 1 340px','width'=>['unit'=>'%','size'=>50]],[
                    bg11_text('sp-del-list-'.$pid,'<div>'.$items.'</div>'),
                ]),
            ],1200,'row','80'),
        ],80,80);

        // CTA
        $el[] = bg11_container('sp-cta-'.$pid,[
            'flex_direction'=>'column','content_width'=>'full',
            'background_background'=>'classic','background_color'=>'#ff6600',
            'padding'=>['unit'=>'px','top'=>'80','right'=>'40','bottom'=>'80','left'=>'40','isLinked'=>false],
            'text_align'=>'center',
        ],[
            bg11_heading('sp-cta-h-'.$pid,'Ready to Get Started?','h2','#000000',42,'center','800'),
            bg11_spacer('sp-cta-sp-'.$pid,16),
            bg11_text('sp-cta-sub-'.$pid,'<p style="color:#333;font-size:18px;max-width:500px;margin:0 auto;line-height:1.6;">Book a free discovery call and let\'s map out your '.strtolower($title).' project.</p>','center'),
            bg11_spacer('sp-cta-sp2-'.$pid,32),
            bg11_btn('sp-cta-btn-'.$pid,'Book a Free Call →',$BOOK,'#000000','#ffffff','center','lg'),
        ]);

        bg11_save($pid, $el, $slug);
        bg11_ok('Service page rebuilt: '.$title.' (ID '.$pid.')');
    }
    echo "\n";
}

// ─────────────────────────────────────────────────────────────
// FLUSH
// ─────────────────────────────────────────────────────────────
function bg11_flush() {
    bg11_head('FLUSHING CACHES');
    wp_cache_flush(); bg11_ok('Object cache');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    bg11_ok('Transients');
    if (class_exists('\Elementor\Plugin')) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
        bg11_ok('Elementor CSS cache');
    }
    do_action('swift_performance_after_clean_cache');
    if (function_exists('rocket_clean_domain'))  { rocket_clean_domain(); bg11_ok('WP Rocket'); }
    if (class_exists('LiteSpeed_Cache_API'))     { LiteSpeed_Cache_API::purge_all(); bg11_ok('LiteSpeed'); }
    bg11_ok('All caches cleared');
    echo "\n";
}
