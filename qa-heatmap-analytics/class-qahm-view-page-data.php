<?php
/**
 * リプレイビューでの操作をやりやすくするクラス
 *
 * @package qa_heatmap
 */

$qahm_view_page_data = new QAHM_View_Page_Data();

class QAHM_View_Page_Data extends QAHM_View_Base {

	public function __construct() {
		$this->regist_ajax_func( 'ajax_create_page_data_file' );
		
		add_action( 'init', array( $this, 'init_wp_filesystem' ) );
	}

	public function get_work_dir_url() {
		return parent::get_data_dir_url() . 'page-data-view-work/';
	}

	/**
	 * ページデータ表示用のファイルを作成
	 */
	public function ajax_create_page_data_file() {
		try {
			$this->write_wp_load_path();
			$this->create_page_data_file();
		
		} catch ( Exception $e ) {
			global $qahm_log;
			$log = $qahm_log->error( $e->getMessage() );
			echo wp_json_encode( $log );

		} finally {
			die();
		}
	}

	
	/**
	 * データベースからリプレイ表示用のファイルを作成
	 */
	private function create_page_data_file() {
		global $qahm_db;

		$page_id     = (int) $this->wrap_filter_input( INPUT_POST, 'page_id' );
		$start_date  = $this->wrap_filter_input( INPUT_POST, 'start_date' );
		$end_date    = $this->wrap_filter_input( INPUT_POST, 'end_date' );

		$page_data_work_dir = $this->get_data_dir_path( 'page-data-view-work' );
		$work_base_name     = $page_id . '-' . $start_date . '-' . $end_date;
		$this->wrap_mkdir( $page_data_work_dir );

		$view_pv_ary = $this->wrap_filter_input( INPUT_POST, 'view_pv_ary' );
		$data_type   = 'pv';
		if ( $view_pv_ary ) {
			$view_pv_ary = json_decode( $view_pv_ary, true );
			$this->wrap_put_contents( $page_data_work_dir . $work_base_name . '-view_pv.php', $this->wrap_serialize( $view_pv_ary ) );
			$this->wrap_put_contents( $page_data_work_dir . $work_base_name . '-view_pv_json.php', wp_json_encode( $view_pv_ary ) );
		} else {
			$view_session_ary = json_decode( $this->wrap_filter_input( INPUT_POST, 'view_session_ary' ), true );
			$this->wrap_put_contents( $page_data_work_dir . $work_base_name . '-view_session.php', $this->wrap_serialize( $view_session_ary ) );
			$this->wrap_put_contents( $page_data_work_dir . $work_base_name . '-view_session_json.php', wp_json_encode( $view_session_ary ) );
			$data_type = 'session';
		}

		// ページの情報を取得
		$title        = '';
		$url          = '';
		$table_name   = $qahm_db->prefix . 'qa_pages';
		$query        = 'SELECT url,title FROM ' . $table_name . ' WHERE page_id = %d';
		$qa_pages_ary = $qahm_db->get_results( $qahm_db->prepare( $query, $page_id ), ARRAY_A );
		if( $qa_pages_ary ) {
			$qa_pages = $qa_pages_ary[0];
			$title = $qa_pages['title'];
			$url   = $qa_pages['url'];
		}

		// info put_contents
		$info_ary               = array();
		$info_ary['data_type']  = $data_type;
		$info_ary['page_id']    = $page_id;
		$info_ary['title']      = $title;
		$info_ary['url']        = $url;
		$info_ary['start_date'] = $start_date;
		$info_ary['end_date']   = $end_date;
		$this->wrap_put_contents( $page_data_work_dir . $work_base_name . '-info.php', $this->wrap_serialize( $info_ary ) );

		// url
		$page_data_view_url  = plugin_dir_url( __FILE__ ) . 'page-data-view.php' . '?';
		$page_data_view_url .= 'work_base_name=' . $work_base_name;

		echo wp_json_encode( $page_data_view_url );
	}
}
