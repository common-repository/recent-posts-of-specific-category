<?php
/*
Plugin Name: Recent posts of specific category
Plugin URI: http://dwm.me/archives/4747
Description: This is a widget that displays a post list that belong to it by selecting the category. Thumbnail image can also be displayed.
Version: 1.0.1
Author: dwm.me
Author URI: http://dwm.me/
License: The BSD 2-Clause License
Text Domain: recent-posts-of-specific-category
Domain Path: /languages/
*/

define('DOMAIN_RPOSC', 'recent-posts-of-specific-category');

load_plugin_textdomain(
    DOMAIN_RPOSC,
    false,
    dirname(plugin_basename(__FILE__)) . '/languages/'
);

class Widget_Recent_Posts_Of_Specific_Category extends WP_Widget{
    public function __construct(){
        $id_base = 'recent_posts_of_specific_category';

        $name = __(
            'Recent posts of specific category',
            DOMAIN_RPOSC
        );

        $classname = sprintf(
            '%s %s',
            'widget_recent_entries',
            'widget_' . $id_base
        );

        $description = __(
            'Displays a post list that belong to it by selecting the category.',
            DOMAIN_RPOSC
        );

        $widget_options = array(
            'classname'   => $classname,
            'description' => $description
        );

        parent::__construct($id_base, $name, $widget_options);

        $this->category_text       = 'category';
        $this->title_text          = 'title';
        $this->show_count_text     = 'number';
        $this->blank_text          = 'blank';
        $this->show_date_text      = 'show_date';
        $this->br_text             = 'br_before_date';
        $this->thumbnail_text      = 'show_thumbnail';
        $this->thumb_width_text    = 'thumbnail_width';
        $this->img_square_text     = 'img_square';
        $this->extract_img_text    = 'extract_img_from_post';
        $this->default_thumb_text  = 'default_thumbnail';
        $this->show_def_thumb_text = 'show_default_thumbnail';
        $this->default_thumnail    = 'image/w-logo-blue.png';
        $this->default_show_count  = 5;
        $this->default_thumb_width = 45;
    }

    public function widget($args, $ins){
        $id    = $this->get_param_isset($ins, $this->category_text, 1);
        $title = $ins[$this->title_text];

        if(empty($title)){
            $text          = __('Recent Posts of %s', DOMAIN_RPOSC);
            $category_name = get_cat_name($id);
            $title         = sprintf($text, $category_name);
        }

        printf(
            '%s%s%s%s%s%s<ul>%s',
            $args['before_widget'],
            PHP_EOL,
            $args['before_title'],
            apply_filters('widget_title', $title),
            $args['after_title'],
            PHP_EOL,
            PHP_EOL
        );

        $count = $this->get_param_empty(
            $ins,
            $this->show_count_text,
            $this->default_show_count
        );

        $contents = get_posts(array(
            'category' => $id,
            'posts_per_page' => $count
        ));

        if($contents){
            $blank_flag = $this->get_param_isset(
                $ins,
                $this->blank_text,
                false
            );

            $date_flag  = $this->get_param_isset(
                $ins,
                $this->show_date_text,
                false
            );

            $thumb_flag = $this->get_param_isset(
                $ins,
                $this->thumbnail_text,
                false
            );

            $blank    = $blank_flag ? ' target="_blank"' : '';
            $li_class = '';

            if($date_flag){
                $br_flag = $this->get_param_isset($ins, $this->br_text, false);
                $br      = $br_flag ? '<br />' : ' ';
                $date_format = get_option('date_format');
            }

            if($thumb_flag){
                $li_class = ' class="with_image"';

                $extract_img = $this->get_param_isset(
                    $ins,
                    $this->extract_img_text,
                    false
                );

                $default_thumb = $this->get_param_empty(
                    $ins,
                    $this->default_thumb_text,
                    ''
                );

                $def_thumb_flag = $this->get_param_isset(
                    $ins,
                    $this->show_def_thumb_text,
                    false
                );

                if(empty($default_thumb) && $def_thumb_flag){
                    $default_thumb = plugins_url(
                        $this->default_thumnail,
                        __FILE__
                    );
                }

                $thumb_width = $this->get_param_empty(
                    $ins,
                    $this->thumb_width_text,
                    $this->default_thumb_width
                );

                $square_flag = $this->get_param_isset(
                    $ins,
                    $this->img_square_text,
                    false
                );

                $thumb_style = sprintf(
                    'width:%dpx;height:%s;',
                    $thumb_width,
                    $square_flag ? sprintf('%dpx', $thumb_width) : 'auto'
                );
            }

            foreach($contents as $post){
                $date      = '';
                $thumbnail = '';
                $link      = get_permalink($post->ID);
                $title     = trim(strip_tags($post->post_title));

                if($date_flag){
                    $date = sprintf(
                        '%s<span class="post-date">%s</span>',
                        $br,
                        get_the_date($date_format, $post->ID)
                    );
                }

                if($thumb_flag){
                    $date .= '</span>';

                    $attr = array(
                        'style' => $thumb_style,
                        'alt'   => $title
                    );

                    if($blank_flag){
                        $attr['target'] = '_blank';
                    }

                    $thumbnail = get_the_post_thumbnail(
                        $post->ID,
                        'thumbnail',
                        $attr
                    );

                    if(!empty($thumbnail)){
                        $thumbnail = sprintf(
                            '<a href="%s"%s>%s</a>',
                            $link,
                            $blank,
                            $thumbnail
                        ) . '<span>';
                    }else{
                        if($extract_img){
                            $thumbnail_url = $this->ext_img(
                                $post,
                                $default_thumb
                            );
                        }else{
                            $thumbnail_url = $default_thumb;
                        }

                        if(!empty($thumbnail_url)){
                            $html = '<a href="%s"%s>'
                                . '<img src="%s" alt="%s" style="%s" />'
                                . '</a>';

                            $thumbnail = sprintf(
                                $html,
                                $link,
                                $blank,
                                $thumbnail_url,
                                $title,
                                $thumb_style
                            ) . '<span>';
                        }else{
                            $thumbnail = sprintf(
                                '<span style="margin-left:%dpx;">',
                                $thumb_width + 10
                            );
                        }
                    }
                }

                printf(
                    '<li%s>%s<a href="%s"%s>%s</a>%s</li>%s',
                    $li_class,
                    $thumbnail,
                    $link,
                    $blank,
                    $title,
                    $date,
                    PHP_EOL
                );
            }
        }

        print '</ul>' . PHP_EOL . $args['after_widget'] . PHP_EOL;
    }

    public function update($new, $old){
        $ins       = $old;
        $category  = $this->category_text;
        $title     = $this->title_text;
        $count     = $this->show_count_text;
        $blank     = $this->blank_text;
        $showdate  = $this->show_date_text;
        $br        = $this->br_text;
        $thumb     = $this->thumbnail_text;
        $width     = $this->thumb_width_text;
        $square    = $this->img_square_text;
        $extract   = $this->extract_img_text;
        $def_thumb = $this->default_thumb_text;
        $def_thumb_flag = $this->show_def_thumb_text;

        $show_count  = (int)$new[$count];
        $thumb_width = (int)$new[$width];

        if($show_count <= 0){
            $show_count = $this->default_show_count;
        }

        if($thumb_width <= 0){
            $thumb_width = $this->default_thumb_width;
        }

        $ins[$category]  = (int)$new[$category];
        $ins[$title]     = trim(strip_tags($new[$title]));
        $ins[$count]     = $show_count;
        $ins[$blank]     = $this->instance($new, $blank);
        $ins[$showdate]  = $this->instance($new, $showdate);
        $ins[$br]        = $this->instance($new, $br);
        $ins[$thumb]     = $this->instance($new, $thumb);
        $ins[$width]     = $thumb_width;
        $ins[$square]    = $this->instance($new, $square);
        $ins[$extract]   = $this->instance($new, $extract);
        $ins[$def_thumb] = trim(strip_tags($new[$def_thumb]));
        $ins[$def_thumb_flag] = $this->instance($new, $def_thumb_flag);

        $alloptions = wp_cache_get('alloptions', 'options');

        if(isset($alloptions[$this->option_name])){
            delete_option($this->option_name);
        }

        return $ins;
    }

    public function form($instance){
        $cats        = get_categories();
        $id          = $this->get_field_id($this->category_text);
        $name        = $this->get_field_name($this->category_text);
        $category    = __('Category:', DOMAIN_RPOSC);
        $hr          = PHP_EOL . '<hr />' . PHP_EOL;
        $html_bold   = '<p style="font-weight:bold;">%s%s</p>';
        $label       = '<label for="%s">%s</label>';
        $select_head = '<select class="widefat" id="%s" name="%s">' . PHP_EOL;

        print '<p style="font-weight:bold;">' . PHP_EOL;
        printf($label . PHP_EOL, $id, $category);
        printf($select_head, $id, $name);

        foreach($cats as $c){
            $val      = $instance[$this->category_text];
            $selected = $c->cat_ID == $val ? ' selected' : '';

            printf(
                '<option value="%d"%s>%s</option>' . PHP_EOL,
                $c->cat_ID,
                $selected,
                $c->name
            );
        }

        print '</select>' . PHP_EOL . '</p>';

        $this->part_str(
            $instance,
            $this->title_text,
            __('Title:', DOMAIN_RPOSC),
            __('Widget header title', DOMAIN_RPOSC)
        );

        $this->part_int(
            $instance,
            $this->show_count_text,
            __('Number of posts to show:'),
            $this->default_show_count
        );

        $this->part_bool(
            $instance,
            $this->blank_text,
            __('Target blank?', DOMAIN_RPOSC)
        );

        print $hr;

        $this->part_bool(
            $instance,
            $this->show_date_text,
            __('Display post date?'),
            $html_bold
        );

        $this->part_bool(
            $instance,
            $this->br_text,
            __('BR before date?', DOMAIN_RPOSC)
        );

        print $hr;

        $this->part_bool(
            $instance,
            $this->thumbnail_text,
            __('Display thumbnail?', DOMAIN_RPOSC),
            $html_bold
        );

        $this->part_int(
            $instance,
            $this->thumb_width_text,
            __('width:', DOMAIN_RPOSC),
            $this->default_thumb_width,
            '<p>%s%spx</p>'
        );

        $this->part_bool(
            $instance,
            $this->img_square_text,
            __('Square image?', DOMAIN_RPOSC)
        );

        $this->part_bool(
            $instance,
            $this->extract_img_text,
            __('Extract image from post?', DOMAIN_RPOSC)
        );

        $this->part_str(
            $instance,
            $this->default_thumb_text,
            __('Default thumbnail:', DOMAIN_RPOSC),
            'http://'
        );

        $this->part_bool(
            $instance,
            $this->show_def_thumb_text,
            __('Display wordpress logo, If no image?', DOMAIN_RPOSC)
        );
    }

    protected function instance($instance, $name){
        return isset($instance[$name]) ? (bool)$instance[$name] : false;
    }

    protected function get_param_isset($ins, $name, $sub){
        return isset($ins[$name]) ? $ins[$name] : $sub;
    }

    protected function get_param_empty($ins, $name, $sub){
        return !empty($ins[$name]) ? $ins[$name] : $sub;
    }

    protected function part_init($name, $html){
        $name = trim($name);
        $html = trim($html);

        return array(
            'id'   => $this->get_field_id($name),
            'name' => $this->get_field_name($name),
            'html' => empty($html) ? '<p>%s%s</p>' : $html
        );
    }

    protected function label($id, $val){
        return sprintf('<label for="%s">%s</label>', $id, $val);
    }

    protected function input($vals){
        $temp = '';

        foreach ($vals as $k => $v){
            $temp .= sprintf('%s="%s" ', $k, (string)$v);
        }

        return sprintf('<input %s/>', $temp);
    }

    protected function part_str($ins, $name, $label, $ph = '', $html = ''){
        $params = $this->part_init($name, $html);
        $val    = isset($ins[$name]) ? esc_attr($ins[$name]) : '';

        $input = array(
            'name'        => $params['name'],
            'id'          => $params['id'],
            'class'       => 'widefat',
            'type'        => 'text',
            'value'       => $val,
            'placeholder' => $ph
        );

        printf(
            $params['html'],
            $this->label($params['id'], $label),
            $this->input($input)
        );
    }

    protected function part_int($ins, $name, $label, $val = 0, $html = ''){
        $params = $this->part_init($name, $html);

        if(!empty($ins[$name])){
            $val = absint($ins[$name]);
        }

        $input = array(
            'name'  => $params['name'],
            'id'    => $params['id'],
            'type'  => 'text',
            'value' => $val,
            'size'  => '3' 
        );

        printf(
            $params['html'],
            $this->label($params['id'], $label),
            $this->input($input)
        );
    }

    protected function part_bool($ins, $name, $label, $html = ''){
        $params = $this->part_init($name, $html);

        $input = array(
            'name'  => $params['name'],
            'id'    => $params['id'],
            'class' => 'checkbox',
            'type'  => 'checkbox'
        );

        if($this->instance($ins, $name)){
            $input['checked'] = 'checked';
        }

        printf(
            $params['html'],
            $this->input($input),
            $this->label($params['id'], $label)
        );
    }

    protected function ext_img($post, $default_thumb){
        $content = preg_replace('/<!--.*?(-->)/s', '', $post->post_content);
        $matches = array();

        $ret = preg_match_all(
            '/<img [^>]*src\s*=\s*["\'](.+?)["\'][^>]*>/',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach($matches as $m){
            if(false === strpos($m[0], 'width="1"')){
                if(false === strpos($m[0], 'height="1"')){
                    return trim($m[1]);
                }
            }
        }

        return $default_thumb;
    }
}

function register_widget_recent_post_of_specific_category(){
    register_widget('Widget_Recent_Posts_Of_Specific_Category');
}

add_action('widgets_init', 'register_widget_recent_post_of_specific_category');

if(!is_admin()){
    $src = plugins_url('/recent-posts-of-specific-category.css', __FILE__);
    wp_enqueue_style(DOMAIN_RPOSC, $src);
}
