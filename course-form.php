<?php
/*
Plugin Name: Blondel Courses
Plugin URI: https://mancon.ae/portfolio/
Description: This is a customized Course Form Plugin made for AZtech Training Dubai
Version: 1.0
Author: Engr. Blondel Seumo
Author URI: https://mancon.ae/portfolio/
License: GPLv2
Copyright 2019 @BlondelSeumo (email : seumoblondel@gmail.com)
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('COURSE_FORM_URL', plugin_dir_url(__FILE__));
define('COURSE_FORM_DIR', plugin_dir_path(__FILE__));

require_once COURSE_FORM_DIR . 'functions.php';
require_once COURSE_FORM_DIR . 'course_list_table.php';

global $is_course_deleted;
$is_course_deleted	= false;

if( ! class_exists( 'Custome_Course_Form' ) ) :

class Custome_Course_Form {
	public function __construct() {
		$this->init();
	}
	
	public function init() {
		add_action( 'admin_menu', array( $this, 'course_form_admin_menu_add' ), 99 );		
		
		add_action( 'wp', array( $this, 'maybe_course_submit' ), 8 );
			
		add_action( 'wp_enqueue_scripts', array( $this, 'course_form_scripts' ) );
		
		add_action( 'load-toplevel_page_custome_courses', array( $this, 'custome_courses_add_screen_options' ) );
		
		add_filter( 'set-screen-option', array( $this, 'custome_courses_set_option' ), 10, 3 );
	}
	
	public function custome_courses_add_screen_options() {
		$option = 'per_page';
	 
		$args 	= array(
			'label' 	=> 'Courses',
			'default' 	=> 20,
			'option' 	=> 'custome_courses_per_page'
		);
	 
		add_screen_option( $option, $args );
	}

	public function custome_courses_set_option( $status, $option, $value ) {
	 
		if ( 'custome_courses_per_page' == $option ) 
			return $value;
	 
		return $status; 
	}

	public function course_form_scripts() {
		wp_enqueue_style( 'course_form', COURSE_FORM_URL . 'css/course_form.css' );
		wp_enqueue_script( 'course_form', COURSE_FORM_URL . 'js/course_form.js', array(), '', true );
	}

	public function course_form_admin_menu_add() {
		add_menu_page( 'Courses', 'Courses', 'administrator', 'custome_courses', array( $this, 'custome_courses_page' ) );		
		add_submenu_page( 'custome_courses', 'Add Course', 'Add Course', 'administrator', 'course_form_settings', array( $this, 'course_form_add_fnc' ) );	 
		 
	}
	
	
		 
	
	public function custome_courses_page() {
		global $wpdb;
	
		$course_list = new Course_List_Page();
		$course_list->prepare_items();
			
		?>
		<div class="wrap">
			
			<div style="clear:both;"></div>
			
			<div id="icon-users" class="icon32"><br/></div>
			<h2>Courses List</h2>
			
			<?php
			global $is_course_deleted;
			if( $is_course_deleted ){ ?>
			<div class="updated settings-error notice is-dismissible" style="margin: 0 0 20px; max-width: 845px;"> 
				<p><strong>Course deleted successfully.</strong></p>
				<button class="notice-dismiss" type="button">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</button>
			</div>
			<?php } ?>
				
			<div style="clear:both;"></div>
			
            <form id="images_table-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $course_list->display() ?>
			</form>
			
		</div>
		<?php	
	}
	 
	 
	public function course_form_add_fnc() {
		
		if( $_POST && isset( $_POST['course_add'] ) ){
			
			$course_title	= trim( sipost( 'course_title' ) );		
		
			if( $course_title ) {
				
				global $wpdb;
				
				$tablename = $wpdb->prefix . 'course_form'; 
				
				$current_login_user = wp_get_current_user();		 	
				$current_user_id 	= $current_login_user->ID ? $current_login_user->ID : ""; 
				
				$course_data = array(
					'user_id'				=> $current_user_id,
					'course_title'			=> ucwords ( $course_title ),					 
					'course_permalink'		=> sipost( 'course_permalink' ),	
					'course_content'		=> trim( sipost( 'course_content' ) ),	
					'starting_date'			=> sipost( 'starting_date' ),
					'end_date'				=> sipost( 'end_date' ),
					'created_at'			=> date( "Y-m-d H:i:s" ),
				);
				 
				// For Add Course records  and return last id
				$wpdb->insert( $tablename, $course_data );
	
			}
			
			
		}		 
		
		
		?>
		<div class="wrap">
            <h1>Add Course</h1>
            
            <form method="post">
            
	            <table class="form-table">
                 
							
                    <tr>
                        <th scope="row"><label for="course_title">Course Title *</label></th>
                        <td>
                            <input type="text" name="course_title" class="regular-text" id="course_title" placeholder="Course Title" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="course_permalink">Course Permalink *</label></th>
                        <td>
                            <input type="text" name="course_permalink" class="regular-text" id="course_permalink" placeholder="Course Permalink" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="starting_date">Starting Date *</label></th>
                        <td>
                            <input type="date" name="starting_date" class="regular-text" id="starting_date" placeholder="Starting Date" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="end_date">End Date *</label></th>
                        <td>
                            <input type="date" name="end_date" class="regular-text" id="end_date" placeholder="End Date" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="course_content">Course Content *</label></th>
                        <td>
                            <textarea name="course_content" class="regular-text" id="course_content" placeholder="Course Content" required rows="10"></textarea>
                        </td>
                    </tr> 
                
                </table>
                
                <p class="submit"><input type="submit" name="course_add" id="submit" class="button button-primary" value="Add Course"></p>
                
            </form>        
        </div>
    	<?php
	} 
	
	public function maybe_course_submit() {
		
		if( ! $_POST )
			return;
		
		if( ! isset( $_POST['course_add'] ) || ! $_POST['course_add'] )			
			return;
		
		$course_title	= trim( sipost( 'course_title' ) );		
		
		if( $course_title ) {
			
			global $wpdb;
			
			$tablename = $wpdb->prefix . 'course_form'; 
			
			$current_login_user = wp_get_current_user();		 	
			$current_user_id 	= $current_login_user->ID ? $current_login_user->ID : ""; 
			
			$course_data = array(
				'user_id'				=> $current_user_id,
				'course_title'			=> ucwords ( $course_title ),					 
				'course_permalink'		=> sipost( 'course_permalink' ),	
				'course_content'		=> trim( sipost( 'course_content' ) ),	
				'starting_date'			=> sipost( 'starting_date' ),
				'end_date'				=> sipost( 'end_date' ),
				'created_at'			=> date( "Y-m-d H:i:s" ),
			);
			 
			// For Add Course records  and return last id
			$wpdb->insert( $tablename, $course_data );

		}				
	}	
	
	
	public function get_domain_name() {
		$domain = site_url( "/" ); 
		$domain = str_replace( array( 'http://', 'https://', 'www' ), '', $domain );
		$domain = explode( "/", $domain );
		$domain	= $domain[0] ? $domain[0] : $_SERVER['SERVER_ADDR'];	
		
		return $domain;
	}
	
	public function base64_url_encode( $data ) { 
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' ); 
	}
	
	public function base64_url_decode( $data ) { 
		return base64_decode( str_pad( strtr( $data, '-_', '+/' ), strlen( $data ) % 4, '=', STR_PAD_RIGHT ) ); 
	}


	public function _log( $msg = "" ) {
		
		define("LOG_FILE", __DIR__ . "/course_form.log");
		
		$msg	= function_exists( 'maybe_unserialize' ) ? maybe_unserialize( $msg ) : $msg;
		
		$msg	= ( is_array( $msg ) || is_object( $msg ) ) ? print_r( $msg, 1 ) : $msg;
		 	
		error_log( date('[Y-m-d H:i:s e] ') . $msg . PHP_EOL, 3, LOG_FILE );
	}
}

endif;

$custome_course_form	= new Custome_Course_Form();



// install course form users 
function install_course_form_user() {
	
	global $wpdb;
	
	$table_name 		= $wpdb->prefix . 'course_form';
	
	$charset_collate 	= $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
	  	id mediumint(9) NOT NULL AUTO_INCREMENT,
	  	user_id mediumint(9) NOT NULL,
		course_title varchar(255)  NOT NULL,
		course_permalink  varchar(100)  NOT NULL,
		course_content  longtext NOT NULL,
		starting_date  varchar(100)  NOT NULL,
		end_date  varchar(100)  NOT NULL,
		course_image  varchar(100)  NOT NULL,
		course_status	int(1) DEFAULT '0' NOT NULL,	
		updated_at datetime NOT NULL,
		created_at datetime NOT NULL,
	  	PRIMARY KEY  (id)
	) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'install_course_form_user' );


// uninstall 
function uninstall_course_form_user(){
	global $wpdb;
	
	$table_name	= $wpdb->prefix . 'course_form';
	$sql 		= "DROP TABLE IF EXISTS $table_name";
   	$wpdb->query( $sql );
}
register_uninstall_hook( __FILE__, 'uninstall_course_form_user' );