<?php

class LeadList extends WP_List_Table {

    public function __construct() {
        // Set parent defaults.
        parent::__construct(array(
            'singular' => 'lead', // Singular name of the listed records.
            'plural' => 'leads', // Plural name of the listed records.
            'ajax' => false, // Does this table support ajax?
        ));
    }

    public function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />', // Render a checkbox instead of text.
            'nome' => _x('Contato', 'Foto', 'wp-list-table-example'),
            'email' => _x('Email', 'Nome', 'wp-list-table-example'),
            'msg' => _x('Mensagem', 'Column label', 'wp-list-table-example'),
            'whats' => _x('Whatsapp', 'Column label', 'wp-list-table-example'),
            'interesse' => _x('Interesse', 'Column label', 'wp-list-table-example'),
            'data' => _x('Data do contato', 'Column label', 'wp-list-table-example'),
            'imovel' => _x('Imóvel de interesse', 'Column label', 'wp-list-table-example'),
            'localizacao' => _x('Localizacao', 'Column label', 'wp-list-table-example'),
        );

        return $columns;
    }

    protected function get_sortable_columns() {
        $sortable_columns = array(
            'title' => array('title', false),
                //   'rating' => array('rating', false),
                //   'director' => array('director', false),
        );

        return $sortable_columns;
    }

    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'localizacao':
            case 'imovel':
            case 'data':
            case 'interesse':
            case 'whats':
            case 'msg':
            case 'email':
            case 'nome':
                return $item[$column_name];
            default:
                return print_r($item, true); // Show the whole array for troubleshooting purposes.
        }
    }

    protected function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], // Let's simply repurpose the table's singular label ("movie").
                $item['ID']                // The value of the checkbox should be the record's ID.
        );
    }

    protected function column_title($item) {
        $page = wp_unslash($_REQUEST['page']); // WPCS: Input var ok.
        // Build edit row action.
        //showPop(action,id)
        //trial cannot upload images
        // $actions['sendMessage'] = '<a href="javascript:selectGroups(' . $item['ID'] . ')">Enviar</a>';
        //  $actions['general'] = '<a href="admin.php?page=app_guiafloripa_leads_add&pid=' . $item['ID'] . ' ">' . _x('Editar', 'Editar') . '</a>';
        //  $actions['group'] = '<a href="admin.php?page=app_guiafloripa_leads_add&pid=' . $item['ID'] . '#groups">' . _x('Grupos', 'Grupos') . '</a>';
        //  $actions['notes'] = '<a href="admin.php?page=app_guiafloripa_leads_add&pid=' . $item['ID'] . '#notes">' . _x('Adicionar nota', 'Add Nota') . '</a>';
        /* $actions['dates'] = '<a href="javascript:showPop(\'dates\',' . $item['ID'] . ')">' . _x('Datas') . '</a>';
          $actions['categ'] = '<a href="javascript:showPop(\'categ\',' . $item['ID'] . ')">' . _x('Categorias') . '</a>';
          $actions['location'] = '<a href="javascript:showPop(\'local\',' . $item['ID'] . ')">' . _x('Localização') . '</a>';
          if (get_user_meta(get_current_user_id(), "_plano_type", true)) {
          $actions['pic'] = '<a href="javascript:showPop(\'image\',' . $item['ID'] . ')">' . _x('Imagem') . '</a>';
          }
          $actions['comp'] = '<a href="javascript:showPop(\'comp\',' . $item['ID'] . ')">' . _x('Complemento') . '</a>';
         */
        // Return the title contents.
        return sprintf('%1$s <span style="color:silver;">(id:%2$s)</span>%3$s', $item['title'], $item['ID'], $this->row_actions($actions));
    }

    protected function get_bulk_actions() {
        $actions = array(
            //   'notes' => _x('Adicionar nota', 'List table bulk action', 'wp-list-table-example'),
            'remover' => _x('Remover contato', 'List table bulk action', 'wp-list-table-example'),
            'genimo' => _x('Sincronizar no genimo', 'List table bulk action', 'wp-list-table-example'),
            'mail' => _x('Enviar email', 'List table bulk action', 'wp-list-table-example'),
                // 'delete' => _x('Remover', 'List table bulk action', 'wp-list-table-example'),
                //  'clone' => _x('Duplicar', 'List table bulk action', 'wp-list-table-example'),
        );


        return $actions;
    }

    protected function syncGenimo($nm, $mail, $whats, $subject, $msg, $idCompany = 41) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "HTTPS://genimo.com.br/api/site/addSiteContact");
        curl_setopt($ch, CURLOPT_POST, 1);
        /* curl_setopt($ch, CURLOPT_POSTFIELDS,
         * curl -d "idCompany=41&nmPerson=Moretto&dsEmail=malacma@gmail.com&nuPhone=+5548996004929&dsSubject=Assunto da mensagem&dsApproach=texto da mensagem" -X POST HTTPS://genimo.com.br/api/site/addSiteContact
          "idCompany=41&nmPerson=Moretto&dsEmail=malacma@gmail.com&nuPhone=+5548996004929&dsSubject=Assunto da mensagem&dsApproach=texto da mensagem"); */
// in real life you should use something like:
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                        array(
                            'idCompanyContact' => 41,
                            'nmPersonContact' => $nm,
                            'dsEmailContact' => $mail,
                            'nuPhoneContact' => $whats,
                            'dsSubjectContact' => $subject,
                            'dsApproachContact' => $msg,
// 'idPropertyContact' => '445'
                        )
                )
        );
// receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        // var_dump($server_output);
        curl_close($ch);
        return $server_output;
    }

    /**
     * Handle bulk actions.
     *
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     *
     * @see $this->prepare_items()
     */
    protected function process_bulk_action() {
        global $wpdb; //This is used only if making any database queries
        // Detect when a bulk action is being triggered.
        //echo $this->current_action();
        $ids = "-1";
        foreach ($_POST['lead'] as $d) {
            $ids .= "," . $d;
        }
        if ('remover' === $this->current_action()) {
            /*  include_once PLUGIN_ROOT_DIR . 'views/contatos/ContatosController.php';
              $ec = new ContatosController(); */

            //echo $ids;

            $query = "delete from wp_postmeta where post_id in ($ids)";
            $wpdb->get_results($query);
            $query = "delete from wp_posts where ID in ($ids)";
            $wpdb->get_results($query);
            echo '<div class="notice notice-info is-dismissible"> 
                    <p><strong>Contatos excluidos com sucesso</strong></p>
                 </div>';
            //var_dump();
        } else if ('genimo' === $this->current_action()) {
            $query = "select 
                    ID,post_date,
                    (select meta_value from wp_postmeta where post_id = ID and meta_key = '_field_6') as email,
                    (select meta_value from wp_postmeta where post_id = ID and meta_key = '_field_7') as whats,
                    (select meta_value from wp_postmeta where post_id = ID and meta_key = '_field_105') as interesse,
                    (select meta_value from wp_postmeta where post_id = ID and meta_key = '_field_9') as msg,
                    (select meta_value from wp_postmeta where post_id = ID and meta_key = '_field_5') as nome
                from wp_posts where post_type = 'nf_sub' and post_status = 'publish' and ID IN ($ids)";
            $result = $wpdb->get_results($query);
            foreach ($result as $r1) {
                $ret = $this->syncGenimo($r1->nome, $r1->email, $r1->whats, $r1->interesse, $r1->msg);
                //echo $ret;
            }

            $query = "SELECT * FROM wp_posts where post_type = 'form_contact'  and ID IN ($ids) order by ID desc";

            //echo $query;
            $result = $wpdb->get_results($query);
            // var_dump($result);
            //  die;
            foreach ($result as $c1) {
                $nome = get_post_meta($c1->ID, "nome", TRUE);
                $email = get_post_meta($c1->ID, "email", TRUE);
                $whats = get_post_meta($c1->ID, "whats", TRUE);
                $ret = $this->syncGenimo($nome, $email, $whats, $c1->post_title, $c1->post_content);
                //echo $ret;
            }
            echo '<div class="notice notice-info is-dismissible"> 
                    <p><strong>Contatos sincronizados no Genimo</strong></p>
                 </div>';
        } else if ('send' === $this->current_action()) {
            $headers = "From: Rute Imoveis | Contato <contato@ruteimoveis.com>\n";
            $headers .= "X-Sender: Rute Imoveis | Contato <contato@ruteimoveis.com>\n";
            $headers .= 'X-Mailer: PHP/' . phpversion();
            $headers .= "X-Priority: 3\n"; // Urgent message!
            $headers .= "Return-Path: contato@ruteimoveis.com\n"; // Return path for errors
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\n";

            $query = "select meta_value from wp_postmeta where post_id in (" . $_POST['ids'] . ") and meta_key = '_field_6' union select meta_value from wp_postmeta where post_id in (" . $_POST['ids'] . ") and meta_key = 'email'";
            //echo $query;
            //echo "<pre>";
            //var_dump($_POST);
            $result = $wpdb->get_results($query);
            foreach ($result as $rt) {
                // var_dump($rt);
                $ret = wp_mail($rt->meta_value, $_POST['subject'], $_POST['mensagem'], $headers);
                // var_dump($ret);
                echo '<div class="notice notice-success is-dismissible"> 
                    <p><strong>Email enviado aos contato:<code>' . $rt->meta_value . '</code></strong></p>
                 </div>';
            }

            // var_dump($result);
        //
        }
    }

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /*
         * First, lets decide how many records per page to show
         */
        $per_page = 15;


        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();


        $this->_column_headers = array($columns, $hidden, $sortable);


        $this->process_bulk_action();


        $vet = [];

        $where = "";
        if (isset($_POST['nmEmail'])) {
            $where = "AND id in (select post_id from wp_postmeta where meta_value like '%" . $_POST['nmEmail'] . "%' and meta_key in('email','nome','_field_6','_field_5'))";
        }
        $query = "SELECT * FROM wp_posts where post_type = 'form_contact' $where order by ID desc";

        //echo $query;
        $result = $wpdb->get_results($query);
        // var_dump($result);
        //  die;
        foreach ($result as $c1) {
            // echo "<pre>";
            //$post_meta = get_post_meta($c1->ID);
            //var_dump($post_meta);
            // var_dump($c1);die;

            $whats = "<a target='blank' href='https://api.whatsapp.com/send?phone=" . get_post_meta($c1->ID, "whats", TRUE) . "'><img src='https://ruteimoveis.com/wp-content/uploads/2018/06/134937.png' width='20' title='" . get_post_meta($c1->ID, "whats", TRUE) . "' alt='" . get_post_meta($c1->ID, "whats", TRUE) . "'></a>";
            $vet[] = array(
            'ID' => $c1->ID,
            'data' => $c1->post_date,
            'nome' => get_post_meta($c1->ID, "nome", TRUE),
            'email' => get_post_meta($c1->ID, "email", TRUE),
            'msg' => $c1->post_content,
            'whats' => $whats,
            'localizacao' => get_post_meta($c1->ID, "city", TRUE) . " |" . get_post_meta($c1->ID, "regionName", TRUE) . "|" . get_post_meta($c1->ID, "country", TRUE),
            'imovel' => get_the_title(get_post_meta($c1->ID, "imovelID", TRUE)),
            'interesse' => '<a href="' . get_post_meta($c1->ID, "link", TRUE) . '" target="_blank">' . $c1->post_title . '</a>',
            );
        }

        $query = "select 
                    ID,post_date,
                    (select meta_value from wp_postmeta where post_id = ID and meta_key = '_field_6') as email,
                    (select meta_value from wp_postmeta where post_id = ID and meta_key = '_field_7') as whats,
                    (select meta_value from wp_postmeta where post_id = ID and meta_key = '_field_105') as interesse,
                    (select meta_value from wp_postmeta where post_id = ID and meta_key = '_field_9') as msg,
                    (select meta_value from wp_postmeta where post_id = ID and meta_key = '_field_5') as nome
                from wp_posts where post_type = 'nf_sub' and post_status = 'publish' $where";
        $result = $wpdb->get_results($query);
        // var_dump($result);
        //  die;
        foreach ($result as $c1) {
            // echo "<pre>";
            //$post_meta = get_post_meta($c1->ID);
            //var_dump($post_meta);
            // var_dump($c1);die;



            $vet[] = array(
                'ID' => $c1->ID,
                'data' => $c1->post_date,
                'nome' => $c1->nome,
                'email' => $c1->email,
                'msg' => $c1->msg,
                'whats' => $c1->whats,
                'localizacao' => '<span class="dashicons dashicons-dismiss"></span>',
                'imovel' => '<span class="dashicons dashicons-dismiss"></span>',
                'interesse' => $c1->interesse,
            );
        }


        $data = $vet;


        //  var_dump($data);die;

        /*
         * This checks for sorting input and sorts the data in our array of dummy
         * data accordingly (using a custom usort_reorder() function). It's for 
         * example purposes only.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary. In other words: remove this when
         * you implement your own query.
         */
        usort($data, array($this, 'usort_reorder'));

        /*
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /*
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);

        /*
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to do that.
         */
        $data = array_slice($data, ( ( $current_page - 1 ) * $per_page), $per_page);

        /*
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(array(
            'total_items' => $total_items, // WE have to calculate the total number of items.
            'per_page' => $per_page, // WE have to determine how many items to show on a page.
            'total_pages' => ceil($total_items / $per_page), // WE have to calculate the total number of pages.
        ));
    }

    /**
     * Callback to allow sorting of example data.
     *
     * @param string $a First value.
     * @param string $b Second value.
     *
     * @return int
     */
    protected function usort_reorder($a, $b) {
        // If no sort, default to title.
        $orderby = !empty($_REQUEST['orderby']) ? wp_unslash($_REQUEST['orderby']) : 'title'; // WPCS: Input var ok.
        // If no order, default to asc.
        $order = !empty($_REQUEST['order']) ? wp_unslash($_REQUEST['order']) : 'asc'; // WPCS: Input var ok.
        // Determine sort order.
        $result = strcmp($a[$orderby], $b[$orderby]);

        return ( 'asc' === $order ) ? $result : - $result;
    }

}
