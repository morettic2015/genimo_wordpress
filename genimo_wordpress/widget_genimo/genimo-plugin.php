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

//define('GENIMO_URL', $current_url);
// Register and load the widget
function genimo_load_widget() {
    register_widget('genimo_widget');
}

add_action('widgets_init', 'genimo_load_widget');

// Creating the widget 
class genimo_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
// Base ID of your widget
                'genimo_widget',
// Widget name will appear in UI
                __('Formulario de cadastro de Imoveis (genimo)', 'genimo_widget_domain'),
// Widget description
                array('description' => __('Widget para mostrar e integrar o form de cadastro de imóveis para o cliente', 'genimo_widget_domain'),)
        );
    }

    /**
     * @Create Custom Post Type For Lead
     */
    public function createPostTitle() {
        $std = new stdClass();
        $std->type = "";
        if ($_POST['idcategory'] === "1") {
            $std->type = "casa";
        } else if ($_POST['idcategory'] === "2") {
            $std->type = "apartamento";
        } else if ($_POST['idcategory'] === "3") {
            $std->type = "terreno";
        } else if ($_POST['idcategory'] === "4") {
            $std->type = "kitnet";
        } else {
            $std->type = $_POST['idcategory-other-value'];
        }

        if ($_POST['cdmode'] === "1") {
            $std->cdMode = "rental";
            $std->tpPricePeriod = "rental_period_1";
        } else if ($_POST['cdmode'] === "2") {
            $std->cdMode = "sale";
            $std->tpPricePeriod = "sale";
        } else if ($_POST['idcategory'] === "3") {
            $std->cdMode = "sale";
            $std->tpPricePeriod = "sale";
        }

        return $std;
    }

    public function createPostContent() {
        $content = "<p>";
        $content .= $_POST['deimovel'];
        $content .= "</p>";
        $content .= "<p>";
        $content .= "<b>Quartos:</b>";
        $content .= $_POST['nrquartos'];
        $content .= "</p>";
        $content .= "<p>";
        $content .= "<b>Banheiros:</b>";
        $content .= $_POST['nrbath'];
        $content .= "</p>";
        $content .= "<p>";
        $content .= "<b>Phone:</b>";
        $content .= $_POST['nuphone'];
        $content .= "</p>";
        $content .= "<p>";
        $content .= "<b>Email:</b>";
        $content .= $_POST['dsemail'];
        $content .= "</p>";
        $content .= "<b>Endereço:</b>";
        $content .= $_POST['deaddress'];
        $content .= "</p>";
        return $content;
    }

    public function validateRecaptcha($key) {
        if (isset($_POST['g-recaptcha-response'])) {
            var_dump($_POST);
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $fields = array(
                'response' => urlencode($_POST['g-recaptcha-response']),
                'secret' => urlencode($key)
            );
            
            $fields_string = "";

            //url-ify the data for the POST
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');

            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            //execute post
            $result = curl_exec($ch);

            //close connection
            curl_close($ch);
            $responseData = json_decode($result);
            var_dump($responseData);
            if ($responseData->success) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function createPost() {
        $info = $this->createPostContent();
        $titleObj = $this->createPostTitle();
        $my_post = array(
            'post_title' => $_POST['nmperson'],
            'post_content' => $info,
            'post_status' => 'publish',
            'post_type' => '_lead_listing',
            'post_author' => 1,
            'post_category' => array(8, 39)
        );
        $postID = wp_insert_post($my_post);

        //Insert post meta
        if ($postID) {
            add_post_meta($postID, '_nmperson', $_POST['nmperson']);
            add_post_meta($postID, '_dsemail', $_POST['dsemail']);
            add_post_meta($postID, '_nuphone', $_POST['nuphone']);
            add_post_meta($postID, '_cdmode', $_POST['cdmode']);
            add_post_meta($postID, '_listing_title', $_POST['vllon'] . '_' . $titleObj->cdMode . '_' . $_POST['nmperson']);
            add_post_meta($postID, '_idcategory', $_POST['idcategory']);
            add_post_meta($postID, '_details_1', $_POST['nrquartos']);
            add_post_meta($postID, '_details_2', $_POST['nrbath']);
            add_post_meta($postID, '_details_3', $_POST['vltotalarea']);
            add_post_meta($postID, '_price', $_POST['vlprice']);
            add_post_meta($postID, '_price_period', $titleObj->tpPricePeriod);
            add_post_meta($postID, '_map_address', $_POST['deaddress']);
            add_post_meta($postID, '_geolocated', 1);
            add_post_meta($postID, '_geolocation_long', $_POST['vllon']);
            add_post_meta($postID, '_geolocation_lat', $_POST['vllat']);
            add_post_meta($postID, '_geolocation_state_short', $_POST['nmstate']);
            add_post_meta($postID, '_geolocation_formatted_address', $_POST['deaddress']);
        }

        return $postID;
    }

    /**
     * @Reorder $FILES
     */
    public function sortFilesVet($vet) {
        return reArrayFiles($vet);
    }

    public function copyImages(&$images, $postID) {
        //var_dump($images);

        $uploadDir = wp_upload_dir();
        $pathUpload = $uploadDir['path'] . "/";
        //var_dump($uploadDir);

        foreach ($images as $attach) {
            //Get file Path
            $filePathFull = $pathUpload . $attach['name'];
            //echo $filePathFull;
            //Upload file to wordpress current directory
            move_uploaded_file($attach['tmp_name'], $filePathFull);
            //Check for file type
            $filetype = wp_check_filetype(basename($filePathFull), null);

            $attachment = array(
                'guid' => $uploadDir['url'] . '/' . basename($filePathFull),
                'post_mime_type' => $filetype['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filePathFull)),
                'post_content' => '',
                'post_status' => 'inherit'
            );

// Insert the attachment.
            $attach_id = wp_insert_attachment($attachment, $filePathFull, $postID);

// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once( ABSPATH . 'wp-admin/includes/image.php' );

// Generate the metadata for the attachment, and update the database record.
            $attach_data = wp_generate_attachment_metadata($attach_id, $filePathFull);
            wp_update_attachment_metadata($attach_id, $attach_data);

            set_post_thumbnail($postID, $attach_id);
        }
        // if (!copy($file, $newfile)) {
        //     echo "falha ao copiar $file...\n";
        // }
    }

// Creating widget front-end

    public function widget($args, $instance) {
        @$title = apply_filters('widget_title', $instance['title']);
        @$cdImobiliaria = apply_filters('widget_cd', $instance['cdImobiliaria']);
        @$gmaps_key = apply_filters('widget_maps_key', $instance['gmaps_key']);
        @$captcha_key = apply_filters('widget_captcha_key', $instance['captcha_key']);
// before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if (!empty($title))
            echo @$args['before_title'] . $title . @$args['after_title'];
        //echo $captcha_key; //die;
        //Form data was submitted
        
        //var_dump($instance);
        
        if ($this->validateRecaptcha($captcha_key)) {
            //var_dump($_POST);
            //var_dump();
            //var_dump($images);
            //Create Custom Post Type
            $postID = $this->createPost();

            $images = $this->sortFilesVet($_FILES['imgdestaque']);
            $this->copyImages($images, $postID);
            echo "<h1>Imóvel salvo com sucesso! ($postID)</h1>";
            echo "<p>Agradecemos a sua confiança!</p>";
            // echo ;
            //New post has been inserted
        } else {

            $content = "";
            $content .= '<form action="' . htmlspecialchars(get_permalink()) . '" id="rendered-form" name="rendered-form" method="post" enctype="multipart/form-data">'
                    . '<div class="rendered-form">'
                    . '<div class="">'
                    . '<h1 id="control-4489373">Dados Pessoais</h1></div>'
                    . '<div class="fb-text form-group field-nmperson">'
                    . '<label for="nmperson" class="fb-text-label">Nome do Proprietário<span class="fb-required">*</span></label>'
                    . '<input type="text" placeholder="Nome do Proprietário" class="form-control" name="nmperson" id="nmperson" required="required" aria-required="true">'
                    . '</div><div class="fb-text form-group field-dsemail">'
                    . '<label for="dsemail" class="fb-text-label">Email<span class="fb-required">*</span></label>'
                    . '<input type="email" placeholder="Email do proprietário" class="form-control" name="dsemail" id="dsemail" required="required" aria-required="true">'
                    . '</div>'
                    . '<div class="fb-text form-group field-text-1507066791966">'
                    . '<label for="nuPhone" class="fb-text-label">Whatsapp<span class="fb-required">*</span></label>'
                    . '<input type="tel" class="form-control" name="nuphone" id="nuphone" required="required" aria-required="true"></div>'
                    . '<div class="">'
                    . '<h1 id="control-2164576">Dados do Imóvel</h1></div>'
                    . '<div class="fb-radio-group form-group field-cdmode">'
                    . '<label for="cdmode" class="fb-radio-group-label">Tipo do negócio<span class="fb-required">*</span></label>'
                    . '<div class="radio-group"><div class="radio">'
                    . '<label>'
                    . '<input name="cdmode" id="cdmode" required="required" aria-required="true" value="1" type="radio">Aluguel</label></div><div class="radio">'
                    . '<label>'
                    . '<input name="cdmode" id="cdmode" required="required" aria-required="true" value="2" type="radio">Venda</label></div><div class="radio">'
                    . '<label>'
                    . '<input name="cdmode" id="cdmode" required="required" aria-required="true" value="3" type="radio">Aluguel e Venda</label></div></div></div>'
                    . '<div class="fb-radio-group form-group field-idcategory">'
                    . '<label for="idcategory" class="fb-radio-group-label">Tipo do Imóvel<span class="fb-required">*</span></label>'
                    . '<div class="radio-group"><div class="radio">'
                    . '<label for="idcategory"><input name="idcategory" id="idcategory" required="required" aria-required="true" value="1" type="radio">Casa</label></div>'
                    . '<div class="radio">'
                    . '<label for="idcategory"><input name="idcategory" id="idcategory" required="required" aria-required="true" value="6" type="radio">Galpão</label></div>'
                    . '<div class="radio">'
                    . '<label for="idcategory"><input name="idcategory" id="idcategory" required="required" aria-required="true" value="5" type="radio">Sala Comercial</label></div>'
                    . '<div class="radio">'
                    . '<label for="idcategory"><input name="idcategory" id="idcategory" required="required" aria-required="true" value="2" type="radio">Apartamento</label></div>'
                    . '<div class="radio">'
                    . '<label for="idcategory"><input name="idcategory" id="idcategory" required="required" aria-required="true" value="3" type="radio">Terreno</label></div><div class="radio">'
                    . '<label for="idcategory"><input name="idcategory" id="idcategory" required="required" aria-required="true" value="4" type="radio">Quitinete</label></div>'
                    . '</div></div>'
                    . '<div class="fb-number form-group field-nrquartos"><label for="nrquartos" class="fb-number-label">Numero de quartos<span class="fb-required">*</span></label>'
                    . '<input type="number" class="form-control" name="nrquartos" id="nrquartos" required="required" aria-required="true"></div>'
                    . '<div class="fb-number form-group field-nrbath"><label for="nrbath" class="fb-number-label">Número de banheiros<span class="fb-required">*</span></label>'
                    . '<input type="number" class="form-control" name="nrbath" min="0" id="nrbath" required="required" aria-required="true"></div>'
                    . '<div class="fb-text form-group field-deaddress">'
                    . '<label for="deaddress" class="fb-text-label">Endereço completo<span class="fb-required">*</span>'
                    . '<span class="tooltip-element" tooltip="Rua General Bittencourt 397 centro, Florianópolis, SC">?</span></label>'
                    . '<input onFocus="geolocate()" type="text" placeholder="Rua General Bittencourt 397 centro, Florianópolis, SC" class="form-control" name="deaddress" id="deaddress" title="Rua General Bittencourt 397 centro, Florianópolis, SC" required="required" aria-required="true">'
                    . '</div>'
                    . '<input type="hidden" name="vllat" id="vllat"/>'
                    . '<input type="hidden" name="vllon" id="vllon"/>'
                    . '<input type="hidden" name="nmcity" id="nmcity"/>'
                    . '<input type="hidden" name="nmbairro" id="nmbairro"/>'
                    . '<input type="hidden" name="nmaddress" id="nmaddress"/>'
                    . '<input type="hidden" name="nmstate" id="nmstate"/>'
                    . '<input type="hidden" name="nmcountry" id="nmcountry"/>'
                    . '<input type="hidden" name="nrzip" id="nrzip"/>'
                    /* . '<div id="locationField">
                      <input id="autocomplete" placeholder="Enter your address"
                      onFocus="geolocate()" type="text"></input>
                      </div>

                      Street address
                      <input class="form-control" id="street_number"
                      type="text"></input>
                      <input class="form-control" id="route"
                      type="text"></input>City<input class="form-control" id="locality"
                      type="text"></input>State<input class="form-control"
                      id="administrative_area_level_1" type="text"></input>
                      Zip code<input class="form-control" id="postal_code"
                      type="text"></input>
                      Country<input cclass="form-control" id="country" type="text"></input>' */
                    . '<div class="fb-text form-group field-vlprice">'
                    . '<label for="vlprice" class="fb-text-label">Preço negociação<span class="fb-required">*</span></label>'
                    . '<input type="text" placeholder="Preço negociação R$" class="form-control" name="vlprice" id="vlprice" required="required" aria-required="true">'
                    . '</div><div class="fb-text form-group field-vltotalarea">'
                    . '<label for="vltotalarea" class="fb-text-label">Área total<span class="fb-required">*</span></label>'
                    . '<input type="number" placeholder="Área total em metros" class="form-control" name="vltotalarea" id="vltotalarea" required="required" aria-required="true">'
                    . '</div>'
                    . '<div class="fb-textarea form-group field-deimovel"><label for="deimovel" class="fb-textarea-label">Descrição do imóvel</label><textarea type="textarea" class="form-control" name="deimovel" id="deimovel"></textarea></div><div class="">'
                    . '<h1 id="control-7495532">Fotos do Imóvel</h1></div><div class="fb-file form-group field-imgdestaque">'
                    . '<label for="imgdestaque" class="fb-file-label">Imagens do imóvel</label>'
                    . '<input type="file" class="form-control" name="imgdestaque[]" multiple="true" id="imgdestaque[]"></div><br>'
                    . '<div class="g-recaptcha" data-sitekey="6LcfDzMUAAAAAHTvS38vkNc6NvjVtE-tOU7-1M_9"></div>'
                    . '<div class="fb-button form-group field-btsubmitimovel"><br>'
                    . '<button type="button" class="btn btn-success" name="btsubmitimovel" style="success" id="btsubmitimovel">Enviar imóvel</button>'
                    . '</div></div></form>';

            // This is where you run the code and display the output
            echo __($content, 'genimo_widget_domain');

            //Create Content HERE
            //Form Validation
            //Form Submission
            echo "<script>";
            include ROOT_PLUGIN . 'widget_genimo/formFunctions.js';
            echo "</script>";
            echo '<script src="https://maps.googleapis.com/maps/api/js?key='.$gmaps_key.'&libraries=places&callback=initAutocomplete" async defer></script>';
            echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
        }
        echo $args['after_widget'];
    }

    // Widget Backend 
    public function form($instance) {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('Descrição', 'genimo_widget_domain');
        }
        if (isset($instance['cdImobiliaria'])) {
            $cdImobiliaria = $instance['cdImobiliaria'];
        } else {
            $cdImobiliaria = __('Codigo Imobiliaria', 'genimo_widget_domain');
        }
        if (isset($instance['gmaps_key'])) {
            $gmaps_key = $instance['gmaps_key'];
        } else {
            $gmaps_key = __('Google Maps API KEY', 'genimo_widget_domain');
        }
        if (isset($instance['captcha_key'])) {
            $captcha_key = $instance['captcha_key'];
        } else {
            $captcha_key = __('Google RECAPTCHA SECRET', 'genimo_widget_domain');
        }
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            <label for="<?php echo $this->get_field_id('cdImobiliaria'); ?>"><?php _e('Codigo Imobiliaria:'); ?></label>
            <input class = "widefat" id = "<?php echo $this->get_field_id('cdImobiliaria'); ?>" name = "<?php echo $this->get_field_name('cdImobiliaria'); ?>" type = "text" value = "<?php echo esc_attr($cdImobiliaria); ?>" />
            <label for="<?php echo $this->get_field_id('gmaps_key'); ?>"><?php _e('Google Maps KEY:'); ?></label>
            <input class = "widefat" id = "<?php echo $this->get_field_id('gmaps_key'); ?>" name = "<?php echo $this->get_field_name('gmaps_key'); ?>" type = "text" value = "<?php echo esc_attr($gmaps_key); ?>" />
            <label for="<?php echo $this->get_field_id('captcha_key'); ?>"><?php _e('Google Recaptcha Key KEY:'); ?></label>
            <input class = "widefat" id = "<?php echo $this->get_field_id('captcha_key'); ?>" name = "<?php echo $this->get_field_name('captcha_key'); ?>" type = "text" value = "<?php echo esc_attr($captcha_key); ?>" />
        </p>
        <?php
    }

    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        $instance['cdImobiliaria'] = (!empty($new_instance['cdImobiliaria']) ) ? strip_tags($new_instance['cdImobiliaria']) : '';
        $instance['gmaps_key'] = (!empty($new_instance['gmaps_key']) ) ? ($new_instance['gmaps_key']) : '';
        $instance['captcha_key'] = (!empty($new_instance['captcha_key']) ) ? ($new_instance['captcha_key']) : '';
        return $instance;
    }

}

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

// Our custom post type function
function create_lead_listing() {

    register_post_type('_lead_listing',
            // CPT Options
            array(
        'labels' => array(
            'name' => __('Imoveis dos Clientes'),
            'singular_name' => __('Imóveis dos Clientes')
        ),
        'public' => true,
        'has_archive' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'leads_listing'),
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'meta')
            )
    );
}

// Hooking up our function to theme setup
add_action('init', 'create_lead_listing');

/**
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
