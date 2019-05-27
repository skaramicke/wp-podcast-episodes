<?php

add_action('parse_request', function($query) {
    
    if (
        isset($query->query_vars['episode']) && 
        substr($query->request, -4, 4) == '.mp3'
    ) {
        $episode_url = substr($query->request, 0, -4);
        $episode_id = url_to_postid($episode_url);
        
        $downloads = get_post_meta($episode_id, 'downloads', true);
        if (empty($downloads)) {
            $downloads = 0;
        }
        update_post_meta($episode_id, 'downloads', $downloads + 1, $downloads);

        $file = get_episode_audio_path($episode_id);
        if ( file_exists( $file )) {
            header('Content-type: {$mime_type}');
            header('Content-length: ' . filesize($file));
            header('Content-Disposition: filename="' . $filename);
            header('X-Pad: avoid browser bug');
            header('Cache-Control: no-cache');
            readfile($file);
            exit;
        } // 404 is handled by WordPress
    }

    return $query;
});