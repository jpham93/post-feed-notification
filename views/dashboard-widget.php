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

//do_action('qm/debug', $id);
?>

<h1><?php echo $wp_term->name; ?></h1>

<?php
$description = $wp_term->description;
if ( !empty($description) ) :
    ?>

    <p><?php echo $description; ?></p>

<?php endif; ?>

<div>
    <ul>
        <?php
        $args = array(
            'post_type'     => 'post',
            'post_status'   => 'any',
            'posts_per_page' => '5',
            'tax_query'     => array(
                array(
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => $wp_term->slug,
                ),
            ),
            'order'         => 'DESC',
            'orderby'       => 'date'
        );

        $wp_query = new WP_Query($args);

        $posts = $wp_query->get_posts();

        do_action('qm/debug', $posts);

        foreach ($posts as $post) :

            ?>

            <li>
                <p class="dashboard-feed-item">
                    <a href="<?php echo get_permalink($post->ID); ?>" class="post-link">
                        <strong><?php echo $post->post_title; ?></strong>
                    </a>
                    <span class="post-date">
                        <strong><?php echo date_format(new DateTime($post->post_date), 'M d, Y'); ?></strong>
                    </span>
                </p>
            </li>

        <?php endforeach; ?>
    </ul>
</div>
