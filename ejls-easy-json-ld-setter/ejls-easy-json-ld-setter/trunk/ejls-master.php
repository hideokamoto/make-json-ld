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

function ejls_get_breadcrumb(){
    global $post;
    $str ='';
    if(!is_home()&&!is_admin()){
        $str.= '<div id="breadcrumb" class="cf"><div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
        $str.= '<a href="'. home_url() .'" itemprop="url"><span itemprop="title">ホーム</span></a> &gt;</div>';
        /*
        Home URLを設定する
        {
            "@context" : "http://data-vocabulary.org/",
            "@type"    : "Breadcrumb",
            "url"      : "http://wp-kyoto.net/",
            "title"    : "WP-kyoto"
        }
        */

        if(is_category()) {
            $cat = get_queried_object();
            if($cat -> parent != 0){
                $ancestors = array_reverse(get_ancestors( $cat -> cat_ID, 'category' ));
                foreach($ancestors as $ancestor){
                    $str.='<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="'. get_category_link($ancestor) .'" itemprop="url"><span itemprop="title">'. get_cat_name($ancestor) .'</span></a> &gt;</div>';
                }
            }
        $str.='<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="'. get_category_link($cat -> term_id). '" itemprop="url"><span itemprop="title">'. $cat-> cat_name . '</span></a></div>';
        } elseif(is_page()){
            if($post -> post_parent != 0 ){
                $ancestors = array_reverse(get_post_ancestors( $post->ID ));
                foreach($ancestors as $ancestor){
                    $str.='<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="'. get_permalink($ancestor).'" itemprop="url"><span itemprop="title">'. get_the_title($ancestor) .'</span></a></div>';
                }
            }
        } elseif(is_single()){
            $categories = get_the_category($post->ID);
            $cat = $categories[0];
            if($cat -> parent != 0){
                $ancestors = array_reverse(get_ancestors( $cat -> cat_ID, 'category' ));
                foreach($ancestors as $ancestor){
                    $str.='<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="'. get_category_link($ancestor).'" itemprop="url"><span itemprop="title">'. get_cat_name($ancestor). '</span></a> &gt;</div>';
                }
            }
            $str.='<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="'. get_category_link($cat -> term_id). '" itemprop="url"><span itemprop="title">'. $cat-> cat_name . '</span></a></div>';
        } else{
            $str.='<div>'. wp_title('', false) .'</div>';
        }
        $str.='</div>';
    }
    return $str;
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