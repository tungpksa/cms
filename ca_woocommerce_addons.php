<?php 
/*
Plugin Name: CA Woocommerce Addons
Plugin URI: http://www.cmsaddons.net
Description: CA Widgets.
Author: CmsAddons
Version: 1.1.0
Text Domain: wpcmsaddons
Domain Path: /languages
Author URI: http://www.blog.cmsaddons.net
*/

if ( ! defined( 'ABSPATH' ) )
{
	exit;   
}	
if (!function_exists('wpcmsaddons_to_boolean')) {

    /*
    * Converting string to boolean is a big one in PHP
    */

    function wpcmsaddons_to_boolean($value) {
        if (!isset($value))
            return false;
        if ($value == 'true' || $value == '1')
            $value = true;
        elseif ($value == 'false' || $value == '0')
            $value = false;
        return (bool)$value; // Make sure you do not touch the value if the value is not a string
    }
}
/**
* Check if WooCommerce is active
**/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
{	
	if( !function_exists('wpcmsaddons_nws_get_option') ){

		function wpcmsaddons_nws_get_option( $option, $section, $default = '' ) {
		 
		    $options = get_option( $section );
		 
		    if ( isset( $options[$option] ) ) {
		        return $options[$option];
		    }
		 
		    return $default;
		}

	}
	/**
	 * WC_List_Grid class
	 **/
	if ( ! class_exists( 'WC_List_Grid' ) ) {

		class WC_List_Grid {

			public function __construct() {
				// Hooks
  				add_action( 'wp' , array( $this, 'setup_gridlist' ) , 20);

  				// Init settings
				$this->settings = array(
					array(
						'name' 	=> __( 'Default catalog view', 'wpcmsaddons' ),
						'type' 	=> 'title',
						'id' 	=> 'wc_glt_options'
					),
					array(
						'name' 		=> __( 'Default catalog view', 'wpcmsaddons' ),
						'desc_tip' 	=> __( 'Display products in grid or list view by default', 'wpcmsaddons' ),
						'id' 		=> 'wc_glt_default',
						'type' 		=> 'select',
						'options' 	=> array(
							'grid'  => __( 'Grid', 'wpcmsaddons' ),
							'list' 	=> __( 'List', 'wpcmsaddons' )
						)
					),
					array( 'type' => 'sectionend', 'id' => 'wc_glt_options' ),
				);

				// Default options
				add_option( 'wc_glt_default', 'grid' );

				// Admin
				add_action( 'woocommerce_settings_image_options_after', array( $this, 'admin_settings' ), 20 );
				add_action( 'woocommerce_update_options_catalog', array( $this, 'save_admin_settings' ) );
				add_action( 'woocommerce_update_options_products', array( $this, 'save_admin_settings' ) );
			}

			/*-----------------------------------------------------------------------------------*/
			/* Class Functions */
			/*-----------------------------------------------------------------------------------*/

			function admin_settings() {
				woocommerce_admin_fields( $this->settings );
			}

			function save_admin_settings() {
				woocommerce_update_options( $this->settings );
			}

			// Setup
			function setup_gridlist() {
				if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
					add_action( 'wp_enqueue_scripts', array( $this, 'setup_scripts_styles' ), 20);
					add_action( 'wp_enqueue_scripts', array( $this, 'setup_scripts_script' ), 20);
					add_action( 'woocommerce_before_shop_loop', array( $this, 'gridlist_toggle_button' ), 1);
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'gridlist_buttonwrap_open' ), 9);
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'gridlist_buttonwrap_close' ), 11);
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'gridlist_hr' ), 30);
					add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_single_excerpt', 5);
					add_action( 'woocommerce_after_subcategory', array( $this, 'gridlist_cat_desc' ) );
				}
			}

			// Scripts & styles
			function setup_scripts_styles() {
				wp_enqueue_style( 'grid-list-layout', plugins_url( '/assests/css/style.css', __FILE__ ) );
				wp_enqueue_style( 'grid-list-button', plugins_url( '/assests/css/button.css', __FILE__ ) );
				wp_enqueue_style( 'dashicons' );
			}

			function setup_scripts_script() {
				wp_enqueue_script( 'cookie', plugins_url( '/assests/js/jquery.cookie.min.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_script( 'grid-list-scripts', plugins_url( '/assests/js/jquery.gridlistview.min.js', __FILE__ ), array( 'jquery' ) );
				add_action( 'wp_footer', array( $this, 'gridlist_set_default_view' ) );
			}

			// Toggle button
			function gridlist_toggle_button() {
				?>
					<nav class="gridlist-toggle">
						<a href="#" id="grid" title="<?php _e('Grid view', 'wpcmsaddons'); ?>"><span class="dashicons dashicons-grid-view"></span> <em><?php _e( 'Grid view', 'wpcmsaddons' ); ?></em></a><a href="#" id="list" title="<?php _e('List view', 'wpcmsaddons'); ?>"><span class="dashicons dashicons-exerpt-view"></span> <em><?php _e( 'List view', 'wpcmsaddons' ); ?></em></a>
					</nav>
				<?php
			}

			// Button wrap
			function gridlist_buttonwrap_open() {
				?>
					<div class="gridlist-buttonwrap">
				<?php
			}
			function gridlist_buttonwrap_close() {
				?>
					</div>
				<?php
			}

			// hr
			function gridlist_hr() {
				?>
					<hr />
				<?php
			}

			function gridlist_set_default_view() {
				$default = get_option( 'wc_glt_default' );
				?>
					<script>
						if (jQuery.cookie( 'gridcookie' ) == null) {
					    	jQuery( 'ul.products' ).addClass( '<?php echo $default; ?>' );
					    	jQuery( '.gridlist-toggle #<?php echo $default; ?>' ).addClass( 'active' );
					    }
					</script>
				<?php
			}

			function gridlist_cat_desc( $category ) {
				global $woocommerce;
				echo '<div itemprop="description">';
					echo $category->description;
				echo '</div>';

			}
		}
		if(wpcmsaddons_nws_get_option( 'wpcmsaddons_list_grid', 'wpcmsaddons_nws_general' )=='1'){
			$WC_List_Grid = new WC_List_Grid();	
		}		
		
	}
	/**
 	* Getting ready the plugin settings 
 	*/

	
	if ( ! class_exists( 'WPCAZ' ) ) {
		class WPCAZ {

		public function __construct() {
			
			add_action( 'wp_enqueue_scripts', array( $this, 'obScriptInitFrontend' ) );
			add_action( 'wp_footer', array( $this, 'execute_cloudzoom' ), 30);		
			add_filter( 'single_product_small_thumbnail_size', array( $this, 'change_catalog_thumbnail'), 10, 2 );		
		}	

		function obScriptInitFrontend() {
			if(is_product()) {
				
				wp_enqueue_script( 'jquery.elevatezoom', plugin_dir_url( __FILE__ ).'assests/js/jquery.elevatezoom.js', array(), false, true );
				
			}
		}

		/**
		 * This function is run when go to product page
		 */
		function execute_cloudzoom( $post_ID ) {

			if(is_product()) {
				global  $product;
				 
				$attachment_count =  $product->get_gallery_attachment_ids() ;
				$full_url = array();
				foreach( $attachment_count as $attachment_id ) 
				{
					  $full_url[] = wp_get_attachment_image_src( $attachment_id, 'full' )[0];	
					 
				}
				$default_options = $this->plugin_cloudzoom_defaults();
	        	$current_options = $default_options;

		        $mouseEvent = ($current_options['mouseEvent']=="")?$default_options['mouseEvent']:$current_options['mouseEvent'];
				$thumbnailsContainer = ($current_options['thumbnailsContainer']=="")?$default_options['thumbnailsContainer']:$current_options['thumbnailsContainer'];
				$productImages = ($current_options['productImages']=="")?$default_options['productImages']:$current_options['productImages'];

				?>
				<style>
				
				<?php echo $thumbnailsContainer;?> img {
					width: <?php echo get_option('woocommerce_thumbnail_image_width'); ?>px;
				}
				</style>
				<script type="text/javascript">
		        jQuery(document).ready(function($){
		        	$('.single-product .images > a > img.size-shop_single').attr('data-zoom-image','<?php the_post_thumbnail_url( 'full' ); ?>');
		        	var jQueryArray = <?php echo json_encode($full_url); ?>;
		        	
		        	$('.single-product .thumbnails a img').each(function(index){
		        		
		        		$(this).attr('data-zoom-image',function(i){return jQueryArray[index]; });		        		

		        	});

		        	 $('.single-product .images > a > img.size-shop_single').elevateZoom({
					   /*scrollZoom : true, zoomType: "inner",*/
						cursor: "crosshair",
						zoomWindowFadeIn: 500,
						zoomWindowFadeOut: 750
					   }); 
					   
		            $('a.zoom').unbind('click.fb');
		            $thumbnailsContainer = $('<?php echo $thumbnailsContainer;?>');
		            $thumbnails = $('a', $thumbnailsContainer);

		            $productImages = $('<?php echo $productImages;?>');		            

		            if($thumbnails.length){
		            	<?php if($mouseEvent == 'click') {
		            		echo '$thumbnails.unbind(\'click\');';
		            	}
		            	?>
		      			
		                $thumbnails.bind('<?php echo $mouseEvent; ?>',function(){
		                    $image = $(this).clone(false);
		                    $image.insertAfter($productImages);
		                    $productImages.remove();
		                    $productImages = $image;
		                    $('.mousetrap').remove();
		            		
		            		$(".single-product .images > a.zoom").prettyPhoto({
								hook: 'data-rel',
								social_tools: false,
								theme: 'pp_woocommerce',
								horizontal_padding: 20,
								opacity: 0.8,
								deeplinking: false
							});
		                    $('.single-product .images > a > img.size-shop_single').elevateZoom({
					   		//scrollZoom : true,
							cursor: "crosshair",
							zoomWindowFadeIn: 500,
							zoomWindowFadeOut: 750
						   }); 
		                   
		                   return false;

		                })
		            }            

		        });
		        </script>
				<?php
			}		

		}

		/* Default JS script configuration */
		function plugin_cloudzoom_defaults(){

		    return array(
		        "thumbnailsContainer" => ".product .thumbnails",
		        "mouseEvent" => "hover",
		        "productImages" => ".product .images > a",
		    );

		}

		/* Change thumb size for correct zoom work */
		function change_catalog_thumbnail(){
		    $return = 'shop_single';
		    return $return;
		}
		}	
	
		if(wpcmsaddons_nws_get_option( 'wpcmsaddons_nws_zoom', 'wpcmsaddons_nws_general' )=='1'){
			$WpCmsAddons_CloudZoom = new WPCAZ();
		}		

	}
	/**/	
	
	add_action('wp_head','wpcmsaddons_frontend_assests');
 
	function wpcmsaddons_frontend_assests(){
		
		wp_enqueue_style( 'wpca-owl-css', plugin_dir_url(__FILE__).'assests/css/owl.carousel.min.css' );
		wp_enqueue_style( 'wpca-custom-widget', plugin_dir_url(__FILE__).'assests/css/custom_widget.css' );
		
		wp_enqueue_style( 'wpca-custom-widget', plugin_dir_url(__FILE__).'assests/css/navAccordion.css' );
		
		wp_enqueue_script( 'wpca-owl-js', plugin_dir_url( __FILE__ ).'assests/js/owl.carousel.min.js', array( 'jquery' ), true);
		wp_enqueue_script( 'wpca-fron-end-js', plugin_dir_url( __FILE__ ).'assests/js/wpca_front_end.js', array( 'jquery' ));
		wp_enqueue_script( 'wpca-navAccordion', plugin_dir_url( __FILE__ ).'assests/js/navAccordion.min.js', array( 'jquery' ));

		wp_register_script( 'countdown_slider_js', plugin_dir_url( __FILE__ ).'assests/js/jquery.countdown.min.js', array( 'jquery' ) );		
		if (!wp_script_is('countdown_slider_js')) {
			wp_enqueue_script('countdown_slider_js');
		}	

	}
	include_once(plugin_dir_path(__FILE__).'shortcodes/woo_shortcodes.php');	
	
	include_once(plugin_dir_path(__FILE__).'widgets/top_seller_widget.php');
	
	include_once(plugin_dir_path(__FILE__).'widgets/featured_widget/featured_widget.php');
	
	include_once(plugin_dir_path(__FILE__).'widgets/new_arrival_widget/new_arrival_widget.php');
	
	include_once(plugin_dir_path(__FILE__).'widgets/sale_off_widget.php');
	
	include_once(plugin_dir_path(__FILE__).'widgets/category_widget/category_widget.php');
	
	include_once(plugin_dir_path(__FILE__).'widgets/service_boxes_widget/service_boxes_widget.php');

	include_once(plugin_dir_path(__FILE__).'widgets/post_carousel/post-carousel-widget.php');

	include_once(plugin_dir_path(__FILE__).'widgets/social_media/social_media_widget.php');	
	
	include_once(plugin_dir_path(__FILE__).'widgets/product_search_form/product_search_form.php');	
	
	include_once(plugin_dir_path(__FILE__).'widgets/nav_accordion/nav_accordion.php');
	include_once(plugin_dir_path(__FILE__).'widgets/child-category/child-category.php');	
	
	include_once(plugin_dir_path(__FILE__).'widgets/related-products-widget.php');	
	
	include_once(plugin_dir_path(__FILE__).'widgets/flickr-widget.php');
	include_once(plugin_dir_path(__FILE__).'widgets/contact-info-widget.php');	
	include_once(plugin_dir_path(__FILE__).'widgets/author-widget.php');
	include_once(plugin_dir_path(__FILE__).'widgets/popular-posts-widget.php');	
	include_once(plugin_dir_path(__FILE__).'widgets/countdown.php');	
	
	/**
	 * Plugin Action Links
	 */

	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wpcmsaddons_add_action_links' );

	function wpcmsaddons_add_action_links ( $links ) {

		$links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=ca_woocommerce_addons') ) .'">'. __( 'Settings', 'wpcmsaddons' ) .'</a>';
		
		return $links;
	}
	
	require_once dirname( __FILE__ ) . '/admin/class.settings-api.php';
	require_once dirname( __FILE__ ) . '/admin/settings-config.php';
	
}
else
{ 
?>
	<div class="error notice is-dismissible " id="message"><p>Please <strong>Activate</strong> WooCommerce Plugin First, to use it.</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
	<?php 
}  
?>