<?php
/**
 * @package Make Json LD TEST
 * @version 1.0
 */
/*
Plugin Name: Make Json LD TEST
Plugin URI: http://wordpress.org/plugins/test-use-sparql-for-saigoku-33/
Description: This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from <cite>Hello, Dolly</cite> in the upper right of your admin screen on every page.
Author: Hidetaka Okamoto
Version: 1.0
Author URI: http://wp-kyoto.net/
*/
function ejls_get_archive ($max_no) {
    if (is_home()){
        $jsonld = '[';
            $mainContents = array(
                'post_type' =>'post',
                'posts_per_page' => $max_no,
                'paged' => $paged
            );
            $the_query = new WP_Query( $mainContents );
            while ( $the_query->have_posts() ) : $the_query->the_post();
                $jsonld .= ejls_get_content();
                if (!ejls_is_last($the_query)){
                    $jsonld .= ',';
                }
            endwhile;
        $jsonld .=']';
        return $jsonld;
    }
}
function ejls_is_last ($the_query) {
    return ($the_query->current_post+1 === $the_query->post_count);
}
function ejls_get_content () {
    $contextUrl = get_home_url() . "/jsonld-context/";
    $postUrl = get_permalink();
    $postId = get_the_ID();
    $customFields = get_post_meta($postId);

    $contentArr = array(
        "@context" => "{$contextUrl}",
        "@id"  => "{$postUrl}",
        );
    foreach($customFields as $key => $value){
    if(substr($key,0,1) === '_'){
        continue;
    } elseif (substr($key,0,6) === 'schema'){
        $contentArr[$key] = $value[0];
    }
    }
    $json = json_encode($contentArr, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    return $json;
}
function ejls_get_article () {
    if (is_page() || is_single()) {
        $jsonld = '[';
        if (have_posts()) : while (have_posts()) : the_post();
            $jsonld .= ejls_get_content();
        endwhile; endif;
        $jsonld .=']';
        rewind_posts();
        return $jsonld;
    }
}
register_activation_hook( __FILE__ , 'ejls_activation_callback');
function ejls_activation_callback() {
    add_rewrite_endpoint( 'json', EP_PERMALINK|EP_ROOT|EP_PAGES );
    add_rewrite_endpoint( 'jsonld-context', EP_ROOT );
    flush_rewrite_rules();
}
add_action( 'init', 'ejls_init');
function ejls_init() {
    add_rewrite_endpoint('json',EP_PERMALINK|EP_ROOT|EP_PAGES );
    add_rewrite_endpoint( 'jsonld-context', EP_ROOT );
}
add_action('template_redirect', 'ejls_template_redirect');
function ejls_template_redirect() {
    header("Access-Control-Allow-Origin: *");
    global $wp_query;
    if( isset( $wp_query->query['json']) ) {
        if( ! $wp_query->query['json'] ){
            header( 'Content-type: application/ld+json; charset=UTF-8');
            if (is_home()){
                $max_no = $_GET['max'];
                $jsonld = ejls_get_archive($max_no);
            } elseif (is_single() || is_page()){
                $jsonld = ejls_get_article();
            }
            echo $jsonld;
            exit;
        } else {
            $wp_query->set_404();
            status_header( 404);
            return;
        }
    }
    if( isset( $wp_query->query['jsonld-context']) ) {
        if( ! $wp_query->query['jsonld-context'] ){
            header( 'Content-type: application/ld+json; charset=UTF-8');
            $context = ejls_get_context();
            echo $context;
            exit;
        } else {
            $wp_query->set_404();
            status_header(404);
            return;
        }
    }
}
function ejls_get_context(){
    $context = '{
    "@context": {
        "schema": "http://schema.org/",
        "yafjp": "http://fp.yafjp.org/terms/place#"
    }
}';
    return $context;
}