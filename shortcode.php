<?php

function custom_recent_posts_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'count' => 5,
        'site_id' => get_current_blog_id(),
    ), $atts, 'custom_recent_posts' );

    ob_start();
    the_widget( 'Custom_Recent_Posts_Widget', $atts, array(
        'before_widget' => '',
        'after_widget' => '',
    ) );
    return ob_get_clean();
}
add_shortcode( 'custom_recent_posts', 'custom_recent_posts_shortcode' );