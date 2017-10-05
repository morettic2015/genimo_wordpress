<?php

/*
 *  @Copyright Morettic.
 */

class LeadController extends stdClass {

    public function createUserRole() {
        $result = add_role('lead_listing', __(
                        'Lead Proprietario'), array(
            'read' => true, // true allows this capability
            'edit_posts' => false, // Allows user to edit their own posts
            'edit_pages' => false, // Allows user to edit pages
            'edit_others_posts' => false, // Allows user to edit others posts not just their own
            'create_posts' => false, // Allows user to create new posts
            'manage_categories' => true, // Allows user to manage post categories
            'publish_posts' => false, // Allows the user to publish, otherwise posts stays in draft mode
                )
        );
        //var_dump($result);
    }

    public function createLead($email_address, $nunome, $phone) {
        if (null == username_exists($email_address)) {

            $password = wp_generate_password(12, true);
            $user_id = wp_create_user($email_address, $password, $email_address);
            wp_update_user(array('ID' => $user_id, 'nickname' => $nunome));
            //echo $phone;
            add_user_meta($user_id, 'phone', $phone, false);
            $user = new WP_User($user_id);
            $user->set_role('lead_listing');

            //wp_mail($email_address, 'Welcome!', 'Your Password: ' . $password);
            return $user;
        } else {
            $user = get_user_by('login', $email_address);
            wp_update_user(array('ID' => $user->ID, 'nickname' => $nunome));
            add_user_meta($user->ID, 'phone', $phone, false);
            //var_dump($user);
            return $user;
        }
    }

}

//Init Lead Controller
$leadController = new LeadController();
$leadController->createUserRole();
