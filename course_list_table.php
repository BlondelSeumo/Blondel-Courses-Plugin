<?php

if( ! class_exists('WP_List_Table') ){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Course_List_Page extends WP_List_Table {

    var $_data = array();

	function __construct(){

        global $status, $page;
		
        //Set parent defaults

        parent::__construct( array(
            'singular'  => 'Course',     	//singular name of the listed records
            'plural'    => 'Courses',    	//plural name of the listed records
            'ajax'      => true       		//does this table support ajax?
        ) );
    }

	function column_default( $item, $column_name ){
        switch( $column_name ){
            case 'name':
                return $item[ $column_name ];						
			default:
                return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';	//print_r( $item, true ) //Show the whole array for troubleshooting purposes
        }
    }

	function column_name( $item ){
        //Build row actions
        $actions = array(
            //'edit'      => sprintf( '<a href="post.php?post=%s&action=edit">Edit</a>', $item['ID'] ),
            //'delete'    => sprintf( '<a href="?page=%s&action=%s&donation[]=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['ID'] ),
        );

        //Return the title contents
        return sprintf( '%1$s %3$s',	// <span style="color:silver">(id:%2$s)</span>
            /*$1%s*/ $item['name'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions( $actions )
        );
    }

    function column_cb( $item ){

        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns(){

        $columns = array( 
            'course_title'		=> 'Course Title',	
			'course_permalink'	=> 'Permalink',	
			'starting_date'		=> 'Starting Date',	
			'end_date'			=> 'End Date',	
			'created_at'		=> 'Date/Time',	
        );

        return $columns;
    }

    function get_sortable_columns() {
		
        $sortable_columns = array(
            //'name'	=> array( 'name', true ),     //true means it's already sorted
            //'url'  	=> array('url', false)
        );

        return $sortable_columns;
    }

    function get_bulk_actions() {

        $actions = array(
        	//'delete'    => 'Delete'
        );
		
        return $actions;
    }

    function process_bulk_action() {

        //Detect when a bulk action is being triggered...

        if( 'delete' === $this->current_action() ) {

			global $wpdb;

			$items	= $_GET['pcourse'];

			if( is_array( $items ) && $items ) {
				foreach( $items as $single_item ) {
					
					$wpdb->delete( $wpdb->prefix . "course_form", array( 'id' => $single_item ) );
				}

				global $is_donation_deleted;
				$is_donation_deleted	= true;	
			}		

			//wp_die('Items deleted (or they would be if we had items to delete)!');
        }        
    }

    function prepare_items() {

        global $wpdb; //This is used only if making any database queries

		// Get Per Page

        $user_ID 	= get_current_user_id();

		$screen 	= get_current_screen();

		$option 	= $screen->get_option( 'per_page', 'option' );

		$per_page 	= get_user_meta( $user_ID, $option, true );

		if ( empty ( $per_page ) || $per_page < 1 ) {
    		$per_page	= $screen->get_option( 'per_page', 'default' );
		}		

		// Get Columns
        $columns 	= $this->get_columns();

        $hidden 	= array();

        $sortable 	= $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );

        $this->process_bulk_action();        

		// For search
		$search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;

        //$data = $this->_data;

        $data	= array();

		$all_courses	= $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "course_form ORDER BY id DESC" );

		if( $all_courses ) {
			foreach( $all_courses as $single_course ) {
				$data[]	= array( 
								"id"				=> $single_course->id,
								'course_title'		=> $single_course->course_title,
								'course_permalink'	=> $single_course->course_permalink,
								'starting_date'		=> $single_course->starting_date,
								'end_date'			=> $single_course->end_date,
								'created_at'		=> $single_course->created_at,	
								);

			}
		}
		
		function usort_reorder( $a, $b ){

            $orderby 	= ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'course_title'; 	//If no sort, default to title

            $order 		= ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; 		//If no order, default to asc

            $result 	= strcmp( $a[ $orderby ], $b[ $orderby ] ); 		//Determine sort order

            return ( $order === 'asc' ) ? $result : -$result; 		//Send final sort direction to usort
        }

        usort($data, 'usort_reorder');

        $current_page 	= $this->get_pagenum();

        $total_items 	= count( $data );
		
        $data 			= array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

        $this->items = $data;

		$this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
        ) );
    }
}