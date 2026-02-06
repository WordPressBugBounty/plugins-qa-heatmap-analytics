<?php
/**
 * 
 *
 * @package qa_heatmap_analytics
 */

 $qahm_admin_page_dashboard = new QAHM_Admin_Page_Dashboard();

 class QAHM_Admin_Page_Dashboard extends QAHM_Admin_Page_Dataviewer {
 
	 // スラッグ
	 const SLUG = QAHM_NAME . '-dashboard';

	// nonce
	const NONCE_ACTION = self::SLUG . '-nonce-action';
	const NONCE_NAME   = self::SLUG . '-nonce-name';

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
		$this->regist_ajax_func( 'ajax_get_two_mon_sessions' );
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
		wp_enqueue_style( QAHM_NAME . '-daterangepicker-css', $css_dir_url . 'lib/date-range-picker/daterangepicker.css', null, QAHM_PLUGIN_VERSION );
		wp_enqueue_style( QAHM_NAME . '-admin-page-chart', $css_dir_url . 'admin-page-chart.css', array( QAHM_NAME . '-reset' ), QAHM_PLUGIN_VERSION );

		// enqueue script
		$this->common_enqueue_script();
		wp_enqueue_script( QAHM_NAME . '-chart', $js_dir_url . 'lib/chart/chart.min.js', null, QAHM_PLUGIN_VERSION, false );
		wp_enqueue_script( QAHM_NAME . '-dayjs', $js_dir_url . 'lib/dayjs/dayjs.min.js', null, QAHM_PLUGIN_VERSION, false );
		wp_enqueue_script( QAHM_NAME . '-dayjs-utc', $js_dir_url . 'lib/dayjs/plugin/utc.js', array( QAHM_NAME . '-dayjs' ), QAHM_PLUGIN_VERSION, false );
		wp_enqueue_script( QAHM_NAME . '-dayjs-timezone', $js_dir_url . 'lib/dayjs/plugin/timezone.js', array( QAHM_NAME . '-dayjs' ), QAHM_PLUGIN_VERSION, false );
		wp_enqueue_script( QAHM_NAME . '-moment-with-locales', $js_dir_url . 'lib/moment/moment-with-locales.min.js', null, QAHM_PLUGIN_VERSION, false );	
		wp_enqueue_script( QAHM_NAME . '-daterangepicker', $js_dir_url . 'lib/date-range-picker/daterangepicker.js', array( QAHM_NAME . '-moment-with-locales' ), QAHM_PLUGIN_VERSION, false );
		wp_enqueue_script( QAHM_NAME . '-admin-page-dataviewer', $js_dir_url . 'admin-page-dataviewer.js', array( QAHM_NAME . '-dayjs', QAHM_NAME . '-daterangepicker' ), QAHM_PLUGIN_VERSION );
		wp_enqueue_script( QAHM_NAME . '-admin-page-dashboard', $js_dir_url . 'admin-page-dashboard.js', array( QAHM_NAME . '-admin-page-dataviewer' ), QAHM_PLUGIN_VERSION );
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
				<!-- ヘッダー -->
				<?php $this->create_header( __( 'Dashboard', 'qa-heatmap-analytics' ) ); ?>

				<div class="bl_news flex_item">
                    <?php $this->print_rss_feed(); ?>
                </div>

				<!-- データ -->
				<div class="qa-zero-data-container">

					<div class="qa-zero-data">
						<div class="qa-zero-data__title">
							<svg xmlns="http://www.w3.org/2000/svg" width="21" height="14" viewBox="0 0 21 14" fill="none">
								<path d="M14.1364 6C15.6455 6 16.8545 4.66 16.8545 3C16.8545 1.34 15.6455 0 14.1364 0C12.6273 0 11.4091 1.34 11.4091 3C11.4091 4.66 12.6273 6 14.1364 6ZM6.86364 6C8.37273 6 9.58182 4.66 9.58182 3C9.58182 1.34 8.37273 0 6.86364 0C5.35455 0 4.13636 1.34 4.13636 3C4.13636 4.66 5.35455 6 6.86364 6ZM6.86364 8C4.74545 8 0.5 9.17 0.5 11.5V14H13.2273V11.5C13.2273 9.17 8.98182 8 6.86364 8ZM14.1364 8C13.8727 8 13.5727 8.02 13.2545 8.05C14.3091 8.89 15.0455 10.02 15.0455 11.5V14H20.5V11.5C20.5 9.17 16.2545 8 14.1364 8Z"/>
							</svg>
							<?php esc_html_e( 'Sessions', 'qa-heatmap-analytics' ); ?>
						</div>
						<div class="qa-zero-data-box-wrapper">
							<div class="qa-zero-data-box">
								<div class="qa-zero-data-box__title">
								<?php esc_html_e( 'This Month', 'qa-heatmap-analytics' ); ?>
								</div>
								<div class="qa-zero-data-box__value qa-zero-data-box--highlight" id="this-month-sessions">
									--
								</div>
							</div>
							<div class="qa-zero-data-box">
								<div class="qa-zero-data-box__title">
								<?php esc_html_e( 'Forecast', 'qa-heatmap-analytics' ); ?>
								</div>
								<div class="qa-zero-data-box__value" id="this-month-estimate">
									--
								</div>
							</div>
							<div class="qa-zero-data-box">
								<div class="qa-zero-data-box__title">
								<?php esc_html_e( 'Last Month', 'qa-heatmap-analytics' ); ?>
								</div>
								<div class="qa-zero-data-box__value" id="last-month-sessions">
									--
								</div>
							</div>
						</div>
						<div class="qa-zero-graph">
							<div class="qa-zero-graph--default">
								<canvas id="access_graph" width="500px"></canvas>
							</div>
						</div>
					</div>

					<div class="qa-zero-data">
						<div class="qa-zero-data__title">
							<svg xmlns="http://www.w3.org/2000/svg" width="21" height="14" viewBox="0 0 21 14" fill="none">
								<path d="M14.1364 6C15.6455 6 16.8545 4.66 16.8545 3C16.8545 1.34 15.6455 0 14.1364 0C12.6273 0 11.4091 1.34 11.4091 3C11.4091 4.66 12.6273 6 14.1364 6ZM6.86364 6C8.37273 6 9.58182 4.66 9.58182 3C9.58182 1.34 8.37273 0 6.86364 0C5.35455 0 4.13636 1.34 4.13636 3C4.13636 4.66 5.35455 6 6.86364 6ZM6.86364 8C4.74545 8 0.5 9.17 0.5 11.5V14H13.2273V11.5C13.2273 9.17 8.98182 8 6.86364 8ZM14.1364 8C13.8727 8 13.5727 8.02 13.2545 8.05C14.3091 8.89 15.0455 10.02 15.0455 11.5V14H20.5V11.5C20.5 9.17 16.2545 8 14.1364 8Z"/>
							</svg>
							<?php esc_html_e( 'Goals', 'qa-heatmap-analytics' ); ?>
						</div>
						<div class="qa-zero-data-box-wrapper">
							<div class="qa-zero-data-box">
								<div class="qa-zero-data-box__title">
								<?php esc_html_e( 'This Month', 'qa-heatmap-analytics' ); ?>
								</div>
								<div class="qa-zero-data-box__value qa-zero-data-box--highlight" id="this-month-goal-cv">
									--
								</div>
							</div>
							<div class="qa-zero-data-box">
								<div class="qa-zero-data-box__title">
								<?php esc_html_e( 'Forecast', 'qa-heatmap-analytics' ); ?>
								</div>
								<div class="qa-zero-data-box__value" id="this-month-goal-estimate">
									--
								</div>
							</div>
							<div class="qa-zero-data-box">
								<div class="qa-zero-data-box__title">
								<?php esc_html_e( 'Last Month', 'qa-heatmap-analytics' ); ?>
								</div>
								<div class="qa-zero-data-box__value" id="last-month-goal-cv">
									--
								</div>
							</div>
						</div>
						<div class="qa-zero-graph">
							<div class="qa-zero-graph--default">
								<canvas id="conversion_graph" width="500px"></canvas>
							</div>
						</div>
					</div>

				</div>
                <!-- QABrains 
                <div id="brain_dashboard_top" data-brains-open="true"></div>
                <script>
                    if ( typeof(qahm) !== "undefined" ) {
                        qahm.generateBrains();
                    }
                </script>-->
			</div>
		</div>
<?php
	}

	public function ajax_get_two_mon_sessions() {
		$tracking_id = $this->wrap_filter_input( INPUT_POST, 'tracking_id' );
		$resary = $this->get_two_mon_sessions( $tracking_id );
		header("Content-type: application/json; charset=UTF-8");
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON response body for AJAX (non-HTML context).
		echo $this->wrap_json_encode($resary);
		die();
	}

	public function get_two_mon_sessions( $tracking_id ) {
		global $qahm_data_api;
		global $qahm_time;

		$g_lmon_cv   = 0;
		$g_nmon_cv   = 0;
		$g_estimate  = 0;

		$pvstart_date  = $qahm_data_api->get_pvterm_start_date();
		$pvstart_yyyy  = (int)$this->wrap_substr( $pvstart_date , 0, 4 );
		$pvstart_mm    = (int)$this->wrap_substr( $pvstart_date , 5, 2 );
		$pvstart_dd    = (int)$this->wrap_substr( $pvstart_date , 8, 2 );

		$g_endday      = $qahm_time->xday_str( -1 );
		$g_end_ym      = $this->wrap_substr( $g_endday, 0, 7 );
		$g_sttday      = $qahm_time->xmonth_str( -1, $g_end_ym . '-01');
		$g_init_term   = 'date = between ' . $g_sttday . ' and ' . $g_endday;
		$g_session_ary = $qahm_data_api->get_goals_sessions( $g_init_term, $tracking_id );

		$g_nmon_01 = $g_end_ym . '-01 00:00:00';
		$g_nmon_01_utime = $qahm_time->str_to_unixtime( $g_nmon_01 );
		$gid0ary = [];
		foreach ( $g_session_ary as $gid => $sessions ) {
			$gid0ary = array_merge( $gid0ary, $sessions );
		}
		$g_session_ary[0] = $gid0ary;
		foreach ( $g_session_ary[0] as $sessions ) {
			$lp_utime = $sessions[0]['access_time'];
			if ($lp_utime < $g_nmon_01_utime ) {
				$g_lmon_cv++;
			}else{
				$g_nmon_cv++;
			}
		}
		$g_n_lastday = (new DateTimeImmutable())->modify('last day of' . $g_end_ym )->format('j');
		$g_n_nowday  = (new DateTime($g_endday))->format('j');
		$past_days   = $g_n_nowday;
		if ( $g_end_ym === ($pvstart_yyyy . '-' . $pvstart_mm) ) {
			$past_days = $g_n_nowday - (int)$pvstart_dd + 1;
		}
		if ( $past_days !== 0 && $g_n_lastday !== 0 ) {
			$g_estimate = round( $g_nmon_cv / $past_days * $g_n_lastday );
		}
		
		$res_ary = array(
			'g_lmon_cv'     => $g_lmon_cv,
			'g_nmon_cv'     => $g_nmon_cv,
			'g_estimate'    => $g_estimate,
			'g_session_ary' => $g_session_ary
		);
	
		return $res_ary;
	}
}// end of class
