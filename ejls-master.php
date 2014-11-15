<?php
/**
 * @package EJLS Easy Json-ld Setter
 * @version 1.2
 */
/*
Plugin Name: EJLS Easy Json-ld Setter
Plugin URI: http://wordpress.org/plugins/ejls-easy-json-ld-setter/
Description: Easy set JSON-ld data on your blog.
Author: Hidetaka Okamoto
Version: 1.2
Author URI: http://wp-kyoto.net/
*/
add_action('wp_head','ejls_insert_json_ld');
function ejls_esc_html($ejlsCnt) {
    return esc_html(str_replace(array("\r\n","\n","\r","\t"), "", strip_tags($ejlsCnt)));
}

function ejls_get_article () {
    if (is_page() || is_single()) {
        if (have_posts()) : while (have_posts()) : the_post();
            $type = 'Article';
            $name = get_the_title();
            $authorType = 'Person';
            $authorName = get_the_author();
            $dataPublished = get_the_date('Y-n-j');
            $image = wp_get_attachment_url(get_post_thumbnail_id());
            $articleBody =ejls_esc_html(get_the_content());
            $url = get_permalink();
            $publisherType = 'Organization';
            $publisherName = get_bloginfo('name');
            $json= '{
                    "@type" : "'.$type.'",
                    "name" : "'.$name.'",
                    "author" : {
                      "@type" : "'.$authorType.'",
                      "name" : "'.$authorName.'"
                     },
                    "datePublished" : "'.$dataPublished.'",
                    "image" : "'.$image.'",
                    "articleBody" : "'.$articleBody.'",
                    "url" : "'.$url.'",
                    "publisher" : {
                      "@type" : "'.$publisherType.'",
                      "name" : "'.$publisherName.'"
                    }
                    }';
        endwhile; endif;
        rewind_posts();
        return $json;
    }
}
function ejls_get_search_Action(){
    if (is_front_page()) {
    $homeUrl = get_home_url();
    $json = '
    "potentialAction": {
        "@type": "SearchAction",
          "target": "' . $homeUrl . '/?s={search_term}",
          "query-input": "required name=search_term"
    },';
    return $json;
    }
}

function ejls_insert_json_ld(){
    $searchAction = ejls_get_search_Action();
    $article = ejls_get_article();
    $homeUrl = get_home_url();

    $json = '
    <script type="application/ld+json">
    {
        "@context" : "http://schema.org",
        "@type": "WebSite",
        "url": "' . $homeUrl . '",
        ' . $searchAction . '
        "@graph" : [
          ' . $article . '
        ]
    }
    </script>';
    echo $json;
}

?>