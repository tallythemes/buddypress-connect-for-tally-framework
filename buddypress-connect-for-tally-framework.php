<?php
/**
 * Plugin Name: BuddyPress Connect For Tally Framework
 * Plugin URI:  http://tallythemes.com/
 * Description: Add basic BuddyPress templating and Style for  <strong> Tally Framework</strong>
 * Author:      TallyThemes
 * Author URI:  http://tallythemes.com/
 * Version:     0.2
 * Text Domain: buddypresstallyc_textdomain
 * Domain Path: /languages/
 * Name Space: buddypresstallyc
 * Name Space: BUDDYPRESSTALLYC
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

$path_dir = trailingslashit(str_replace('\\','/',dirname(__FILE__)));
$path_abs = trailingslashit(str_replace('\\','/',ABSPATH));

define('BUDDYPRESSTALLYC', 'BuddyPress Connect For Tally Framework' );
define('BUDDYPRESSTALLYC_URL', site_url(str_replace( $path_abs, '', $path_dir )) );
define('BUDDYPRESSTALLYC_DRI', $path_dir );
define('BUDDYPRESSTALLYC_TEMPLATE', BUDDYPRESSTALLYC_DRI.'template' );
define('BUDDYPRESSTALLYC_VERSION', 0.2);


class buddypresstallyc{
	function __construct(){
		add_action('init', array($this,'load_textdomain'));
		add_action('after_setup_theme', array($this,'after_setup_theme'));
	}
	
	
	/** Load TextDomain ********************************************************************/
	/**
	 * Add languages files.
	 *
	 * @since 0.1
	 *
	 * @uses load_plugin_textdomain()
	 */
	function load_textdomain(){
		load_plugin_textdomain( 'bbpresstallyc_textdomain', false, dirname(plugin_basename(__FILE__)).'/languages/' );
	}
	
	
	/** after_setup_theme hook function ****************************************************/
	/**
	 * This function contain all elements that's need 
	 * to attached in "after_setup_theme" hook.
	 *
	 * @since 0.1
	 *
	 * @used with "after_setup_theme" hook
	 */
	function after_setup_theme(){
		/** Fail silently if WooCommerce is not activated */
		if ( ! in_array( 'buddypress/bp-loader.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;	
		if(!function_exists('tally_option')) return;
		if(!class_exists('BuddyPress')) return;
	
		/* Setup bbPress sidebar*/
		register_sidebar( array(
			'name'			=> __('BuddyPress Sidebar', 'buddypresstallyc_textdomain'),
			'id'			=> 'tally_buddypress',
			'description'	=> __('BuddyPress shop Sidebar Widgets', 'buddypresstallyc_textdomain'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> "</div><div class='clear' style='height:30px;'></div>",
			'before_title'	=> '<h4 class="heading">',
			'after_title'	=> '</h4>',
		));
		add_action('tally_sidebar', array($this,'add_sidebar') );
		add_filter('tally_sitebar_layout_option', array($this,'sidebar_layout_option'));
		add_filter('tally_sidebar_active', array($this,'disable_theme_sidebar'));
		add_action('tally_template_init', array($this,'fix_bbpress_sidebar'));
		
		add_action('wp_enqueue_scripts', array($this,'custom_scripts'));
		
		add_filter('option_tree_settings_args', array($this, 'add_theme_options'));
	}
	
	
	
	/** Load Custom frontend scripts ***********************************************/
	/**
	 * This function add custom css and jsvascript for bbpress
	 *
	 * @since 0.1
	 *
	 * @used with "bbp_enqueue_scripts" hook
	 */
	function custom_scripts(){
		if(apply_filters('buddypresstallyc_custom_css', false) == true){
			wp_dequeue_style( 'bp-legacy-css' );
			//wp_dequeue_script( 'bp-legacy-js' );
			wp_enqueue_style( 'buddypress-tally-c', BUDDYPRESSTALLYC_URL . 'assets/css/buddypress.css' );
		}
	}
	
	
	
	/** Add Sidebar To the theme ****************************************************/
	/**
	 * This function add "tally_buddypress" in the theme.
	 *
	 * @since 0.1
	 *
	 * @used with "tally_sidebar" hook
	 */
	function add_sidebar(){
		if($this->is_buddypress()){
			if ( ! dynamic_sidebar( 'tally_buddypress' ) && current_user_can( 'edit_theme_options' )  ) {
				if(function_exists('tally_default_widget_area_content')){ tally_default_widget_area_content( __( 'BuddyPress Sidebar Widget Area', 'buddypresstallyc_textdomain' ) ); };
			}	
		}
	}
	
	
	
	/** Disable Theme Sidebar *****************************************************/
	/**
	 * This function disable deafult sidebar of the theme
	 *
	 * @since 0.1
	 *
	 * @used with "tally_sidebar_active" filter
	 */
	function disable_theme_sidebar($active){
		if($this->is_buddypress()){
			$active = false;
		}
		
		return $active;
	}
	
	/** Fix bbPress Sidebar *****************************************************/
	/**
	 * This function remove bbpress sidebar from buddypress
	 *
	 * @since 0.1
	 *
	 * @used with "tally_sidebar_active" filter
	 */
	function fix_bbpress_sidebar(){
		global $bbpresstallyc;
		if($this->is_buddypress()){
			remove_action('tally_sidebar', array($bbpresstallyc, 'add_sidebar'));
		}
		//echo bp_current_component();
	}
	
	
	
	/** Sidebar Laouout option *****************************************************
	 * @since 0.1
	**/
	function sidebar_layout_option($sidebar_layout){
		global $wp_query;
		$custom_field = get_post_meta( get_the_ID(), 'tally_sidebar_layout', true );
		
		if($this->is_buddypress()){
			if(bp_current_component() == 'activity'){
				$sidebar_layout  = $custom_field ? $custom_field : tally_option('buddypress_activity_sidebar_layout');
			}elseif(bp_current_component() == 'friends'){
				$sidebar_layout  = $custom_field ? $custom_field : tally_option('buddypress_friends_sidebar_layout');
			}elseif(bp_current_component() == 'groups'){
				$sidebar_layout  = $custom_field ? $custom_field : tally_option('buddypress_groups_sidebar_layout');
			}elseif(bp_current_component() == 'messages'){
				$sidebar_layout  = $custom_field ? $custom_field : tally_option('buddypress_messages_sidebar_layout');
			}elseif(bp_current_component() == 'notifications'){
				$sidebar_layout  = $custom_field ? $custom_field : tally_option('buddypress_notifications_sidebar_layout');
			}elseif(bp_current_component() == 'profile'){
				$sidebar_layout  = $custom_field ? $custom_field : tally_option('buddypress_profile_sidebar_layout');
			}elseif(bp_current_component() == 'settings'){
				$sidebar_layout  = $custom_field ? $custom_field : tally_option('buddypress_settings_sidebar_layout');
			}elseif(bp_current_component() == 'forums'){
				$sidebar_layout  = $custom_field ? $custom_field : tally_option('buddypress_forums_sidebar_layout');
			}
		}
		
		return $sidebar_layout;
	}
	
	
	
	/** Add Theme Options *****************************************************
	 * @since 0.1
	**/
	function add_theme_options($custom_settings){
		$custom_settings['sections'][] = array( 'id' => 'buddypress','title' => 'BuddyPress');
		
		$custom_settings['settings']['buddypress_activity_sidebar_layout'] = array(
			'id'          => 'buddypress_activity_sidebar_layout',
			'label'       => __('Activity Pages', 'tally_taxdomain'),
			'desc'        => __('Sidebar layout for BuddyPress activity pages.', 'tally_taxdomain'),
			'std'         => tally_option_std('buddypress_activity_sidebar_layout'),
			'type'        => 'radio-image',
			'section'     => 'buddypress',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'choices'     => array(
				 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
				 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
				 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
				 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
				 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
				 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
			)
		);

		$custom_settings['settings']['buddypress_friends_sidebar_layout'] = array(
			'id'          => 'buddypress_friends_sidebar_layout',
			'label'       => __('Friends Pages', 'tally_taxdomain'),
			'desc'        => __('Sidebar layout for BuddyPress friends pages.', 'tally_taxdomain'),
			'std'         => tally_option_std('buddypress_friends_sidebar_layout'),
			'type'        => 'radio-image',
			'section'     => 'buddypress',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'choices'     => array(
				 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
				 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
				 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
				 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
				 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
				 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
			)
		);
		
		$custom_settings['settings']['buddypress_groups_sidebar_layout'] = array(
			'id'          => 'buddypress_groups_sidebar_layout',
			'label'       => __('Groups Pages', 'tally_taxdomain'),
			'desc'        => __('Sidebar layout for BuddyPress groups pages.', 'tally_taxdomain'),
			'std'         => tally_option_std('buddypress_groups_sidebar_layout'),
			'type'        => 'radio-image',
			'section'     => 'buddypress',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'choices'     => array(
				 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
				 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
				 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
				 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
				 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
				 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
			)
		);
		
		$custom_settings['settings']['buddypress_messages_sidebar_layout'] = array(
			'id'          => 'buddypress_messages_sidebar_layout',
			'label'       => __('Messages Pages', 'tally_taxdomain'),
			'desc'        => __('Sidebar layout for BuddyPress messages pages.', 'tally_taxdomain'),
			'std'         => tally_option_std('buddypress_messages_sidebar_layout'),
			'type'        => 'radio-image',
			'section'     => 'buddypress',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'choices'     => array(
				 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
				 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
				 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
				 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
				 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
				 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
			)
		);
		
		$custom_settings['settings']['buddypress_notifications_sidebar_layout'] = array(
			'id'          => 'buddypress_notifications_sidebar_layout',
			'label'       => __('Notifications Pages', 'tally_taxdomain'),
			'desc'        => __('Sidebar layout for BuddyPress notifications pages.', 'tally_taxdomain'),
			'std'         => tally_option_std('buddypress_notifications_sidebar_layout'),
			'type'        => 'radio-image',
			'section'     => 'buddypress',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'choices'     => array(
				 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
				 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
				 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
				 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
				 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
				 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
			)
		);
		
		$custom_settings['settings']['buddypress_profile_sidebar_layout'] = array(
			'id'          => 'buddypress_profile_sidebar_layout',
			'label'       => __('Profile Pages', 'tally_taxdomain'),
			'desc'        => __('Sidebar layout for BuddyPress profile pages.', 'tally_taxdomain'),
			'std'         => tally_option_std('buddypress_profile_sidebar_layout'),
			'type'        => 'radio-image',
			'section'     => 'buddypress',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'choices'     => array(
				 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
				 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
				 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
				 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
				 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
				 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
			)
		);
		
		$custom_settings['settings']['buddypress_settings_sidebar_layout'] = array(
			'id'          => 'buddypress_settings_sidebar_layout',
			'label'       => __('Settings Pages', 'tally_taxdomain'),
			'desc'        => __('Sidebar layout for BuddyPress settings pages.', 'tally_taxdomain'),
			'std'         => tally_option_std('buddypress_settings_sidebar_layout'),
			'type'        => 'radio-image',
			'section'     => 'buddypress',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'choices'     => array(
				 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
				 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
				 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
				 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
				 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
				 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
			)
		);
		
		$custom_settings['settings']['buddypress_forums_sidebar_layout'] = array(
			'id'          => 'buddypress_forums_sidebar_layout',
			'label'       => __('Forums Pages', 'tally_taxdomain'),
			'desc'        => __('Sidebar layout for BuddyPress forums pages.', 'tally_taxdomain'),
			'std'         => tally_option_std('buddypress_forums_sidebar_layout'),
			'type'        => 'radio-image',
			'section'     => 'buddypress',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'choices'     => array(
				 array( 'label' => 'full-width-content', 'value' => 'full-width-content', 'src' => TALLY_URL.'/core/assets/images/admin/c.gif'),
				 array( 'label' => 'Content - Sidebar', 'value' => 'content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/cs.gif'),
				 array( 'label' => 'Content - Sidebar - Sidebar', 'value' => 'content-sidebar-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/css.gif'),
				 array( 'label' => 'Sidebar - Content', 'value' => 'sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/sc.gif'),
				 array( 'label' => 'Sidebar - Content - Sidebar', 'value' => 'sidebar-content-sidebar', 'src' => TALLY_URL.'/core/assets/images/admin/scs.gif'),
				 array( 'label' => 'Sidebar - Sidebar - Content', 'value' => 'sidebar-sidebar-content', 'src' => TALLY_URL.'/core/assets/images/admin/ssc.gif'),
			)
		);
		
		return $custom_settings;
	}
	
	
	
	/** Condition *****************************************************
	 * @since 0.1
	**/
	function is_buddypress(){
		if(
		( bp_current_component() == 'activity' ) ||
		( bp_current_component() == 'forums' ) ||
		( bp_current_component() == 'friends' ) ||
		( bp_current_component() == 'groups' ) ||
		( bp_current_component() == 'messages' ) ||
		( bp_current_component() == 'notifications' ) ||
		( bp_current_component() == 'profile' ) ||
		( bp_current_component() == 'settings' )
		){
			return true;
		}else{
			return false;
		}
	}
	
}

$buddypresstallyc = new buddypresstallyc;