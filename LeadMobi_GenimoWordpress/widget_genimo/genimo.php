<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form id="movies-filter" method="post">

        <div class="notice notice-info"> 
            <p>Lista de <code>Imóveis para importação do Genimo</code>.</p>
        </div>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->

        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <!-- Now we can render the completed list table -->

        <?php $test_list_table->display(); ?>
    </form>
</div>
<?php
//Close all connections
global $wpdb;
$wpdb->close();
?>