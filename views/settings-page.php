<?php
/**
 * @title           Settings Page View
 * @author          James Pham
 * @description     Settings page HTML for Post Feed Notification plugin.
 */
?>

<h1>Settings Page</h1>

<p class="description">
    Please select users
</p>


<?php
$args = array(
    'taxonomy'   => 'category',
    'hide_empty' => false
);
$post_categories = get_categories($args);

do_action('qm/debug', $post_categories);
?>

<form action="">

</form>
