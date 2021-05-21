<?php
/*
Plugin Name:    Post Feed Notification
Plugin URI:     https://github.com/jpham93/post-feed-notification
Description:    A basic plugin that provides a dashboard feed of the newest post in a specified category. Also emails users of newly created posts.
Author:         James Pham
Version:        1.0
Author URI:     https://jamespham.io
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PostFeedNotification {

    /**
     * CONSTRUCTOR
     * Run hooks and init scripts here.
     */
    function __construct() {

        add_action( 'admin_menu', [ $this, 'register_settings_page' ], 10, 0 );
        add_action( 'admin_post_create_new_feed', [ $this, 'create_new_feed' ], 10, 0 );
        add_action( 'admin_notices', [$this, 'settings_page_notifications'], 10, 0 );

    }

    /**
     * SUBMENU CALLBACK
     * Registers the submenu page under WP's core "Settings" tab.
     */
    public function register_settings_page() {

        add_submenu_page(
            'options-general.php',
            'Post Feed Notification',
            'Post Feed',
            'manage_options',
            'post-feed',
            [$this, 'settings_page']
        );

    }

    /**
     * FRONT END SETTINGS PAGE
     * HTML for admin access.
     */
    public function settings_page() {

        require_once 'views/settings-page.php';

    }

    /**
     * POST ENDPOINT FOR CREATING NEW FEED
     * Form submission handler for creating new feed.
     */
    public function create_new_feed() {

        $term_id = $_POST['term_id'] ?? null;

        // CHECK IF VALID PAYLOAD
        if ( empty($term_id) ) {

            $_SESSION['error'] = 'Invalid selection.';
            wp_redirect('/wp-admin/options-general.php?page=post-feed');
            exit;

        }

        // CHECK IF WP_TERM EXISTS W/ ID
        if ( !term_exists((int) $term_id) ) {

            $_SESSION['error'] = 'Error. Category does not exist';
            wp_redirect('/wp-admin/options-general.php?page=post-feed');
            exit;

        }

        $feeds = get_option('pfn_dashboard_feeds');

        // CREATE NEW ARRAY OF FEED IF OPTION DOES NOT EXIST YET
        if ( !$feeds ) {
            add_option( 'pfn_dashboard_feeds', array( $term_id ) );

        } elseif ( in_array($term_id, $feeds) ) {

            $wp_term            = get_term($term_id);
            $_SESSION['error']  = "Feed for {$wp_term->name} already exists. Please select another.";
            wp_redirect('/wp-admin/options-general.php?page=post-feed');
            exit;

        }

    }

    public function settings_page_notifications() {

        // Display errors
        if ( isset($_SESSION['error']) ) {
            ?>

            <div class="notice notice-error is-dismissible">
                <p><?php echo $_SESSION['error'] ?></p>
            </div>

            <?php
            unset($_SESSION['error']);
        }

        // Display success
        if ( isset($_SESSION['success']) ) {
            ?>

            <div class="notice notice-success is-dismissible">
                <p><?php echo $_SESSION['success'] ?></p>
            </div>

            <?php
            unset($_SESSION['success']);
        }

    }

}
new PostFeedNotification();
