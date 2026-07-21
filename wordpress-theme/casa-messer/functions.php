<?php
if (!defined('ABSPATH')) exit;

function casa_messer_setup() {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  register_nav_menus(array('primary' => __('Menú principal', 'casa-messer')));
}
add_action('after_setup_theme', 'casa_messer_setup');

function casa_messer_assets() {
  wp_enqueue_style('casa-messer-fonts', 'https://fonts.googleapis.com/css2?family=DM+Mono&family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Manrope:wght@400;500;600;700&display=swap', array(), null);
  wp_enqueue_style('casa-messer', get_stylesheet_uri(), array('casa-messer-fonts'), '1.0.0');
  $heading = get_theme_mod('casa_heading_font', 'Fraunces');
  $body = get_theme_mod('casa_body_font', 'Manrope');
  $scale = get_theme_mod('casa_text_scale', '1');
  wp_add_inline_style('casa-messer', ":root{--heading:'{$heading}',serif;--body:'{$body}',sans-serif;--scale:{$scale};}");
}
add_action('wp_enqueue_scripts', 'casa_messer_assets');

function casa_messer_register_sections() {
  register_post_type('messer_section', array('labels' => array('name' => 'Secciones', 'singular_name' => 'Sección', 'add_new_item' => 'Agregar sección'), 'public' => true, 'has_archive' => false, 'menu_icon' => 'dashicons-cover-image', 'supports' => array('title','editor','thumbnail','page-attributes'), 'rewrite' => array('slug' => 'experiencias')));
}
add_action('init', 'casa_messer_register_sections');

function casa_messer_seed_sections() {
  if (get_option('casa_messer_seeded')) return;
  $sections = array(
    array('Catas', 'Una mesa para descubrir el mezcal con calma: sus regiones, procesos, aromas y las manos que lo hacen posible.'),
    array('Experiencias', 'Desayunar, comer, brindar o quedarte a la sobremesa: cada visita encuentra su propio ritmo.'),
    array('Menú', 'Cocina de temporada para compartir, acompañada de mezcal, coctelería y buena conversación.'),
    array('Eventos', 'Cumpleaños, aniversarios, reuniones y celebraciones pensadas para quedarse en la memoria.'),
    array('Bodas', 'Pedidas de mano, cenas de compromiso y celebraciones íntimas con todos los detalles de la casa.'),
    array('Eventos privados', 'Un espacio para equipos, familias y comunidades que buscan reunirse con privacidad y buena mesa.'),
    array('La comida', 'Cocina con memoria mexicana, ingredientes bien elegidos y platos hechos para compartir.')
  );
  foreach ($sections as $order => $section) {
    wp_insert_post(array('post_type' => 'messer_section', 'post_status' => 'publish', 'post_title' => $section[0], 'post_content' => '<p>' . esc_html($section[1]) . '</p>', 'menu_order' => $order));
  }
  update_option('casa_messer_seeded', 1);
}
add_action('init', 'casa_messer_seed_sections', 20);

function casa_messer_meta_box() { add_meta_box('casa_card', 'Tarjeta y botón', 'casa_messer_meta_box_content', 'messer_section', 'normal', 'high'); }
add_action('add_meta_boxes', 'casa_messer_meta_box');
function casa_messer_meta_box_content($post) { wp_nonce_field('casa_messer_save_meta', 'casa_messer_nonce'); $label = get_post_meta($post->ID, '_casa_button_label', true); $url = get_post_meta($post->ID, '_casa_button_url', true); ?>
  <p><label><strong>Texto en tarjeta y botón</strong><br><input style="width:100%" name="casa_button_label" value="<?php echo esc_attr($label ?: $post->post_title); ?>"></label></p>
  <p><label><strong>URL del botón</strong> <em>(opcional; si se deja vacío abre esta misma página)</em><br><input style="width:100%" type="url" name="casa_button_url" value="<?php echo esc_url($url); ?>"></label></p>
<?php }
function casa_messer_save_meta($post_id) { if (!isset($_POST['casa_messer_nonce']) || !wp_verify_nonce($_POST['casa_messer_nonce'], 'casa_messer_save_meta') || defined('DOING_AUTOSAVE')) return; if (!current_user_can('edit_post', $post_id)) return; update_post_meta($post_id, '_casa_button_label', sanitize_text_field($_POST['casa_button_label'] ?? '')); update_post_meta($post_id, '_casa_button_url', esc_url_raw($_POST['casa_button_url'] ?? '')); }
add_action('save_post_messer_section', 'casa_messer_save_meta');

function casa_messer_customize($wp_customize) {
  $wp_customize->add_section('casa_home', array('title' => 'Casa Messer — Portada'));
  $fields = array('hero_label' => array('Ciudad Juárez, Chihuahua','Texto superior'), 'hero_title' => array('El ritual de compartir.','Título principal'), 'hero_text' => array('Una casa de degustación que celebra la esencia del mezcal y la gastronomía con alma mexicana.','Descripción'), 'hero_button' => array('Descubre la experiencia','Texto del botón'), 'hero_url' => array('#secciones','URL del botón'));
  foreach ($fields as $id => $data) { $wp_customize->add_setting("casa_{$id}", array('default' => $data[0], 'sanitize_callback' => $id === 'hero_url' ? 'esc_url_raw' : 'sanitize_text_field')); $wp_customize->add_control("casa_{$id}", array('section' => 'casa_home', 'label' => $data[1], 'type' => $id === 'hero_text' ? 'textarea' : 'text')); }
  $wp_customize->add_setting('casa_hero_image'); $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'casa_hero_image', array('label' => 'Fotografía de portada', 'section' => 'casa_home', 'mime_type' => 'image')));
  $wp_customize->add_section('casa_design', array('title' => 'Casa Messer — Diseño'));
  foreach (array('heading_font' => array('Tipografía de títulos', array('Fraunces'=>'Fraunces','Georgia'=>'Georgia','Playfair Display'=>'Playfair Display')), 'body_font' => array('Tipografía de textos', array('Manrope'=>'Manrope','Arial'=>'Arial','Georgia'=>'Georgia')), 'text_scale' => array('Tamaño general de texto', array('.9'=>'Pequeño','1'=>'Normal','1.1'=>'Grande','1.2'=>'Muy grande'))) as $id => $data) { $wp_customize->add_setting("casa_{$id}", array('default' => $id === 'text_scale' ? '1' : ($id === 'heading_font' ? 'Fraunces' : 'Manrope'), 'sanitize_callback' => 'sanitize_text_field')); $wp_customize->add_control("casa_{$id}", array('section' => 'casa_design', 'label' => $data[0], 'type' => 'select', 'choices' => $data[1])); }
}
add_action('customize_register', 'casa_messer_customize');

function casa_messer_fallback_image($index = 0) { $images = array('assets/instagram-mezcal.jpg','assets/instagram-casa.jpg','assets/instagram-plato.jpg','assets/instagram-celebracion.jpg','assets/instagram-brunch.jpg'); return get_template_directory_uri() . '/' . $images[$index % count($images)]; }
