<?php
/**
 * Kikushima Jobs
 *
 * Plugin Name: Kikushima Jobs
 * Plugin URI:
 * Description: Add jobs shortcode to display jobs on the recruit page
 * Version:     1.0
 * Author:      kikushima
 * Author URI:  https://kikushima-japan.co.jp
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
/*
Copyright 2020 KIKUSHIMA,Inc. (email: info@kikushima-japan.co.jp)
Kikushima Jobs is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Kikushima Jobs is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Kikushima Jobs. If not, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.html.
 */
if (!defined('ABSPATH')) {
	die('Invalid request.');
}

if (!class_exists('KIKUSHIMA_Jobs')) :

	class KIKUSHIMA_Jobs
	{
		public function __construct() {}

		public function init_actions()
		{
			$this->set_language();
			add_action( 'init', array( $this, 'cptui_register_my_cpts_jobs' ) );
			add_shortcode( "jobs", array( $this, "add_jobs_shortcode" ) );
			add_action( "wp_enqueue_scripts", array( $this, "bootstrap_enqueue" ) );
		}

		public function set_language() {
			$langDir = basename( dirname(__FILE__) ) . "/languages/";
			load_plugin_textdomain( "kikushima-jobs", false,  $langDir );
		}

		public function cptui_register_my_cpts_jobs()
		{

			/**
			 * Post Type: jobs.
			 */
			$labels = array(
				"name" => "jobs",
				"singular_name" => "job",
				"menu_name" => __( "job", "kikushima-jobs" ),
				"all_items" => __( "jobs", "kikushima-jobs" ),
				"add_new" => __( "new job", "kikushima-jobs" ),
				"add_new_item" => __( "new job", "kikushima-jobs" ),
				"edit_item" => __( "edit job", "kikushima-jobs" ),
				"new_item" => __( "new job", "kikushima-jobs" ),
				"view_item" => __( "show job", "kikushima-jobs" ),
				"view_items" => __( "show job", "kikushima-jobs" ),
				"search_items" => __( "search job", "kikushima-jobs" ),
				"not_found" => __( "no such job.", "kikushima-jobs" ),
				"not_found_in_trash" => __( "no jobs in trash.", "kikushima-jobs" ),
				"archives" => __( "jobs", "kikushima-jobs" ),
			);

			$args = array(
				"label" => "jobs",
				"labels" => $labels,
				"description" => "",
				"public" => true,
				"publicly_queryable" => true,
				"show_ui" => true,
				"show_in_rest" => true,
				"rest_base" => "jobs",
				"rest_controller_class" => "WP_REST_Posts_Controller",
				"has_archive" => true,
				"show_in_menu" => true,
				"show_in_nav_menus" => true,
				"delete_with_user" => false,
				"exclude_from_search" => false,
				"capability_type" => "post",
				"map_meta_cap" => true,
				"hierarchical" => false,
				"rewrite" => array( "slug" => "jobs", "with_front" => true ),
				"query_var" => true,
				"menu_icon" => "dashicons-businessman",
				"supports" => array( "title", "editor", "thumbnail", "excerpt" ),
				'taxonomies' => array('post_tag'),
			);

			register_post_type( "jobs", $args );
		}

		public function cptui_register_my_taxes_tags()
		{

			/**
			 * Taxonomy: タグ.
			 */
			$labels = array(
				"name" => __( "tag", "kikushima-jobs" ),
				"singular_name" => __( "tag", "kikushima-jobs" ),
				"menu_name" => __( "tag", "kikushima-jobs" ),
				"all_items" => __( "tags", "kikushima-jobs" ),
				"edit_item" => __( "edit tag", "kikushima-jobs" ),
				"view_item" => __( "show tag", "kikushima-jobs" ),
				"update_item" => __( "update tag", "kikushima-jobs" ),
				"add_new_item" => __( "new tag", "kikushima-jobs" ),
				"new_item_name" => __( "new tag", "kikushima-jobs" ),
			);

			$args = array(
				"label" => __( "tag", "kikushima-jobs" ),
				"labels" => $labels,
				"public" => true,
				"publicly_queryable" => true,
				"hierarchical" => false,
				"show_ui" => true,
				"show_in_menu" => true,
				"show_in_nav_menus" => true,
				"query_var" => true,
				"rewrite" => ['slug' => 'tags', 'with_front' => true,],
				"show_admin_column" => false,
				"show_in_rest" => true,
				"rest_base" => "tags",
				"rest_controller_class" => "WP_REST_Terms_Controller",
				"show_in_quick_edit" => false,
			);
			register_taxonomy( "jobs_tags", ["jobs"], $args );
		}

		public function add_jobs_shortcode($atts)
		{

			extract(shortcode_atts(array(
				'show_excerpt' => 1,
				"show_detail" => 1,
				"posts_number" => -1
			), $atts));

			$show_excerpt = !empty($show_excerpt) && $show_excerpt == 1 ? true : false;
			$show_detail = !empty($show_detail) && $show_detail == 1 ? true : false;
			$posts_number = !empty($posts_number) && (int)$posts_number ? (int)$posts_number : -1;

			$output = '<div id="jobs" class="container">';


			$args = array(
				"post_type" => "jobs"
			);
			if ($posts_number != -1) {
				$args["posts_per_page"] = $posts_number;
			}
			$jobs_query = new WP_Query($args);

			if ($jobs_query->have_posts()) {

				while ($jobs_query->have_posts()) {
					$jobs_query->the_post();
					$output .= $this->get_job_info($show_excerpt, $show_detail);
				}

			} else {
				$output .= '<h2>' . __( "We are not hiring at the moment.", "kikushima-jobs" ) . '</h2>';
			}

			wp_reset_postdata();


			$output .= '</div>';
			return $output;

		}

		public function bootstrap_enqueue()
		{
			// JS
			if( ! wp_script_is("bootstrap", "enqueued" ) ) {
				wp_register_script(
					'bootstrap',
					plugins_url('js/bootstrap-4.5.0.min.js', __FILE__),
					array("jquery")
				);

				wp_enqueue_script("bootstrap");
			}

			if( ! wp_style_is( "bootstrap", "enqueued" ) ) {
				wp_register_style(
					'bootstrap',
					plugins_url('css/bootstrap-4.5.0.min.css', __FILE__)
				);
				wp_enqueue_style('bootstrap');
			}


			// customize css
			wp_register_style(
				"kikushima_jobs",
				plugins_url('css/style.css', __FILE__),
				array("bootstrap")
			);
			wp_enqueue_style('kikushima_jobs');
		}

		private function get_job_info($show_excerpt, $show_detail)
		{


			$id = get_the_ID();

			$title = get_the_title();

			$excerpt = get_the_excerpt();

			$tags = $this->get_job_tags(get_the_ID());

			$content = get_the_content();

			$show_detail = __( "show detail", "kikushima-jobs" );
			$close_detail = __( "close detail", "kikushima-jobs" );

			$output = <<<EOF
<style>
#jobs a.collapse-controller.collapsed:before {
    content: '$show_detail';
}

#jobs a.collapse-controller:before {
    content: '$close_detail';
}

</style>
EOF;



			$output .= <<<EOF
    <div class="card">
        <div class="row no-gutters">
EOF;

			$output .= $this->get_job_thumnails(get_the_ID());


			$output .= <<<EOF

            <div class="col-sm-8">
                <div class="card-block px-2">
                    <h3 class="card-title job-title">$title</h3>
                    <div class="job-tag-list">
                        $tags
                    </div>
                    <p class="card-text excerpt">
EOF;

			if ($show_excerpt) {
				$output .= $excerpt;
			} else {
				$output .= $content;
			}

			$output .= <<<EOF
                    </p>
                    <!--<a href="#" class="btn btn-primary">詳細を表示</a>-->
                </div>
            </div>
        </div>
        
EOF;

			if ($show_detail) {

				$output .= <<<EOF
        
        <div class="card-footer w-100 text-muted text-center">
            <a 
                class="collapse-controller collapsed" 
                data-toggle="collapse" 
                data-target="#detail-$id" 
                href="#detail-$id">    
            </a>
        </div>
        
EOF;

			}


			$output .= <<<EOF
    </div>
    
EOF;

			if ($show_detail) {
				$output .= <<<EOF
    
    <div id="detail-$id" class="collapse-detail collapse border">
        $content
        
        <div class="card-footer w-100 text-muted text-center">
            <a 
                class="collapse-controller collapsed" 
                data-toggle="collapse" 
                data-target="#detail-$id" 
                href="#detail-$id">    
            </a>
        </div>
        
    </div>
    
EOF;
			}

			$output .= <<<EOF

    <br/>

EOF;

			return $output;
		}

		private function get_job_thumnails($post_id)
		{

			$post = get_post($post_id);

			$images = array();

			if (has_post_thumbnail($post)) {
				$thumbnail_id = get_post_thumbnail_id($post);

				$thumbnail = wp_get_attachment_image_src($thumbnail_id, 'full');

				$thumbnail_src = $thumbnail[0];

				$images[] = $thumbnail_src;
			}

			if (class_exists('Dynamic_Featured_Image')) {
				global $dynamic_featured_image;
				$featured_images = $dynamic_featured_image->get_featured_images();

				foreach ($featured_images as $featured_image) {
					if ($featured_image["full"]) {
						$images[] = $featured_image["full"];
					}
				}
			}

			if (count($images) == 0) {
				return "";
			}

			if (count($images) == 1) {

				$thumbnail_src = esc_url($thumbnail_src);

				$thumbnail_html = <<<EOF
<div class="col-sm-4">
<img src="{$thumbnail_src}" class="img-fluid" alt="thumbnail_src">
</div>
EOF;
			} else {
				$thumbnail_html = <<<EOF
<div class="col-sm-4">
<!--Carousel Wrapper-->
    <div id="carousel-thumb-$post_id" class="carousel slide carousel-fade carousel-thumbnails" data-ride="carousel">
      <!--Slides-->
      <div class="carousel-inner" role="listbox">
EOF;

				foreach ($images as $index => $image) {

					$image = esc_url($image);

					$thumbnail_html .= <<<EOF
<div class="carousel-item
EOF;
					if ($index == 0) {
						$thumbnail_html .= " active";
					}

					$thumbnail_html .= <<<EOF
                    ">
          <img class="d-block w-100" src="$image" alt="$index slide">
        </div>
EOF;

				}

				$thumbnail_html .= <<<EOF
      </div>
      <!--/.Slides-->
      <!--Controls-->
      <a class="carousel-control-prev" href="#carousel-thumb-$post_id" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#carousel-thumb-$post_id" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
      
      <ol class="carousel-indicators">
      
EOF;

				foreach ($images as $index => $image) {

					$image = esc_url($image);

					$thumbnail_html .= <<<EOF
<li data-target="#carousel-thumb-$post_id" data-slide-to="$index" 
EOF;
					if ($index == 0) {
						$thumbnail_html .= ' class="active"';
					}

					$thumbnail_html .= <<<EOF
                    "><img class="d-block w-100" src="$image"
            class="img-fluid"></li>
EOF;
				}

				$thumbnail_html .= <<<EOF
      </ol>
    </div>
</div>
EOF;

			}

			return $thumbnail_html;
		}

		private function get_job_tags($post_id)
		{
			$post = get_post($post_id);

			$post_type = $post->post_type;

			$taxonomies = get_object_taxonomies($post_type, 'post_tag');

			$out = array();
			foreach ($taxonomies as $taxonomy_slug => $taxonomy) {

				$terms = get_the_terms($post_id, $taxonomy_slug);

				if (!empty($terms)) {
					foreach ($terms as $term) {
						$css_class = $term->description;
						if (!$term->description) {
							$css_class = "badge-primary";
						}

						$out[] =
							'<span class="job-tags badge badge-pill ' . esc_attr( $css_class ) . '">'
							. esc_html( $term->name )
							. "</span>";
					}
				}
			}

			return implode('', $out);
		}

	}

	$kikushima_jobs = new KIKUSHIMA_Jobs();
	add_action('plugins_loaded', array($kikushima_jobs, 'init_actions'));

endif;