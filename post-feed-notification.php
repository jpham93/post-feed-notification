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
        add_action( 'admin_post_delete_feed', [ $this, 'delete_feed' ], 10, 0 );
        add_action( 'admin_notices', [ $this, 'settings_page_notifications' ], 10, 0 );
        add_action( 'wp_dashboard_setup', [ $this, 'setup_dashboard_feeds' ], 10, 0 );
        add_action( 'admin_head', [ $this, 'dashboard_styles' ], 10, 0 );

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
        $wp_term = get_term($term_id);

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

        if ( !$feeds ) { // CREATE NEW ARRAY OF FEED IF OPTION DOES NOT EXIST YET

            add_option( 'pfn_dashboard_feeds', array( $term_id ) );
            $_SESSION['success']  = "Feed for \"{$wp_term->name}\" successfully created!.";
            wp_redirect('/wp-admin/options-general.php?page=post-feed');
            exit;

        } elseif ( in_array($term_id, $feeds) ) { // ERROR IF TERM_ID EXISTS IN OPTION ARRAY ALREADY

            $_SESSION['error']  = "Feed for \"{$wp_term->name}\" already exists. Please select another.";
            wp_redirect('/wp-admin/options-general.php?page=post-feed');
            exit;

        } else { // ADD TO ARRAY IF NOT IN ARRAY YET

            $feeds[] = $term_id;
            update_option('pfn_dashboard_feeds', $feeds);

            $_SESSION['success']  = "Feed for \"{$wp_term->name}\" successfully created!";
            wp_redirect('/wp-admin/options-general.php?page=post-feed');
            exit;

        }

    }

    /**
     * POST HANDLER FOR DELETING INDIVIDUAL FEED
     * Deletes dashboard feed when
     */
    public function delete_feed() {

        $term_id = $_POST['term_id'];
        $wp_term = get_term($term_id);

        $feeds = get_option('pfn_dashboard_feeds');

        // CHECK IF USER IS DELETING WHEN OPTIONS HASN'T BEEN SET
        if ( !$feeds ) {

            $_SESSION['error']  = "Feeds does not exist yet. Cannot perform delete option.";
            wp_redirect('/wp-admin/options-general.php?page=post-feed');
            exit;

        }

        // CHECK IF USER IS ATTEMPTING TO DELETE FEEDS THAT'S NOT YET STORED IN OPTIONS
        if ( !in_array($term_id, $feeds) ) {

            $_SESSION['error']  = "Cannot delete from current feeds. ID #{$term_id} does not exist.";
            wp_redirect('/wp-admin/options-general.php?page=post-feed');
            exit;

        }

        $feeds = array_filter( $feeds, fn ($t_id) => $t_id !==  $term_id );

        // delete entire option if there was only one feed
        count($feeds)
            ? update_option('pfn_dashboard_feeds', $feeds)
            : delete_option('pfn_dashboard_feeds');

        $_SESSION['success']  = "Feed for \"{$wp_term->name}\" successfully deleted!";
        wp_redirect('/wp-admin/options-general.php?page=post-feed');
        exit;
    }

    /**
     * ADMIN NOTIFICATIONS FOR SETTINGS PAGE
     * Sends alert notifications for settings page.
     */
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

    /**
     * CALLBACK HOOK FOR SETTING UP DASHBOARD WIDGETS
     * Reads set feeds from Options API.
     */
    public function setup_dashboard_feeds() {

        /**
         * @var int[]
         */
        $feeds = get_option('pfn_dashboard_feeds');

        if ( !empty($feeds) ) {

            /**
             * @var WP_Term[]
             */
            $wp_terms = array_map( fn(int $term_id) => get_term($term_id), $feeds );

            foreach ( $wp_terms as $wp_term ) {

                wp_add_dashboard_widget(
                    "pfn-dashboard-{$wp_term->slug}",
                    "{$wp_term->name} Feed ",
                    [$this, 'dashboard_widget'],
                    null,
                    $wp_term
                );

            }


        }

    }

    /**
     * DASHBOARD WIDGET TEMPLATE
     * Renders HTML for admin dashboard widget.
     * @param string $var             - not sure...
     * @param array $callback_args    - assoc array with id, title, and callback. args key is option (arg from wp_add_dashboard_widget)
     */
    public function dashboard_widget( string $var, array $callback_args ) {

        require 'views/dashboard-widget.php';

    }

    /**
     * STYLING FOR DASHBOARD WIDGETS
     */
    public function dashboard_styles() {

        $feeds          = get_option('pfn_dashboard_feeds');
        $wp_terms       = array_map( fn (int $term_id) => get_term($term_id), $feeds );
        $dashboard_ids  = array_map( fn (WP_Term $wp_term) => "#pfn-dashboard-{$wp_term->slug}", $wp_terms );
        $css_selectors  = join(', ', $dashboard_ids);

        if ( !empty($feeds) ) {
            ?>

            <style>
                <?php echo $css_selectors ?> {
                    background: #feff9c;
                }
            </style>

            <?php
        }
    }

}
new PostFeedNotification();
