<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function _genimo_metabox() {

    // Can only be used on a single post type (ie. page or post or a custom post type).
    // Must be repeated for each post type you want the metabox to appear on.
    add_meta_box(
            '_namespace_metabox', // Metabox ID
            '<h1>Negócio Imobiliário</h1>', // Title to display
            '_genimo_rende_metabox', // Function to call that contains the metabox content
            '_lead_listing', // Post type to display metabox on
            'high', // Where to put it (normal = main colum, side = sidebar, etc.)
            'default' // Priority relative to other metaboxes
    );

    // To add the metabox to a page, too, you'd repeat it, changing the location
    // add_meta_box( '_namespace_metabox', 'Some Metabox', '_genimo_rende_metabox', 'page', 'normal', 'default' // Priority relative to other metaboxes );
}

add_action('add_meta_boxes', '_genimo_metabox');

function _genimo_rende_metabox() {
    // Variables
    global $post; // Get the current post data
    $preco = get_post_meta($post->ID, '_price', true); // Get the saved values
    $lat = get_post_meta($post->ID, '_geolocation_lat', true); // Get the saved values
    $lon = get_post_meta($post->ID, '_geolocation_long', true); // Get the saved values
    $nrbath = get_post_meta($post->ID, '_details_2', true); // Get the saved values
    $vlarea = get_post_meta($post->ID, '_details_3', true); // Get the saved values
    $deaddres = get_post_meta($post->ID, '_geolocation_formatted_address', true); // Get the saved values
    $cdiam = get_post_meta($post->ID, '_cdiam', true); // Get the saved values
    // var_dump($post);die;
    $user_id = $post->post_author;
    $user = new WP_User($user_id);
    $r1 = get_user_meta($user_id, 'phone');
    //var_dump($r1);die;
    //var_dump($user);die;
//  $post->get
    ?>
    <h1>
        Dados do Contato
    </h1>
    <fieldset>
        <div>
            <h4>
                Tipo do Lead:<b> <?php echo $cdiam; ?></b>
            </h4>
        </div>
        <hr>   
        <div>
            <h4>
                Contato:<b> <?php echo $user->user_email; ?></b>
            </h4>
        </div>
        <hr>        
        <div>
            <h4>
                Cadastrado em:<b> <?php echo $user->user_registered; ?></b>
            </h4>
        </div>
        <hr>        
        <div>
            <h4>
                Celular:<b> <?php echo $r1[0]; ?></b>
            </h4>
        </div>
    </fieldset>
    <h1>
        Dados do Imóvel
    </h1>
    <fieldset>
        <div>
            <h4>
                Preço:<b> <?php echo esc_attr($preco); ?></b>
            </h4>
        </div>
        <hr>
        <div>
            <h4>
                Banheiros:<b> <?php echo esc_attr($nrbath); ?></b>
            </h4>
        </div>
        <hr>
        <div>
            <h4>
                Área total:<b> <?php echo esc_attr($vlarea); ?></b>
            </h4>
        </div>
        <hr>
        <div>
            <h4>
                Endereço:<b> <?php echo esc_attr($deaddres); ?></b>
            </h4>
        </div>
        <hr>
        <div>
            <h4>
                Coordenadas:<b> (<?php echo esc_attr($lat); ?>,<?php echo esc_attr($lon); ?>)</b>
            </h4>
        </div>
    </fieldset>

    <?php
    // Security field
    // This validates that submission came from the
    // actual dashboard and not the front end or
    // a remote server.
    wp_nonce_field('_namespace_form_metabox_nonce', '_namespace_form_metabox_process');
}
?>