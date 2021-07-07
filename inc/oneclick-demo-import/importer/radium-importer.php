<?php

/**
 * Class Radium_Theme_Importer
 *
 * This class provides the capability to import demo content as well as import widgets and WordPress menus
 *
 * @since 0.0.2
 *
 * @category RadiumFramework
 * @package  NewsCore WP
 * @author   Franklin M Gitonga
 * @link     http://radiumthemes.com/
 *
 */

 // Exit if accessed directly
 if ( !defined( 'ABSPATH' ) ) exit;

 // Don't duplicate me!
 if ( !class_exists( 'Radium_Theme_Importer' ) ) {

	class Radium_Theme_Importer {

		/**
		 * Set the theme framework in use
		 *
		 * @since 0.0.3
		 *
		 * @var object
		 */
		public $theme_options_framework = 'radium'; //supports radium framework and option tree

		/**
		 * Holds a copy of the object for easy reference.
		 *
		 * @since 0.0.2
		 *
		 * @var object
		 */
		public $theme_options_file;

		/**
		 * Holds a copy of the object for easy reference.
		 *
		 * @since 0.0.2
		 *
		 * @var object
		 */
		public $widgets;

		/**
		 * Holds a copy of the object for easy reference.
		 *
		 * @since 0.0.2
		 *
		 * @var object
		 */
		public $content_demo;

		/**
		 * Flag imported to prevent duplicates
		 *
		 * @since 0.0.3
		 *
		 * @var array
		 */
		public $flag_as_imported = array( 'content' => false, 'menus' => false, 'options' => false, 'widgets' =>false );

		/**
		 * imported sections to prevent duplicates
		 *
		 * @since 0.0.3
		 *
		 * @var array
		 */
		public $imported_demos = array();

		/**
		 * Flag imported to prevent duplicates
		 *
		 * @since 0.0.3
		 *
		 * @var bool
		 */
		public $add_admin_menu = true;

	    /**
	     * Holds a copy of the object for easy reference.
	     *
	     * @since 0.0.2
	     *
	     * @var object
	     */
	    private static $instance;

	    /**
	     * Constructor. Hooks all interactions to initialize the class.
	     *
	     * @since 0.0.2
	     */
	    public function __construct() {

	        self::$instance = $this;

	        $this->demo_files_path 		= apply_filters('radium_theme_importer_demo_files_path', $this->demo_files_path);

	        $this->theme_options_file 	= apply_filters('radium_theme_importer_theme_options_file', $this->demo_files_path . $this->theme_options_file_name);

	        $this->widgets 				= apply_filters('radium_theme_importer_widgets_file', $this->demo_files_path . $this->widgets_file_name);

	        $this->content_demo 		= apply_filters('radium_theme_importer_content_demo_file', $this->demo_files_path . $this->content_demo_file_name);

			$this->imported_demos = get_option( 'radium_imported_demo' );

            if( $this->theme_options_framework == 'optiontree' ) {
                $this->theme_option_name = ot_options_id();
            }

	        if( $this->add_admin_menu ) add_action( 'admin_menu', array($this, 'add_admin') );

			add_filter( 'add_post_metadata', array( $this, 'check_previous_meta' ), 10, 5 );

      		add_action( 'radium_import_end', array( $this, 'after_wp_importer' ) );

	    }

		/**
		 * Add Panel Page
		 *
		 * @since 0.0.2
		 */
	    public function add_admin() {

	        add_submenu_page('themes.php', "Import Demo Data", "<span>Import Demo Data</span>", 'switch_themes', 'radium_demo_installer', array($this, 'demo_installer'));

	    }

	    /**
         * Avoids adding duplicate meta causing arrays in arrays from WP_importer
         *
         * @param null    $continue
         * @param unknown $post_id
         * @param unknown $meta_key
         * @param unknown $meta_value
         * @param unknown $unique
         *
         * @since 0.0.2
         *
         * @return
         */
        public function check_previous_meta( $continue, $post_id, $meta_key, $meta_value, $unique ) {

			$old_value = get_metadata( 'post', $post_id, $meta_key );

			if ( count( $old_value ) == 1 ) {

				if ( $old_value[0] === $meta_value ) {

					return false;

				} elseif ( $old_value[0] !== $meta_value ) {

					update_post_meta( $post_id, $meta_key, $meta_value );
					return false;

				}

			}

    	}

    	/**
    	 * Add Panel Page
    	 *
    	 * @since 0.0.2
    	 */
    	public function after_wp_importer() {

			do_action( 'radium_importer_after_content_import');

			update_option( 'radium_imported_demo', $this->flag_as_imported );

		}

		function let_to_num( $size ) {
		  $l   = substr( $size, -1 );
		  $ret = substr( $size, 0, -1 );
		  switch ( strtoupper( $l ) ) {
		    case 'P':
		      $ret *= 1024;
		    case 'T':
		      $ret *= 1024;
		    case 'G':
		      $ret *= 1024;
		    case 'M':
		      $ret *= 1024;
		    case 'K':
		      $ret *= 1024;
		  }
		  return $ret;
		}

    	public function intro_html() {

			?>
		 	<div class="theme-important-notice">
		 	<p>Importing theme demo data is the easiest way to setup your theme. This will
			    allow you to quickly edit everything instead of creating content from scratch. IMPORTANT: Theme required plugins must be installed and activated before demo import. If you need shop demo install WooCommerce plugin first. Check "Server requirements" section below to ensure that your server meets all requirements for a successful import. Demo data does not include real images - grey placeholders will be imported.</p>
			 </div>
			 <h2>Server requirements (PHP.ini settings)</h2>
			 <div class="theme-important-notice">
			 <?php
			 	$hosting_passed = true;

			 	echo '<ul>';

				$memory_limit = $this->let_to_num(ini_get('memory_limit'));

				if ( $memory_limit < 128000000 ) {
					$memory_limit_message_html = ' <span class="configuration-error">WARNING: <strong>128MB</strong> value required.</span>';
					$hosting_passed = false;
				} else {
					$memory_limit_message_html = ' <span class="configuration-success"><strong>OK</strong></span>';
				}

				echo '<li><strong>Server Memory Limit:</strong> '.size_format(esc_html($memory_limit)).'.'.wp_kses_post($memory_limit_message_html).'</li>';

				$memory_limit = $this->let_to_num( WP_MEMORY_LIMIT );

				if ( $memory_limit < 128000000 ) {
					$memory_limit_message_html = ' <span class="configuration-info">INFO: <strong>128MB</strong> value recommended. <a href="https://wordpress.org/support/article/editing-wp-config-php/#increasing-memory-allocated-to-php" target="_blank">How to set?</a></span>';
				} else {
					$memory_limit_message_html = ' <span class="configuration-success"><strong>OK</strong></span>';
				}

				echo '<li><strong>WordPress Memory Limit:</strong> '.size_format(esc_html($memory_limit)).'.'.wp_kses_post($memory_limit_message_html).'</li>';

				$time_limit = ini_get('max_execution_time');

				if ( $time_limit < 60 && $time_limit != 0 ) {
					$time_limit_message_html = ' <span class="configuration-error">WARNING: <strong>60</strong> value required.</span>';
					$hosting_passed = false;
				} else {
					$time_limit_message_html = ' <span class="configuration-success"><strong>OK</strong></span>';
				}

				echo '<li><strong>Max Execution Time:</strong> '.esc_html($time_limit).' seconds.'.wp_kses_post($time_limit_message_html).'</li>';

				$max_input_vars = ini_get('max_input_vars');

				if ( $max_input_vars < 1000 && $max_input_vars != 0 || $max_input_vars == '' ) {
					$max_input_vars_limit_message_html = ' <span class="configuration-info">INFO: <strong>1000</strong> value recommended.</span>';
				} else {
					$max_input_vars_limit_message_html = ' <span class="configuration-success"><strong>OK</strong></span>';
				}

				if($max_input_vars == '') {
					$max_input_vars = 'Not set';
				}

				echo '<li><strong>Max Input Vars:</strong> '.esc_html($max_input_vars).'.'.wp_kses_post($max_input_vars_limit_message_html).'</li>';

				$s_max_input_vars = ini_get( 'suhosin.post.max_vars' );

				if(!empty($s_max_input_vars)) {

					if ( $s_max_input_vars < 1000 && $s_max_input_vars != 0 ) {
						$max_input_vars_limit_message_html = ' <span class="configuration-info">INFO: <strong>1000</strong> value recommended.</span>';
					} else {
						$max_input_vars_limit_message_html = ' <span class="configuration-success"><strong>OK</strong></span>';
					}

					echo '<li><strong>Suhosin Post Max Vars:</strong> '.esc_html($s_max_input_vars).'.'.wp_kses_post($max_input_vars_limit_message_html).'</li>';

			    }

				echo '</ul>';

				if(!$hosting_passed) {
					echo wp_kses_post('<p class="configuration-error"><strong>Your hosting configuration values is not suifficient for demo data import process.</strong> You need to contact your hosting support and ask how to increase required values for PHP configuration.</p>');
				} else {
					echo wp_kses_post('<p class="configuration-success"><strong>Your server settings suitable for theme demo data import.</strong></p>');
				}

			?>
			</div>
			 <h2>Theme demo import settings</h2>
			 <div class="theme-important-notice">
			 <p><label><input type="checkbox" name="demo_import_content" value="1" id="demo_import_content" checked=""/> <strong>Import Demo Content</strong> (Pages, Posts, Menus, Users, Comments)</label></p>
			 <!-- <p><label><input type="checkbox" name="demo_import_sliders" value="1" id="demo_import_sliders"/> <strong>Import Demo Sliders</strong>  (All Revolution Slider Demo Sliders)</label></p> -->
			 <p><label><input type="checkbox" name="demo_import_widgets" value="1" id="demo_import_widgets" checked=""/> <strong>Import Widgets Configuration</strong> (All Widgets positions and settings)</label></p>
			 <p><label><input type="checkbox" name="demo_import_themeoptions" value="1" id="demo_import_themeoptions" checked=""/> <strong>Import Theme Control Panel settings</strong> (All Theme control panel settings)</label></p>
			 </div>


			 <h2>Select theme demo to import</h2>
			 <div class="theme-demo-import-items">
				<!-- Theme demo -->
				 <div class="theme-demo-import-item">
					 <label>
					 <div class="theme-demo-import-item-title">
					 <input type="radio" name="theme_demo" value="1" checked>Demo 1<br>
					 </div>
					 <img src="<?php echo plugin_dir_url( __FILE__ ); ?>../demo-files/1/theme-preview.jpg" alt="Demo site image"/>
					 </label>
					 <div class="theme-demo-import-item-button">
					 <a class="theme-live-preview button button-primary" href="https://wp.wp-preview.com/inhype/inhype-1/" target="_blank">Live preview</a>
					 </div>
				 </div>
				<!-- // Theme demo -->

				<!-- Theme demo -->
				 <div class="theme-demo-import-item">
					 <label>
					 <div class="theme-demo-import-item-title">
					 <input type="radio" name="theme_demo" value="2">Demo 2<br>
					 </div>
					 <img src="<?php echo plugin_dir_url( __FILE__ ); ?>../demo-files/2/theme-preview.jpg" alt="Demo site image"/>
					 </label>
					 <div class="theme-demo-import-item-button">
					 <a class="theme-live-preview button button-primary" href="https://wp.wp-preview.com/inhype/inhype-2/" target="_blank">Live preview</a>
					 </div>
				 </div>
				<!-- // Theme demo -->

				<!-- Theme demo -->
				 <div class="theme-demo-import-item">
					 <label>
					 <div class="theme-demo-import-item-title">
					 <input type="radio" name="theme_demo" value="3">Demo 3<br>
					 </div>
					 <img src="<?php echo plugin_dir_url( __FILE__ ); ?>../demo-files/3/theme-preview.jpg" alt="Demo site image"/>
					 </label>
					 <div class="theme-demo-import-item-button">
					 <a class="theme-live-preview button button-primary" href="https://wp.wp-preview.com/inhype/inhype-3/" target="_blank">Live preview</a>
					 </div>
				 </div>
				<!-- // Theme demo -->

				<!-- Theme demo -->
				 <div class="theme-demo-import-item">
					 <label>
					 <div class="theme-demo-import-item-title">
					 <input type="radio" name="theme_demo" value="4">Demo 4<br>
					 </div>
					 <img src="<?php echo plugin_dir_url( __FILE__ ); ?>../demo-files/4/theme-preview.jpg" alt="Demo site image"/>
					 </label>
					 <div class="theme-demo-import-item-button">
					 <a class="theme-live-preview button button-primary" href="https://wp.wp-preview.com/inhype/inhype-4/" target="_blank">Live preview</a>
					 </div>
				 </div>
				<!-- // Theme demo -->

				<!-- Theme demo -->
				 <div class="theme-demo-import-item">
					 <label>
					 <div class="theme-demo-import-item-title">
					 <input type="radio" name="theme_demo" value="5">Demo 5<br>
					 </div>
					 <img src="<?php echo plugin_dir_url( __FILE__ ); ?>../demo-files/5/theme-preview.jpg" alt="Demo site image"/>
					 </label>
					 <div class="theme-demo-import-item-button">
					 <a class="theme-live-preview button button-primary" href="https://wp.wp-preview.com/inhype/inhype-5/" target="_blank">Live preview</a>
					 </div>
				 </div>
				<!-- // Theme demo -->

				<!-- Theme demo -->
				 <div class="theme-demo-import-item">
					 <label>
					 <div class="theme-demo-import-item-title">
					 <input type="radio" name="theme_demo" value="6">Demo 6<br>
					 </div>
					 <img src="<?php echo plugin_dir_url( __FILE__ ); ?>../demo-files/6/theme-preview.jpg" alt="Demo site image"/>
					 </label>
					 <div class="theme-demo-import-item-button">
					 <a class="theme-live-preview button button-primary" href="https://wp.wp-preview.com/inhype/inhype-6/" target="_blank">Live preview</a>
					 </div>
				 </div>
				<!-- // Theme demo -->

				<!-- Theme demo -->
				 <div class="theme-demo-import-item">
					 <label>
					 <div class="theme-demo-import-item-title">
					 <input type="radio" name="theme_demo" value="7">Demo 7<br>
					 </div>
					 <img src="<?php echo plugin_dir_url( __FILE__ ); ?>../demo-files/7/theme-preview.jpg" alt="Demo site image"/>
					 </label>
					 <div class="theme-demo-import-item-button">
					 <a class="theme-live-preview button button-primary" href="https://wp.wp-preview.com/inhype/inhype-7/" target="_blank">Live preview</a>
					 </div>
				 </div>
				<!-- // Theme demo -->

				<!-- Theme demo -->
				 <div class="theme-demo-import-item">
					 <label>
					 <div class="theme-demo-import-item-title">
					 <input type="radio" name="theme_demo" value="8">Demo 8<br>
					 </div>
					 <img src="<?php echo plugin_dir_url( __FILE__ ); ?>../demo-files/8/theme-preview.jpg" alt="Demo site image"/>
					 </label>
					 <div class="theme-demo-import-item-button">
					 <a class="theme-live-preview button button-primary" href="https://wp.wp-preview.com/inhype/inhype-8/" target="_blank">Live preview</a>
					 </div>
				 </div>
				<!-- // Theme demo -->

				<!-- Theme demo -->
				 <div class="theme-demo-import-item">
					 <label>
					 <div class="theme-demo-import-item-title">
					 <input type="radio" name="theme_demo" value="9">Demo 9<br>
					 </div>
					 <img src="<?php echo plugin_dir_url( __FILE__ ); ?>../demo-files/9/theme-preview.jpg" alt="Demo site image"/>
					 </label>
					 <div class="theme-demo-import-item-button">
					 <a class="theme-live-preview button button-primary" href="https://wp.wp-preview.com/inhype/inhype-9/" target="_blank">Live preview</a>
					 </div>
				 </div>
				<!-- // Theme demo -->

				<!-- Theme demo -->
				 <div class="theme-demo-import-item">
					 <label>
					 <div class="theme-demo-import-item-title">
					 <input type="radio" name="theme_demo" value="10">Demo 10<br>
					 </div>
					 <img src="<?php echo plugin_dir_url( __FILE__ ); ?>../demo-files/10/theme-preview.jpg" alt="Demo site image"/>
					 </label>
					 <div class="theme-demo-import-item-button">
					 <a class="theme-live-preview button button-primary" href="https://wp.wp-preview.com/inhype/inhype-10/" target="_blank">Live preview</a>
					 </div>
				 </div>
				<!-- // Theme demo -->

			 </div>

			 <?php

			 if( !empty($this->imported_demos) ) { ?>

			  		<div class="import-description" style="background-color: #FAFFFB; margin:10px 0 30px;padding: 5px 30px;color: darkgreen;border: 2px solid #a1d3a2; clear:both; width:auto;">
			  		<p><?php echo wp_kses_post('You already launched demo data import before, but you can import demo content again. Some of your content (menus, etc) can be dublicated in this case. We recommend you to <a href="https://wordpress.org/plugins/wordpress-reset/" target="_blank">reset your WordPress</a> first.'); ?></p>
			  	</div><?php
			   	//return;

			  }
    	}

	    /**
	     * demo_installer Output
	     *
	     * @since 0.0.2
	     *
	     * @return null
	     */
	    public function demo_installer() {

			$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

			if( !empty($this->imported_demos ) ) {

				$button_text = esc_html__('Import Again', 'radium');

			} else {

				$button_text = esc_html__('Import Demo Data', 'radium');

			}

	        ?><div id="icon-tools" class="icon32"><br></div>
	        <h2><?php echo esc_html__('One Click InHype Theme Demo Data import', 'inhype'); ?></h2>

	        <?php if(get_option( 'inhype_license_key_status', false ) !== 'activated'): ?>

	        <div class="radium-importer-wrap">
		        <div class="theme-important-notice">
			 	<p><?php echo esc_html__('Please register your purchase to get themes updates notifications, import theme demos and get access to premium dedicated support.', 'inhype'); ?></p>

	          	<a href="themes.php?page=inhype_activate_theme" class="button button-hero button-primary"><?php echo esc_html__('Register theme', 'inhype'); ?></a>
	          	<br/><br/>
				</div>
			</div>

			<?php else: ?>

			<script>
			(function($){
			$(document).ready(function() {

				$("body").on( "click", ".check-import-log", function() {
					$('.import-log-text').show();
				});

				$('.radium-import-start').click(function(){

					var theme_demo_value = $("input:radio[name ='theme_demo']:checked").val();

					var demo_import_content = $("input[name ='demo_import_content']:checked").val();
					var demo_import_sliders = $("input[name ='demo_import_sliders']:checked").val();
					var demo_import_widgets = $("input[name ='demo_import_widgets']:checked").val();
					var demo_import_themeoptions = $("input[name ='demo_import_themeoptions']:checked").val();

					$(".radium-import-start").slideUp();
					$( ".radium-importer-message" ).html( '<div class="theme-important-notice"><p><img src="<?php echo plugin_dir_url( __FILE__ ); ?>img/loader.gif" align="absmiddle" alt="Import..."/></p><p>Importing demo data... Please wait, this process can take 1-5 minutes. Do not close your browser until process will finish.</p></div>' );

					$.get( "themes.php?page=radium_demo_installer&import_theme_demo="+theme_demo_value+"&demo_import_content="+demo_import_content+"&demo_import_sliders="+demo_import_sliders+"&demo_import_widgets="+demo_import_widgets+"&demo_import_themeoptions="+demo_import_themeoptions+"&action=demo-data&demononce=<?php echo wp_create_nonce('radium-demo-code'); ?>", function( data ) {
					  $( ".radium-importer-message" ).html( '<div class="theme-important-notice"><p><span style="color: darkgreen;"><strong>Demo data was imported succesfully.</strong></span><br>Any real images that may be imported with demo data are just examples and you do not have legal rights to use these images on your website, you must replace these images with your own.</p></div><h2>What to do next?</h2><div class="theme-important-notice"><p><a class="button-secondary" href="http://magniumthemes.com/go/inhype-docs/" target="blank">Read Theme Documentation Guide</a> <a href="customize.php?autofocus[panel]=theme_settings_panel" class="button-primary">Manage Theme Settings</a> <a href="<?php echo esc_url(get_site_url());?>" target="_blank" class="button-primary">Visit your website frontend</a> </p><p>You can <a class="check-import-log" style="cursor: pointer;">check import log</a> if you think your website looks incorrectly after demo data import.</p><div class="import-log-text" style="display: none;">' + $(data).find(".radium-importer-message").html() + '</div>' );
					});
				});

			});
			})(jQuery);

			</script>
	      	<div class="radium-importer-wrap" data-demo-id="1"  data-nonce="<?php echo wp_create_nonce('radium-demo-code'); ?>">

		        <form method="post">
		        	<?php $this->intro_html(); ?>
		          	<input type="hidden" name="demononce" value="<?php echo wp_create_nonce('radium-demo-code'); ?>" />
		          	<input name="reset" class="panel-save button-primary radium-import-start" type="button" value="<?php echo esc_attr($button_text) ; ?>" />
		          	<input type="hidden" name="action" value="demo-data" />


					<div class="radium-importer-message clear">
				        <?php if( 'demo-data' == $action && check_admin_referer('radium-demo-code' , 'demononce')){
				         	$this->process_imports();
			 	        } ?>
					</div>
	 	        </form>

 	        </div>

	        <br />
	        <br />

			<?php endif; ?>

	        <?php

	    }

	    /**
	     * Process all imports
	     *
	     * @params $content
	     * @params $options
	     * @params $widgets
	     *
	     * @since 0.0.3
	     *
	     * @return null
	     */
	    public function process_imports( $content = true, $options = true, $widgets = true) {

			if(isset($_GET['demo_import_sliders']) && $_GET['demo_import_sliders'] == 1) {
		    	$this->import_sliders();
		    }

	    	if(isset($_GET['demo_import_content']) && $_GET['demo_import_content'] == 1) {

				if ( $content && !empty( $this->content_demo ) && is_file( $this->content_demo ) ) {
					$this->set_demo_data( $this->content_demo );
				}
			}

			if(isset($_GET['demo_import_themeoptions']) && $_GET['demo_import_themeoptions'] == 1) {

				if ( $options && !empty( $this->theme_options_file ) && is_file( $this->theme_options_file ) ) {
					$this->set_demo_theme_options( $this->theme_options_file );
				}
			}

			if(isset($_GET['demo_import_content']) && $_GET['demo_import_content'] == 1) {

				if ( $options ) {
					$this->set_demo_menus();
				}

			}

			if(isset($_GET['demo_import_widgets']) && $_GET['demo_import_widgets'] == 1) {
				if ( $widgets && !empty( $this->widgets ) && is_file( $this->widgets ) ) {

					// Remove all existing widgets
					update_option( 'sidebars_widgets', array() );

					$this->process_widget_import_file( $this->widgets );
				}
			}

			do_action( 'radium_import_end');

        }

	    /**
	     * add_widget_to_sidebar Import sidebars
	     * @param  string $sidebar_slug    Sidebar slug to add widget
	     * @param  string $widget_slug     Widget slug
	     * @param  string $count_mod       position in sidebar
	     * @param  array  $widget_settings widget settings
	     *
	     * @since 0.0.2
	     *
	     * @return null
	     */
	    public function add_widget_to_sidebar($sidebar_slug, $widget_slug, $count_mod, $widget_settings = array()){

	        $sidebars_widgets = get_option('sidebars_widgets');

	        if(!isset($sidebars_widgets[$sidebar_slug]))
	           $sidebars_widgets[$sidebar_slug] = array('_multiwidget' => 1);

	        $newWidget = get_option('widget_'.$widget_slug);

	        if(!is_array($newWidget))
	            $newWidget = array();

	        $count = count($newWidget)+1+$count_mod;
	        $sidebars_widgets[$sidebar_slug][] = $widget_slug.'-'.$count;

	        $newWidget[$count] = $widget_settings;

	        update_option('sidebars_widgets', $sidebars_widgets);
	        update_option('widget_'.$widget_slug, $newWidget);

	    }

	    public function set_demo_data( $file ) {

		    if ( !defined('WP_LOAD_IMPORTERS') ) define('WP_LOAD_IMPORTERS', true);

	        require_once ABSPATH . 'wp-admin/includes/import.php';

	        $importer_error = false;

	        if ( !class_exists( 'WP_Importer' ) ) {

	            $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';

	            if ( file_exists( $class_wp_importer ) ){

	                require_once($class_wp_importer);

	            } else {

	                $importer_error = true;

	            }

	        }

	        if ( !class_exists( 'WP_Import' ) ) {

	            $class_wp_import = plugin_dir_path( __FILE__ ) . 'wordpress-importer.php';

	            if ( file_exists( $class_wp_import ) )
	                require_once($class_wp_import);
	            else
	                $importer_error = true;

	        }

	        if($importer_error){

	            die("Error on import");

	        } else {

	            if(!is_file( $file )){

	                echo "The XML file containing the dummy content is not available or could not be read .. You might want to try to set the file permission to chmod 755.<br/>If this doesn't work please use the Wordpress importer and import the XML file (should be located in your download .zip: Sample Content folder) manually ";

	            } else {

	               	$wp_import = new WP_Import();
	               	$wp_import->fetch_attachments = true;
	               	$wp_import->import( $file );
					$this->flag_as_imported['content'] = true;

	         	}

	    	}

	    	do_action( 'radium_importer_after_theme_content_import');


	    }

	    public function set_demo_menus() {}

	    public function import_sliders() {}

	    public function set_demo_theme_options( $file ) {

	    	// Does the File exist?
			if ( file_exists( $file ) ) {

				echo 'Importing theme settings from ...'.$file.'<br>';

				// Get file contents and decode
				$data = file_get_contents( $file );

				if( $this->theme_options_framework == 'radium') {

					//radium framework
					$data = unserialize( trim($data, '###') );

				} elseif( $this->theme_options_framework == 'optiontree' ) {

					//option tree import
					$data = $this->optiontree_decode($data);

					update_option( ot_options_id(), $data );

					$this->flag_as_imported['options'] = true;

				} elseif( $this->theme_options_framework == 'ipanel' ) {

					//ipanel import
					$data = unserialize( $data );

					$this->flag_as_imported['options'] = true;

				} elseif( $this->theme_options_framework == 'customizer' ) {

					$data = unserialize( $data );

					// Save theme mods
					foreach ( $data['mods'] as $key => $val ) {
						if($key !== 'nav_menu_locations') { // Don't assign menus
							set_theme_mod( $key, $val );
						}
					}

					// Reset caches
		            update_option( 'themeoptions_saved_date', 1, 'no' );
		            update_option( 'editor_cache_css_saved_date', 0, 'no' );
		            update_option( 'cache_css_saved_date', 0, 'no' );

		            // Remove editor CSS cache files
		            WP_Filesystem();

        			global $wp_filesystem;

		            $editor_cache_file_name = 'style-editor-cache-'.wp_get_theme()->get('TextDomain');
			        $wp_upload_dir = wp_upload_dir();
			        $css_editor_cache_file = $wp_upload_dir['basedir'].'/'.$editor_cache_file_name.'.css';

			        $wp_filesystem->delete( $css_editor_cache_file );

				} else {

					//other frameworks
					$data = maybe_unserialize( $data );

				}

				// Only if there is data
				if ( !empty( $data ) ) {

					$this->flag_as_imported['options'] = true;
				}

				echo '<b>Theme settings imported ...</b><br>';

				if(!empty($this->active_import)) {
					do_action( 'radium_importer_after_theme_options_import', $this->active_import, $this->demo_files_path );
				}

      		} else {

	      		wp_die(
      				esc_html__( 'Theme options Import file could not be found. Please try again.', 'radium' ),
      				'',
      				array( 'back_link' => true )
      			);
       		}

	    }

	    /**
	     * Available widgets
	     *
	     * Gather site's widgets into array with ID base, name, etc.
	     * Used by export and import functions.
	     *
	     * @since 0.0.2
	     *
	     * @global array $wp_registered_widget_updates
	     * @return array Widget information
	     */
	    function available_widgets() {

	    	global $wp_registered_widget_controls;

	    	$widget_controls = $wp_registered_widget_controls;

	    	$available_widgets = array();

	    	foreach ( $widget_controls as $widget ) {

	    		if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[$widget['id_base']] ) ) { // no dupes

	    			$available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
	    			$available_widgets[$widget['id_base']]['name'] = $widget['name'];

	    		}

	    	}

	    	return apply_filters( 'radium_theme_import_widget_available_widgets', $available_widgets );

	    }


	    /**
	     * Process import file
	     *
	     * This parses a file and triggers importation of its widgets.
	     *
	     * @since 0.0.2
	     *
	     * @param string $file Path to .wie file uploaded
	     * @global string $widget_import_results
	     */
	    function process_widget_import_file( $file ) {

	    	// File exists?
	    	if ( ! file_exists( $file ) ) {
	    		wp_die(
	    			esc_html__( 'Widget Import file could not be found. Please try again.', 'radium' ),
	    			'',
	    			array( 'back_link' => true )
	    		);
	    	}

	    	// Get file contents and decode
	    	$data = file_get_contents( $file );
	    	$data = json_decode( $data );

	    	// Delete import file
	    	//unlink( $file );

	    	// Import the widget data
	    	// Make results available for display on import/export page
	    	$this->widget_import_results = $this->import_widgets( $data );

	    }


	    /**
	     * Import widget JSON data
	     *
	     * @since 0.0.2
	     * @global array $wp_registered_sidebars
	     * @param object $data JSON widget data from .json file
	     * @return array Results array
	     */
	    public function import_widgets( $data ) {

	    	global $wp_registered_sidebars;

	    	// Have valid data?
	    	// If no data or could not decode
	    	if ( empty( $data ) || ! is_object( $data ) ) {
	    		return;
	    	}

	    	// Hook before import
	    	$data = apply_filters( 'radium_theme_import_widget_data', $data );

	    	// Get all available widgets site supports
	    	$available_widgets = $this->available_widgets();

	    	// Get all existing widget instances
	    	$widget_instances = array();
	    	foreach ( $available_widgets as $widget_data ) {
	    		$widget_instances[$widget_data['id_base']] = get_option( 'widget_' . $widget_data['id_base'] );
	    	}

	    	// Begin results
	    	$results = array();

	    	// Loop import data's sidebars
	    	foreach ( $data as $sidebar_id => $widgets ) {

	    		// Skip inactive widgets
	    		// (should not be in export file)
	    		if ( 'wp_inactive_widgets' == $sidebar_id ) {
	    			continue;
	    		}

	    		// Check if sidebar is available on this site
	    		// Otherwise add widgets to inactive, and say so
	    		if ( isset( $wp_registered_sidebars[$sidebar_id] ) ) {
	    			$sidebar_available = true;
	    			$use_sidebar_id = $sidebar_id;
	    			$sidebar_message_type = 'success';
	    			$sidebar_message = '';
	    		} else {
	    			$sidebar_available = false;
	    			$use_sidebar_id = 'wp_inactive_widgets'; // add to inactive if sidebar does not exist in theme
	    			$sidebar_message_type = 'error';
	    			$sidebar_message = esc_html__( 'Sidebar does not exist in theme (using Inactive)', 'radium' );
	    		}

	    		// Result for sidebar
	    		$results[$sidebar_id]['name'] = ! empty( $wp_registered_sidebars[$sidebar_id]['name'] ) ? $wp_registered_sidebars[$sidebar_id]['name'] : $sidebar_id; // sidebar name if theme supports it; otherwise ID
	    		$results[$sidebar_id]['message_type'] = $sidebar_message_type;
	    		$results[$sidebar_id]['message'] = $sidebar_message;
	    		$results[$sidebar_id]['widgets'] = array();

	    		// Loop widgets
	    		foreach ( $widgets as $widget_instance_id => $widget ) {

	    			$fail = false;

	    			// Get id_base (remove -# from end) and instance ID number
	    			$id_base = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
	    			$instance_id_number = str_replace( $id_base . '-', '', $widget_instance_id );

	    			// Does site support this widget?
	    			if ( ! $fail && ! isset( $available_widgets[$id_base] ) ) {
	    				$fail = true;
	    				$widget_message_type = 'error';
	    				$widget_message = esc_html__( 'Site does not support widget', 'radium' ); // explain why widget not imported
	    			}

	    			// Filter to modify settings before import
	    			// Do before identical check because changes may make it identical to end result (such as URL replacements)
	    			$widget = apply_filters( 'radium_theme_import_widget_settings', $widget );

	    			// Does widget with identical settings already exist in same sidebar?
	    			if ( ! $fail && isset( $widget_instances[$id_base] ) ) {

	    				// Get existing widgets in this sidebar
	    				$sidebars_widgets = get_option( 'sidebars_widgets' );
	    				$sidebar_widgets = isset( $sidebars_widgets[$use_sidebar_id] ) ? $sidebars_widgets[$use_sidebar_id] : array(); // check Inactive if that's where will go

	    				// Loop widgets with ID base
	    				$single_widget_instances = ! empty( $widget_instances[$id_base] ) ? $widget_instances[$id_base] : array();
	    				foreach ( $single_widget_instances as $check_id => $check_widget ) {

	    					// Is widget in same sidebar and has identical settings?
	    					if ( in_array( "$id_base-$check_id", $sidebar_widgets ) && (array) $widget == $check_widget ) {

	    						$fail = true;
	    						$widget_message_type = 'warning';
	    						$widget_message = esc_html__( 'Widget already exists', 'radium' ); // explain why widget not imported

	    						break;

	    					}

	    				}

	    			}

	    			// No failure
	    			if ( ! $fail ) {

	    				// Add widget instance
	    				$single_widget_instances = get_option( 'widget_' . $id_base ); // all instances for that widget ID base, get fresh every time
	    				$single_widget_instances = ! empty( $single_widget_instances ) ? $single_widget_instances : array( '_multiwidget' => 1 ); // start fresh if have to
	    				$single_widget_instances[] = (array) $widget; // add it

    					// Get the key it was given
    					end( $single_widget_instances );
    					$new_instance_id_number = key( $single_widget_instances );

    					// If key is 0, make it 1
    					// When 0, an issue can occur where adding a widget causes data from other widget to load, and the widget doesn't stick (reload wipes it)
    					if ( '0' === strval( $new_instance_id_number ) ) {
    						$new_instance_id_number = 1;
    						$single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
    						unset( $single_widget_instances[0] );
    					}

    					// Move _multiwidget to end of array for uniformity
    					if ( isset( $single_widget_instances['_multiwidget'] ) ) {
    						$multiwidget = $single_widget_instances['_multiwidget'];
    						unset( $single_widget_instances['_multiwidget'] );
    						$single_widget_instances['_multiwidget'] = $multiwidget;
    					}

    					// Update option with new widget
    					update_option( 'widget_' . $id_base, $single_widget_instances );

	    				// Assign widget instance to sidebar
	    				$sidebars_widgets = get_option( 'sidebars_widgets' ); // which sidebars have which widgets, get fresh every time
	    				$new_instance_id = $id_base . '-' . $new_instance_id_number; // use ID number from new widget instance
	    				$sidebars_widgets[$use_sidebar_id][] = $new_instance_id; // add new instance to sidebar
	    				update_option( 'sidebars_widgets', $sidebars_widgets ); // save the amended data

	    				// Success message
	    				if ( $sidebar_available ) {
	    					$widget_message_type = 'success';
	    					$widget_message = esc_html__( 'Imported', 'radium' );
	    				} else {
	    					$widget_message_type = 'warning';
	    					$widget_message = esc_html__( 'Imported to Inactive', 'radium' );
	    				}

	    			}

	    			// Result for widget instance
	    			$results[$sidebar_id]['widgets'][$widget_instance_id]['name'] = isset( $available_widgets[$id_base]['name'] ) ? $available_widgets[$id_base]['name'] : $id_base; // widget name or ID if name not available (not supported by site)
	    			if(!empty($results[$sidebar_id]['widgets'][$widget_instance_id]['title'])) {
	    				$results[$sidebar_id]['widgets'][$widget_instance_id]['title'] = $widget->title ? $widget->title : esc_html__( 'No Title', 'radium' ); // show "No Title" if widget instance is untitled
	    			}
	    			$results[$sidebar_id]['widgets'][$widget_instance_id]['message_type'] = $widget_message_type;
	    			$results[$sidebar_id]['widgets'][$widget_instance_id]['message'] = $widget_message;

	    		}

	    	}

			$this->flag_as_imported['widgets'] = true;

	    	// Hook after import
	    	do_action( 'radium_theme_import_widget_after_import' );

	    	echo '<b>Widgets imported ...</b><br>';

	    	// Return results
	    	return apply_filters( 'radium_theme_import_widget_results', $results );

	    }

	    /**
	     * Helper function to return option tree decoded strings
	     *
	     * @return    string
	     *
	     * @access    public
	     * @since     0.0.3
	     * @updated   0.0.3.1
	     */
	    public function optiontree_decode( $value ) {

			$func = 'base64' . '_decode';
			$prepared_data = maybe_unserialize( $func( $value ) );

			return $prepared_data;

	    }

	}//class

}//function_exists
?>
