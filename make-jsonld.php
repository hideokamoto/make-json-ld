<?php
/**
 * @package Make JSON LD
 * @version 1.2
 */
/*
Plugin Name: Make JSON LD
Plugin URI: http://hideokamoto.github.io/make-json-ld/
Description: This Plugin can make JSON-LD for Linked Open Data.Using Advanced CustomField Plugin.
Author: Hidetaka Okamoto
Version: 1.2
Author URI: http://wp-kyoto.net/
*/
function ejls_get_archive ($max_no) {
    if (is_home()){
        $mainContents = array(
            'post_type' =>'post',
            'posts_per_page' => $max_no,
            'paged' => $paged
        );
        $the_query = new WP_Query( $mainContents );
        while ( $the_query->have_posts() ) : $the_query->the_post();
            $content = ejls_get_content();
            if(!$content){ continue; }
            $jsonld[] = $content;
        endwhile;
        $jsonld = json_encode($jsonld, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
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

    $context = array(
        "@context" => "{$contextUrl}",
        "@id"  => "{$postUrl}",
        );
    foreach($customFields as $key => $value){
        if(substr($key,0,1) === '_'){
            continue;
        } elseif (ejls_is_opendata ($key)){
            $content[$key] = $value[0];
        }
    }
    if ($content) {
        $json = array_merge_recursive($context, $content);
    } else {
        $json = null;
    }
    return $json;
}
function ejls_get_article () {
    if (is_page() || is_single()) {
        if (have_posts()) : while (have_posts()) : the_post();
            $jsonld[] = ejls_get_content();
        endwhile; endif;
        rewind_posts();
        $jsonld = json_encode($jsonld, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        return $jsonld;
    }
}
register_activation_hook( __FILE__ , 'ejls_activation_callback');
function ejls_activation_callback() {
    add_rewrite_endpoint( 'json', EP_PERMALINK|EP_ROOT|EP_PAGES);
    add_rewrite_endpoint( 'jsonld-context', EP_ROOT );
    flush_rewrite_rules();
}

function ejls_is_opendata ($key) {
    if (
        substr($key,0,3) === 'rdf'    ||
        substr($key,0,4) === 'rdfs'   ||
        substr($key,0,5) === 'vCard'  ||
        substr($key,0,4) === 'foaf'   ||
        substr($key,0,2) === 'dc'     ||
        substr($key,0,7) === 'dcterms'||
        substr($key,0,3) === 'cal'    ||
        substr($key,0,3) === 'geo'    ||
        substr($key,0,3) === 'owl'    ||
        substr($key,0,6) === 'schema' ||
        substr($key,0,4) === 'skos'   ||
        substr($key,0,5) === 'yafjp'
    ) {
        return true;
    }
    return false;
}

add_action( 'init', 'ejls_init');
function ejls_init() {
    add_rewrite_endpoint('json',EP_PERMALINK|EP_ROOT|EP_PAGES);
    add_rewrite_endpoint('jsonld-context', EP_ROOT);
}

add_action('template_redirect', 'ejls_template_redirect');
function ejls_template_redirect() {
    header("Access-Control-Allow-Origin: *");
    global $wp_query;
    if( isset( $wp_query->query['json']) ) {
        if( ! $wp_query->query['json'] ){
            if (is_home()){
                $max_no = $_GET['max'];
                $jsonld = ejls_get_archive($max_no);
            } elseif (is_single() || is_page()){
                $jsonld = ejls_get_article();
            }
            if ($jsonld == '[null]') {
                $wp_query->set_404();
                status_header(404);
                return;
            } else {
                header('Content-type: application/ld+json; charset=UTF-8');
                echo $jsonld;
                exit;
            }
        } else {
            $wp_query->set_404();
            status_header(404);
            return;
        }
    }
    if( isset($wp_query->query['jsonld-context'])) {
        if(!$wp_query->query['jsonld-context']) {
            header('Content-type: application/ld+json; charset=UTF-8');
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
function ejls_get_context() {
    /*
     * @TODO 配列で取得できるようにする
     */
    $contextData;
    if (get_option('context')) {
        $contextData[0]['vocabulary'] = esc_attr(get_option('context'));
    }
    if (get_option('iri')) {
        $contextData[0]['iri']        = esc_url(get_option('iri'));
    }

    switch (count($contextData)) {
        case 0:
            $context['@context'] = array(
                "rdf"    => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
                "rdfs"   => "http://www.w3.org/2000/01/rdf-schema#",
                "vCard"  => "http://www.w3.org/2006/vcard/ns#",
                "foaf"   => "http://xmlns.com/foaf/0.1/",
                "dc"     => "http://purl.org/dc/elements/1.1/",
                "dcterms"=> "http://purl.org/dc/terms/",
                "cal"    => "http://www.w3.org/2002/12/cal/icaltzd#",
                "geo"    => "http://www.w3.org/2003/01/geo/wgs84_pos#",
                "owl"    => "http://www.w3.org/2002/07/owl#",
                "schema" => "http://schema.org/",
                "skos"   => "http://www.w3.org/2004/02/skos/core#",
                "yafjp"  => "http://fp.yafjp.org/terms/place#",
                );
            break;
        
        case 1:
            $context['@context'] = $contextData[0]['iri'];
            break;

        default:
            foreach ($contextData as $key => $value) {
                $contextArray = array(
                    $value['vocabulary'] => $value['iri']
                );
            }
            $context["@context"] = $contextArray;
            break;
    }
    $context = json_encode($context);
    return $context;
}

add_action( 'admin_menu', 'ejls_setting_menu' );
function ejls_setting_menu(){
    add_menu_page(
        __('Make JSON-LD', 'ejls-admin-menu'),
        __('Make JSON-LD', 'ejls-admin-menu'),
        'administrator',
        'ejls-admin-menu',
        'ejls_admin_menu'
    );
}

function ejls_admin_menu(){
$twitter_account = 'hoge';
?>
<div class="wrap">
    <h2>Make JSON-LD</h2>
    <h3>Setting Vocabulary</h3>
    <p>ここで使用する語彙を登録します。</p>
<form method="post" action="" novalidate="novalidate">
<?php wp_nonce_field( 'my-nonce-key', 'ejls-admin-menu');?>
<table class="widefat form-table">
<thead>
<tr><th>　Vocabulary Name</th><th>URI</th></tr>
</thead>
<tbody>
<tr>
    <td><input name="context" type="text" id="vocabulary" value="<?php echo esc_attr(get_option('context'));?>" class="regular-text code"></td>
    <td><input name="iri" type="url" id="siteurl" value="<?php echo esc_url(get_option('iri'));?>" class="regular-text code"></td>
</tr>
</tbody></table>
<p class="submit"><input type="submit" class="button button-primary" value="変更を保存"></p>
</form>
</div>
<?php
}

add_action( 'admin_init', 'ejls_admin_init');
function ejls_admin_init()
{
    if( isset ( $_POST['ejls-admin-menu']) && $_POST['ejls-admin-menu'] ){
        if( check_admin_referer('my-nonce-key', 'ejls-admin-menu')) {
            $e = new WP_Error();
                update_option('context', trim($_POST['context']));
                update_option('iri', trim($_POST['iri']));
        } else {
            update_option('context', '');
        }
        wp_safe_redirect(menu_page_url('ejls-admin-menu', false));    
    }
}

add_action('admin_notices', 'ejls_admin_notices');
function ejls_admin_notices(){
    ?>
    <?php if($messages = get_transient('ejls-admin-errors')):?>
        <div class="updated">
            <ul>
                <?php foreach( $messages as $message):?>
                    <li><?php echo esc_html($message);?></li>
                <?php endforeach;?>
            </ul>
        </div>
        <?php endif;
}