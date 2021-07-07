<?php
/**
 * Version 0.0.3
 *
 * This file is just an example you can copy it to your theme and modify it to fit your own needs.
 * Watch the paths though.
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Don't duplicate me!
if ( !class_exists( 'Radium_Theme_Demo_Data_Importer' ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'importer/radium-importer.php' ); //load admin theme data importer

	class Radium_Theme_Demo_Data_Importer extends Radium_Theme_Importer {

		/**
		 * Set framewok
		 *
		 * options that can be used are 'default', 'radium' or 'optiontree'
		 *
		 * @since 0.0.3
		 *
		 * @var string
		 */
		public $theme_options_framework = 'customizer';

		/**
		 * Holds a copy of the object for easy reference.
		 *
		 * @since 0.0.1
		 *
		 * @var object
		 */
		private static $instance;

		/**
		 * Set the key to be used to store theme options
		 *
		 * @since 0.0.2
		 *
		 * @var string
		 */
		public $theme_option_name       = 'INHYPE_PANEL'; //set theme options name here (key used to save theme options). Optiontree option name will be set automatically

		/**
		 * Set name of the theme options file
		 *
		 * @since 0.0.2
		 *
		 * @var string
		 */
		public $theme_options_file_name = 'theme_options.dat';

		/**
		 * Set name of the widgets json file
		 *
		 * @since 0.0.2
		 *
		 * @var string
		 */
		public $widgets_file_name       = 'widgets.json';

		/**
		 * Set name of the content file
		 *
		 * @since 0.0.2
		 *
		 * @var string
		 */
		public $content_demo_file_name  = 'content.xml';

		/**
		 * Holds a copy of the widget settings
		 *
		 * @since 0.0.2
		 *
		 * @var string
		 */
		public $widget_import_results;

		/**
		 * Constructor. Hooks all interactions to initialize the class.
		 *
		 * @since 0.0.1
		 */
		public function __construct() {
			if(!isset($_GET['import_theme_demo'])) {
				$_GET['import_theme_demo'] = 0;
			}

			$this->demo_files_path = plugin_dir_path( __FILE__ ) . 'demo-files/'.$_GET['import_theme_demo'].'/';

			self::$instance = $this;
			parent::__construct();

		}

		/**
		 * Add menus - the menus listed here largely depend on the ones registered in the theme
		 *
		 * @since 0.0.1
		 */
		public function set_demo_menus(){

			// Menus to Import and assign - you can remove or add as many as you want

			$top_menu   = get_term_by('name', 'Top Menu', 'nav_menu');
			$main_menu   = get_term_by('name', 'Main Menu', 'nav_menu');
			$footer_menu = get_term_by('name', 'Footer Menu 1', 'nav_menu');

			// Default demo
			set_theme_mod( 'nav_menu_locations', array(
				'top' => $top_menu->term_id,
				'main' => $main_menu->term_id,
				'footer' => '' // $footer_menu->term_id
				)
			);

			if($_GET['import_theme_demo'] == 7) {

				set_theme_mod( 'nav_menu_locations', array(
					'top' => $main_menu->term_id,
					'main' => '',
					'footer' => ''
					)
				);
			}

			if($_GET['import_theme_demo'] == 8) {

				set_theme_mod( 'nav_menu_locations', array(
					'top' => $top_menu->term_id,
					'main' => $main_menu->term_id,
					'footer' => $footer_menu->term_id
					)
				);
			}

			// Set Pages > Reading
			update_option( 'show_on_front', 'posts' );

			// Set WooCommerce pages
			$checkout_page = get_page_by_title( "Checkout" );
			update_option( 'woocommerce_checkout_page_id', $checkout_page->ID );

			$cart_page = get_page_by_title( "Cart" );
			update_option( 'woocommerce_cart_page_id', $cart_page->ID );

			$shop_page = get_page_by_title( "Shop" );
			update_option( 'woocommerce_shop_page_id', $shop_page->ID );

			$account_page = get_page_by_title( "My Account" );
			update_option( 'woocommerce_myaccount_page_id', $account_page->ID );

			// MailChimp plugin settings import:
			$mailchimp_form_data = get_option('mc4wp_lite_form');

			$mailchimp_form_data['markup'] = '<div class="mailchimp-widget-signup-form"><input type="email" name="EMAIL" placeholder="Please enter your e-mail" required /><button type="submit" class="btn">Subscribe</button></div>	';

			update_option( 'mc4wp_lite_form', $mailchimp_form_data );

			// Instagram plugin settings import:
			$instagram_data = get_option('sb_instagram_settings');

			$instagram_userid = 1522886839;
			$instagram_data['sb_instagram_at'] = '';
			$instagram_data['sb_instagram_user_id'][0] = '1901371320';
			$instagram_data['connected_accounts'][$instagram_userid]['access_token'] = '';
			$instagram_data['connected_accounts'][$instagram_userid]['user_id'] = $instagram_userid;
			$instagram_data['connected_accounts'][$instagram_userid]['username'] = '';
			$instagram_data['connected_accounts'][$instagram_userid]['is_valid'] = true;
			$instagram_data['sb_instagram_num'] = '5';
			$instagram_data['sb_instagram_cols'] = '5';
			$instagram_data['sb_instagram_image_padding'] = '';
			$instagram_data['sb_instagram_show_header'] = '';
			$instagram_data['sb_instagram_show_btn'] = '';
			$instagram_data['sb_instagram_show_follow_btn'] = '';

			update_option( 'sb_instagram_settings', $instagram_data );

			$this->flag_as_imported['menus'] = true;
		}

		// Import Revo sliders
		public function import_sliders() {

			if(!isset($_GET['import_theme_demo'])) {
				$_GET['import_theme_demo'] = 0;
			}

			// Revolution Sliders
			if (file_exists(WP_PLUGIN_DIR . '/revslider/revslider.php')) {
				require_once(WP_PLUGIN_DIR . '/revslider/revslider.php');
				$dir = plugin_dir_path( __FILE__ ) . 'demo-files/'.$_GET['import_theme_demo'].'/revslider';

				if ( is_dir($dir) ) {
					$hdir = @opendir( $dir );
					if ( $hdir ) {
						echo '<br><b>'.esc_html__('Import Revolution sliders ...', 'inhype-ta').'</b><br>'; flush();
						$slider = new RevSlider();
						while (($file = readdir( $hdir ) ) !== false ) {
							$pi = pathinfo( $dir . '/' . $file );
							if ( substr($file, 0, 1) == '.' || is_dir( $dir . '/' . $file ) || $pi['extension']!='zip' )
								continue;

							if (!is_array($_FILES)) $_FILES = array();
							$_FILES["import_file"] = array("tmp_name" => $dir . '/' . $file);
							$response = $slider->importSliderFromPost();

							if ($response["success"] == false) {
								echo ' '.esc_html__('Slider was not imported due to error.', 'inhype-ta').'<br>';
							} else {
								echo ' '.esc_html__('Slider imported.', 'inhype-ta').'<br>';
							}
							flush();
						}
						@closedir( $hdir );
					}
				} else {
					echo 'Demo sliders files not found.';
				}
			} else {
				printf(esc_html__('Revolution slider plugin not installed.', 'inhype-ta'), WP_PLUGIN_DIR.'/revslider/revslider.php<br>'); flush();
			}
		}

	}

	new Radium_Theme_Demo_Data_Importer;

}
