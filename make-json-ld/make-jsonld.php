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

    $containedIn = get_post_meta( $postId, 'field_page_1', true );
    $locality    = get_post_meta( $postId, 'field_page_2', true );
    $address     = get_post_meta( $postId, 'field_page_3', true );
    $url         = get_post_meta( $postId, 'field_page_4', true );
    $facebook    = get_post_meta( $postId, 'field_page_5', true );
    $twitter     = get_post_meta( $postId, 'field_page_6', true );
    $name        = get_post_meta( $postId, 'field_page_7', true );
    $parking     = get_post_meta( $postId, 'field_page_8', true );
    $price       = get_post_meta( $postId, 'field_page_9', true );
    $telephone   = get_post_meta( $postId, 'field_page_10', true );
    $openingHour = get_post_meta( $postId, 'field_page_11', true );
    $closed      = get_post_meta( $postId, 'field_page_12', true );

    $contentArr = array(
        "@context" => "{$contextUrl}",
        "@id"  => "{$postUrl}",
        );
    if (has_post_thumbnail()) {
        $contentArr['schema:image'] = wp_get_attachment_url(get_post_thumbnail_id());
    }
    if (get_the_content()) {
        $contentArr['schema:description'] = get_the_content();
    }
    if ($containedIn) {
        $contentArr["schema:containedIn"] = $containedIn;
    }
    if ($locality) {
        $contentArr["yafjp:locality"] = $locality;
    }
    if ($address) {
        $contentArr["schema:address"] = $address;
    }
    if ($url) {
        $contentArr["schema:url"] = $url;
    }
    if ($facebook) {
        $contentArr["schema:sameAs"][] = $facebook;
    }
    if ($twitter) {
        $contentArr["schema:sameAs"][] = $twitter;
    }
    if ($name) {
        $contentArr["schema:name"] = $name;
    }
    if ($parking) {
        $contentArr["yafjp:parking"] = $parking;
    }
    if ($price) {
        $contentArr["schema:price"] = $price;
    }
    if ($telephone) {
        $contentArr["schema:telephone"] = $telephone;
    }
    if ($openingHour) {
        $contentArr["schema:openingHour"] = $openingHour;
    }
    if ($closed) {
        $contentArr["yafjp:closed"] = $closed;
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