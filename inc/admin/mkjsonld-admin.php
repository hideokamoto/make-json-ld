<?php
require_once 'mkjsonld-mapping.php';
require_once 'mkjsonld-schema.php';
require_once 'mkjsonld-top.php';
add_action(   'admin_menu', 'odg_setting_menu' );
add_action(   'admin_init', 'odg_admin_init');
add_action('admin_notices', 'odg_admin_notices');

function odg_setting_menu(){
    add_menu_page(
        __('Make JSON-LD', 'make_json_ld'),
        __('Make JSON-LD', 'make_json_ld'),
        'administrator',
        'odg-admin-menu',
        'odg_admin_menu'
    );
    add_submenu_page(
        'odg-admin-menu',
        __('Schema Settings', 'make_json_ld'),
        __('Schema Settings', 'make_json_ld'),
        'administrator',
        'odg-schema',
        'odg_schema'
    );
    add_submenu_page(
        'odg-admin-menu',
        __('Mapping', 'make_json_ld'),
        __('Mapping', 'make_json_ld'),
        'administrator',
        'odg-mapping',
        'odg_mapping'
    );
}

function odg_admin_init()
{
    if( isset ( $_POST['odg-schema'] ) && $_POST['odg-schema'] ){
        if( check_admin_referer( 'my-nonce-key' , 'odg-schema') ) {
            $e = new WP_Error();
            update_option( 'odg-context' , odg_check_context_arr() );
        } else {
            update_option( 'odg-context' , '' );
        }
        wp_safe_redirect(menu_page_url('odg-schema', false));
    } elseif ( isset ( $_POST['odg-mapping'] ) && $_POST['odg-mapping'] ){
        if( check_admin_referer('my-nonce-key' , 'odg-mapping' ) ) {
            $e = new WP_Error();
            update_option( 'odg-mapping' , odg_check_context_arr( ) );
        } else {
            update_option( 'odg-mapping' , '' );
        }
        wp_safe_redirect( menu_page_url( 'odg-mapping' , false ) );
    }
}

function odg_check_context_arr()
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

function odg_admin_notices(){
    ?>
    <?php if($messages = get_transient('odg-admin-errors')):?>
        <div class="updated">
            <ul>
                <?php foreach( $messages as $message):?>
                    <li><?php echo esc_html($message);?></li>
                <?php endforeach;?>
            </ul>
        </div>
        <?php endif;
}
