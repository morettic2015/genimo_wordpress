<?php

/*
 * @Plugin Name: Plugin para cadastrar Imóvel do cliente no Wordpress https://experienciasdigitais.com.br
 * @Description: Plugin Widget para mostrar formulário de cadastro de imóveis do cliente no site da Imobiliaria Wordpress
 * @Author: Luis Augusto Machado Moretto - projetos@Morettic.com.br
 */
/* Start Adding Functions Below this Line */
global $wp;
//$current_url = ;

define('ROOT_PLUGIN', dirname(__DIR__) . '/');
//Widget Class
include ROOT_PLUGIN . 'widget_genimo/widget_lead_listing.php';
include ROOT_PLUGIN . 'widget_genimo/lead_user.php';
include ROOT_PLUGIN . 'widget_genimo/metabox.php';
include ROOT_PLUGIN . 'widget_genimo/widget_lead_contact.php';

//define('GENIMO_URL', $current_url);
// Register and load the widget
function genimo_load_widget() {
    register_widget('genimo_widget');
}

add_action('widgets_init', 'genimo_load_widget');
add_action('widgets_init', create_function('', 'return register_widget("WidgetContacts");'));

// Creating the widget 

/**
 *  @Utilities
 *  */
// Class wpb_widget ends here
function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}

/**
 *  @
 *  */
function create_lead_listing() {

    register_post_type('_lead_listing',
            // CPT Options
            array(
        'labels' => array(
            'name' => __('Imoveis dos Clientes'),
            'singular_name' => __('Imóveis dos Clientes')
        ),
        'public' => false,
        'has_archive' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'has_archive' => false,
        'rewrite' => array('slug' => 'leads_listing'),
        'supports' => array('thumbnail')
            // 'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'meta')
            )
    );
}

// Hooking up our function to theme setup
add_action('init', 'create_lead_listing');
/**
 * Custom Column for Lead Listing
 */
add_filter('manage_posts_columns', 'my_columns');

function my_columns($columns) {

    $screen = get_current_screen();

    // Return early if we're not on the book post type.
    if ('_lead_listing' != $screen->post_type)
        return $columns;

    var_dump($columns);
    $columns['nrbedroom'] = 'Quartos';
    $columns['nrbathrooom'] = 'Banheiros';
    $columns['deaddress'] = 'Endereço';
    $columns['vlprice'] = 'Valor sugerido';
    $columns['vltotalarea'] = 'Área total';
    return $columns;
}

/**
 * @Add custom columns to Wordpress Admin
 *  */
add_action('manage_posts_custom_column', 'my_show_columns');

function my_show_columns($name) {
    global $post;
    switch ($name) {
        case 'vlprice':
            $views = get_post_meta($post->ID, '_price', true);
            echo $views;
            break;
        case 'deaddress':
            $views = get_post_meta($post->ID, '_geolocation_formatted_address', true);
            echo $views;
            break;
        case 'nrbathrooom':
            $views = get_post_meta($post->ID, '_details_2', true);
            echo $views;
            break;
        case 'nrbedroom':
            $views = get_post_meta($post->ID, '_details_1', true);
            echo $views;
            break;
        case 'vltotalarea':
            $views = get_post_meta($post->ID, '_details_3', true);
            echo $views;
            break;
    }
}

/**
 * @Add Help context Menu
 */
function lead_listing_help_tab() {

    $screen = get_current_screen();

    // Return early if we're not on the book post type.
    if ('_lead_listing' != $screen->post_type)
        return;

    // Setup help tab args.
    $args = array(
        'id' => '1234123', //unique id for the tab
        'title' => 'Leadmobi Help', //unique visible title for the tab
        'content' => '<h3>Leadmobi</h3><p>Visite nosso site <a href="https://leadmobi.com.br" target=_blank>Leadmobi</a> para obter suporte</p><p>Desenvolvido por: <a href=https://morettic.com.br target=_blank>Morettic</a>', //actual help text
    );

    // Add the help tab.
    $screen->add_help_tab($args);
}

add_action('admin_head', 'lead_listing_help_tab');

/**
 * @Disable add new on Wordpress
 */
function disable_create_lead_listing() {
    global $wp_post_types;
    $wp_post_types['_lead_listing']->cap->create_posts = 'do_not_allow';
}
add_action('init','disable_create_lead_listing');


/**
 * Register a custom menu page.
 */
function wp_leads_imobiliarios(){
    add_menu_page( 
        __( 'Contatos imobiliários', 'textdomain' ),
        'Contatos imobiliários',
        'manage_options',
        'leads_imobiliarios',
        'leads_imobiliarios',
        6
    ); 
}
add_action( 'admin_menu', 'wp_leads_imobiliarios' );
 
/**
 * Display a custom menu page
 */
function leads_imobiliarios(){
    include dirname(__FILE__) . '/grid.php';
}

