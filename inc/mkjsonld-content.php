<?php
class mkjsonldContent
{
    function get_archive ($contextType, $query) {
        $the_query = new WP_Query( $query );
        while ( $the_query->have_posts() ) : $the_query->the_post();
            $content = $this->get_content($contextType);
            if(!$content){ continue; }
            $jsonld[] = $content;
        endwhile;
        if(!isset($jsonld)){ return null;}
        $jsonld = json_encode($jsonld, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        return $jsonld;
    }

    function get_content ($contextType) {
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

    function get_article ($contextType) {
        if (is_page() || is_single()) {
            if (have_posts()) : while (have_posts()) : the_post();
                $jsonld[] = $this->get_content($contextType);
            endwhile; endif;
            rewind_posts();
            $jsonld = json_encode($jsonld, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            return $jsonld;
        }
    }

    function get_context() {
        $contextData = array();
        if (get_option('context')) {
            $contextData = get_option('context');
        }

        $context = $this->get_context_data($contextData);
        $context = json_encode($context, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        return $context;
    }

    //Tested
    function get_context_data($contextData){
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
        return $context;
    }
}
