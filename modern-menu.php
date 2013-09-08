<?php
/*
Plugin Name: Modern Menu
Plugin URI: http://viktoriya.tsymbal.ws/opensource/modernmenu
Description: Modern style CSS3 menu
Version: 0.3.2
Author: Viktoriya Tsymbal
Author URI: http://viktoriya.tsymbal.ws
License: GPL2
*/
function style_init(){
    global $post;
    wp_register_style('modernMenuDefaultStyle', plugin_dir_url()."modern-menu/css/default.css");
    wp_enqueue_style('modernMenuDefaultStyle');
    wp_register_style('modernMenuComponentStyle', plugin_dir_url()."modern-menu/css/component.css");
    wp_enqueue_style('modernMenuComponentStyle');
    wp_register_script( 'modernMenuModernizerScript', plugin_dir_url()."modern-menu/js/modernizr.custom.js");
    wp_enqueue_script( 'modernMenuModernizerScript' );
    wp_register_script( 'modernMenuToucheffectsScript', plugin_dir_url()."modern-menu/js/toucheffects.js");
    wp_enqueue_script( 'modernMenuToucheffectsScript' );
    
    $dbackground = get_post_meta($post->ID, 'mmenu-description-background', true);
    if(empty($dbackground)){
        $dbackground = "#2c3f52";
    }
    $dcolor = get_post_meta($post->ID, 'mmenu-description-color', true);
    if(empty($dcolor)){
        $dcolor = "#ed4e6e";
    }
    $lbackground = get_post_meta($post->ID, 'mmenu-link-background', true);
    if(empty($lbackground)){
        $lbackground = "#ed4e6e";
    }
    $lcolor = get_post_meta($post->ID, 'mmenu-link-color', true);
    if(empty($lcolor)){
        $lcolor = "#fff";
    }
    $custom_css="
        .grid figcaption {
            background: {$dbackground};
            color: {$dcolor};
        }
        .grid figcaption a{
            background: {$lbackground};
            color: {$lcolor};
        }
	/* quick fix */
	.gdl-page-content{
	    width: 720px;
	    margin-left: -100px;
	}
	.grid li {
	    width:330px;
	}
    ";
    wp_add_inline_style( 'modernMenuComponentStyle', $custom_css );
    wp_register_style('modernMenuUserStyle', plugin_dir_url()."modern-menu/css/user.css");
    wp_enqueue_style('modernMenuUserStyle');
}
add_action('wp_enqueue_scripts', 'style_init');
/* */

function rt($title, $description, $link, $link_title, $img){
    /* TODO: add custom style to template */
    $template = "<li>
                      <figure>
                          <div><img src=\"{$img}\" alt=\"{$title}\"></div>
                          <figcaption>
                              <div class=\"item-title-splash\"></div>
                              <h3>{$title}</h3>
                              <span>{$description}</span>
                              <a href=\"{$link}\">{$link_title}</a>
                          </figcaption>
                      </figure>
                  </li>";
    return $template;
}


// [mmenu cat="menu"]
function modern_menu( $atts ) {
	extract( shortcode_atts( array(
		'cat' => 'NC',
		'template' => 4,
        'count' => 10,
        'order' => 'ASC',
        'orderby' => 'title',
        'default_link_title' => "Перейти"
        
	), $atts ) );
    
    if($cat == "NC"){
        return "<!-- NO CATEGORY-->";
    }
    
    // quick fix, because style 6 not work correctly
    if($template < 1 || $template > 7 || $template == 6){
        $template = 4;
    }
    
    $category_for_post = get_cat_ID( $cat );
    
    $args_for_menu_posts = array( 'category' => $category_for_post, 'posts_per_page'  => $count, 'order'=> $order, 'orderby' => $orderby );
    $menu_posts = get_posts( $args_for_menu_posts );
    $render = "";
    foreach( $menu_posts as $post ) :
        try{
            $img_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'large');
        }catch(Exception $e){}
        try {
            $link_url = get_post_meta($post->ID, 'mmenu-link', true);
            if(empty($link_url)){
                $post_id = get_post_meta($post->ID, 'mmenu-page-id', true);
                if(empty($post_id)){
                    $post_title = get_post_meta($post->ID, 'mmenu-page-title', true);
                    if(!empty($post_title)){
                        $post = get_page_by_title($post_title);
                        $post_id = $post->ID;
                    }
                }
                if(!empty($post_id)){
                    $link_url = get_permalink($post_id);
                }
            }
        }catch(Exception $e){}
        try{
            $link_title = get_post_meta($post->ID, 'mmenu-link-title', true);
            if(empty($link_title)){
                $link_title = $default_link_title;
            }
        }catch(Exception $e){}
        $render = $render.rt($post->post_title, $post->post_content, $link_url, $link_title, $img_url[0]);
    endforeach;
    $ret = "
    <!-- modern menu -->
    <ul class=\"grid cs-style-".$template."\">
    {$render}
    </ul>
    <!-- / modern menu -->";
	return $ret;
}

add_shortcode( 'mmenu', 'modern_menu' );

?>