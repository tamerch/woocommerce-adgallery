<?php
/*
Plugin Name: WooCommerce Ad-Gallery
Plugin URI: 
Description: WooCommerce custom plugin for replacing LightBox by Ad-Gallery
Author: Samuel Boutron
Author URI: samuel.boutron@gmail.com
Version: 1.1.382020
 
	Copyright: © 2012 Samuel Boutron
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if (!class_exists('WoocommerceAdGalery')) {

class WoocommerceAdGalery {
	private $tab_data = false;
	const VERSION = "1.1.0";
	
	/**
	 * Gets things started by adding an action to initialize this plugin once
	 * WooCommerce is known to be active and initialized
	 */
	public function __construct() {
		add_action( 'woocommerce_init', array(&$this, 'init_adgallery' ));
		
		// Installation
		if (is_admin() && !defined('DOING_AJAX')) $this->install();
	}
	
	/**
	 * Init WooCommerce RUltralight extension once we know WooCommerce is active
	 */
	public function init_adgallery() {
		// product page
		//add_action('woocommerce_product_thumbnails',	 array($this, 'product_adgallery'), 26, 2);
		add_action('woocommerce_before_single_product_summary',	 array($this, 'product_adgallery'), 26, 2);
		add_action('wp_enqueue_scripts', array($this, 'ad_gallery_call')); // For use on the Front end (ie. Theme)
		add_filter( 'woocommerce_get_settings_products', array($this, 'add_adgallery_setting'), 10, 2 );

	}
	
	function add_adgallery_setting( $settings, $current_section ) {
		/**
		 * Check the current section is what we want
		 **/
		if ( $current_section == 'display' ) {
			$updated_settings = array();
			foreach ( $settings as $section ) {
				// After Scripts \ LightBox settings
				if ( isset( $section['type'] ) && 'sectionend' == $section['type'] && $section['id']=='image_options' ) {
					$updated_settings[] = array(
						'title' => __( 'AdGallery', 'woocommerce' ),
						'desc' 	=> __( 'Enable Ad-gallery', 'woocommerce' ),
						'id' 		=> 'woocommerce_enable_adgallery',
						'default'	=> 'yes',
						'desc_tip'	=> __( 'Include Ad-Gallery. Product gallery images will use Ad-Gallery', 'woocommerce' ),
						'type' 		=> 'checkbox',
						'checkboxgroup'		=> 'start'
					);
					
				}
				$updated_settings[] = $section;
			}
			return $updated_settings;
		
		/**
		 * If not, return the standard settings
		 **/
		} else {
			return $settings;
		}

	}

		
	/**
	* enqueue ad-Gallery
	*/
	function ad_gallery_call() {
		if ( get_option( 'woocommerce_enable_adgallery') == 'no' ) return;
		// only load ad-gallery if is post : prevent from loading on other pages
		if ( !is_single() ) return;
		wp_register_script('wc_adgallery', plugins_url('/ad-gallery/jquery.ad-gallery.js', __FILE__), array('jquery') );
		wp_enqueue_script('wc_adgallery');
		wp_register_style( 'wc_adgallery-style', plugins_url('/ad-gallery/jquery.ad-gallery.css', __FILE__) );
        wp_enqueue_style( 'wc_adgallery-style' );
		wp_register_style( 'wc_adgallery-style-responsive', plugins_url('/wc-adgallery.css', __FILE__) );
        wp_enqueue_style( 'wc_adgallery-style-responsive' );
		
		wp_register_script('wc_prettyPhoto', plugins_url('/prettyPhoto/prettyPhoto-adGallery.js', __FILE__), array('jquery') );
		wp_enqueue_script('wc_prettyPhoto');
		wp_register_style( 'wc_prettyPhotoStyle', plugins_url('/prettyPhoto/style.css', __FILE__) );
        wp_enqueue_style( 'wc_prettyPhotoStyle' );
	}
	
	/**
	/* create ad-gallery in product view
	*/	
	public function product_adgallery() {
		global $post, $woocommerce, $product;
		if ( get_option( 'woocommerce_enable_adgallery') == 'no' ) return;
		
		ob_start();
		?>
		var galleries = jQuery(".ad-gallery").adGallery({
			loader_image: ' <?php echo plugins_url('/ad-gallery/loader.gif', __FILE__) ?>',
			lightbox_compliance: true, 
			effect: "fade"
		});

		<?php
		$javascript = ob_get_clean();
		wc_enqueue_js( $javascript );		
		?>
		
		<div id="container">
			<div id="gallery" class="ad-gallery">
				<div class="ad-image-wrapper">
				</div>
				<!--<div class="ad-controls"></div>-->
				<div class="ad-nav">
					<div class="ad-thumbs">
						<div class="thumbnails">
						<ul class="ad-thumb-list">
							
							<?php
							
							// add all thumbnails execept excluded images
							$attachment_ids = $product->get_gallery_attachment_ids();							
							if ( $attachment_ids ) {
								foreach ( $attachment_ids as $attachment ) {
								
								if ( wp_get_attachment_url( $attachment ) && ( get_post_meta( $attachment, '_woocommerce_exclude_image', true ) == 0 ) ) {
								   echo '<li class="ad-thumb-list-li"><a itemprop="image" href='.wp_get_attachment_url( $attachment ).' >';
								   echo wp_get_attachment_image( $attachment, 'thumbnail' );
								   echo '</a></li>';
								   }
								}
							} else {
								$image = get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ) );
								$image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
								$image_link = wp_get_attachment_url( get_post_thumbnail_id() );
								if ( $attachment_count != 1 ) {
									$gallery = '[product-gallery]';
								} else {
									$gallery = '';
								}
								echo '<li class="ad-thumb-list-li"><a itemprop="image" href='.$image_link.' >';
								echo $image;
								echo '</a></li>';

							}
							 ?>
						</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script>

		try {
			image = jQuery("#main-image");
			image.link = image.image;
			var link = $('<a href="'+ image.link +'" target="_blank" rel="prettyPhoto[product-gallery]"></a>');
			link.append(img);
			link.addClass('zoom');
			img_container.append(link);
			jQuery('a.zoom, a.show_review_form').prettyPhoto({
				social_tools: true,
				theme: 'pp_woocommerce',
				horizontal_padding: 40,
				opacity: 0.9
			});
		} 
		catch (err) {
		};
	  
		</script>
		<?php	
	}
	
	/**
	 * Run every time since the activation hook is not executed when updating a plugin
	 */
	private function install() {
		if(get_option('woocommerce_adgallery') != WoocommerceAdGalery::VERSION) {
			$this->upgrade();
			
			// new version number
			update_option('woocommerce_adgallery', WoocommerceAdGalery::VERSION);
		}
	}
	
	/**
	 * Run when plugin version number changes
	 */
	private function upgrade() {
		}
	
	/**
	 * Runs various functions when the plugin first activates (and every time
	 * its activated after first being deactivated), and verifies that
	 * the WooCommerce plugin is installed and active
	 * 
	 * @see register_activation_hook()
	 * @link http://codex.wordpress.org/Function_Reference/register_activation_hook
	 */
	public static function on_activation() {
		// checks if the woocommerce plugin is running and disables this plugin if it's not (and displays a message)
		if (!is_plugin_active('woocommerce/woocommerce.php')) {
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die(__('The WooCommerce rultralight bundle product requires <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> to be activated in order to work. Please install and activate <a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a> first. <a href="'.admin_url('plugins.php').'"> <br> &laquo; Go Back</a>'));
		}
		
		// set version number
		update_option('woocommerce_adgallery', WoocommerceAdGalery::VERSION);
	}
}

/**
 * instantiate class
 */
$woocommerce_adgallery = new WoocommerceAdGalery();

} // class exists check

/**
 * run the plugin activation hook
 */
register_activation_hook(__FILE__, array('WoocommerceAdGallery', 'on_activation'));
