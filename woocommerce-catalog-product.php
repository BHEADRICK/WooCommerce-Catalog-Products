<?php
/*
Plugin Name: WooCommerce Catalog Product
Plugin URI: https://github.com/BHEADRICK/WooCommerce-Catalog-Products
Description: This adds a meta box on each product edit page to allow you to prevent it from being added to the cart (remove add to cart button) without having to leave the price field empty.
Version: 1.0.0
Author: Catman Studios
Author URI: https://catmanstudios.com
Tested up to: 4.5.3
 License: GNU General Public License v3.0
 License URI: http://www.gnu.org/licenses/gpl-3.0.html

*/


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooCommerceCatalogProduct {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'WooCommerce Catalog Product';
	const slug = 'woocommerce-catalog-product';
    var $availability_html;
	/**
	 * Constructor
	 */
	function __construct() {
		//register an activation hook for the plugin
		register_activation_hook( __FILE__, array( $this, 'install_woocommerce_catalog_product' ) );

		//Hook up to the init action
		add_action( 'init', array( $this, 'init_woocommerce_catalog_product' ) );
	}

	/**
	 * Runs when the plugin is activated
	 */
	function install_woocommerce_catalog_product() {
		// do not generate any output here
	}

	/**
	 * Runs when the plugin is initialized
	 */
	function init_woocommerce_catalog_product() {
		// Setup localization
		load_plugin_textdomain( self::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		// Load JavaScript and stylesheets
		//$this->register_scripts_and_styles();

		// Register the shortcode [my_shortcode]
		//
        //
        //add_shortcode( 'my_shortcode', array( $this, 'render_shortcode' ) );

		if ( is_admin() ) {
			//this will run when in the WordPress admin
		} else {
			//this will run when on the frontend
		}

		/*
		 * TODO: Define custom functionality for your plugin here
		 *
		 * For more information:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */

		$this->availability_html = 'Only available in <a href="/steel-pool-kits/">Steel Pool Kits</a> and <a href="/polymer-pool-kits/">Polymer Pool Kits</a>';

        add_action( 'add_meta_boxes', array( $this,'catalog_only_add_meta_box') );
        add_action( 'save_post', array( $this,'catalog_only_save') );
        add_filter('woocommerce_get_availability', array($this, 'product_availability'), 10, 2);
        add_filter('woocommerce_available_variation', array($this, 'variation_availability'), 10, 3);
    }
function variation_availability($props, $variation, $variable){

    error_log(print_r($props, true));
    $catalog_only = get_post_meta($variation->id, '_catalog_only', true);


    if($catalog_only){
        $props['is_purchasable'] = false;

    }

    return $props;
}
    function product_availability($props,  $product){

      //  error_log(print_r($product, true));

        $catalog_only = get_post_meta($product->id, '_catalog_only', true);


        if($catalog_only){
            $props['availability']=$this->availability_html;
            $props['class']='out-of-stock';
        }

        return $props;

    }

    function catalog_only_get_meta( $value ) {
        global $post;

        $field = get_post_meta( $post->ID, $value, true );
        if ( ! empty( $field ) ) {
            return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
        } else {
            return false;
        }
    }

    function catalog_only_add_meta_box() {

        add_meta_box(
            'catalog_only-catalog-only',
            __( 'Catalog Only', 'catalog_only' ),
            array($this, 'catalog_only_html'),
            'product',
            'side',
            'high'
        );
    }
    function catalog_only_html( $post) {
        wp_nonce_field( '_catalog_only_nonce', 'catalog_only_nonce' ); ?>

        <p>Prevent this product from being added to cart</p>

        <p>

        <input type="checkbox" name="_catalog_only" id="_catalog_only" value="catalog-only" <?php echo ( $this->catalog_only_get_meta( '_catalog_only' ) === 'catalog-only' ) ? 'checked' : ''; ?>>
        <label for="_catalog_only"><?php _e( 'Catalog Only', 'catalog_only' ); ?></label>	</p><?php
    }

    function catalog_only_save( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! isset( $_POST['catalog_only_nonce'] ) || ! wp_verify_nonce( $_POST['catalog_only_nonce'], '_catalog_only_nonce' ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['_catalog_only'] ) )
            update_post_meta( $post_id, '_catalog_only', esc_attr( $_POST['_catalog_only'] ) );
        else
            update_post_meta( $post_id, '_catalog_only', null );
    }

	function render_shortcode($atts) {
		// Extract the attributes
		extract(shortcode_atts(array(
			'attr1' => 'foo', //foo is a default value
			'attr2' => 'bar'
			), $atts));
		// you can now access the attribute values using $attr1 and $attr2
	}

	/**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	private function register_scripts_and_styles() {
		if ( is_admin() ) {
			$this->load_file( self::slug . '-admin-script', '/js/admin.js', true );
			$this->load_file( self::slug . '-admin-style', '/css/admin.css' );
		} else {
			$this->load_file( self::slug . '-script', '/js/script.js', true );
			$this->load_file( self::slug . '-style', '/css/style.css' );
		} // end if/else
	} // end register_scripts_and_styles

	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	private function load_file( $name, $file_path, $is_script = false ) {

		$url = plugins_url($file_path, __FILE__);
		$file = plugin_dir_path(__FILE__) . $file_path;

		if( file_exists( $file ) ) {
			if( $is_script ) {

				wp_enqueue_script($name, $url, array('jquery'), false, true ); //depends on jquery
			} else {

				wp_enqueue_style( $name, $url );
			} // end if
		} // end if

	} // end load_file

} // end class
new WooCommerceCatalogProduct();
