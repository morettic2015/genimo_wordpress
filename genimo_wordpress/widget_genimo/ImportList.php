<?php

CONST IMOB_ID = 41;

class ImportList extends WP_List_Table {

    public function __construct() {
        // Set parent defaults.
        parent::__construct(array(
            'singular' => 'genimoIMOB', // Singular name of the listed records.
            'plural' => 'genimoIMOBS', // Plural name of the listed records.
            'ajax' => false, // Does this table support ajax?
        ));
    }

    public function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />', // Render a checkbox instead of text.
            'codigo' => "Codigo",
            'mod' => "Modalidade",
            'name' => "Nome do imóvel",
            'locale' => "Localização",
            'last' => "Última atualização",
            'sync' => "Foi importado?",
            'categ' => "Tipo do imóvel"
        );

        return $columns;
    }

    protected function get_sortable_columns() {
        $sortable_columns = array(
            'codigo' => array('codigo', false),
            'last' => array('last', false),
            'sync' => array('sync', false),
            'locale' => array('locale', false),
            'name' => array('locale', false),
            'mod' => array('locale', false),
            'categ' => array('locale', false),
                //   'rating' => array('rating', false),
                //   'director' => array('director', false),
        );

        return $sortable_columns;
    }

    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'codigo':
            case 'mod':
            case 'data':
            case 'name':
            case 'locale':
            case 'last':
            case 'categ':
            case 'sync':
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
        $actions['genimo'] = '<a href="admin.php?page=app_guiafloripa_leads_add&pid=' . $item['ID'] . ' ">Importar do Genimo</a>';

        return sprintf('%1$s <span style="color:silver;">(id:%2$s)</span>%3$s', $item['name'], $item['ID'], $this->row_actions($actions));
    }

    protected function get_bulk_actions() {
        $actions = array(
            'genimo_import' => _x('Importar do genimo', 'List table bulk action', 'wp-list-table-example'),
        );


        return $actions;
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
        if ($_POST['action'] === "genimo_import") {
            $ids = "-1";
            foreach ($_POST['genimoimob'] as $d) {
                $url = "https://ruteimoveis.com/synchronize/property/41/" . $d;
                wp_remote_get($url);
                echo '<div class="notice notice-success is-dismissible"> 
                    <p><strong>O imóvel  com o código interno <code>' . $d . '</code> foi importado com sucesso.</strong></p>
                 </div>
                 ';
                //echo $url;
            }
            wp_die('<a href="admin.php?page=imo_import" class="page-title-action">Voltar</a>');
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


        if (false === ( $vet = get_transient('genimo_wordpress_tmp2') )) {

            $imoveis = wp_remote_get("https://genimo.com.br/api/site/propertyForPublication/" . IMOB_ID);
            //echo "<pre>";
            $jsonImoveis = json_decode($imoveis['body']);
            // var_dump($jsonImoveis);die;


            foreach ($jsonImoveis as $c1) {

                // var_dump($c1);die;
                $vet[] = array(
                    'ID' => $c1->idProperty,
                    'codigo' => $c1->cdInternal,
                    'mod' => $this->getMode($c1->cdMode),
                    'categ' => $this->getCategory($c1->idCategory),
                    'name' => $c1->nmPropertySite,
                    'locale' => $c1->nmNeighborhood,
                    'sync' => $this->hasBeenIimported($c1->idProperty),
                    'last' => $c1->dtLastUpdate
                );
            }
            set_transient('genimo_wordpress_tmp2', $vet, HOUR_IN_SECONDS / 2);
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

    public function hasBeenIimported($id) {
        global $wpdb;
        $query = "select count(*) as total from wp_postmeta where meta_key = '_listing_id' and meta_value = '" . $id . "'";
        $result = $wpdb->get_results($query);


        return ($result[0]->total > 0) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no-alt"></span>';
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

    private function getMode($id) {
        $tp = ";";
        $pid = intval($id);
        switch ($pid) {
            case 1:
                $tp = "Aluguel";
                break;
            case 2:
                $tp = "Venda";
                break;
            case 4:
                $tp = "Aluguel de temporada";
                break;
            case 5:
                $tp = "Aluguel baixa temporada";
                break;
            default:
                $tp = "Não informado";
                break;
        }
        return $tp;
    }

    private
            function getCategory($id) {
        // echo "---$id----";
        // echo "$idPropertyDB-----";
        $pid = intval($id);
        $cate = ";";
        switch ($pid) {
            case 1:
                $cate = "Casa";
                // echo "CASA";
                break;
            case 2:
                $cate = "Apartamento";
                //  echo "APTO";
                break;
            case 3:
                $cate = "Terreno";
                //  echo "TERRENO";
                break;
            case 4:
                $cate = "Quitinete";
                break;
            case 5:
                $cate = "Sala comercial";
                break;
            case 6:
                $cate = "Galpao";
                //   echo "GAlpão";
                break;
            case 7:
                $cate = "Cobertura";
                //   echo "Cobertura";
                break;
        }
        return $cate;
    }

}
