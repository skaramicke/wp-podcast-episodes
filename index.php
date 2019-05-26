<?php
/*
Plugin Name: Podcast Episode - A Post type.
Plugin URI:  https://mikaelgron.se/podcast-episode-plugin
Description: 
Version:     0.0.1a
Author:      Mikael Grön <skaramicke@gmail.com>
Author URI:  http://mikaelgron.se
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

add_action('init', function() {
    register_post_type(
        'episode', array(
            'labels'             => array(
                'name'               => 'Episodes',
                'singular_name'      => 'Episode',
                'menu_name'          => 'Episodes',
                'name_admin_bar'     => 'Episode',
                'add_new'            => 'Add New',
                'add_new_item'       => 'Add New Episode',
                'new_item'           => 'New Episode',
                'edit_item'          => 'Edit Episode',
                'view_item'          => 'View Episode',
                'all_items'          => 'All Episodes',
                'search_items'       => 'Search Episodes',
                'parent_item_colon'  => 'Parent Episode:',
                'not_found'          => 'No episodes found.',
                'not_found_in_trash' => 'No episodes found in Trash.'
            ),
            'description'        => '',
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'episode' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
        )
    );
});

require( plugin_dir_path( __FILE__ ) . '/post-meta.php' );

// Load template for content display.
add_filter( 'the_content', function( $the_content ) {
	if ( get_post_type() == 'episode' ) {
		$the_content = podcast_episode_content_filter( $the_content );
	}
	return $the_content;
});

function podcast_episode_content_filter( $the_content ) {
    return get_the_podcast_episode() . $the_content;
}

function get_the_podcast_episode( $args = array() ) {
    $args = wp_parse_args( $args, array(
		'id'        => null,
		'title'     => false,
    ) );
    $audio_url = get_episode_audio_src( $args['id'] );
	if ($audio_url) {
        return 'audio player';
    }
    return 'no audio player';
}

function get_episode_audio_src( $id ) {
	$audio_attachment_id = get_episode_audio_attachment_id( $id );
	return ( $audio_attachment_id ) ? wp_get_attachment_url( $audio_attachment_id ) : '';
}

function get_episode_audio_attachment_id( $id = null ) {
	if ( ! absint( $id ) ) {
		$id = get_the_ID();
	}
	return absint( get_post_meta( $id, 'episode-audio', true ) );
}
