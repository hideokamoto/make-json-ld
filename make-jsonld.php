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
function ejls_get_archive ($max_no, $contextType) {
    if (is_home()){
        $mainContents = array(
            'post_type' =>'post',
            'posts_per_page' => $max_no,
            'paged' => $paged
        );
        $the_query = new WP_Query( $mainContents );
        while ( $the_query->have_posts() ) : $the_query->the_post();
            $content = ejls_get_content($contextType);
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

function ejls_get_content ($contextType) {
    $contextUrl = get_home_url() . "/jsonld-context/";
    $postUrl = get_permalink();
    $postId = get_the_ID();
    $customFields = get_post_meta($postId);

    $context = array(
        "@context" => "{$contextUrl}",
        "@id"  => "{$postUrl}",
        );

    $customFieldKeys = array_keys($customFields);
    $matchedContext = array();
    foreach ($contextType as $contexts) {
        if (preg_grep("/^{$contexts}/", $customFieldKeys)) {
            $matchedContext = array_merge($matchedContext, preg_grep("/^{$contexts}/", $customFieldKeys));
        }
    }
    if ($matchedContext) {
        foreach ($matchedContext as $k => $v) {
            $content[$v] = $customFields[$v];
        }
    } else {
        return null;
    }

    if ($content) {
        $json = array_merge_recursive($context, $content);
    } else {
        $json = null;
    }
    return $json;
}
function ejls_get_article ($contextType) {
    if (is_page() || is_single()) {
        if (have_posts()) : while (have_posts()) : the_post();
            $jsonld[] = ejls_get_content($contextType);
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
        if( !$wp_query->query['json']){

            if (get_option('context')) {
                $contextData = get_option('context');
                //want to use array_column
                foreach ($contextData as $key => $context) {
                    $contextType[] = $context['type'];
                }
            } else {
                $contextType[] = 'schema';
            }

            if (is_home()){
                $max_no = $_GET['max'];
                $jsonld = ejls_get_archive($max_no, $contextType);
            } elseif (is_single() || is_page()){
                $jsonld = ejls_get_article($contextType);
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
    $contextData;
    if (get_option('context')) {
        $contextData        = get_option('context');
    }

    switch (count($contextData)) {
        case 0:
            $context['@context'] = array(
                "schema" => "http://schema.org/"
                );
            break;
        
        case 1:
            $context['@context'] = esc_url($contextData[0]['iri']);
            break;

        default:
            foreach ($contextData as $key => $value) {
                $contextArray[] = array(
                    esc_attr($value['type']) => esc_url($value['iri'])
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
        <?php
        $contextArr = get_option('context');
        $i = 0;
        if (!$contextArr) :
            $contextArr[0] = array(
                "type" =>"schema",
                "iri"  =>"http://schema.org/"
            );
        endif;

        foreach($contextArr as  $context):
            if ($context['type']) :?>
            <tr>
                <td><input name="context[<?php echo $i;?>][type]" type="text" id="vocabulary" value="<?php echo esc_attr($context['type']);?>" class="regular-text code"></td>
                <td><input name="context[<?php echo $i;?>][iri]" type="url" id="siteurl" value="<?php echo esc_url($context['iri']);?>" class="regular-text code"></td>
            </tr>
            <?php
            $i++;
            endif;
        endforeach;?>
        <tr>
            <td><input name="context[<?php echo $i;?>][type]" type="text" id="vocabulary" value="" class="regular-text code"></td>
            <td><input name="context[<?php echo $i;?>][iri]" type="url" id="siteurl" value="" class="regular-text code"></td>
        </tr>
    </tbody>
</table>
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
                update_option('context', ejls_check_context_arr());
        } else {
            update_option('context', '');
        }
        wp_safe_redirect(menu_page_url('ejls-admin-menu', false));    
    }
}

function ejls_check_context_arr()
{
    $contextArr = $_POST['context'];
    foreach ($contextArr as $key => $value) {
        if(array_filter($value)){
            $context[] = array_filter($value);
        }
    }
    if (!$context) {
        $context[0] = array(
            "type" =>"schema",
            "iri"  =>"http://schema.org/"
        );
    }
    return $context;
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