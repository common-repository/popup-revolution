<?php

class Wprp_Popup_Post_Type {

	private $slug = POPUP_PLUGIN_REV_POPUP_POST_TYPE;

	function __construct() {

		$this->start();

	}

	function start() {

		$this->hooks();

		$this->filters();

		$this->shortcodes();

	}

	function includes() {


	}

	function hooks() {

		add_action( 'init', array( $this, 'register_post_type' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );


		/** Add and remove meta boxes **/
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'admin_menu', array( $this, 'remove_meta_boxes' ) );


		/** Save the theme and popup options data into database **/
		add_action( 'save_post', array( $this, 'save_post' ) );

		/** Add js scripts and css styles to frontend **/
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ) );
		add_action( 'wp_head', array( $this, 'frontend_js_wp_head' ) );

		//Add the intro box with the support and social network links
		add_action( 'admin_footer', array( $this, 'intro_box' ) );

		add_action( 'load-post-new.php', array( $this, 'count_and_control_popup' ) );

	}

	function filters() {

		add_filter( 'post_updated_messages',
			array( $this, 'custom_update_messages' ) );

		add_filter( 'post_row_actions',
			array( $this, 'remove_post_row_actions' ) );

		add_filter( 'the_content', array( $this, 'add_when_rule_element' ) );

	}

	function shortcodes() {

		$shortcode = POPUP_PLUGIN_REV_LINK_POPUP_SHORTCODE;

		add_shortcode( $shortcode, array( $this, 'link_popup' ) );

	}

	function link_popup( $atts, $content = null ) {

		extract( shortcode_atts( array(
				'id' => null,
				'link_text' => 'Click to open popup',
				'image_url' => null,
				'theme_id' => null,
				'href' => null
			), $atts ) );
		
		if ( ! $id )
			return FALSE;

		if ( get_post_status( $id ) !== 'publish' )
			return FALSE;

		if ( ! isset( $atts['link_text'] ) )
			$atts['link_text'] = $link_text;

		$popup_id = $id;

		ob_start();

		do_action( 'wprp_link_popup_shortcode', $popup_id, $atts );

		if ( $image_url && $theme_id )
			include wprp_view_path( 'image_link_popup.php' );

		if ( $href && $theme_id )
			include wprp_view_path( 'href_link_popup.php' );

		return ob_get_clean();

	}

	function register_post_type() {

		$labels = array(
			'name' => _x( 'Popups', 'post type general name' ),
			'singular_name' => _x( 'Popup', 'post type singular name' ),
			'add_new' => _x( 'Add New', 'Popup' ),
			'add_new_item' => __( 'Add New Popup' ),
			'edit_item' => __( 'Edit Popup' ),
			'new_item' => __( 'New Popup' ),
			'all_items' => __( 'All Popup' ),
			'view_item' => __( 'View Popup' ),
			'search_items' => __( 'Search Popup' ),
			'not_found' =>  __( 'No Popups found' ),
			'not_found_in_trash' => __( 'No Popups found in Trash' ),
			'parent_item_colon' => '',
			'menu_name' => __( 'Popups' )

		);

		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title' )
		);

		register_post_type( $this->slug, $args );

	}

	function add_meta_boxes() {


		//Custom publish or save box
		add_meta_box(
			'wprp_custom_publish_meta_box',
			__( 'Save', POPUP_PLUGIN_REV_PREFIX ),
			array( $this, 'custom_publish_meta_box' ),
			$this->slug,
			'side'
		);

		//Theme Meta Box
		add_meta_box(
			'wprp_theme_meta_box',
			__( 'Theme', POPUP_PLUGIN_REV_PREFIX ),
			array( $this, 'theme_meta_box' ),
			$this->slug,
			'normal'
		);

		//Popup options/settings meta box
		add_meta_box(
			'wprp_options_meta_box',
			__( 'Options', POPUP_PLUGIN_REV_PREFIX ),
			array( $this, 'options_meta_box' ),
			$this->slug,
			'normal'
		);

		//Link Popup shortcode metabox
		add_meta_box(
			'wprp_link_popup_shortcode',
			__( 'Link Popup Shortcode', POPUP_PLUGIN_REV_PREFIX ),
			array( $this, 'link_popup_shortcode_meta_box' ),
			$this->slug,
			'side'
		);		

		//Theme style metabox
		add_meta_box(
			'wprp_theme_style',
			__( 'Theme Style', POPUP_PLUGIN_REV_PREFIX ),
			array( $this, 'theme_style_metabox' ),
			$this->slug,
			'side'
		);		

	}

	function theme_style_metabox( $post ) {

		$popup_id = $post->ID;

		$active_theme = wprp_get_popup_theme( $popup_id );

		include wprp_view_path( __FUNCTION__ );

	}

	function custom_publish_meta_box( $post ) {

		$popup_id = $post->ID;

		$post_status = get_post_status( $popup_id );

		$delete_link = get_delete_post_link( $popup_id );

		$nonce = wp_create_nonce( 'wprp_popup_nonce' );

		include wprp_view_path( __FUNCTION__ );

	}

	function theme_meta_box( $post ) {

		$popup_id = $post->ID;

		include wprp_view_path( __FUNCTION__ );

	}

	function options_meta_box( $post ) {

		$popup_id = $post->ID;

		$themes = wprp_get_popup_themes();

		$active_theme = wprp_get_popup_theme( $popup_id );

		$options = wprp_get_popup_meta( $popup_id, 'options' );

		$default_options = $this->default_options();

		$options = wp_parse_args( $options, $default_options );

		if ( ! $active_theme )
			$active_theme = 'default_theme';

		include wprp_view_path( __FUNCTION__ );

	}

	function link_popup_shortcode_meta_box( $post ) {

		$popup_id = $post->ID;

		$shortcode = POPUP_PLUGIN_REV_LINK_POPUP_SHORTCODE;

		if ( get_post_status( $popup_id ) !== 'publish' ) {

			echo __( '<p>Click on the Create Popup button to get the shortcode.</p>', POPUP_PLUGIN_REV_PREFIX );

			return;

		}

		$popup_title = get_the_title( $popup_id );

		$shortcode = sprintf( "[%s id='%s' link_text='%s' name='%s']", 
			$shortcode, $popup_id, 'Click to open popup', $popup_title );

		include wprp_view_path( __FUNCTION__ );

	}

	function validate_page() {

		if ( isset( $_GET['post_type'] ) )
			if ( $_GET['post_type'] == $this->slug )
				return TRUE;

		if ( get_post_type() === $this->slug )
			return TRUE;

		return FALSE;

	}

	function admin_enqueue() {


		if ( ! $this->validate_page() )
			return FALSE;

		wp_enqueue_script( 'jquery' );

		wp_enqueue_style( 'wprp-popup-admin',
			plugins_url( 'css/wprp-popup-admin.css', POPUP_PLUGIN_REV_MAIN_FILE ) ,
			array(), POPUP_PLUGIN_REV_VERSION );

		wp_enqueue_style( 'jquery-ibutton',
			plugins_url( 'css/jquery-ibutton/jquery.ibutton.min.css',
				POPUP_PLUGIN_REV_MAIN_FILE ),
			array(), POPUP_PLUGIN_REV_VERSION );

		wp_enqueue_script( 'jquery-ibutton',
			plugins_url( 'js/jquery.ibutton.min.js', POPUP_PLUGIN_REV_MAIN_FILE ) ,
			array(), POPUP_PLUGIN_REV_VERSION );

		wp_enqueue_script( 'wprp-popup-admin',
			plugins_url( 'js/wprp-popup-admin.js', POPUP_PLUGIN_REV_MAIN_FILE ) ,
			array(), POPUP_PLUGIN_REV_VERSION );
		
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'media-upload' );

		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

		wp_dequeue_script( 'autosave' );

		wp_dequeue_script( 'media-upload' );

	}

	function frontend_enqueue() {

		wp_enqueue_script( 'jquery' );

		wp_enqueue_style( 'magnific-popup',
			plugins_url( 'css/magnific-popup.css', POPUP_PLUGIN_REV_MAIN_FILE ) ,
			array(), POPUP_PLUGIN_REV_VERSION );

		wp_enqueue_script( 'wprp-frontend',
			plugins_url( 'js/wprp-popup-frontend.js', POPUP_PLUGIN_REV_MAIN_FILE ) ,
			array(), POPUP_PLUGIN_REV_VERSION );

		$settings = wprp_get_settings();

		if ( ! isset( $settings['exit_alert_text'] ) )
			$settings['exit_alert_text'] = 'I need you to see something - stay on this page';

		wp_localize_script( 'wprp-frontend', 'wprp', array(
				'exit_alert_text' => $settings['exit_alert_text']
			) );

	}

	function frontend_js_wp_head() {

		return;
		echo '<script>';

		include POPUP_PLUGIN_REV_PATH . '/js/wprp-popup-frontend.js';

		echo '</script>';

	}

	function custom_update_messages( $messages ) {

		global $post;

		$messages[$this->slug] = array(
			0 => '',
			1 =>  __( 'Popup updated.' ),
			2 => __( 'Custom field updated.' ),
			3 => __( 'Custom field deleted.' ),
			4 => __( 'Popup updated.' ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'Popup restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Popup created.' ),
			7 => __( 'Popup saved.' ),
			8 => '',
			9 => sprintf( __( 'Popup scheduled for: <strong>%1$s</strong>.' ),
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Popup draft updated.' )
		);

		return $messages;

	}

	function remove_meta_boxes() {

		remove_meta_box( 'submitdiv', $this->slug, 'side' );

	}

	function remove_post_row_actions( $actions ) {

		if ( ! $this->validate_page() )
			return $actions;

		unset( $actions['view'] );
		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['pgcache_purge'] );

		return $actions;

	}

	function save_post( $post_id ) {

		if ( ! $this->validate_page() )
			return FALSE;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return FALSE;

		if ( wp_is_post_revision( $post_id ) )
			return FALSE;
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		if ( ! isset( $_POST['wprp_popup_nonce'] ) )
			return FALSE;

		if ( ! wp_verify_nonce( $_POST['wprp_popup_nonce'], 'wprp_popup_nonce' ) )
			return FALSE;

		$popup_id = $post_id;

		$options = $_POST['options'];

		if ( isset( $options['enabled'] ) )
			$options['enabled'] = true;
		else
			$options['enabled'] = false;

		if ( ! isset( $options['rules']['show_on_homepage'] ) )
			$options['rules']['show_on_homepage'] = false;

		if ( ! isset( $options['rules']['show_only_on_homepage'] ) )
			$options['rules']['show_only_on_homepage'] = false;

		if ( ! isset( $options['rules']['show_to_logged_in_users'] ) )
			$options['rules']['show_to_logged_in_users'] = false;

		if ( ! isset( $options['rules']['hide_on_mobile_devices'] ) )
			$options['rules']['hide_on_mobile_devices'] = false;

		if ( ! isset( $options['rules']['show_only_to_search_engine_visitors'] ) )
			$options['rules']['show_only_to_search_engine_visitors'] = false;

		if ( ! isset( $options['rules']['exit_popup'] ) )
			$options['rules']['exit_popup'] = false;

		if ( ! isset( $options['rules']['when_post_end_rule'] ) )
			$options['rules']['when_post_end_rule'] = false;

		if ( ! isset( $options['rules']['use_cookies'] ) )
			$options['rules']['use_cookies'] = false;

		foreach( $options['rules'] as $key => $rule ):

			if ( $rule === "true" )
				$options['rules'][$key] = true;

			if ( $rule === "false" )
				$options['rules'][$key] = false;

		endforeach;

		wprp_save_popup_meta( $popup_id, 'options', $options );

		wprp_save_popup_meta( $popup_id, 'theme', $options['theme'] );

		do_action( 'wprp_save_popup_data', $popup_id );

		if ( function_exists('w3tc_pgcache_flush') ) {
		  w3tc_pgcache_flush();
		} else if ( function_exists('wp_cache_clear_cache') ) {
		  wp_cache_clear_cache();
		}
		
	}

	public static function default_options() {

		$rules = array(
					'show_on_homepage' => true,
					'show_only_on_homepage' => false,
					'show_to_logged_in_users' => true,
					'hide_on_mobile_devices' => false,
					'show_only_to_search_engine_visitors' => false,
					'use_cookies' => true,
					'cookie_expiration_time' => '',
					'when_post_end_rule' => false,
					'exit_popup' => false,
					'comment_autofill' => false
				);

		$rules = apply_filters( 'wprp_default_popup_options_rules', $rules );

		$options = array(
				'enabled' => false,
				'theme' => 'default_theme',
				'delay_time' => 500,
				'mask_color' => '#000',
				'border_color' => '#000',
				'transition' => 'elastic',
				'rules' => $rules
			);

		return apply_filters( 'wprp_default_popup_options', $options );

	}

	function add_when_rule_element( $content ) {

		//Add the when rule element at the end of a post content
		$end_element = '<div style="display: block !important; margin:0 !important; padding: 0 !important" id="wprp_popup_post_end_element"></div>';

		return $content . PHP_EOL . $end_element;

	}

	function intro_box() {

		if ( ! $this->validate_page() )
			return FALSE;

		if ( defined( 'WPRP_PREMIUM_FUNCTIONALITY' ) )
			$path = 'premium_intro_box';
		else
			$path = 'intro_box';

		include wprp_view_path( $path );

	}

	function count_and_control_popup() {

		global $wpdb;

	    $userid = get_current_user_id();

	    $where = get_posts_by_author_sql( 'wprp_popup', true, $userid );

	    $count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );

	    $screen = get_current_screen();

	    $red_loc = plugins_url( 'views/pro-version-notification.html' , POPUP_PLUGIN_REV_VIEW_DIRECTORY );

	    if ( current_user_can( 'edit_posts') and $screen->post_type === 'wprp_popup' ) { 
	        
	        if ( $count >= 1 && ! defined( 'WPRP_PREMIUM_FUNCTIONALITY' ) )
	            wp_redirect( $red_loc );
	        
	    }

	}

}
