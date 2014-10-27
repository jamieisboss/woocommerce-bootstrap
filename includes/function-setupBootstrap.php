<?php

function setupBootstrap(){
    
    remove_shortcode( 'featured_products' );
    remove_shortcode( 'recent_products' );
    add_shortcode( 'featured_products', array($this, 'featured_products' ));
    add_shortcode( 'recent_products', array($this, 'recent_products' ));
    
    add_action( 'wp_enqueue_scripts', array($this, 'woocommerce_bootstrap_setstylesheets'), 99 );
    add_action( 'shop_loop', array($this, 'bs_shop_loop'), 99 );
    add_action( 'woocommerce_before_single_product_summary', array($this, 'woocommerce_before_single_product_summary_bs'), 1 );
    add_action( 'woocommerce_before_single_product_summary', array($this, 'woocommerce_before_single_product_summary_bs_end'), 100 );
    add_action( 'woocommerce_after_single_product_summary', array($this, 'woocommerce_after_single_product_summary_bs'), 1 );
    /* thumbnails */
    add_action('bs_before_shop_loop_item_title','woocommerce_show_product_loop_sale_flash',10);
    add_action('bs_before_shop_loop_item_title',array($this, 'bs_get_product_thumbnail'),10,3);
    add_action('woocommerce_after_shop_loop',array($this, 'endsetupgrid'),1);
    add_action('woocommerce_before_shop_loop',array($this, 'setupgrid'),999);


	// Store column count for displaying the grid

	global $woocommerce_loop;
	
	if ( empty( $woocommerce_loop['columns'] ) ){
		$woocommerce_loop['columns'] = $this->get_option( 'number_of_columns' );	
	}
	
	if($woocommerce_loop['columns']==3){
		add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 9;' ), 10 );
	}
	
	elseif($woocommerce_loop['columns']>2){
		add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 12;' ), 10 );
	}
	
	define('WooCommerce_Bootstrap_grid_classes', $this->get_grid_classes($woocommerce_loop));
    
}