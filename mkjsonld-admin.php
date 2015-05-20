<?php
add_action(   'admin_menu', 'mkjsonld_setting_menu' );
add_action(   'admin_init', 'mkjsonld_admin_init');
add_action('admin_notices', 'mkjsonld_admin_notices');

function mkjsonld_setting_menu(){
    add_menu_page(
        __('Make JSON-LD', 'mkjsonld-admin-menu'),
        __('Make JSON-LD', 'mkjsonld-admin-menu'),
        'administrator',
        'mkjsonld-admin-menu',
        'mkjsonld_admin_menu'
    );
}

function mkjsonld_admin_menu(){
?>
<div class="wrap">
    <h2>Make JSON-LD</h2>
    <h3>Setting Vocabulary</h3>

<form method="post" action="" novalidate="novalidate">
<?php wp_nonce_field( 'my-nonce-key', 'mkjsonld-admin-menu');?>
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
<p class="submit"><input type="submit" class="button button-primary" value="Save Change"></p>
</form>
</div>
<?php
}

function mkjsonld_admin_init()
{
    if( isset ( $_POST['mkjsonld-admin-menu']) && $_POST['mkjsonld-admin-menu'] ){
        if( check_admin_referer('my-nonce-key', 'mkjsonld-admin-menu')) {
            $e = new WP_Error();
                update_option('context', mkjsonld_check_context_arr());
        } else {
            update_option('context', '');
        }
        wp_safe_redirect(menu_page_url('mkjsonld-admin-menu', false));
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
