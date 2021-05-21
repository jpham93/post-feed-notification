<?php
/**
 * @title       Dashboard Widget for Post Feed Notification
 * @author      James Pham
 * @description Front-end component to display and feeds are defined.
 * @since       1.0
 * @updated     5/21/2021
 */

/**
 * @var WP_Term
 */
$wp_term = $callback_args['args'];
$id      = $callback_args['id'];

//do_action('qm/debug', $id);
?>

<style>
    <?php echo "#$id;" ?> {
        background: #feff9c;
    }
</style>

<h1><?php echo $wp_term->name; ?></h1>

<?php
$description = $wp_term->description;
if ( !empty($description) ) : ?>
    <p><?php echo $description; ?></p>
<?php endif; ?>
