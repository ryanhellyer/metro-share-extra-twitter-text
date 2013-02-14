<?php
/*
Plugin Name: Metro Share Extra Twitter text
Plugin URI: http://metronet.no/
Description: Adds meta box to post edit screen to allow for custom text to be displayed in tweets
Version: 0.1
Author: Metronet AS
Author URI: http://metronet.no/
*/


new Extra_Twitter_Text_Meta;

/**
 * Add Meta Box for adding custom text to Tweets from Metro Share plugin
 * 
 * @copyright Copyright (c), Metronet
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @author Ryan Hellyer <ryan@metronet.no>
 * @since 0.1
 */
class Extra_Twitter_Text_Meta {

	/*
	 * Class Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes',          array( $this, 'add_metabox' ) );
		add_action( 'init',                    array( $this, 'meta_boxes_save' ) );
		add_filter( 'metroshare_destinations', array( $this, 'filter_destinations' ) );
		add_filter( 'metroshare_tag',          array( $this, 'add_tag' ) );
	}
	
	/*
	 * Add new tags
	 */
	public function add_tag( $replace ) {
		global $post;
		$replace['{{per_post_text}}'] = ' ' . get_post_meta( $post->ID, '_twitter_per_post_text', true );
		return $replace;
	}	

	/*
	 * Change help text
	 */
	public function filter_destinations( $destinations ) {
		$twitter = $destinations['twitter'];
		$fields = $twitter['fields'];
		$message = $fields['message'];

		$message['help'] = __( 'You can use the following tags: <code>{{title}}</code>, <code>{{link}}</code>, <code>{{per_post_text}}</code>.', 'metroshare' );

		$fields['message'] = $message;
		$twitter['fields'] = $fields;
		$destinations['twitter'] = $twitter;

		return $destinations;
	}
	
	/**
	 * Add admin metabox for thumbnail chooser
	 */
	public function add_metabox() {
		add_meta_box(
			'Enter some custom text for the Twitter sharing icon here',
			'Twitter sharing',
			array(
				$this,
				'meta_box',
			),
			'post',
			'side',
			'low'
		);
	}

	/**
	 * Output the thumbnail meta box
	 *
	 * @return string HTML output
	 * @global $post
	 */
	public function meta_box() {
		global $post;

		if ( isset( $_GET['post'] ) )
			$post_ID = (int) $_GET['post'];
		else
			$post_ID = '';

		?>
		<input type="hidden" name="_extratweet_hidden" id="_extratweet_hidden" value="1" />  
		<p>
			<label for="_twitter_per_post_text"><?php _e( 'Enter some custom text for the Twitter sharing icon here', 'metronet' ); ?></label>  
			<br />
			<input type="text" name="_twitter_per_post_text" id="_twitter_per_post_text" value="<?php echo get_post_meta( $post_ID, '_twitter_per_post_text', true ); ?>" />  
		</p><?php
	}

	/**
	 * Save opening times meta box data
	 */
	function meta_boxes_save() {

		// Bail out now if something not set
		if (
			isset( $_POST['_wpnonce'] ) &&
			isset( $_POST['post_ID'] ) &&
			isset( $_POST['_extratweet_hidden'] ) // This is required to ensure that auto-saves are not processed
		) {

			// Do nonce security check
			wp_verify_nonce( '_wpnonce', $_POST['_wpnonce'] );

			// Grab post ID
			$post_ID = (int) $_POST['post_ID'];

			// Sanitizing data
			if ( isset( $_POST['_twitter_per_post_text'] ) ) {
				$_twitter_per_post_text = esc_html( $_POST['_twitter_per_post_text'] );
				update_post_meta( $post_ID, '_twitter_per_post_text',    $_twitter_per_post_text );
			}

		}

	}

}
