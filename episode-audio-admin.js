// Source: https://wordpress.org/plugins/featured-audio/ by Nick Halsey

/**
 * Sheet music library admin script. Primarily handles file upload UI.
 */

var episode_audio = {};
( function( $ ) {
	console.log('episode-audio-init');
	episode_audio = {
		container: '',
		frame: '',
		settings: episodeAudioOptions || {},

		init: function() {
			episode_audio.container = $( '#episode-audio' );
			episode_audio.initFrame();

			// Bind events, with delegation to facilitate re-rendering.
			episode_audio.container.on( 'click', '#episode-audio-upload', episode_audio.openAudioFrame );
			episode_audio.container.on( 'click', '#episode-audio-remove', episode_audio.removeAudio );
			episode_audio.initAudioPreview();
		},

		/**
		 * Open the episode audio media modal.
		 */
		openAudioFrame: function( event ) {
			if ( ! episode_audio.frame ) {
				episode_audio.initFrame();
			}
			episode_audio.frame.open();
		},

		/**
		 * Create a media modal select frame, and store it so the instance can be reused when needed.
		 */
		initFrame: function() {
			episode_audio.frame = wp.media({
				button: {
					text: episode_audio.settings.l10n.select
				},
				states: [
					new wp.media.controller.Library({
						title:     episode_audio.settings.l10n.episodeAudio,
						library:   wp.media.query({ type: 'audio' }),
						multiple:  false,
						date:      false
					})
				]
			});

			// When a file is selected, run a callback.
			episode_audio.frame.on( 'select', episode_audio.selectAudio );
		},

		/**
		 * Callback handler for when an attachment is selected in the media modal.
		 * Gets the selected attachment information, and sets it within the control.
		 */
		selectAudio: function() {
			// Get the attachment from the modal frame.
			var attachment = episode_audio.frame.state().get( 'selection' ).first().toJSON();
			$( '#audio-attachment-id' ).val( attachment.id );
			$( '#audio-attachment-title' ).text( attachment.title );
			episode_audio.audioEmbed( attachment );
		},

		/**
		 * Embed the audio player preview.
		 */
		audioEmbed: function( attachment ) {
			wp.ajax.send( 'parse-embed', {
				data : {
					post_ID: wp.media.view.settings.post.id,
					shortcode: '[audio src="' + attachment.url + '"][/audio]'
				}
			} ).done( function( response ) {
				var html = ( response && response.body ) || '';
				$( '#audio-preview-container' ).html( html );
				$( '#episode-audio-remove' ).show();
				$( '#episode-audio-upload' ).text( episode_audio.settings.l10n.change );
			} );
		},

		/**
		 * Remove the selected audio.
		 */
		removeAudio: function() {
			$( '#audio-attachment-id' ).val( 0 );
			$( '#audio-attachment-title' ).text( '' );
			$( '#audio-preview-container' ).html( '' );
			$( '#episode-audio-upload' ).text( episode_audio.settings.l10n.select );
			$( '#episode-audio-remove' ).hide();
		},

		/**
		 * Initialize episode audio preview.
		 */
		initAudioPreview: function() {
			var attachment = initialAudioAttachment;
			if ( attachment ) {
				episode_audio.audioEmbed( attachment );
				$( '#episode-audio-upload' ).text( episode_audio.settings.l10n.change );
				$( '#episode-audio-remove' ).show();
			}
		}
	}

	$(document).ready( function() { episode_audio.init(); } );

} )( jQuery );