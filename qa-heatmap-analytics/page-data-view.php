<?php
try {
	$temp_dir          = dirname( __FILE__ ) . '/temp/';
	$wp_load_temp_path = $temp_dir . 'wp-load-path.php';
	$wp_load_rel_path  = file_get_contents( $wp_load_temp_path );
	if ( ! $wp_load_rel_path ) {
		throw new Exception( 'Error loading wp_load.php' );
	}

	require_once $wp_load_rel_path;

	// GETパラメーター判定
	$work_base_name    = $this->wrap_filter_input( INPUT_GET, 'work_base_name' );
	if ( ! $work_base_name ) {
		throw new Exception( 'Query string has no value.' );
	}

	global $qahm_time;
	global $wp_filesystem;
	$work_dir = $qahm_view_page_data->get_data_dir_path( 'page-data-view-work' );
	$work_url = $qahm_view_page_data->get_work_dir_url();

	// info 読み込み
	$info_ary  = $this->wrap_unserialize( $qahm_view_page_data->wrap_get_contents( $work_dir . $work_base_name . '-info.php' ) );

	// view 配列読み込み
	$view_pv_ary      = null;
	$view_session_ary = null;
	$view_pv_ary_json      = null;
	$view_session_ary_json = null;
	switch ( $info_ary['data_type'] ) {
		case 'pv':
			$view_pv_ary = $this->wrap_unserialize( $qahm_view_page_data->wrap_get_contents( $work_dir . $work_base_name . '-view_pv.php' ) );
			$view_pv_ary_json = $qahm_view_page_data->wrap_get_contents( $work_dir . $work_base_name . '-view_pv_json.php' );
			break;
		case 'session':
			$view_session_ary = $this->wrap_unserialize( $qahm_view_page_data->wrap_get_contents( $work_dir . $work_base_name . '-view_session.php' ) );
			$view_session_ary_json = $qahm_view_page_data->wrap_get_contents( $work_dir . $work_base_name . '-view_session_json.php' );
			break;
		default:
			throw new Exception( 'Invalid data type.' );
	}
	
	// ログイン判定
	if ( ! $qahm_view_page_data->check_access_role() ) {
		throw new Exception( 'You do not have access privileges.' );
	} 

	// 翻訳ファイルの読み込み
	load_plugin_textdomain( 'qa-heatmap-analytics', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	
	// パラメータ設定
	$ajax_url           = admin_url( 'admin-ajax.php' );
	$plugin_version     = QAHM_PLUGIN_VERSION;
	$plugin_dir_url     = plugin_dir_url( __FILE__ );
	$debug_level        = wp_json_encode( QAHM_DEBUG_LEVEL );
	$debug              = QAHM_DEBUG;
	$license_plan       = (int) get_option( QAHM_OPTION_PREFIX . 'license_plan' );
	$devices            = wp_json_encode( QAHM_DEVICES );

} catch ( Exception $e ) {
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>';
	echo '<p>Error : ' . esc_html( $e->getMessage() ) . '</p>';
	echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=qahm-help' ) ) . '" target="_blank">' . esc_html__( 'HELP' ) . '</a></p>';
	echo '</body></html>';
	exit();
}
?>

<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_locale() ); ?>">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>QA Page Data View</title>

		<link rel="stylesheet" type="text/css" href="./css/doctor-reset.css?ver=<?php echo QAHM_PLUGIN_VERSION; ?>">
		<link rel="stylesheet" type="text/css" href="./css/common.css?ver=<?php echo QAHM_PLUGIN_VERSION; ?>">
		<link rel="stylesheet" type="text/css" href="./css/page-data-view.css?ver=<?php echo QAHM_PLUGIN_VERSION; ?>">

		<script src="./js/lib/jquery/jquery-3.6.0.min.js?ver=<?php echo QAHM_PLUGIN_VERSION; ?>"></script>
		<script src="./js/lib/sweet-alert-2/sweetalert2.min.js?ver=<?php echo QAHM_PLUGIN_VERSION; ?>"></script>
		<script src="./js/alert-message.js?ver=<?php echo QAHM_PLUGIN_VERSION; ?>"></script>
		<script src="./js/lib/font-awesome/all.min.js?ver=<?php echo QAHM_PLUGIN_VERSION; ?>"></script>
	</head>
	<body>

		<div class="page-data-view-container">
			<div class="ds_item">
				<h3><?php echo $info_ary['title']; ?>の情報</h3>
				<p>抽出条件</p>
				<p><?php echo $info_ary['start_date']; ?>～<?php echo $info_ary['end_date']; ?>の期間に<?php echo $info_ary['url']; ?>をみた人</p>
			</div>
			<hr>
			<div class="ds_item">
				<h3>合算メディア別ヒートマップ</h3>
				<div id="total-media-table-progbar"></div>
				<div id="total-media-table"></div>
			</div>
			<hr>
			<div class="ds_item">
				<h3>参照元別ヒートマップ</h3>
				<div id="source-domain-table-progbar"></div>
				<div id="source-domain-table"></div>
			</div>
		</div>

		<script>
			var qahm = {
				'ajax_url':'<?php echo $ajax_url; ?>',
				'const_debug_level':<?php echo $debug_level; ?>,
				'const_debug':<?php echo $debug; ?>,
				'plugin_dir_url':'<?php echo $plugin_dir_url; ?>',
				'license_plan':'<?php echo $license_plan; ?>',
				'devices':'<?php echo $devices; ?>',
				'pvDataAry':'<?php echo $view_pv_ary_json; ?>',
				'sessionDataAry':'<?php echo $view_session_ary_json; ?>',
				'pageId':'<?php echo $info_ary['page_id']; ?>',
				'startDate':'<?php echo $info_ary['start_date']; ?>',
				'dsEndDate':'<?php echo $info_ary['end_date']; ?>',
			};
			
			var qahml10n = {
				'medium_organic':'<?php esc_html_e( 'organic', 'qa-heatmap-analytics' ); ?>',
			};
		</script>
		
		<script type="text/javascript" src="./js/common.js?ver=<?php echo QAHM_PLUGIN_VERSION; ?>"></script>
		<script type="text/javascript" src="./js/load-screen.js?ver=<?php echo QAHM_PLUGIN_VERSION; ?>"></script>
		<script type="text/javascript" src="./js/progress-bar-exec.js?ver=<?php echo QAHM_PLUGIN_VERSION; ?>"></script>
		<script type="text/javascript" src="./js/table.js?ver=<?php echo QAHM_PLUGIN_VERSION; ?>"></script>
		<script type="text/javascript" src="./js/page-data-view.js?ver=<?php echo QAHM_PLUGIN_VERSION; ?>"></script>
		<script type="text/javascript" src="./js/cap-create.js?ver=<?php echo QAHM_PLUGIN_VERSION; ?>"></script>
	</body>
</html>
