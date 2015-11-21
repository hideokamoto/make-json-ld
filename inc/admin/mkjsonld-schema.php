<?php
function mkjsonld_schema(){
?>
<div class="wrap">
    <h2><?php printf(__('Make JSON-LD','make_json_ld'));?></h2>
    <h3><?php printf(__('Setting Vocabulary','make_json_ld'));?></h3>

    <form method="post" action="" novalidate="novalidate">
        <?php wp_nonce_field( 'my-nonce-key', 'mkjsonld-schema');?>
        <table class="widefat form-table">
            <thead>
                <tr><th>　<?php printf(__('Vocabulary Name','make_json_ld'));?></th><th>URI</th></tr>
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
        <p class="submit">
          <input type="submit"
            class="button button-primary"
            value="<?php printf(__('Save Change','make_json_ld'));?>">
        </p>
    </form>
</div>
<?php
}
