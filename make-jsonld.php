<?php
/**
 * @package Make JSON LD
 * @version 1.5
 */
/*
Plugin Name: Make JSON LD
Plugin URI: http://hideokamoto.github.io/make-json-ld/
Description: This Plugin can make JSON-LD for Linked Open Data.Using Advanced CustomField Plugin.
Author: Hidetaka Okamoto
Version: 1.5
Author URI: http://wp-kyoto.net/
*/
require_once 'inc/admin/mkjsonld-admin.php';
require_once 'inc/mkjsonld-content.php';

register_activation_hook( __FILE__ , 'odg_activation_callback');
add_action( 'init', 'odg_init');
add_action('template_redirect', 'odg_template_redirect');


function odg_activation_callback() {
    add_rewrite_endpoint( 'json-ld', EP_PERMALINK|EP_ROOT|EP_PAGES|EP_CATEGORIES);
    add_rewrite_endpoint( 'jsonld-context', EP_ROOT );
    flush_rewrite_rules();
}

function odg_init() {
    add_rewrite_endpoint('json-ld',EP_PERMALINK|EP_ROOT|EP_PAGES|EP_CATEGORIES);
    add_rewrite_endpoint('jsonld-context', EP_ROOT);
}

function odg_template_redirect() {
    header("Access-Control-Allow-Origin: *");
    global $wp_query;
    $odg = new odgContent;
    if( isset( $wp_query->query['json-ld']) ) {
        if( !$wp_query->query['json-ld']){
            odg_set_content($odg);
        } else {
            $wp_query->set_404();
            status_header(404);
            return;
        }
    }
    if( isset($wp_query->query['jsonld-context'])) {
        odg_context($odg);
    }
}

function odg_set_content($odg){
    $jsonld = odg_getJsonld($odg);

    header('Content-type: application/ld+json; charset=UTF-8');
    if (!isset($jsonld) || $jsonld == '[null]') {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        exit;
    } else {
        echo $jsonld;
        exit;
    }
}

function odg_get_context_data(){
    if (get_option('context')) {
        $contextData = get_option('context');
        //want to use array_column
        foreach ($contextData as $key => $context) {
            $contextType[] = $context['type'];
        }
    } else {
        $contextType[] = 'schema';
    }
    return $contextType;
}

function odg_getQuery($wp_query){
  $query = array(
      'post_type' =>'post',
      'posts_per_page' => 10
  );
  if(isset($_GET['filter'])){
      $query = $_GET['filter'];
  }
  if(!isset($query['posts_per_page'])){
    $query['posts_per_page'] = -1;
  }
  if(isset($wp_query->query_vars["category_name"])){
    $query['category_name'] = $wp_query->query_vars["category_name"];
  }
  return $query;

}

function odg_getJsonld($odg){
    global $wp_query;
    $contextType = odg_get_context_data();

    if (is_home() || is_archive()){
        $query  = odg_getQuery($wp_query);
        $jsonld = $odg->get_archive($contextType, $query);
    } elseif (is_single() || is_page()){
        $jsonld = $odg->get_article($contextType);
    } else {
        return null;
    }
    return $jsonld;
}

function odg_context($odg){
    header('Content-type: application/ld+json; charset=UTF-8');
    global $wp_query;
    if(!$wp_query->query['jsonld-context']) {
        $context = $odg->get_context();
        echo $context;
        exit;
    } else {
        $wp_query->set_404();
        status_header(404);
        exit;
    }
}
