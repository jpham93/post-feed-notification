<?php
/**
 * @title           Settings Page View
 * @author          James Pham
 * @description     Settings page HTML for Post Feed Notification plugin.
 */

$feeds = get_option('pfn_dashboard_feeds');
do_action('qm/debug', $feeds);
?>


<h1>Settings Page</h1>

<p class="description">
    Select a post category to display as a feed on the main dashboard.
</p>

<form action="<?php echo admin_url('admin-post.php') ?>" method="post">
    <input type="hidden" name="action" value="create_new_feed">
    <table>
        <tr>
            <td>
                <label for="category-select">Select a new</label>
            </td>
            <td>
                <select name="term_id" id="category-select">
                    <option value="" disabled selected>Select a Category</option>

                    <?php
                    $args = array(
                        'taxonomy'   => 'category',
                        'hide_empty' => false
                    );

                    /**
                     * strip "uncategorized" terms from the drop down
                     * @var WP_Term[]
                     */
                    $post_categories = array_filter( get_categories( $args ), fn( WP_Term $wp_term ) => $wp_term->slug !== 'uncategorized' );

                    foreach ( $post_categories as $wp_term ) :
                        ?>

                        <option value="<?php echo $wp_term->term_id ; ?>">
                            <?php echo $wp_term->name ; ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </td>
            <td>
                <button class="button" type="submit">Create Feed</button>
            </td>
        </tr>
    </table>
</form><!-- CREATE NEW SETTINGS PAGE -->

<br>

<?php
$feeds = get_option('pfn_dashboard_feeds');

if ( !empty($feeds) ) : // DISPLAY TABLE IF IT OPTIONS DEFINED
?>
    <h2>Active Post Feeds</h2>
    <p class="description">
        Delete current post category from dashboard feeds.
    </p>

    <form action="<?php echo admin_url('admin-post.php') ?>" method="post">
        <input type="hidden" name="action" value="delete_feed">
        <table>
            <thead>
                <th>
                    Post Category
                </th>
            </thead>
            <tbody>

                <?php
                foreach ( $feeds as $term_id ) :
                    /**
                     * @var WP_Term
                     */
                    $wp_term = get_term($term_id);
                ?>
                    <tr>
                        <td>
                            <?php echo $wp_term->name ?>
                        </td>
                        <td>
                            <button class="button button-update button-link-delete"
                                    name="term_id" value="<?php echo $term_id; ?>" type="submit">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </form>
<?php endif; ?>
