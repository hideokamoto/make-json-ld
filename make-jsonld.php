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
require_once 'mkjsonld-admin.php';
require_once 'mkjsonld-content.php';

register_activation_hook( __FILE__ , 'mkjsonld_activation_callback');
add_action( 'init', 'mkjsonld_init');
add_action('template_redirect', 'mkjsonld_template_redirect');


function mkjsonld_activation_callback() {
    add_rewrite_endpoint( 'json-ld', EP_PERMALINK|EP_ROOT|EP_PAGES|EP_CATEGORIES);
    add_rewrite_endpoint( 'jsonld-context', EP_ROOT );
    flush_rewrite_rules();
}

function mkjsonld_init() {
    add_rewrite_endpoint('json-ld',EP_PERMALINK|EP_ROOT|EP_PAGES|EP_CATEGORIES);
    add_rewrite_endpoint('jsonld-context', EP_ROOT);
}

function mkjsonld_template_redirect() {
    header("Access-Control-Allow-Origin: *");
    global $wp_query;
    $mkjsonld = new mkjsonldContent;
    if( isset( $wp_query->query['json-ld']) ) {
        if( !$wp_query->query['json-ld']){
            mkjsonld_set_content($mkjsonld);
        } else {
            $wp_query->set_404();
            status_header(404);
            return;
        }
    }
    if( isset($wp_query->query['jsonld-context'])) {
        mkjsonld_context($mkjsonld);
    }
}

function mkjsonld_set_content($mkjsonld){
    $jsonld = mkjsonld_getJsonld($mkjsonld);

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

function mkjsonld_get_context_data(){
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

function mkjsonld_getQuery($wp_query){
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

function mkjsonld_getJsonld($mkjsonld){
    global $wp_query;
    $contextType = mkjsonld_get_context_data();

    if (is_home() || is_archive()){
        $query  = mkjsonld_getQuery($wp_query);
        $jsonld = $mkjsonld->get_archive($contextType, $query);
    } elseif (is_single() || is_page()){
        $jsonld = $mkjsonld->get_article($contextType);
    } else {
        return null;
    }
    return $jsonld;
}

function mkjsonld_context($mkjsonld){
    header('Content-type: application/ld+json; charset=UTF-8');
    global $wp_query;
    if(!$wp_query->query['jsonld-context']) {
        $context = $mkjsonld->get_context();
        echo $context;
        exit;
    } else {
        $wp_query->set_404();
        status_header(404);
        exit;
    }
}
