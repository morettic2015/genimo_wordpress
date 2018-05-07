<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form id="movies-filter" method="post">
        <?php
        if (isset($_POST['action']) && ($_POST['action'] === "mail")) {
            //var_dump($_POST);
            ?>
            <div id="normal-sortables" class="meta-box-sortables ui-sortable"><div id="_namespace_metabox" class="postbox ">
                    <div class="inside">

                        <h1>
                            Enviar email para seus contatos
                        </h1>
                        <fieldset>
                            <div>
                                <h4>
                                    Assunto:
                                </h4>
                                <input type='text' name="subject" placeholder="Assunto da mensagem" class="stuffbox" style="width: 100%"/>
                            </div>
                            <div>
                                <h4>
                                    Mensagem:
                                </h4>
                                <?php wp_editor("", "mensagem"); ?>
                            </div>
                            <?php
                            $ids = "-1";
                            foreach ($_POST['lead'] as $d) {
                                $ids .= "," . $d;
                            }
                            ?>
                            <input type="hidden" name='ids' id="ids" value="<?php echo $ids; ?>"/>
                            <input type="hidden" name='action' id="action" value="send"/>
                            <input type="submit" name="Enviar" value="Enviar"/>
                        </fieldset>

                        <input type="hidden" id="_namespace_form_metabox_process" name="_namespace_form_metabox_process" value="6420003645"><input type="hidden" name="_wp_http_referer" value="/wp-admin/post.php?post=320115&amp;action=edit"></div>
                </div>
                <div id="slugdiv" class="postbox  hide-if-js" style="">
                    <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Alternar painel: Slug</span><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle ui-sortable-handle"><span>Slug</span></h2>
                    <div class="inside">
                        <label class="screen-reader-text" for="post_name">Slug</label><input name="post_name" type="text" size="13" id="post_name" value="rua-general-bittencourt-centro-florianopolis-sc-brasil">
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <p class="search-box">
                <span class="dashicons dashicons-search"></span>Buscar contatos<br>
               <!-- <select id="business_id-search-input" name="f_business">
                    <option>Filtrar por negócio</option>
                </select>-->
                <label class="screen-reader-text" for="f_group">nome ou email</label> 
                <input type="text" name="nmEmail" id="nmEmail" value="<?php echo $_POST['nmEmail']; ?>"/>
                <input placeholder="Nome ou email do contato" id="search-submit" class="button" type="submit" name="" value="Buscar" />
            </p>
            <div class="notice notice-info"> 
                <p>Lista de <code>Contatos imobiliários</code>.</p>
            </div>
            <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->

            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->

            <?php
            $test_list_table->display();
        }
        ?>
    </form>
</div>
<?php
//Close all connections
global $wpdb;
$wpdb->close();
?>