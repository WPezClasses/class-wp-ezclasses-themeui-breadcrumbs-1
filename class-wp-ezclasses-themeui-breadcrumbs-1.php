<?
/** 
 * WordPress theme breadcrumbs control
 *
 * TODO - long desc (@link http://)
 *
 * PHP version 5.3
 *
 * LICENSE: TODO
 *
 * @package WP ezClasses
 * @author Mark Simchock <mark.simchock@alchemyunited.com>
 * @since 0.5.0
 * @license TODO
 */
 
 /*
 * == Change Log == 
 *
 * --- 
*/

if ( !defined('ABSPATH') ) {
	header('HTTP/1.0 403 Forbidden');
    die();
}
// -- TODO -----


if (! class_exists('Class_WP_ezClasses_ThemeUI_Breadcrumbs_1') ) {
  class Class_WP_ezClasses_ThemeUI_Breadcrumbs_1 extends Class_WP_ezClasses_Master_Singleton {
    
	protected $_arr_init;
	
	public function __construct(){
	  parent::__construct();
	}
		
		/**
		 * Kinda like the __construct(), but different. the get_instance() in the master parent calls the ez_init()
		 */
		public function ez__construct($arr_args = ''){
		
		  $arr_init_defaults = $this->init_defaults();
		
		  $this->_arr_init = WPezHelpers::ez_array_merge(array($arr_init_defaults, $arr_args));
		  
		}
		
		protected function init_defaults(){
		
		  $arr_defaults = array(
		    'echo' => false,
		    'filters' => false,
			'validation' => false,
			);
		  return $arr_defaults;
		}

		/**
		 *
		 */
		public function breadcrumbs($arr_args = '') {

			// are we going to echo or return the str_return
			$bool_echo = $this->_arr_init['echo'];
			if ( isset($arr_args['echo']) && is_bool($arr_args['echo']) ){
				$bool_echo = $arr_args['echo'];
			}
			
			// Validation
			
			if ( WPezHelpers::ez_array_pass($arr_args) ){
			
			  $arr_args = array_merge($this->breadcrumbs_defaults(), $arr_args);
			} else {
			
			  $arr_args = $this->breadcrumbs_defaults();	
			}
					
			$str_delimiter = '<span class="' . $arr_args['separator_class'].'"></span>'; 
			$str_home = $arr_args['home'];  
			$str_before = $arr_args['before'];  // tag before the current crumb
			$str_after = $arr_args['after'];  // tag after the current crumb

			$str_to_return = '' ;
			
				global $post;
				
				$str_to_return .= '<ul class="breadcrumb">';
				$str_home_link = home_url();
				$str_to_return .= '<li><a href="' . $str_home_link . '">' . $str_home . '</a></li> ' . $str_delimiter . ' ';

				if ( is_category() ) {
				
					global $wp_query;
					
					$obj_cat = $wp_query->get_queried_object();
					$str_this_cat = $obj_cat->term_id;
					$obj_this_cat = get_category($str_this_cat);
					$parentCat = get_category($obj_this_cat->parent);
					
					if ($obj_this_cat->parent != 0) {
						$str_to_return .= (get_category_parents($parentCat, TRUE, ' ' . $str_delimiter . ' '));
					}
					
					$str_to_return .=  $str_before . sanitize_text_field($arr_args['category_label']) . ' "' . single_cat_title('', false) . '"' . $str_after;

				} elseif ( is_day() ) {
				
					$str_to_return .= '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li> ' . $str_delimiter . ' ';
					$str_to_return .= '<li><a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a></li> ' . $str_delimiter . ' ';
					$str_to_return .=  $str_before . get_the_time('d') . $str_after;

				} elseif ( is_month() ) {
				
					$str_to_return .= '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li> ' . $str_delimiter . ' ';
					$str_to_return .= $str_before . get_the_time('F') . $str_after;
					
				} elseif ( is_year() ) {
				
					$str_to_return .= $str_before . 'Year: '. get_the_time('Y') . $str_after;
					
				} elseif ( is_single() && ! is_attachment() ) {
				
					if ( get_post_type() != 'post' ) {
					
						$post_type = get_post_type_object(get_post_type());
						$slug = $post_type->rewrite;
						$str_to_return .= '<li><a href="' . $str_home_link . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a></li> ' . $str_delimiter . ' ';
						$str_to_return .= $str_before . get_the_title() . $str_after;
						
					} else {
					
						$cat = get_the_category(); $cat = $cat[0];
						$str_to_return .= get_category_parents($cat, TRUE, ' ' . $str_delimiter . ' ');
						$str_to_return .= $str_before . get_the_title() . $str_after;
						
					}
				} elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
				
					$post_type = get_post_type_object(get_post_type());
					$str_to_return .= $str_before . $post_type->labels->singular_name . $str_after;

				} elseif ( is_attachment() ) {
				
					$parent = get_post($post->post_parent);
					$cat = get_the_category($parent->ID); $cat = $cat[0];
					$str_to_return .= get_category_parents($cat, TRUE, ' ' . $str_delimiter . ' ');
					$str_to_return .= '<li><a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a></li> ' . $str_delimiter . ' ';
					$str_to_return .=  $str_before . get_the_title() . $str_after;

				} elseif ( is_page() && !$post->post_parent ) {
				
					$str_to_return .= $str_before . get_the_title() . $str_after;

				} elseif ( is_page() && $post->post_parent ) {
				
					$parent_id  = $post->post_parent;
					$breadcrumbs = array();
					
					while ($parent_id) {
						$page = get_page($parent_id);
						$breadcrumbs[] = '<li><a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a></li>';
						$parent_id  = $page->post_parent;
					}
					
					$breadcrumbs = array_reverse($breadcrumbs);
					foreach ($breadcrumbs as $crumb) {
						$str_to_return .= $crumb . ' ' . $str_delimiter . ' ';
					}
						$str_to_return .= $str_before . get_the_title() . $str_after;

				} elseif ( is_search() ) {
				
					$str_to_return .= $str_before . $arr_args['search_label'] . '"' . get_search_query() . '"' . $str_after; 

				} elseif ( is_tag() ) {
				
					$str_to_return .= $str_before . $arr_args['tag_label'] . '"' . single_tag_title('', false) . '"' . $str_after;

				} elseif ( is_author() ) {
				
					global $author;
					$userdata = get_userdata($author);
					$str_to_return .= $str_before . $arr_args['author_label'] . $userdata->display_name . $str_after;
					
				} elseif ( is_404() ) {
				
					$str_to_return .= $str_before . sanitize_text_field($arr_args['404_label']) . $str_after;
				}

				if ( get_query_var('paged') && !is_page() ) {
				
					if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) {
						$str_to_return .= $arr_args['page_open'];
					}
					
					$str_to_return .= sanitize_text_field($arr_args['page_label']) . ' ' . get_query_var('paged');
					if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) {
						$str_to_return .= $arr_args['page_close'];
					}
				}

				$str_to_return .= '</ul>';

					
			if ( $bool_echo ) {
				echo $str_to_return;
			}
			return array('status' => true, 'msg' => 'success', 'source' => get_class(), 'str_to_return' => $str_to_return, 'arr_args' => $arr_args);
		}
		
		/**
		 * TODO
		 */
		public function breadcrumbs_validation($arr_args){
		
		  return $arr_args;
		}
		
		/**
		 *
		 */
		public function breadcrumbs_defaults(){
		
			$arr_defaults = array(	
								'home' 				=> '<span class="icon-home"></span>',   // FYI - text / string (e.g., 'Home') is also allowed for breadcrumbs-home
								'before'			=> '<li class="active">',
								'after'				=> '</li>',
								'separator_class'	=> 'icon-chevron-right',
								'category_label'	=> 'Category: ', // FYI - you can add a span here with a Font Awesome separator if you want to get fancy
								'search_label'		=> 'Search Term: ',
								'tag_label'			=> 'Tag: ',
								'author_label'		=> 'Author: ',
								'404_label'			=> '404 Error ',
								'page_open'			=> ' (',
								'page_label'		=> 'Page ',
								'page_close'		=> ') ',
							);
			/*
			 * Allow filters?
			 */
			if ( $this->_arr_init['filters'] ){
				$arr_defaults_via_filter = apply_filters('filter_ezc_themeui_breadcrumbs_1_defaults', $arr_defaults);
				$arr_defaults = WPezHelpers::_ez_array_merge($arr_defaults, $arr_defaults_via_filter);
			}
			return $arr_defaults;	
		}

	} // close class
} // close if class_exists()
?>