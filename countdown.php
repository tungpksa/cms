<?php 
/**
	* Layout Countdown 1
	* @version     1.0.0
**/
function wc_salecountdown_register_widget() {
	register_widget( 'wpcmsaddons_salecountdown_widget' );
}
add_action( 'widgets_init', 'wc_salecountdown_register_widget' );

class wpcmsaddons_salecountdown_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
		/*Base ID of your widget*/ 
		'wpcmsaddons_salecountdown_widget', 

		/*Widget name will appear in UI*/ 
		__('CA Sale Countdown widget', 'wpcmsaddons'), 

		/*Widget description*/ 
		array( 'description' => __( 'A widget that displays the Sale Countdown.', 'wpcmsaddons' )) 
		);
	}
    /**
     * How to display the widget on the screen.
     */
    function widget($args, $instance) {
 		extract($args);
		
		$title = $instance['title'];
		
		//echo $args['before_widget'];	
		if ( $title ) {
			echo $args['before_title'] . wp_kses_post( $title ) . $args['after_title'];
		}	
		
		$default = array(
			'post_type' => 'product',	
			'meta_query' => array(
				array(
					'key' => '_visibility',
					'value' => array('catalog', 'visible'),
					'compare' => 'IN'
				),
				array(
					'key' => '_sale_price',
					'value' => 0,
					'compare' => '>',
					'type' => 'NUMERIC'
				),
				array(
					'key' => '_sale_price_dates_to',
					'value' => 0,
					'compare' => '>',
					'type' => 'NUMERIC'
				)
			),
			'orderby' => $orderby,
			'order' => $order,
			'post_status' => 'publish',
			'showposts' => $numberposts	
		);
		if( $category != '' ){
			$default['tax_query'] = array(
				array(
					'taxonomy'  => 'product_cat',
					'field'     => 'slug',
					'terms'     => $category ));
		}
		$id = 'sw-count-down_'.rand().time();
		$list = new WP_Query( $default );
		if ( $list -> have_posts() ){ ?>
			<div id="<?php echo $category.'_'.$id; ?>" class=" responsive-slider countdown-slider ">       
				<div class="resp-slider-container">
				
				    <div class="slider-wrapper clearfix">
						<div class="slider responsive">	
						<?php 
							$count_items = 0;
							$count_items = ( $numberposts >= $list->found_posts ) ? $list->found_posts : $numberposts;
							$i = 0;
							while($list->have_posts()): $list->the_post();					
							global $product, $post, $wpdb, $average;

							$start_time = get_post_meta( $post->ID, '_sale_price_dates_from', true );
							$countdown_time = get_post_meta( $post->ID, '_sale_price_dates_to', true );	
							$orginal_price = get_post_meta( $post->ID, '_regular_price', true );	
							$sale_price = get_post_meta( $post->ID, '_sale_price', true );	
							$symboy = get_woocommerce_currency_symbol( get_woocommerce_currency() );
							
						?>
							<div class="box-slider-title" >
								
								<div class="product-countdown-layout1"  data-price="<?php echo esc_attr( $symboy.$orginal_price ); ?>" data-starttime="<?php echo esc_attr( $start_time ); ?>" data-cdtime="<?php echo esc_attr( $countdown_time ); ?>" data-id="<?php echo 'product_'.$id.$post->ID; ?>"></div>
							</div>
								
								<div class="item-wrap">
									<div class="item-detail">
										<div class="item-image-countdown products-thumb">									
											<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>								
										</div>
										<div class="item-content">
										
											<h4><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute();?>"><?php the_title(); ?></a></h4>
											
											<?php if ( $price_html = $product->get_price_html() ){?>
											<div class="item-price">
												<span>
													<?php echo $price_html; ?>
												</span>
											</div>
											<?php } ?>
										</div>															
									</div>
								</div>
							
						<?php  endwhile; wp_reset_postdata();?>
						</div>
					</div>
				</div>            
			</div>
<?php
		} 
	} /*end widget*/

	public function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}
	public function form( $instance ) {
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'wpcmsaddons' ) ?>:</label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( isset( $instance['title'] ) ? $instance['title'] : '' ); ?>" />
		</p>
		<?php
	}
}	
