<?php
/**
 * Report Event Handler
 *
 * @class 		CPM_Report
 * @version		1.2
 */
class CPM_Pro_Report {
	/**
     * @var The single instance of the class
     * @since 1.2
     */
    protected static $_instance = null;

    /**
     * @var $_POST or $_GET data
     * @since 1.2
     */
    protected static $form_data = null;

	/**
     * Main Instance
     *
     * @since 1.2
     * @return Main instance
     */
    public static function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new CPM_Pro_Report();
        }
        return self::$_instance;
    }

    /**
     * Class initial do
     *
     * @since 1.2
     * @return type
     */
    function __construct() {
        add_action( 'init', array( $this, 'report_form_redirect' ) );
    }

    /**
     * Redirect report form data
     *
     * @since 1.2
     * @return type
     */
    public static function report_form_redirect() {

        if ( isset( $_POST['cpm-report-generat'] ) ) {
            $url = add_query_arg( $_POST, cpm_report_page_url() );
            wp_redirect( $url );
        }

        if ( isset( $_POST['cpm_report_csv_generat'] ) ) {
            self::download_send_headers("Project-manager-report-" . date("Y-m-d") . ".csv");

            $data = $_POST;
            self::csv_generate( $data );
            exit();
        }
    }

    /**
     * Send header
     *
     * @param str $filename
     * @since 1.2
     * @return type
     */
    public static function download_send_headers( $filename ) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename='. $filename);
        header('Pragma: no-cache');
        header("Expires: 0");
    }

    /**
     * Report CSV file generate
     *
     * @param object $data
     * @since 1.2
     * @return type
     */
    public static function csv_generate( $data ) {

        $output = fopen("php://output", "w");
        $reports = self::report_generate( $data );
        $posts = $reports->posts;

        if ( in_array( 'co-worker', $data['action'] ) && $data['co_worker'] != '-1' ) {
            $uesr_restrict = true;
        } else {
            $uesr_restrict = false;
        }
        $items = array();
        $start_enable = cpm_get_option('task_start_field');

        $time      = false;
        $task_mode = false;
        $list_mode = false;
        $items     = array();

        if ( in_array( 'time', $data['action'] ) ) {
            if ( $data['interval'] == 1  ) {
                $interval = 'post_year';
            } else if ( $data['interval'] == 2 ) {
                $interval = 'post_month';
            } else if ( $data['interval'] == 3 ) {
                $interval = 'post_week';
            }

            foreach ( $posts as $key => $obj ) {
                if ( !isset( $obj->list_id ) && !$obj->list_id ) {
                    continue;
                }
                if ( !isset( $obj->task_id ) && !$obj->task_id ) {
                    continue;
                }

                $assigned_to = get_post_meta( $obj->task_id, '_assigned' );

                //when search by uesr
                if ( $uesr_restrict ) {
                    if ( in_array( $data['co_worker'], $assigned_to ) ) {
                        $items[$obj->$interval][$obj->ID][$obj->list_id][$obj->task_id] = $key;
                    }
                } else {
                    $items[$obj->interval][$obj->ID][$obj->list_id][$obj->task_id] = $key;
                }

            }
        } else {
            foreach ( $posts as $key => $obj ) {
                if ( !isset( $obj->list_id ) && !$obj->list_id ) {
                    continue;
                }
                if ( !isset( $obj->task_id ) && !$obj->task_id ) {
                    continue;
                }
                $assigned_to = get_post_meta( $obj->task_id, '_assigned' );
                //when search by uesr
                if ( $uesr_restrict ) {
                    if ( in_array( $data['co_worker'], $assigned_to ) ) {
                        $items[$obj->ID][$obj->list_id][$obj->task_id] = $key;
                    }
                } else {
                    $items[$obj->ID][$obj->list_id][$obj->task_id] = $key;
                }
            }
        }

        if ( in_array( 'time', $data['action'] ) ) {
            $time = true;

            if ( $data['interval'] == 1  ) {
                $interval = 'post_year';
                $interval_view = __( 'Year', 'cpm' );
            } else if ( $data['interval'] == 2 ) {
                $interval = 'post_month';
                $interval_view = __( 'Month', 'cpm' );
            } else if ( $data['interval'] == 3 ) {
                $interval = 'post_week';
                $interval_view = __( 'Week', 'cpm' );
            }

            if ( $data['timemode'] == 'list' ) {
                $list_mode = true;
            } else if ( $data['timemode'] == 'task' ) {
                $task_mode = true;
            }

            $from = $data['from']  ? $data['from'] : current_time( 'mysql' );
            $to   = $data['to'] ? $data['to'] : current_time( 'mysql' );
        }

        if ( ! $items ) {
            _e( 'No result found!', 'cpm' );
            return;
        }

        $i = 1;
        if ( in_array( 'time', $data['action'] ) ) {
            foreach ( $items as $key => $item ) {

                if ( $data['interval'] != '-1' ) {
                    echo cpm_ordinal( $i ) .' '. $interval_view . '  ';
                } else {
                    echo $interval_view . '  ';
                }


                foreach ($item as $project_id => $projects ) {
                    $project = get_post($project_id);
                    _e( 'Project Title: ', 'cpm' ) . '  ';
                    echo $project->post_title . "\n";

                    foreach ($projects as $list_id => $lists ) {
                        $list = get_post($list_id);
                        _e('Task List Title: ', 'cpm' ) . '  ';
                        echo $list->post_title . "\n";

                        $task_cell   = __( 'Task', 'cpm' );
                        $assign_cell = __( 'Assign To', 'cpm' );
                        $sdate_cell  = __( 'Start Date', 'cpm' );
                        $edate_cell  = __( 'Due Date', 'cpm' );
                        $status_cell = __( 'Status', 'cpm' );

                        echo "$task_cell, $assign_cell, $sdate_cell, $edate_cell, $status_cell \n";

                        foreach ($lists as $task_id => $tasks) {
                            $task = cpm()->task->get_task( $task_id );
                            if ( $start_enable == 'on' ) {
                                $start_date = $task->start_date;
                            } else {
                                $start_date = $task->post_date;
                            }
                            //when search by uesr
                            if ( $uesr_restrict ) {
                                if ( !in_array( $data['co_worker'], $task->assigned_to ) ) {
                                    continue;
                                }
                            }
                            echo $task->post_title . ",";

                            foreach ( $task->assigned_to as $user_id ) {
                                $user = get_user_by( 'id', $user_id );
                                echo $user->display_name . '  ';
                            }
                            echo "," . cpm_get_date_without_html( $start_date ) . ",";
                            echo cpm_get_date_without_html( $task->due_date) . ",";

                            $status = $task->completed ? __( 'Completed', 'cpm' ) : __( 'Incompleted', 'cpm' );
                            echo $status . "\n";
                        }

                        echo "\n";
                    }
                }

                $i++;
            }
        } else {

            foreach ($items as $project_id => $projects ) {
                $project = get_post($project_id);

                _e( 'Project Title: ', 'cpm' ) . '  ';
                echo $project->post_title . "\n";

                foreach ($projects as $list_id => $lists ) {
                    $list = get_post($list_id);
                    _e('Task List Title: ', 'cpm' ) . '  ';
                    echo $list->post_title . "\n";

                    $task_cell   = __( 'Task', 'cpm' );
                    $assign_cell = __( 'Assign To', 'cpm' );
                    $sdate_cell  = __( 'Start Date', 'cpm' );
                    $edate_cell  = __( 'Due Date', 'cpm' );
                    $status_cell = __( 'Status', 'cpm' );

                    echo "$task_cell, $assign_cell, $sdate_cell, $edate_cell, $status_cell \n";

                    foreach ($lists as $task_id => $tasks) {
                        $task = cpm()->task->get_task( $task_id );
                        if ( $start_enable == 'on' ) {
                            $start_date = $task->start_date;
                        } else {
                            $start_date = $task->post_date;
                        }
                        //when search by uesr
                        if ( $uesr_restrict ) {
                            if ( !in_array( $data['co_worker'], $task->assigned_to ) ) {
                                continue;
                            }
                        }
                        echo $task->post_title . ",";

                        foreach ( $task->assigned_to as $user_id ) {
                            $user = get_user_by( 'id', $user_id );
                            echo $user->display_name . '  ';
                        }

                        echo "," . cpm_get_date_without_html( $start_date ) . ",";
                        echo cpm_get_date_without_html( $task->due_date) . ",";

                        $status = $task->completed ? __( 'Completed', 'cpm' ) : __( 'Incompleted', 'cpm' );
                        echo $status . "\n";
                    }

                    echo "\n";
                }
            }
        }
    }

	/**
	 * Report header
	 *
	 * @version 1.2
	 * @return type
	 */
	function get_header() {
		cpmpro()->pro_router->get_report_header();
	}

    /**
     * Report generate
     *
     * @param object $data
     * @version 1.2
     * @return object
     */
    public static function report_generate( $data ) {
        self::$form_data = $data;

        $args = array(
            'post_type'      => 'cpm_project',
            'post_status'    => 'publish',
            'posts_per_page' => '-1',
        );

        self::project_select( $args, $data );
        self::project_date_query( $args, $data );
        self::project_status_query( $args, $data );

        add_filter( 'posts_join', array( cpm()->report, 'co_worker_table' ) );
        add_filter( 'posts_where', array( cpm()->report, 'co_worker_where' ) );
        add_filter( 'posts_fields', array( cpm()->report, 'select_field' ), 10, 2 );
        add_filter( 'posts_groupby', array( cpm()->report, 'posts_groupby' ) );

        $args = apply_filters( 'cpm_report_args', $args );
        $results = new WP_Query($args);

        remove_filter( 'posts_join', array( cpm()->report, 'co_worker_table' ) );
        remove_filter( 'posts_where', array( cpm()->report, 'co_worker_where' ) );
        remove_filter( 'posts_fields', array( cpm()->report, 'select_field' ), 10, 2 );
        remove_filter( 'posts_groupby', array( cpm()->report, 'posts_groupby' ) );

        return $results;
    }

    /**
     * Remove group by from wp_query
     *
     * @version 1.2
     * @return str
     */
    function posts_groupby( $groupby ) {
        return '';
    }

    /**
     * Render report table
     *
     * @param array $post
     * @param array $data
     *
     * @version 1.2
     * @return str
     */
    public static function render_table( $posts, $data ) {
        ob_start();
        cpmpro()->pro_router->generate_report_table( $posts, $data );

        return ob_get_clean();
    }

    /**
     * Render report table
     *
     * @param init $project_id
     *
     * @version 1.2
     * @return str
     */
    public static function get_tasklist_task( $project_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'cpm_project_items';

        $sql = "SELECT * FROM {$table} WHERE item_type IN( 'task_list', 'task' ) AND project_id = $project_id";
        return $wpdb->get_results( $sql );
    }

    /**
     * Select field
     *
     * @version 1.2
     * @return str
     */
    public static function select_field( $fields, $self ) {
        $data = self::$form_data;

        global $wpdb;
        $post = $wpdb->prefix . 'posts';
        $start_date_enable = cpm_get_option( 'task_start_field' );

        if ( in_array( 'time', $data['action'] ) && $data['timemode'] == 'task' ) {
            if ( $start_date_enable == 'on' ) {
                $interval = "YEAR(tskmeta.meta_value) AS post_year, MONTH(tskmeta.meta_value) AS post_month,
                WEEK(tskmeta.meta_value) as post_week, tsk.ID as task_id, tsk.post_title as task_title,
                tl.ID as list_id, tl.post_title as list_title ";
            } else {
                $interval = "YEAR($post.post_date) AS post_year, MONTH($post.post_date) AS post_month,
                WEEK($post.post_date) AS post_week,
                tsk.ID as task_id, tsk.post_title as task_title, tl.ID as list_id, tl.post_title as list_title";
            }
        } else {
            $interval = "YEAR($post.post_date) AS post_year, MONTH($post.post_date) AS post_month,
            WEEK($post.post_date) AS post_week, tl.ID as list_id,
            tl.post_title as list_title, tsk.ID as task_id, tsk.post_title as task_title";
        }

        $fields .= ", $interval ";
        return $fields;
    }

    /**
     * Co worker query where condition
     *
     * @param str $where
     *
     * @version 1.2
     * @return str
     */
    public static function co_worker_where( $where ) {
        $data = self::$form_data;

        global $wpdb;
        $post = $wpdb->prefix . 'posts';

        $start_date_enable = cpm_get_option( 'task_start_field' );

        if ( in_array( 'co-worker', $data['action'] ) &&  $data['co_worker'] != '-1' ) {
            $user_id = $data['co_worker'];
            $table = $wpdb->prefix . 'cpm_user_role';
            $where .= " AND ur.user_id = $user_id";
        }

        if ( in_array( 'time', $data['action'] ) && $data['timemode'] == 'list' ) {

            $from = $data['from'];
            $to   = $data['to'];
            $from = $from ? date( 'Y-m-d H:i:m', strtotime( $from ) ) : current_time( 'mysql' );
            $to   = $to ? date( 'Y-m-d H:i:m', strtotime( $to ) ) : current_time( 'mysql' );
            $where .= " AND tl.post_type='cpm_task_list' AND tl.post_date >= '$from' AND tl.post_date <= '$to'";

        } else if ( in_array( 'time', $data['action'] ) && $data['timemode'] == 'task' ) {

            $from = $data['from'];
            $to   = $data['to'];
            $from = $from ? date( 'Y-m-d H:i:m', strtotime( $from ) ) : current_time( 'mysql' );
            $to   = $to ? date( 'Y-m-d H:i:m', strtotime( $to ) ) : current_time( 'mysql' );

            if ( $start_date_enable == 'on' ) {
                $where .= " AND tsk.post_type='cpm_task' AND tskmeta.meta_key = '_start' AND tskmeta.meta_value >= '$from' AND tskmeta.meta_value <= '$to'";
            } else {
                $where .= " AND tsk.post_type='cpm_task' AND tsk.post_date >= '$from' AND tsk.post_date <= '$to'";
            }

        }

        return $where;
    }

    /**
     * Co worker tabel join
     *
     * @param str $join
     *
     * @version 1.2
     * @return str
     */
    public static function co_worker_table( $join ) {
        $data = self::$form_data;

        global $wpdb;
        $table = $wpdb->prefix . 'posts';
        $table_post_meta = $wpdb->prefix . 'postmeta';
        $start_date_enable = cpm_get_option( 'task_start_field' );

        if ( $start_date_enable == 'on' ) {
            $start_query = " LEFT JOIN {$table_post_meta} as tskmeta ON tskmeta.post_id = tsk.ID";
        } else {
            $start_query = '';
        }

        if ( in_array( 'co-worker', $data['action'] ) ) {
            $user_table = $wpdb->prefix . 'cpm_user_role';
            $join .= " LEFT JOIN {$user_table} AS ur ON $table.ID = ur.project_id";
        }

        $join .= " LEFT JOIN {$table} AS tl ON $table.ID = tl.post_parent
        LEFT JOIN {$table} AS tsk ON tl.ID = tsk.post_parent $start_query";

        return $join;
    }

    /**
     * Select all project or specific project
     *
     * @param array $args
     * @param array $data
     *
     * @version 1.2
     * @return type
     */
    public static function project_select( &$args, $data ) {
        if ( !in_array( 'project', $data['action'] ) ) {
            return;
        }
        if ( $data['project'] == '-1' ) {
            return;
        }
        $args = array_merge( $args, array( 'p' =>  $data['project'] ) );
    }

    /**
     * Select project by date
     *
     * @param array $args
     * @param array $data
     *
     * @version 1.2
     * @return type
     */
    public static function project_date_query( &$args, $data ) {

        if ( !in_array( 'time', $data['action'] ) ) {
            return;
        }
        if ( !isset( $data['from'] ) ) {
            return;
        }

        if ( $data['timemode'] != 'project' ) {
            return;
        }

        $date['date_query'] = array();

        $per_date = array(
            'after'     => isset( $data['from'] ) && $data['from'] ? $data['from'] : current_time( 'mysql' ),
            'before'    => isset( $data['to'] ) && $data['to'] ? $data['to'] : current_time( 'mysql' ),
            'inclusive' => true,
        );

        $date['date_query'] = array_merge( $date['date_query'], $per_date );
        $args = array_merge( $args, $date );
    }

     /**
     * Select project by activity status
     *
     * @param array $args
     * @param array $data
     *
     * @version 1.2
     * @return type
     */
    public static function project_status_query( &$args, $data ) {
        if ( !in_array( 'status', $data['action'] ) ) {
            return;
        }

        if ( $data['status'] == '-1' ) {
            return;
        }

        $meta['meta_query'] = array();
        $status[] = array(
            'key'     => '_project_active',
            'value'   => $data['status'] == 1 ? 'yes' : 'no',
            'compare' => '=',
        );

        $meta['meta_query'] = array_merge( $meta['meta_query'], $status );

        $args = array_merge( $args, $meta );
    }
}




