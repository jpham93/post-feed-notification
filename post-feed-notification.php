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

    }

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

    public function settings_page() {

        require_once 'views/settings-page.php';

    }

}
new PostFeedNotification();
