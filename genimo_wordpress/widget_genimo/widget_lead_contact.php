<?php

class WidgetContacts extends WP_Widget {

// constructor
    function WidgetContacts() {
// Give widget name here
        parent::WP_Widget(false, $name = __('WidgetContacts', 'wp_widget_contacts'));
    }

    function form($instance) {

// Check values
        if ($instance) {
            $title = esc_attr($instance['title']);
            $textarea = $instance['textarea'];
        } else {
            $title = '';
            $textarea = '';
        }
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wp_widget_plugin'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('textarea'); ?>"><?php _e('Description:', 'wp_widget_plugin'); ?></label>
            <textarea class="widefat" id="<?php echo $this->get_field_id('textarea'); ?>" name="<?php echo $this->get_field_name('textarea'); ?>" rows="7" cols="20" ><?php echo $textarea; ?></textarea>
        </p>
        <?php
    }

    function update($new_instance, $old_instance) {
        echo "FUch hostile";
        var_dump($_POST);
        // die;
        $instance = $old_instance;
// Fields
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['textarea'] = strip_tags($new_instance['textarea']);
        return $instance;
    }

    public static function validateRecaptcha($key) {
        //return true;
        if (isset($_POST['g-recaptcha-response'])) {
            //echo "--------------".$key."---------------------------";
            //var_dump($_POST);
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
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            //execute post
            $result = curl_exec($ch);
            //var_dump($result);
            //close connection
            curl_close($ch);
            $responseData = json_decode($result);

            //echo "----$result----";
            // var_dump($responseData);
            //if ($responseData->success) {
            return $responseData->success;
            //} else {
            //    return false;
            // }
        } else {
            return false;
        }
    }

    function saveLead($request) {

        $jsonLocation = wp_remote_get("http://ip-api.com/json/" . $_SERVER['REMOTE_ADDR']);
        //  echo "<pre>";
        // echo "http://ip-api.com/json/" . $_SERVER['REMOTE_ADDR'];
        //var_dump($jsonLocation);

        $jsonObjectLO = json_decode($jsonLocation['body']);

        //  var_dump($jsonObjectLO);
        //  die;
        // Create post object
        $my_post = array(
            'post_title' => "Contato Imóvel | " . get_the_title(get_the_ID()),
            'post_content' => $_POST['mensagem'],
            'post_status' => 'publish',
            'post_type' => "form_contact",
            'post_author' => 1,
        );

// Insert the post into the database
        $contactID = wp_insert_post($my_post);
        add_post_meta($contactID, "nome", $_POST['nome'], true);
        add_post_meta($contactID, "email", $_POST['dsemail'], true);
        add_post_meta($contactID, "whats", $_POST['nuphone'], true);
        add_post_meta($contactID, "imovelID", get_the_ID(), true);
        add_post_meta($contactID, "link", get_permalink(get_the_ID()), true);



        add_post_meta($contactID, "city", $jsonObjectLO->city, true);
        add_post_meta($contactID, "country", $jsonObjectLO->country, true);
        add_post_meta($contactID, "region", $jsonObjectLO->region, true);
        add_post_meta($contactID, "regionName", $jsonObjectLO->regionName, true);
        add_post_meta($contactID, "lat", $jsonObjectLO->lat, true);
        add_post_meta($contactID, "lon", $jsonObjectLO->lon, true);
        echo "<h1>Recebemos sua mensagem. Em breve você vai receber uma mensagem nossa! Obrigado.</h1>";
        unset($_POST);
    }

    function widget($args, $instance) {
        echo $args['before_widget'];
        $captcha_key = "6LcfDzMUAAAAAACSNgoo7MemLMPseQtRGNX1B9Ws";
        if (WidgetContacts::validateRecaptcha($captcha_key) === true) {
            //var_dump($_POST);
            $this->saveLead($_POST);
        } else {
            ?>

        <a href="javascript:loadModal()" class="elementor-button-link elementor-button elementor-size-lg" style="color: white;width: 100%;background: #910000 !important;" id="myBtn">Quero fazer negócio</a>

            <div id="myModal" class="modal">

                <!-- Modal content -->
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <span class="dashicons dashicons-format-chat"></span><strong>Fale conosco</strong>
                    <p>
                        Curtiu este imóvel? Faça contato usando o formuário abaixo.
                    <form action="<?php echo htmlspecialchars(get_permalink(get_the_ID())); ?>" id="rendered-form" name="rendered-form" method="post" enctype="multipart/form-data">
                        <div class="rendered-form">
                            <div class="">
                                <h1 id="control-4489373">Dados Pessoais</h1>
                            </div>
                            <div class="fb-text form-group field-text-1507066791966">
                                <label for="nome" class="fb-text-label">Nome<span class="fb-required">*</span></label>
                                <input type="text" placeholder="Nome para contato" class="form-control" name="nome" id="dsemail" required="required" aria-required="true">
                            </div>
                            <div class="fb-text form-group field-dsemail">
                                <label for="dsemail" class="fb-text-label">Email<span class="fb-required">*</span></label>
                                <input type="email" placeholder="Email para contato" class="form-control" name="dsemail" id="dsemail" required="required" aria-required="true">
                            </div>
                            <div class="fb-text form-group field-text-1507066791966">
                                <label for="nuPhone" class="fb-text-label">Whatsapp<span class="fb-required">*</span></label>
                                <input type="tel" class="form-control" name="nuphone" id="nuphone" required="required" aria-required="true">
                            </div>
                            <div class="fb-text form-group field-text-1507066791966">
                                <label for="nuPhone" class="fb-text-label">Mensagem<span class="fb-required">*</span></label>
                                <textarea name="mensagem" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="fb-button form-group field-btsubmitimovel"><br>
                                <div class="g-recaptcha" data-sitekey="6LcfDzMUAAAAAHTvS38vkNc6NvjVtE-tOU7-1M_9"></div>
                                <button type="submit" class="btn btn-success" name="btsubmitimovel" style="success" id="btsubmitimovel">Enviar mensagem</button>
                            </div>
                        </div>
                    </form>
                    </p>
                </div>

            </div>
            <style>
                .btn {
                    -webkit-border-radius: 0;
                    -moz-border-radius: 0;
                    border-radius: 0px;
                    border-radius: 3px;
                    color: #ffffff !important;
                    font-size: 14px;
                    background: #E64A19;
                    padding: 15px 15px 15px 15px;
                    text-decoration: none;
                    margin: 5px;
                }

                .btn:hover {
                    background: #3cb0fd;
                    background-image: -webkit-linear-gradient(top, #3cb0fd, #3498db);
                    background-image: -moz-linear-gradient(top, #3cb0fd, #3498db);
                    background-image: -ms-linear-gradient(top, #3cb0fd, #3498db);
                    background-image: -o-linear-gradient(top, #3cb0fd, #3498db);
                    background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
                    text-decoration: none;
                }
                .modal {
                    display: none; /* Hidden by default */
                    position: fixed; /* Stay in place */
                    z-index: 1; /* Sit on top */
                    left: 0;
                    top: 0;
                    width: 100%; /* Full width */
                    height: 100%; /* Full height */
                    overflow: auto; /* Enable scroll if needed */
                    background-color: rgb(0,0,0); /* Fallback color */
                    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
                }

                /* Modal Content/Box */
                .modal-content {
                    background-color: #fefefe;
                    margin: 15% auto; /* 15% from the top and centered */
                    padding: 10px;
                    border: 1px solid #888;
                    width: 70%; /* Could be more or less, depending on screen size */
                }

                /* The Close Button */
                .close {
                    color: #aaa;
                    float: right;
                    font-size: 28px;
                    font-weight: bold;
                }

                .close:hover,
                .close:focus {
                    color: black;
                    text-decoration: none;
                    cursor: pointer;
                }
            </style>
            <script src='https://www.google.com/recaptcha/api.js'></script>
            <script>
                var modal;
                jQuery(function ($) {
                    // Get the modal
                    modal = document.getElementById('myModal');

                    // Get the button that opens the modal

                    // Get the <span> element that closes the modal
                    var span = document.getElementsByClassName("close")[0];

                    // When the user clicks on the button, open the modal 


                    // When the user clicks on <span> (x), close the modal
                    span.onclick = function () {
                        modal.style.display = "none";
                    }

                    // When the user clicks anywhere outside of the modal, close it
                    window.onclick = function (event) {
                        if (event.target == modal) {
                            modal.style.display = "none";
                        }
                    }
                });
                function loadModal() {
                    modal.style.display = "block";
                }
            </script>
            <?php
        }
        echo $args['after_widget'];
    }

}
?>