<?php

/*
Plugin Name: Ajax Portfolio
Plugin URI: https://github.com/Farhansadiqmullick/ajax-portfolio.git
Description: Creates Portfolio and Load with Ajax
Version: 1.0
Author: Farhan Mullick
License: GPLv2 or later
Text Domain: ajax-port
Domain Path: /languages/
*/
require_once "class.datatable.php";
class AJAX_PORT
{

    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'load_ajax_port_textdomain'));
        add_action('admin_enqueue_scripts', array($this, 'plugin_assets_load'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_assets_load'));
        add_action('init', array($this, 'register_cpt'));
        add_shortcode('portfolio_items', array($this, 'display_portfolio'));

        //AJAX loading
        add_action('wp_ajax_ajax-portfolio', array($this, 'ajax_portfolio_fetch'));
        add_action('wp_ajax_nopriv_ajax-portfolio',  array($this, 'ajax_portfolio_fetch'));

        //hooks for display columns
        add_action('manage_posts_columns', array($this, 'col_sort_manage_post_column'));
        add_action('manage_posts_custom_column', array($this, 'col_sort_manage_custom_column'), 10, 2);
    }

    function load_ajax_port_textdomain()
    {
        load_plugin_textdomain('ajax-port', false, plugin_dir_path(__FILE__) . '/languages');
    }

    function plugin_assets_load()
    {
    }

    function frontend_assets_load()
    {
        global $wp_query;
        $ajaxurl = admin_url("admin-ajax.php");
        wp_enqueue_style('style-css', plugin_dir_url(__FILE__) . 'assets/css/style.css', null, rand(111, 999), 'all');
        wp_enqueue_script('main-js', plugin_dir_url(__FILE__) . 'assets/js/main.js', ['jquery'], rand(111, 999), true);
        wp_localize_script(
            'main-js',
            'portfolio_fetch',
            array(
                'ajaxurl' => $ajaxurl,
                'category' => get_the_category(),
            ),
        );
    }

    function register_cpt()
    {
        $labels = array(
            "name"               => __("Portfolio", "ajax-port"),
            "singular_name"      => __("Portfolio", "ajax-port"),
        );

        $args = array(
            "label"               => __("Portfolio", "ajax-port"),
            "labels"              => $labels,
            "description"         => "",
            "public"              => false,
            "publicly_queryable"  => true,
            "show_ui"             => true,
            'taxonomies'          => array('category', 'post_tag'),
            "delete_with_user"    => false,
            "show_in_rest"        => true,
            "has_archive"         => true,
            "show_in_menu"        => true,
            "show_in_nav_menus"   => false,
            "capability_type"     => "post",
            "map_meta_cap"        => true,
            "hierarchical"        => false,
            "rewrite"             => array("slug" => "portfolio", "with_front" => true),
            "query_var"           => true,
            "supports"           => array('title', "excerpt", "page-attributes", 'thumbnail', "custom-fields"),
        );

        register_post_type("portfolio", $args);
    }

    function col_sort_manage_post_column($columns)
    {
        unset($columns['date']);
        unset($columns['categories']);
        $columns['id'] = __("Portfolio ID", "ajax_port");
        $columns['thumbnail'] = __("Feature Image", "ajax_port");
        $columns['categories'] = __('Category', "ajax_port");
        $columns['date'] = __('Date', "ajax_port");
        return $columns;
    }

    function col_sort_manage_custom_column($column, $post_id)
    {
        if ('id' == $column) {
            echo $post_id;
        } elseif ('thumbnail' == $column) {
            $thumbnail = get_the_post_thumbnail($post_id, array(50, 50));
            echo $thumbnail;
        }
    }

    function display_portfolio()
    {

        $args = [
            'post_type' => 'portfolio',
            'posts_per_page' => -1,
            'cat' => get_the_category(),
        ];

        $portfolio_query = new WP_Query($args);
        $portfolio = [];

        while ($portfolio_query->have_posts()) {
            $portfolio_query->the_post();
            $portfolio[] = array(
                'title' => get_the_title(get_the_ID()),
                'link' => get_the_permalink(get_the_ID()),
                'id' => get_the_ID(),
                'excerpt' => get_the_excerpt(get_the_ID()),
                'image' => get_the_post_thumbnail_url(get_the_ID(), array(300, 300)),
                'price' => get_post_meta(get_the_ID(), 'price'),
                'category' => get_the_category(get_the_ID()),
            );
        }
        wp_reset_query();
?>
        <div class="menu">
            <!--title-->
            <div class="title">
                <h2>Our Menu</h2>
                <div class="underline"></div>
            </div>
            <!--Filter Buttons-->
            <div class="btn-container">
                <button type="button" class="filter-btn" data-category="all">All</button>
                <?php for ($i = 0; $i < count($portfolio); $i++) {
                    echo '<button type="button" class="filter-btn" data-category="' . $portfolio[$i]['category'][0]->category_nicename . '">' . $portfolio[$i]['category'][0]->name . '</button>' . '</br>';
                } ?>
            </div>
            <div class="portfolio_load"></div>
            <!--menu items-->
            <div class="section-center">
                <?php for ($i = 0; $i < count($portfolio); $i++) : ?>
                    <article class="menu-item" id="<?php echo $portfolio[$i]['category'][0]->category_nicename; ?>">
                        <a class="portfolio-image" href="<?php echo $portfolio[$i]['link']; ?>" data-id="<?php echo $portfolio[$i]['id'] ?>"><img src="<?php echo $portfolio[$i]['image'] ?>" class="photo" alt="image-1"></a>
                        <div class="item-info">
                            <header>
                                <a class="portfolio-image" href="<?php echo $portfolio[$i]['link']; ?>" data-id="<?php echo $portfolio[$i]['id'] ?>">
                                    <h4><?php echo $portfolio[$i]['title']; ?></h4>
                                </a>
                                <h4 class="price"><?php echo $portfolio[$i]['price'][0] ?></h4>
                            </header>
                            <p class="item-text">
                                <?php echo $portfolio[$i]['excerpt']; ?>
                            </p>
                        </div>
                    </article>
                <?php endfor; ?>
            </div>
        </div>
        <?php
    }


    function ajax_portfolio_fetch()
    {
        $args = [
            'posts_per_page' => '1',
            'p' => intval($_POST['post_id']),
            'post_type' => get_post_type($_POST['post_id']),
        ];

        // it is always better to use WP_Query but not here
        $query = new WP_Query($args);
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="ajax_item">
                        <a class="portfolio-image" href="<?php echo get_the_permalink(get_the_ID()); ?>"><img src="<?php echo get_the_post_thumbnail_url(get_the_ID()); ?>" class="photo" alt="Portfolio Image"></a>
                        <div class="item-info">
                            <header>
                            <a class="portfolio-image" href="<?php echo get_the_permalink(get_the_ID());?>" data-id="<?php echo get_the_ID(); ?>"><h4><?php echo get_the_title(get_the_ID()); ?></h4></a>
                                <h4 class="price"><?php echo get_post_meta(get_the_ID(), 'price')[0]; ?></h4>
                            </header>
                            <p class="item-text">
                                <?php echo get_the_excerpt(get_the_ID()); ?>
                            </p>
                        </div>
                    </div>
                
<?php
            endwhile;
        endif;
        wp_reset_postdata();
        die();
    }
}

new AJAX_PORT();
