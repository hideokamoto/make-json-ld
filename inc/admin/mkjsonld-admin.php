<?php
require_once 'mkjsonld-mapping.php';
require_once 'mkjsonld-schema.php';
require_once 'mkjsonld-top.php';
add_action(   'admin_menu', 'mkjsonld_setting_menu' );
add_action(   'admin_init', 'mkjsonld_admin_init');
add_action('admin_notices', 'mkjsonld_admin_notices');

function mkjsonld_setting_menu(){
    add_menu_page(
        __('Make JSON-LD', 'make_json_ld'),
        __('Make JSON-LD', 'make_json_ld'),
        'administrator',
        'mkjsonld-admin-menu',
        'mkjsonld_admin_menu'
    );
    add_submenu_page(
        'mkjsonld-admin-menu',
        __('Schema Settings', 'make_json_ld'),
        __('Schema Settings', 'make_json_ld'),
        'administrator',
        'mkjsonld-schema',
        'mkjsonld_schema'
    );
    add_submenu_page(
        'mkjsonld-admin-menu',
        __('Mapping', 'make_json_ld'),
        __('Mapping', 'make_json_ld'),
        'administrator',
        'mkjsonld-mapping',
        'mkjsonld_mapping'
    );
}

function mkjsonld_admin_init()
{
    if( isset ( $_POST['mkjsonld-schema']) && $_POST['mkjsonld-schema'] ){
        if( check_admin_referer('my-nonce-key', 'mkjsonld-schema')) {
            $e = new WP_Error();
                update_option('context', mkjsonld_check_context_arr());
        } else {
            update_option('context', '');
        }
        wp_safe_redirect(menu_page_url('mkjsonld-schema', false));
    }
}

function mkjsonld_check_context_arr()
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

function mkjsonld_admin_notices(){
    ?>
    <?php if($messages = get_transient('mkjsonld-admin-errors')):?>
        <div class="updated">
            <ul>
                <?php foreach( $messages as $message):?>
                    <li><?php echo esc_html($message);?></li>
                <?php endforeach;?>
            </ul>
        </div>
        <?php endif;
}
