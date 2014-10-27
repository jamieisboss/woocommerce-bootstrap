<?php
/*
Plugin Name: WooCommerce Bootstrap
Depends: WooCommerce
Plugin URI: https://github.com/jamieisboss/woocommerce-bootstrap
Description: Adds Bootstrap's Grid to WooCommerce
Version: 1.4.0
Author: Jamie Roberts (Originally forked from Bass Jobsen)
Author URI: http://solvehq.co.uk
License: GPLv2
*/

/*  Copyright 2013 Bass Jobsen (email : bass@w3masters.nl)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    

if ( ! class_exists( 'WooCommerce_Bootstrap' ) ) :

class WooCommerce_Bootstrap {

	/**
	* Construct the plugin.
	*/
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	* Initialize the plugin.
	*/
	public function init() {

		// Checks if WooCommerce is installed.
		if ( class_exists( 'WC_Integration' ) ) {
			// Include our integration class.
			include_once 'includes/class-wc-integration-bootstrap.php';
			include_once 'includes/function-setupBootstrap.php';

			// Register the integration.
			add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
		
			setupBootstrap();
			
		} else {
			// throw an admin error if you like
		}
	}

	/**
	 * Add a new integration to WooCommerce.
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'WC_Integration_Bootstrap_Integration';
		return $integrations;
	}
	
	/** Replace the featured_products shortcode **/
	
	function featured_products( $atts ) {

	    extract(shortcode_atts(array(
	        'per_page' => 12,
	        'columns' => 4,
	        'orderby' => 'date',
	        'order' => 'desc',
	        'content_product_template' => 'bs-content-product'
	    ), $atts));
	    
	    $args = array(
	        'post_type'=> 'product',
	        'post_status' => 'publish',
	        'ignore_sticky_posts'=> 1,
	        'posts_per_page' => $per_page,
	        'orderby' => $orderby,
	        'order' => $order,
	        'meta_query' => array(
	            array(
	                'key' => '_visibility',
	                'value' => array('catalog', 'visible'),
	                'compare' => 'IN'
	            ),
	            array(
	                'key' => '_featured',
	                'value' => 'yes'
	            )
	        )
	    );
	    
	    return $this->showproductspeciallist($args,$content_product_template,$columns);
	
	}
	
	
	/** Replace the recent_products shortcode **/
	
	public function recent_products( $atts ) {

	    global $woocommerce;
	    
	    extract(shortcode_atts(array(
	    'per_page' => '12',
	    'columns' => '4',
	    'orderby' => 'date',
	    'order' => 'desc',
	    'content_product_template' => 'bs-content-product'
	    ), $atts));
	    
	    $meta_query = $woocommerce->query->get_meta_query();
	    
	    $args = array(
	    'post_type'=> 'product',
	    'post_status' => 'publish',
	    'ignore_sticky_posts'=> 1,
	    'posts_per_page' => $per_page,
	    'orderby' => $orderby,
	    'order' => $order,
	    'meta_query' => $meta_query
	    );	
	    
	    return $this->showproductspeciallist($args,$content_product_template,$columns);
	
	}
	
	// 
	
	function showproductspeciallist($args,$content_product_template,$columns=null){
	
	    global $woocommerce_loop;	
	    ob_start();
	    
	    $products= new WP_Query( $args );
	    
	    $woocommerce_loop['columns'] = ($columns)?$columns:$this->get_option( 'number_of_columns' );
	    
	    if ( $products->have_posts() ) {
	        $this->bs_shop_loop($products,$content_product_template,$columns);	
	    }
	    
	    wp_reset_postdata();
	    
	    return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}
	
	
	// Register and Enque the styles
	
	function woocommerce_bootstrap_setstylesheets(){
    
		wp_register_style ( 'woocommerce-bootstrap', plugins_url( 'css/woocommerce-boostrap.css' , __FILE__ ), 'woocommerce' );
		wp_enqueue_style ( 'woocommerce-bootstrap');
	    
	}
	
	// Set up the relevant grid classes
	
	function get_grid_classes($woocommerce_loop){

		if($this->get_option( 'bootstrap_version' )==2){
			
			switch($woocommerce_loop['columns']){
				
				case 6: $classes = 'span2'; break;
				case 4: $classes = 'span3'; break;
				case 3: $classes = 'span4'; break;
				case 31: $classes = 'span4'; break;
				case 2: $classes = 'span6'; break;
				default: $classes = 'span12';
				
			}
		
			
		} else {
			
			switch($woocommerce_loop['columns']){
				
				case 6: $classes = 'col-xs-6 col-sm-3 col-md-2'; break;
				case 4: $classes = 'col-xs-12 col-sm-6 col-md-3'; break;
				case 3: $classes = 'col-xs-12 col-sm-12 col-md-4'; break;
				case 31: $classes = 'col-xs-12 col-sm-6 col-md-4'; break;
				case 2: $classes = 'col-xs-12 col-sm-6 col-md-6'; break;
				default: $classes = 'col-xs-12 col-sm-12 col-md-12';
				
			}
		}
			return $classes;
	}
	
	//  Set up the product Loop
	
	function bs_product_loop(&$woocommerce_loop,$classes,$template='bs-content-product'){

		if(!file_exists( $template = get_stylesheet_directory() . '/woocommerce-bootstrap/'.$template.'.php' )){
			$template = plugin_dir_path( __FILE__ ). 'templates/bs-content-product.php';
		};
		
	
		include($template);
	
	
		if($this->get_option( 'bootstrap_version' ) == 3 ){
			if($woocommerce_loop['columns'] == 6) {
				
				if(0 == ($woocommerce_loop['loop'] % 6)){?><div class="clearfix visible-md visible-lg"></div><?php }
				if(0 == ($woocommerce_loop['loop'] % 4)){?><div class="clearfix visible-sm"></div><?php }
				if(0 == ($woocommerce_loop['loop'] % 2)){?><div class="clearfix visible-xs"></div><?php }
				
			} elseif($woocommerce_loop['columns'] == 4) {
				
				if(0 == ($woocommerce_loop['loop'] % 4)){?><div class="clearfix visible-md visible-lg"></div><?php }
				if(0 == ($woocommerce_loop['loop'] % 2)){?><div class="clearfix visible-sm"></div><?php 
				
			} elseif($woocommerce_loop['columns'] == 3) {
				
				if(0 == ($woocommerce_loop['loop'] % 3)){?><div class="clearfix visible-md visible-lg"></div><?php }
					
			} elseif($woocommerce_loop['columns'] == 31) {
				
				if(0 == ($woocommerce_loop['loop'] % 3)){?><div class="clearfix visible-md visible-lg"></div><?php }
				if(0 == ($woocommerce_loop['loop'] % 2)){?><div class="clearfix visible-sm"></div><?php }
			
			} elseif($woocommerce_loop['columns'] == 2) {
				
				if(0 == ($woocommerce_loop['loop'] % 2)){?><div class="clearfix invisible-xs"></div><?php }
			}
		
		}
					
		$woocommerce_loop['loop']++;
	
		}
		
	}
	
	function bs_shop_loop($product=null,$template='bs-content-product',$columns=null) {

	    $woocommerce_loop = array('loop'=>0,'columns' => ($columns)?$columns:$this->get_option( 'number_of_columns', 4 ));	
	
	
	    /* double check */
	    if($woocommerce_loop['columns']!=31 && ( $woocommerce_loop['columns']>6 || in_array($woocommerce_loop['columns'],array(5,7)))) { return; }
	
	
	    // Increase loop count
	    $woocommerce_loop['loop']++;
	
	    ob_start();
	    woocommerce_product_subcategories(array('before'=>'<div class="clearfix"></div><div class="subcategories"><div class="row">','after'=>'</div></div>')); 
	    $subcategories = ob_get_contents();
	    ob_end_clean();
	    $classes = $this->get_grid_classes($woocommerce_loop);
					
	    echo preg_replace(array('/<li[^>]*>/','/<\/li>/'),array('<div class="'.$classes.'">','</div>'),$subcategories);
					
	    if($product){
					
	    ?><div class="clearfix"></div><div class="products"><div class="row"><?php
					
	    while ( $product->have_posts()) : $product->the_post(); 
	    $this->bs_product_loop($woocommerce_loop,$classes,$template);
	    endwhile; 
						
	    } else {
	    ?><div class="clearfix"></div><div class="products"><div class="row"><?php
						
	    while ( have_posts() ) : the_post(); 
	    $this->bs_product_loop($woocommerce_loop,$classes);
	    endwhile;
						
	    }				
	    
	    if($woocommerce_loop['columns']==31)$woocommerce_loop['columns']=3;
	    
	    if ( 0 != ($woocommerce_loop['loop']-1) % $woocommerce_loop['columns'] ){
		
	        ?><div class="<?php echo $classes?>"></div><?php
									
	        while ( 0 != $woocommerce_loop['loop'] % $woocommerce_loop['columns'] )	{
	            $woocommerce_loop['loop']++;
	            ?><div class="<?php echo $classes?>"></div><?php
	        }
	
	    }	
					
		?></div></div><?php	
		
	}	
	
		/**
	 * Output the start of the page wrapper.
	 *
	 * @access public
	 * @return void
	 */
	function woocommerce_output_content_wrapper_bs() {

		//woocommerce_get_template( 'shop/wrapper-start.php' );
	}

	/**
	 * Output the end of the page wrapper.
	 *
	 * @access public
	 * @return void
	 */
	function woocommerce_output_content_wrapper_end_bs() {
		//woocommerce_get_template( 'shop/wrapper-end.php' );
	}



	function woocommerce_before_single_product_summary_bs() { 
		
		if($this->get_option( 'bootstrap_version' )==2)
		{
			$bssingleproductclass = 'span6';
		}
		else
		{
			$bssingleproductclass = 'col-sm-6';
		}	
		
		echo '<div class="row"><div class="'.$bssingleproductclass.' bssingleproduct">'; 
		
	}



	function woocommerce_before_single_product_summary_bs_end() {
		echo '</div><div class="col-sm-6 bssingleproduct">'; 
	}
	
	function woocommerce_after_single_product_summary_bs(){
		echo '</div></div>'; 
	}


	function endsetupgrid(){
		ob_end_clean();
		$this->bs_shop_loop();
	}	

	function setupgrid(){
	ob_start();
	}

}

$WooCommerce_Bootstrap = new WooCommerce_Bootstrap( __FILE__ );

endif;

}