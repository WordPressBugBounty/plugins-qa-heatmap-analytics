<?php
/**
 * 
 *
 * @package qa_heatmap
 */

$qahm_admin_page_realtime = new QAHM_Admin_Page_Realtime();

class QAHM_Admin_Page_Realtime extends QAHM_Admin_Page_Dataviewer {

	// スラッグ
	const SLUG = QAHM_NAME . '-realtime';

	// nonce
	const NONCE_ACTION = self::SLUG . '-nonce-action';
	const NONCE_NAME   = self::SLUG . '-nonce-name';

	function __construct() {
		parent::__construct();
		$this->regist_ajax_func( 'ajax_get_session_num' );
		$this->regist_ajax_func( 'ajax_get_realtime_list' );
	}


	/**
	 * 初期化
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if( $this->hook_suffix !== $hook_suffix ||
			! $this->is_enqueue_jquery()
		) {
			return;
		}

        $css_dir_url = $this->get_css_dir_url();
		$js_dir_url  = $this->get_js_dir_url();
		
		// enqueue style
		$this->common_enqueue_style();

		// enqueue script
		$this->common_enqueue_script();
		wp_enqueue_style( QAHM_NAME . '-admin-page-home-realtime-css', $css_dir_url. 'admin-page-realtime.css', null, QAHM_PLUGIN_VERSION );
		//wp_enqueue_style( QAHM_NAME . '-admin-page-chart', $css_dir_url . 'admin-page-chart.css', array( QAHM_NAME . '-reset' ), QAHM_PLUGIN_VERSION );

		wp_enqueue_script( QAHM_NAME . '-admin-page-realtime', $js_dir_url . 'admin-page-realtime.js', array( QAHM_NAME . '-effect' ), QAHM_PLUGIN_VERSION );
		wp_enqueue_script( QAHM_NAME . '-chart', $js_dir_url . 'lib/chart/chart.min.js', null, QAHM_PLUGIN_VERSION, false );
		wp_enqueue_script( QAHM_NAME . '-dayjs', $js_dir_url . 'lib/dayjs/dayjs.min.js', null, QAHM_PLUGIN_VERSION, false );
		wp_enqueue_script( QAHM_NAME . '-dayjs-utc', $js_dir_url . 'lib/dayjs/plugin/utc.js', array( QAHM_NAME . '-dayjs' ), QAHM_PLUGIN_VERSION, false );
		wp_enqueue_script( QAHM_NAME . '-dayjs-timezone', $js_dir_url . 'lib/dayjs/plugin/timezone.js', array( QAHM_NAME . '-dayjs' ), QAHM_PLUGIN_VERSION, false );
		wp_enqueue_script( QAHM_NAME . '-admin-page-dataviewer', $js_dir_url . 'admin-page-dataviewer.js', array( QAHM_NAME . '-chart' ), QAHM_PLUGIN_VERSION );

		// inline script
		$this->regist_inline_script();

		// localize
		$this->regist_localize_script();
	}


	/**
	 * ページの表示
	 */
	public function create_html() {
		if( ! $this->is_enqueue_jquery() ) {
			$this->print_not_enqueue_jquery_html();
			return;
		}

		if( $this->is_maintenance() ) {
			$this->print_maintenance_html();
			return;
		}
?>
		<div id="<?php echo esc_attr( basename( __FILE__, '.php' ) ); ?>">
			<div class="qa-zero-content">

					<?php $this->create_header( __( 'Realtime', 'qa-heatmap-analytics' ) ); ?>

					<!--リアルタイム-->
					<div class="bl_reportField">
						<div id="tday_container">
							<div id="tday_upper" class="bl_contentsArea">
								<?php if ( ! QAHM_CONFIG_TWO_SYSTEM_MODE ) { ?>
									<h3 id="h_realtimeoverview"><?php esc_html_e( 'Realtime Activity', 'qa-heatmap-analytics' ); ?></h3>
									<div class="realtime_num flex" style="flex-wrap: wrap; margin-bottom: 48px;">
										<div class="flex_item" style="width: 260px; margin-right: 6px;">
											<div>
												<div class="num_title"><?php esc_html_e( 'Users in Last 30 Min', 'qa-heatmap-analytics' ); ?></div>
												<div id="session_num" class="tday_now_rtn"></div>
											</div><!-- now_number_end -->
										</div>
										<div class="flex_item" style="width: 260px; margin-right: 6px;">
											<div>
												<div class="num_title"><?php esc_html_e( 'Active Users in Last Min', 'qa-heatmap-analytics' ); ?></div>
												<div id="session_num_1min" class="tday_now_rtn"></div>
											</div><!-- now_number_end -->
										</div>
										<!--<div class="flex_item bl_dayGraph" style="max-width: 100%; width: 500px;"><canvas id="realtime" height="200px"></canvas></div>-->
									</div><!-- realtime_num_end -->
								<?php } ?>

								<h3 id="h_realtimereplay"><?php esc_html_e( 'Session Recordings', 'qa-heatmap-analytics' ); ?></h3>
								<div class="tday_upper_title">
									<div class="tday_title_icon tday_bg_org">
										<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/icon_01.png' ); ?>" alt="Today">
									</div>
									<div class="tday_upper_txt">
										<div class="tday_upper_eng color_red">
											<?php esc_html_e( 'Last update', 'qa-heatmap-analytics' ); ?>: 
											<span id="update_time"></span>
											<script>
												function time(){
													var now = new Date();
													var formatnow = now.toLocaleString().slice(0,-3);
													document.getElementById("time").innerHTML = formatnow;
												}
											</script>
										</div>
										<div class="tday_upper_jpn"><?php echo esc_html( __( 'Recent Sessions (Replayable)', 'qa-heatmap-analytics' ) ); ?></div>
									</div>
								</div><!-- upper_title_end -->


								<div class="tday_scroll-table" id="tday_table"></div>
							</div><!-- upper_end -->
						</div><!-- tday_container_end -->
					</div><!-- bl_reportField_end -->


				
				<?php
				/* 他のページとあわせたレイアウトのサンプル
				<!-- ヘッダー -->
				<?php $this->create_header( __( 'Realtime', 'qa-heatmap-analytics' ) ); ?>

				<!-- データ -->
				<div class="qa-zero-data-container">
					<div class="qa-zero-data">
						<div class="qa-zero-data__title">
							<svg xmlns="http://www.w3.org/2000/svg" width="21" height="14" viewBox="0 0 21 14" fill="none">
								<path d="M14.1364 6C15.6455 6 16.8545 4.66 16.8545 3C16.8545 1.34 15.6455 0 14.1364 0C12.6273 0 11.4091 1.34 11.4091 3C11.4091 4.66 12.6273 6 14.1364 6ZM6.86364 6C8.37273 6 9.58182 4.66 9.58182 3C9.58182 1.34 8.37273 0 6.86364 0C5.35455 0 4.13636 1.34 4.13636 3C4.13636 4.66 5.35455 6 6.86364 6ZM6.86364 8C4.74545 8 0.5 9.17 0.5 11.5V14H13.2273V11.5C13.2273 9.17 8.98182 8 6.86364 8ZM14.1364 8C13.8727 8 13.5727 8.02 13.2545 8.05C14.3091 8.89 15.0455 10.02 15.0455 11.5V14H20.5V11.5C20.5 9.17 16.2545 8 14.1364 8Z"/>
							</svg>
							<?php esc_html_e( 'Realtime Activity', 'qa-heatmap-analytics' ); ?>
						</div>

						<?php if ( ! QAHM_CONFIG_TWO_SYSTEM_MODE ) { ?>
							<div class="qa-zero-data-box-wrapper">
								<div class="qa-zero-data-box">
									<div class="qa-zero-data-box__title">
									<?php esc_html_e( 'Users in Last 30 Min', 'qa-heatmap-analytics' ); ?>
									</div>
									<div class="qa-zero-data-box__value qa-zero-data-box--highlight">
									<span id="qa-zero-num-sessions"><div id="session_num" class="tday_now_rtn"></div></span>
									</div>
								</div>
								<div class="qa-zero-data-box">
									<div class="qa-zero-data-box__title">
									<?php esc_html_e( 'Active Users in Last Min', 'qa-heatmap-analytics' ); ?>
									</div>
									<div class="qa-zero-data-box__value qa-zero-data-box--highlight">
									<span id="qa-zero-num-pvs"><div id="session_num_1min" class="tday_now_rtn"></div></span>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>

				<!-- セッションレコーディング -->
				<div class="qa-zero-data-container">
					<div class="qa-zero-data">
						<div class="qa-zero-data__title">
							<svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
								<g clip-path="url(#clip0_36_30432)">
									<path d="M23.5 11.01L18.5 11C17.95 11 17.5 11.45 17.5 12V21C17.5 21.55 17.95 22 18.5 22H23.5C24.05 22 24.5 21.55 24.5 21V12C24.5 11.45 24.05 11.01 23.5 11.01ZM23.5 20H18.5V13H23.5V20ZM20.5 2H2.5C1.39 2 0.5 2.89 0.5 4V16C0.5 16.5304 0.710714 17.0391 1.08579 17.4142C1.46086 17.7893 1.96957 18 2.5 18H9.5V20H7.5V22H15.5V20H13.5V18H15.5V16H2.5V4H20.5V9H22.5V4C22.5 3.46957 22.2893 2.96086 21.9142 2.58579C21.5391 2.21071 21.0304 2 20.5 2ZM12.47 9L11.5 6L10.53 9H7.5L9.97 10.76L9.03 13.67L11.5 11.87L13.97 13.67L13.03 10.76L15.5 9H12.47Z"/>
								</g>
								<defs>
									<clipPath id="clip0_36_30432">
									<rect width="24" height="24" fill="white" transform="translate(0.5)"/>
									</clipPath>
								</defs>
							</svg>
							<?php echo esc_html( __( 'Session Recordings', 'qa-heatmap-analytics' ) ); ?>
						</div>
						<div>
							<?php esc_html_e( 'Last update', 'qa-heatmap-analytics' ); ?>: 
							<span id="update_time"></span>
							<script>
								function time(){
									var now = new Date();
									var formatnow = now.toLocaleString().slice(0,-3);
									document.getElementById("time").innerHTML = formatnow;
								}
							</script>
						</div>
						<div class="tday_scroll-table" id="tday_table"></div>
					</div>
				</div>
				*/ ?>

				<?php $this->create_footer_follow(); ?>
			</div><!-- qa-zero-content_end -->
		</div>
<?php
	}

	/**
	 * セッション数の取得
	 */
	public function ajax_get_session_num() {
		if( $this->is_maintenance() ) {
			return;
		}

		$data = array();
		$session_num = 0;
		$session_num_1min = 0;

		global $wp_filesystem;
		global $qahm_time;
		$before1min = $qahm_time->now_unixtime() - 60;
		$session_temp_dir_path = $this->get_data_dir_path( 'readers/temp' );
		if( $wp_filesystem->exists( $session_temp_dir_path ) ) {

			$session_temp_dirlist = $this->wrap_dirlist( $session_temp_dir_path );
			if ( $session_temp_dirlist ) {
				$session_num = $this->wrap_count( $session_temp_dirlist );
				foreach ( $session_temp_dirlist as $session_temp_fileobj ) {
					if ( $session_temp_fileobj['lastmodunix'] > $before1min ) {
						++$session_num_1min;
					}
				}
			}
		}

		$data['session_num']      = $session_num;
		$data['session_num_1min'] = $session_num_1min;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON response body for AJAX (non-HTML context).
		echo $this->wrap_json_encode( $data );
		die();
	}


	/**
	 * リアルタイムリストの取得
	 */
	public function ajax_get_realtime_list() {
		if( $this->is_maintenance() ) {
			return;
		}

		$data = array();
        $alldataary = array();
		$realtime_list = '';

		global $wp_filesystem;
		global $qahm_time;

		$ellipsis     = '...';
		$title_width  = 80 + mb_strlen( $ellipsis );
		$domain_width = 30 + mb_strlen( $ellipsis );

		$realtime_view_path = $this->get_data_dir_path( 'readers' ) . 'realtime_view.php';
		if ( ! $wp_filesystem->exists( $realtime_view_path ) ) {
			echo 'null';
			die();
		}

		$realtime_view_ary = $this->wrap_unserialize( $this->wrap_get_contents( $realtime_view_path ) );
		if ( ! $realtime_view_ary ) {
			echo 'null';
			die();
		}

		$realtime_cnt = $this->wrap_count( $realtime_view_ary['body'] );
		if ( $realtime_cnt === 0 ) {
			echo 'null';
			die();
		}

		for ( $i = 0; $i < $realtime_cnt; $i++ ) {
			$body = $realtime_view_ary['body'][$i];
			$first_title        = $body['first_title'];
			$first_title_el     = mb_strimwidth( $first_title, 0, $title_width, $ellipsis );
			$first_url          = $body['first_url'];
			$last_title         = $body['last_title'];
			$last_title_el      = mb_strimwidth( $last_title, 0, $title_width, $ellipsis );
			$last_url           = $body['last_url'];
			$last_exit_time     = $body['last_exit_time'];
			$sec_on_site        = $qahm_time->seconds_to_timestr( (int) $body['sec_on_site'] );
			$referrer           = $body['first_referrer'];
			$source_domain_html = 'direct';
			$work_base_name     = pathinfo( $body['file_name'], PATHINFO_FILENAME );

			if ( ! empty( $referrer ) ) {
				if ( 0 === strncmp( $referrer, 'http', 4 ) ) {
					$parse_url          = wp_parse_url( $referrer );
					$ref_host           = $parse_url['host'];
					$source_domain      = mb_strimwidth( $ref_host, 0, $domain_width, $ellipsis );
					$source_domain_html = '<a href="' . esc_url( $referrer ) . '" target="_blank" class="qahm-tooltip" data-qahm-tooltip="'. esc_url( $referrer ) . '">' . esc_html( $source_domain ) . '</a>';
				} else {
					$source_domain      = mb_strimwidth( $referrer, 0, $domain_width, $ellipsis );
					$source_domain_html = esc_html( $source_domain );
				}
			}

			$device = $body['device_name'];
			if ( 'dsk' === $device ) {
				$device = 'pc';
			}

			$dataary = [];
			$dataary[] = esc_html( $device );
			$dataary[] = esc_html( $last_exit_time );
			$dataary[] = esc_url( $first_url );
			$dataary[] = esc_html( $first_title_el );
			$dataary[] = esc_url( $last_url );
			$dataary[] = esc_html( $last_title_el );
			$dataary[] = esc_url( $referrer );
			$dataary[] = esc_html( $source_domain );
			$dataary[] = esc_html( $body['page_view'] );
			$dataary[] = esc_html( $body['sec_on_site'] );
			$dataary[] = esc_attr( $work_base_name );
			$alldataary[] = $dataary;
		}

		$data['update_time']   = $qahm_time->now_str();

		//$data['realtime_list'] = $realtime_list;
		$data['realtime_list'] = $alldataary;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON response body for AJAX (non-HTML context).
		echo $this->wrap_json_encode( $data );
		die();
	}
} // end of class
