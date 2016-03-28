<?php
/**
 * Where did they go from here Admin interface.
 *
 * This page is accessible via Settings > Where did they go
 *
 * @package   WHEREGO
 * @subpackage	Admin
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://ajaydsouza.com
 * @copyright 2008-2016 Ajay D'Souza
 */


/**
 * Function to add meta box in Write screens.
 *
 * @since 1.7
 * @param text   $post_type
 * @param object $post
 * @return void
 */
function wherego_add_meta_box( $post_type, $post ) {

		add_meta_box(
			'wherego_metabox',
			__( 'Where did they go from here', 'where-did-they-go-from-here' ),
			'wherego_call_meta_box',
			$post_type,
			'advanced',
			'default'
		);

}
add_action( 'add_meta_boxes', 'wherego_add_meta_box' , 10, 2 );


/**
 * Function to call the meta box.
 *
 * @since 1.7
 * @return void
 */
function wherego_call_meta_box() {
	global $post;

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'wherego_meta_box', 'wherego_meta_box_nonce' );

	$results = get_post_meta( $post->ID, 'wheredidtheycomefrom', true );
	$value = ( $results ) ? implode( ',', $results ) : '';
	$output = '';
?>
    <p>
		<label for="wherego_post_ids"><?php _e( "Followed posts' IDs:", 'where-did-they-go-from-here' ); ?></label>
		<input type="text" id="wherego_post_ids" name="wherego_post_ids" value="<?php echo esc_attr( $value ) ?>" size="25" />
		<em><?php _e( 'Enter a comma separated list of valid post/page IDs. Save this post to see the updated list below.', 'where-did-they-go-from-here' ); ?></em>
    </p>
	<?php if ( $results ) { ?>

		<h3><?php _e( 'Followed posts:', 'where-did-they-go-from-here' ); ?></h3>
        <ol>
		<?php
		foreach ( $results as $result ) {
			$title = get_the_title( $result );
			echo '<li>';
			echo '<a href="' . get_permalink( $result ) . '" target="_blank" title="' . $title . '" class="wherego_title">' . $title . '</a>'; // Add title if post thumbnail is to be displayed after
			echo '</li>';
		}
		?>
        </ol>
	<?php } ?>

<?php
}


/**
 * Function to save the meta box.
 *
 * @since 1.7
 * @param mixed $post_id
 * @return void
 */
function wherego_save_meta_box( $post_id ) {
	// Bail if we're doing an auto save
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }

	// if our nonce isn't there, or we can't verify it, bail
	if ( ! isset( $_POST['wherego_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['wherego_meta_box_nonce'], 'wherego_meta_box' ) ) { return; }

	// if our current user can't edit this post, bail
	if ( ! current_user_can( 'edit_post' ) ) { return; }

	if ( isset( $_POST['wherego_post_ids'] ) ) {

		$wherego_post_ids = array_map( 'intval', explode( ',', $_POST['wherego_post_ids'] ) );

		foreach ( $wherego_post_ids as $key => $value ) {
			if ( 'publish' != get_post_status( $value ) ) {
				unset( $wherego_post_ids[ $key ] );
			}
		}
	}

	$linkpostids = get_post_meta( $post_id, 'wheredidtheycomefrom', true );
	if ( $linkpostids && '' != $linkpostids ) {
		$gotmeta = true;
	} else {
		$gotmeta = false;
	}

	if ( $gotmeta && '' != $wherego_post_ids ) {
		update_post_meta( $post_id, 'wheredidtheycomefrom', $wherego_post_ids );
	} elseif ( ! $gotmeta && '' != $wherego_post_ids ) {
		add_post_meta( $post_id, 'wheredidtheycomefrom', $wherego_post_ids );
	} else {
		delete_post_meta( $post_id, 'wheredidtheycomefrom' );
	}

}
add_action( 'save_post', 'wherego_save_meta_box' );

