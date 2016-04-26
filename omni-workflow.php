<?php

/*------------------------------------------------------------------------------
Plugin Name: Omni-Workflow
Description: A WordPress Workflow Engine that is BPMN 2.0 compliant
Author: Ralph Hanna
Version: 1.12
License:     GPL2
Author URI: http://workflow.omnibuilder.com
Plugin URI: http://workflow.omnibuilder.com

Omni-Workflow is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Omni-Workflow is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Omni-Workflow. If not, see {License URI}.
------------------------------------------------------------------------------*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


// Define certain plugin variables as constants.
define( 'OMNIWORKFLOW_ABSPATH', plugin_dir_path( __FILE__ ) );
define( 'OMNIWORKFLOW__FILE__', __FILE__ );
define( 'OMNIWORKFLOW_BASENAME', plugin_basename( OMNIWORKFLOW__FILE__ ) );


include_once (__DIR__.'/_startup.php');


OmniWorkFlowPlugin::init();


class OmniWorkFlowPlugin {

    static $processed=false;
    static $output;
static function omniworkflow_add_custom_menu() {
    //add an item to the menu
        self::debug( "omniworkflow_add_custom_menu");
    add_menu_page (
        'Omni-Workflow',
        'Workflow',
        'manage_options',
        'omni-workflow',
        array( get_called_class(),'omniworkflow_admin_page'),
        plugin_dir_url( __FILE__ ).'images/omniworkflow_icon.png',
        '23.56'
    );
}

static function omniworkflow_get_excerpt($excerpt )
{
        self::debug( "get_excerpt");
    
}
static function omniworkflow_scripts_method() {
	
        self::debug( "omniworkflow_scripts_method");

	OmniFlow\Helper::HeaderInclude(__FILE__);
	
	wp_enqueue_script( 'local-script', plugins_url( '/js/local.js', __FILE__ ), array('jquery') );
	
	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'local-script', 'ajax_object',
			array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
	
}
static function omniworkflow_include()
{
    
        self::debug( "omniworkflow_include");
	include_once (__DIR__.'\_startup.php');
	include_once (__DIR__.'\classes\Context.php');
	include_once (__DIR__.'\views\views.php');
	include_once (__DIR__.'\controllers\controller.php');
	include_once (__DIR__.'\config.php');
        
        $dir1=plugin_dir_url( __FILE__ );
        $dir2=plugins_url(basename(__DIR__));
        OmniFlow\Context::getInstance()->omniBaseURL=plugins_url(basename(__DIR__)).'/';

}
static function omniworkflow_admin_page($content)
{	
        self::debug( "omniworkflow_admin_page");

        echo  self::omniworkflow_page_content($content,true);

}

static function omniworkflow_page_content($content,$admin=false)
{
    
    if (isset($_REQUEST['_processed_']))
        return self::$output;
    
    self::debug( "omniworkflow_page_contents");
	self::omniworkflow_include();

        ob_start();
        
        OmniFlow\Context::Log('INFO', "omniworkflow_page_content");
        if ($admin)
        {
            echo '<script>
                   var omni_admin_page=true; 
                 </script>';        

            OmniFlow\Config::$pageUrl="page=omni-workflow";
        }
        
        OmniFlow\Context::getInstance()->fromWordPress=true;

        if ( is_user_logged_in() )
        {
           $current_user = wp_get_current_user();
           
           $user= OmniFlow\Context::getUser();
           $user->id=$current_user->data->ID;
           $user->name=$current_user->data->display_name;
           $user->email=$current_user->data->user_email;
           $user->roles=$current_user->roles;
           
            if (current_user_can('omni_model')) 
                $user->addCapability('model');
            if (current_user_can('omni_design')) 
                $user->addCapability('design');
            if (current_user_can('omni_view_model')) 
                $user->addCapability('view_model');
            if (current_user_can('omni_view_design')) 
                $user->addCapability('view_design');
            if (current_user_can('omni_admin')) 
                $user->addCapability('admin');

            OmniFlow\Context::debug("user info ".var_export($user,true));


        } else {
           $user->id=null;
        }

        $pagid=get_option( 'omniworkflow_page_id');

        $url = add_query_arg(
            'redirect_to',
            get_permalink($pagid),
            site_url('wp-login.php')
            );
        OmniFlow\Context::getInstance()->loginURL=$url;
        
        
        $site=  OmniFlow\Context::getSite();
        
        foreach($GLOBALS['wp_roles']->role_objects as $name => $specs) {
            $role=Array();
            $role['name']=$name;
            $site->userRoles[]=$role;
        }

	$contr=new OmniFlow\Controller();
        
        $contr->Action($_REQUEST);
        
    self::debug( "omni-workflow:omniworkflow_page_content end");
    self::$output=ob_get_clean();
    $_REQUEST['_processed_']='Yes';
    return self::$output;
}

static function checkIfLoggedIn()
{
    if ( ( is_single() || is_front_page() || is_page() ) 
       && !is_page('login') && !is_user_logged_in())
    {
        $url = add_query_arg(
            'redirect_to',
            get_permalink($pagid),
            site_url('wp-login.php')
        );
     
    $page="<a href=$url>Please login</a>
<br />To login use the following userids
<table>
    <tr><td>user name</td><td>password</td><td>function</td></tr>
    <tr><td>analyst1</td><td>demo</td><td>To Model and Design Processes</td></tr>
    <tr><td>employee1</td><td>demo</td><td>To create an expense </td></tr>
    <tr><td>manager1</td><td>demo</td><td>To approve the expense</td></tr>
    <tr><td>accounting1</td><td>demo</td><td>To review and process the expense</td></tr>
</table>    ";
    return $page;
    }
    return null;
}
static function omniworkflow_content_filter($content) {

    $post=$GLOBALS['post'];
    global $wp_query;
    
    $pg=$wp_query->query['pagename'];
    
//  if ($GLOBALS['post']->post_name == 'omniworkflow') {
  if   ($pg==='omniworkflow') {

    self::debug( "omni-workflow:omniworkflow_content_filter");
    
	return self::omniworkflow_page_content($content);
  }
  // otherwise returns the database content
  return $content;
}



// Add Shortcodes

static function debug($msg)
{
//    error_log(($msg);
}
static function  omniworkflow_register_shortcodes() {
    self::debug( "omni-workflow:omniworkflow_register_shortcodes");

	add_shortcode( 'omni-wf-form', array( get_called_class(),'omni_wf_shortcodeform') );
	add_shortcode( 'omni-wf', array( get_called_class(),'omni_wf_shortcode') );
}
static function omni_enqueue($hook) {

	wp_enqueue_script( 'local-script', plugins_url( '/js/local.js', __FILE__ ), array('jquery') );
	
		// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'local-script', 'ajax_object',
				array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );

}
/*
 * respond to Ajax Call
 */
static function omni_ajax_call(){
    self::debug( "omni-workflow:omni_ajax_call");
	global $wpdb; // this is how you get access to the database

	$_REQUEST['action']=$_REQUEST['command'];
        
        echo self::omniworkflow_page_content('');

	wp_die(); // this is required to terminate immediately and return a proper response
}

static function omni_wf_forms_register(){
    self::debug( "omni-workflow:omni_wf_forms_register");
	
	add_action( 'ninja_forms_display_init',
			'omni_wf_form_loadForm' );
	
	add_action( 'ninja_forms_post_process',
			'omni_wf_form_postProcess' );
}

static function omni_wf_form_loadForm(){
    self::debug( "omni-workflow:omni_wf_form_loadForm");
	global $ninja_forms_processing;

	include_once(__DIR__."/classes/NinjaForm.php");
	self::omniworkflow_include();
	NinjaForms::formDisplay();
}

static function omni_wf_form_postProcess(){
    self::debug( "omni-workflow:omni_wf_form_postProcess");
	global $ninja_forms_processing;

	self::omniworkflow_include();
	include_once(__DIR__."/classes/NinjaForm.php");
	NinjaForms::formProcessed();
}

/*
	shortcodes are
	[omni-wf option='dashboard']
	
*/
static function omni_wf_shortcode( $atts ) {
    self::debug( "omni-workflow:omni_wf_shortcode");

	return self::omniworkflow_page_content();
}
static function omni_wf_shortcodeform( $atts ) {
    self::debug( "omni-workflow:omni_wf_shortcodeform");
	$output="";
	foreach($atts as $att=>$val)
	{
		$output.=", attribute : $att =$val";
	}
	$output.='';

	return $output;
}

 
static function omniworkflow_add_cron_interval( $schedules ) {
    $schedules['omniworkflow'] = array(
        'interval' => 5,
        'display'  => esc_html__( 'Every Five Seconds' ),
    );
 
    return $schedules;
}
public static function on_uninstall()
{
    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();
    $om=new OmniFlow\OmniModel();
    $om->uninstallDB();    
    
    
    delete_option("omniworkflow_page_title");
    delete_option("omniworkflow_page_name");
    delete_option("omniworkflow_page_id");
    
}
public static function omni_default_styles($styles)
{
	//use release date for version
	$styles->default_version = "1.2001";
}

/*
 *  installation
 */
public static function on_activation()
{
    self::debug( "omni-workflow:on_activation");

    $custom_caps =array('omni_admin','omni_design','omni_model','omni_view_model','omni_view_design');
    $min_cap    = 'the_minimum_required_built_in_cap'; // Check "Roles and objects table in codex!
    $grant      = true; 

    $om=new OmniFlow\OmniModel();
    $om->installDB();    
    foreach ($GLOBALS['wp_roles']->role_objects as $key => $role)
    {
        echo $key;
        if ($key=='administrator') 
            $grant=true;
        else
           $grant=false;
        
        foreach($custom_caps as $cap) {
            $role->add_cap( $cap,$grant );
            }
    }
    // adding custom pages
    
    
        
    $pageTitle = 'OmniWorflow';
    $pageName = 'omniworkflow';

    // the menu entry...
    delete_option("omniworkflow_page_title");
    delete_option("omniworkflow_page_name");
    delete_option("omniworkflow_page_id");
    
    add_option("omniworkflow_page_title", $pageTitle, '', 'yes');
    add_option("omniworkflow_page_name", $page_name, '', 'yes');
        
        
    $page = get_page_by_title( $pageTitle );
    $page = get_page_by_path( $pageName );
    
    
    if ( ! $page ) {

        // Create post object
        $_p = array();
        
        $_p['post_name'] = $pageName;
        $_p['post_title'] = $pageTitle;
        $_p['post_content'] = "This page is OmniWorkflow home page. This text may be overridden by the plugin. You shouldn't edit it.";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $page_id = wp_insert_post( $_p );

    }
    else {
        // the plugin may have been previously active and the page may just be trashed...

        $page_id = $page->ID;

        //make sure the page is not trashed...
        $page->post_status = 'publish';
        $page_id = wp_update_post( $page );
    }
    
    
    add_option( 'omniworkflow_page_id', $page_id );

    
    // add demo model
    
           $current_user = wp_get_current_user();
           
           $user= OmniFlow\Context::getUser();
           $user->id=$current_user->data->ID;
           $user->name=$current_user->data->display_name;
           $user->email=$current_user->data->user_email;
           $user->roles=$current_user->roles;    
    $req['action']='modeler.installDemoModel';
    $cont=new \OmniFlow\Controller();
    $cont->Action($req);
    
    ob_clean();
    
}
	public static function registerMessages()
	{
                self::debug( "omni-workflow:registerMessages");
	}

	public static function init() {
                self::debug( "omni-workflow init");

		add_filter( 'the_content', array( get_called_class(), 'omniworkflow_content_filter') );
		add_action( 'wp_enqueue_scripts',array( get_called_class(),  'omniworkflow_scripts_method') );
		add_action( 'admin_enqueue_scripts',array( get_called_class(),  'omniworkflow_scripts_method') );
                
		add_action( 'admin_menu',array( get_called_class(),  'omniworkflow_add_custom_menu') );
		add_action( 'init', array( get_called_class(), 'omniworkflow_register_shortcodes') );
		add_filter( 'cron_schedule',array( get_called_class(),  'omniworkflow_add_cron_interval') );
                
                add_filter( 'get_the_excerpt', array( get_called_class(),  'omniworkflow_get_excerpt') );
                
                
//		add_action( 'init',array( get_called_class(),	'omni_wf_forms_register' ));
		self::registerMessages();
		
//		add_action( 'admin_enqueue_scripts', array( get_called_class(),'omni_enqueue' ));
		/* ajax calls */
		add_action( 'wp_ajax_omni_ajax', array( get_called_class(),'omni_ajax_call' ));
		add_action( 'wp_ajax_omni_ajax_call', array( get_called_class(),'omni_ajax_call' ));
		add_action( 'wp_ajax_nopriv_omni_ajax_call', array( get_called_class(),'omni_ajax_call' ));
                
		add_action( 'wp_default_styles', array( get_called_class(),'omni_default_styles' ));
                add_action("", "my_wp_default_styles");
                
                register_activation_hook(   __FILE__, array( get_called_class(), 'on_activation' ) );                
                register_uninstall_hook(__FILE__, array( get_called_class(), 'on_uninstall' ) );                
                
                
	}

}
