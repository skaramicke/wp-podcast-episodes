<?php
// Derived from https://wordpress.org/plugins/featured-audio/ by Nick Halsey

/**
 * Add post meta box for Episode audio.
 */

// Add the Piece Files Meta box, for the sheet music post type.
function episode_audio_add_meta_box() {
	$post_types = apply_filters( 'episode_audio_post_types', array( 'post', 'page' ) );
	add_meta_box(
		'episode_audio',
		__( 'Episode Audio', 'episode-audio' ),
		'episode_audio_meta_box',
		'episode',
		'side',
		'low',
		array(
			'__block_editor_compatible_meta_box' => true,
			'__back_compat_meta_box'             => false,
		)
	);
}
add_action( 'add_meta_boxes', 'episode_audio_add_meta_box' );

// Enqueue scripts & styles.
function episode_audio_admin_scripts() {
    global $post_type, $post;
	if ( $post_type == 'episode' ) {
		// Enqueue admin JS.
		wp_enqueue_script( 'episode-audio-admin', plugins_url( '/episode-audio-admin.js', __FILE__), '', '', true );

		// Load data into JS, including translated strings.
		$stored_meta = get_post_meta( $post->ID );
		if ( isset ( $stored_meta['episode-audio'] ) && 0 != absint( $stored_meta['episode-audio'] ) ) {
			$audio_attachment = wp_prepare_attachment_for_js( absint( $stored_meta['episode-audio'] ) );
		} else {
			$audio_attachment = false;
		}
		wp_localize_script( 'episode-audio-admin', 'episodeAudioOptions', array(
			'audioAttachment' => $audio_attachment,
			'l10n' => array(
				'episodeAudio' => __( 'Episode Audio', 'episode-audio' ),
				'select' => __( 'Select', 'episode-audio' ),
				'change' => __( 'Change', 'episode-audio' ),
			),
		) );
	}
}
add_action( 'admin_print_scripts-post-new.php', 'episode_audio_admin_scripts' );
add_action( 'admin_print_scripts-post.php', 'episode_audio_admin_scripts' );

// Callback that renders the contents of the Episode Audio meta box.
function episode_audio_meta_box( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'episode_audio_nonce' );
	$stored_meta = get_post_meta( $post->ID );
	if ( isset ( $stored_meta['episode-audio'] ) && 0 !== absint( $stored_meta['episode-audio'][0] ) ) {
		$audio_attachment_id = absint( $stored_meta['episode-audio'][0] );
		$audio = get_post( $audio_attachment_id );
		$audio_attachment_title = $audio->post_title;
		$audio_attachment = wp_prepare_attachment_for_js( $audio_attachment_id );
	} else {
		$audio_attachment_id = '';
		$audio_attachment_title = '';
		$audio_attachment = false;
	}
	?>
	<div id="episode-audio" class="piece-attachment">
		<script type="text/javascript">var initialAudioAttachment = <?php echo wp_json_encode( $audio_attachment ); ?></script>
		<p><strong id="audio-attachment-title"><?php echo $audio_attachment_title; ?></strong></p>
		<div id="audio-preview-container" style="margin-top: -.5em; margin-bottom: 1em;"></div>
		<button type="button" class="button button-secondary" id="episode-audio-upload"><?php _e( 'Select', 'episode-audio' ); ?></button>
		<button type="button" class="button-link" style="margin: .4em 0 0 .5em; display: none;" id="episode-audio-remove"><?php _e( 'Remove', 'episode-audio' ); ?></button>
		<input type="hidden" name="episode-audio" id="audio-attachment-id" value="<?php echo $audio_attachment_id; ?>" />
	</div>
	<?php
}

/**
 * Save the custom fields on post save.
 */
function episode_audio_post_meta_save( $post_id ) {
	// Bail if this isn't a valid time to save post meta.
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'episode_audio_nonce' ] ) && wp_verify_nonce( $_POST[ 'episode_audio_nonce' ], basename( __FILE__ ) ) ) ? true : false;
	if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}

	// Sanitize and save post meta.
	if ( isset( $_POST[ 'episode-audio' ] ) ) {
		update_post_meta( $post_id, 'episode-audio', absint( $_POST[ 'episode-audio' ] ) );
	}
}
add_action( 'save_post', 'episode_audio_post_meta_save' );
