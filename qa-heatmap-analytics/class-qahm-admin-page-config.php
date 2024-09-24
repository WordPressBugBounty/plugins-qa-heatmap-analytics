<?php
/**
 * 
 *
 * @package qa_heatmap
 */

$qahm_admin_page_config = new QAHM_Admin_Page_Config();

class QAHM_Admin_Page_Config extends QAHM_Admin_Page_Base {

	// スラッグ
	const SLUG = QAHM_NAME . '-config';

	// nonce
	const NONCE_ACTION = self::SLUG . '-nonce-action';
	const NONCE_NAME   = self::SLUG . '-nonce-name';

	private static $error_msg = array();
	private $localize_ary;

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();

		 // コールバック
		add_action( 'init', array( $this, 'init_wp_filesystem' ) );
		add_action( 'load-qa-analytics_page_qahm-config', array( $this, 'admin_init' ) );

		$this->regist_ajax_func( 'ajax_save_plugin_config' );
	}
	
	// 管理画面の初期化
	public function admin_init(){
		if( defined('DOING_AJAX') && DOING_AJAX ){
			return;
		}

		global $qahm_google_api;

		$scope   = array( 'https://www.googleapis.com/auth/webmasters.readonly' );

		// nonceで設定したcredentialのチェック
		// 設定画面
		if ( isset( $_POST[ self::NONCE_NAME ] ) && $_POST[ self::NONCE_NAME ] && check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME ) ) {
			// フォームから値が送信されていればDBに保存
			$client_id     = $this->wrap_filter_input( INPUT_POST, 'client_id' );
			$client_secret = $this->wrap_filter_input( INPUT_POST, 'client_secret' );
			$qahm_google_api->set_credentials( $client_id, $client_secret, null );

			$qahm_google_api->init(
				'Google API Integration',
				$scope,
				admin_url( 'admin.php?page=qahm-config' ),
				true
			);
		} else {
			$qahm_google_api->init(
				'Google API Integration',
				$scope,
				admin_url( 'admin.php?page=qahm-config' )
			);
		}
	}
	
	/**
	 * 初期化
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if( $this->hook_suffix !== $hook_suffix ) {
			return;
		}

		if( ! $this->is_enqueue_jquery() ) {
			return;
		}

		if( $this->is_maintenance() ) {
			return;
		}

		if( $this->wrap_get_option( 'plugin_first_launch' ) ) {
			$this->common_enqueue_style();
			$this->common_enqueue_script();
			$scripts = $this->get_common_inline_script();
			wp_add_inline_script( QAHM_NAME . '-common', 'var ' . QAHM_NAME . ' = ' . QAHM_NAME . ' || {}; let ' . QAHM_NAME . 'Obj = ' . wp_json_encode( $scripts ) . '; ' . QAHM_NAME . ' = Object.assign( ' . QAHM_NAME . ', ' . QAHM_NAME . 'Obj );', 'before' );
			$localize = $this->get_common_localize_script();
			wp_localize_script( QAHM_NAME . '-common', QAHM_NAME . 'l10n', $localize );
			return;
		}

		global $qahm_time;
		$js_dir       = $this->get_js_dir_url();
		$data_dir     = $this->get_data_dir_url();
		$license_plan = $this->wrap_get_option( 'license_plans' );
		$css_dir_url  = $this->get_css_dir_url();

        $GOALMAX = 1;
        if ( $this->get_license_plan( 'goal_3' ) ) {
            $GOALMAX = 3;
		}
        if ( $this->get_license_plan( 'goal_10' ) ) {
            $GOALMAX = 10;
		}

		// enqueue_style
		$this->common_enqueue_style();
		wp_enqueue_script( QAHM_NAME . '-admin-page-config', $js_dir . 'admin-page-config.js', array( QAHM_NAME . '-admin-page-base' ), QAHM_PLUGIN_VERSION );
		wp_enqueue_style( QAHM_NAME . '-admin-page-base-css', $css_dir_url . 'admin-page-base.css', array( QAHM_NAME . '-reset' ), QAHM_PLUGIN_VERSION );
		wp_enqueue_style( QAHM_NAME . '-admin-page-config-css', $css_dir_url . 'admin-page-config.css', array( QAHM_NAME . '-reset' ), QAHM_PLUGIN_VERSION );
		if ( $license_plan ) {
//			wp_enqueue_script( QAHM_NAME . '-powerup-config', $data_dir . 'js/powerup-config.js', array( QAHM_NAME . '-admin-page-base' ), $qahm_time->now_unixtime() );
		}
		// enqueue script
		$this->common_enqueue_script();

        // g_clickpage の変数作成
		global $qahm_data_api;
		$goals_ary = $qahm_data_api->get_goals_array();
		$click_iframe_url = esc_url( get_home_url() );
		$g_clickpage_ary = array();
		for ( $iii = 1; $iii <= $GOALMAX; $iii++ ) {
			$g_clickpage = isset( $goals_ary[$iii]['g_clickpage'] ) ? urldecode( $goals_ary[$iii]['g_clickpage'] ) : '';
			//set default
			if ( ! $g_clickpage ) {
				$g_clickpage = $click_iframe_url;
			}
			$g_clickpage_ary[$iii] = $g_clickpage;
		}
		// inline script
		$scripts = $this->get_common_inline_script();
		$scripts['access_role']     = $this->wrap_get_option( 'access_role' );
		$scripts['wp_time_adj'] = get_option('gmt_offset');
		$scripts['wp_lang_set'] =  get_bloginfo('language');
		$scripts['goalmax']     =  $GOALMAX;
        $scripts['g_clickpage'] = $g_clickpage_ary;
		wp_add_inline_script( QAHM_NAME . '-common', 'var ' . QAHM_NAME . ' = ' . QAHM_NAME . ' || {}; let ' . QAHM_NAME . 'Obj = ' . wp_json_encode( $scripts ) . '; ' . QAHM_NAME . ' = Object.assign( ' . QAHM_NAME . ', ' . QAHM_NAME . 'Obj );', 'before' );

		// localize
		$localize = $this->get_common_localize_script();
		$localize['data_save_month_title']     = esc_html__( 'Data Storage Period', 'qa-heatmap-analytics' );
		$localize['data_save_month_desc']      = esc_html__( 'With free plans, to the previous month. For subscribed, there is no period limit in principal.', 'qa-heatmap-analytics' );
		$localize['all_page_measure_title']    = esc_html__( 'Measure all pages', 'qa-heatmap-analytics' );
		$localize['all_page_measure_desc']     = esc_html__( 'Ignore the individual measurement settings on Heatmap management screen and measure all pages. Measurement starts automatically including when a new page is added (published).', 'qa-heatmap-analytics' );
		$localize['access_role_title']         = esc_html__( 'Access Privileges', 'qa-heatmap-analytics' );
		$localize['access_role_desc']          = esc_html__( 'Set the privileges to access the heatmap view.', 'qa-heatmap-analytics' );
		$localize['access_role_option_admin']  = esc_html__( 'Administrator only', 'qa-heatmap-analytics' );
		$localize['access_role_option_editor'] = esc_html__( 'Editor or above', 'qa-heatmap-analytics' );
        $localize['settings_saved']            = esc_attr__( 'Settings saved.', 'qa-heatmap-analytics' );
        $localize['goal_saved']                = esc_attr__( 'Goal saved.', 'qa-heatmap-analytics' );
        $localize['cnv_couldnt_saved']         = esc_html__( 'Could not be saved. The value is same as before or is incorrect.', 'qa-heatmap-analytics' );
        $localize['cnv_delete_title']          = esc_html__( 'Delete Goal %d', 'qa-heatmap-analytics' );
        $localize['cnv_delete_confirm']        = esc_html__( 'Are you sure to delete this goal?', 'qa-heatmap-analytics' );
        $localize['cnv_success_delete']        = esc_html__( 'Goal %d deleted.', 'qa-heatmap-analytics' );
        $localize['cnv_couldnt_delete']        = esc_html__( 'Could not delete. The value is incorrect.', 'qa-heatmap-analytics' );
        $localize['cnv_page_set_alert']        = esc_html__( 'You are trying to set all the pages as a goal.', 'qa-heatmap-analytics' );
        $localize['cnv_goal_numbering_alert']  = esc_html__( 'There is a skip in goal numbers. Please set goals sequentially.', 'qa-heatmap-analytics' );
        $localize['cnv_reaching_goal_notice']  = esc_attr__( 'You have the goal reached.', 'qa-heatmap-analytics' );
        $localize['cnv_saving']                = esc_attr__( 'Saving...', 'qa-heatmap-analytics' );
        $localize['cnv_load_page']             = esc_html__( 'Load the Page', 'qa-heatmap-analytics' );
        $localize['cnv_loading']               = esc_html__( 'Loading...', 'qa-heatmap-analytics' );
        $localize['cnv_saved_1']               = esc_html__( 'Goal %d saved successfully.', 'qa-heatmap-analytics' );
        $localize['cnv_saved_2']               = esc_html__( 'Due to data processing, it may take a few minutes to about 30 minutes for the data to be displayed in the conversion report.', 'qa-heatmap-analytics' );
        $localize['nothing_page_id']           = esc_html__( 'Sorry, a post or page that is either newly created or never visited cannot be set as a goal. Please allow at least one day.', 'qa-heatmap-analytics' );
        $localize['nothing_page_id2']          = esc_html__( 'Or, please ensure the URL belongs to this WordPress site.', 'qa-heatmap-analytics' );
        $localize['wrong_regex_delimiter']     = esc_html__( 'The pattern does not have a valid starting or ending delimiter.', 'qa-heatmap-analytics' );
        $localize['mail_btn_updating']         = esc_html__( 'Updating...', 'qa-heatmap-analytics' );
        $localize['mail_alert_update_failed']  = esc_html__( 'Failed updating. Please retry again.', 'qa-heatmap-analytics' );
        $localize['setting_option_saved']      = esc_html__( 'Plugin options saved successfully.', 'qa-heatmap-analytics' );
        $localize['setting_option_failed']     = esc_html__( 'Failed saving plugin options.', 'qa-heatmap-analytics' );
		$localize['site_info_saved']           = esc_html__( 'Site category saved.', 'qa-heatmap-analytics' );
        $localize['site_info_failed']          = esc_html__( 'Failed to save Site Category.', 'qa-heatmap-analytics' );
        $localize['alert_message_success']     = esc_html__( 'Success', 'qa-heatmap-analytics' );
		$localize['alert_message_failed']      = esc_html__( 'Failed to update settings', 'qa-heatmap-analytics' );

        wp_localize_script( QAHM_NAME . '-common', QAHM_NAME . 'l10n', $localize );

		$this->localize_ary = $localize;

	}

	/**
	 * ページの表示
	 */
	public function create_html() {
		if( ! $this->is_enqueue_jquery() ) {
			$this->view_not_enqueue_jquery_html();
			return;
		}

		if( $this->is_maintenance() ) {
			$this->view_maintenance_html();
			return;
		}

		if( $this->wrap_get_option( 'plugin_first_launch' ) ) {
			$this->view_first_launch_html();
			return;
		}

		// データを取得
        global $qahm_data_api;
		global $qahm_google_api;

        $GOALMAX = 1;
        if ( $this->get_license_plan( 'goal_3' ) ) {
            $GOALMAX = 3;
		}
        if ( $this->get_license_plan( 'goal_10' ) ) {
            $GOALMAX = 10;
		}

        $siteinfo_ary = $qahm_data_api->get_siteinfo_array();



        $goals_ary = $qahm_data_api->get_goals_array();

        $sitetype_ary = [
            "general_company",
            "media_affiliate",
            "service_matching",
            "ec_ec" ,
            "general_shop" ,
            "media_owned" ,
            "service_ugc" ,
            "ec_contents" ,
            "general_ir" ,
            "media_other" ,
            "service_membershi",
            "ec_license" ,
            "general_recruit",
            "service_other" ,
            "ec_other" ,
        ];

        $lang_ja['target_user_question'] =  esc_html__( 'Which type of users do you want meet the goal?', 'qa-heatmap-analytics' );
        $lang_ja['target_individual'] =  esc_html__( 'Personal', 'qa-heatmap-analytics' );
        $lang_ja['target_corporation'] =  esc_html__( 'Corporations/Organizations', 'qa-heatmap-analytics' );


        $lang_ja['select_sitetype_question'] =  esc_html__( 'Choose a category that describes your site best.', 'qa-heatmap-analytics' );
        $lang_ja['general'] =  esc_html__( 'General', 'qa-heatmap-analytics' );
        $lang_ja['media'] =  esc_html__( 'Media', 'qa-heatmap-analytics' );
        $lang_ja['service'] =  esc_html__( 'Providing services', 'qa-heatmap-analytics' );
        $lang_ja['ec_mall'] =  esc_html__( 'EC/Mall', 'qa-heatmap-analytics' );

        $lang_ja['general_company'] = esc_html__( 'About a company/services', 'qa-heatmap-analytics' );
        $lang_ja['media_affiliate'] = esc_html__( 'Affiliate blogs/Media', 'qa-heatmap-analytics' );
        $lang_ja['service_matching'] = esc_html__( 'Matching', 'qa-heatmap-analytics' );
        $lang_ja['ec_ec'] = esc_html__( 'Product sales', 'qa-heatmap-analytics' );
        $lang_ja['general_shop'] = esc_html__( 'About stores/facilities', 'qa-heatmap-analytics' );
        $lang_ja['media_owned'] = esc_html__( 'Owned media', 'qa-heatmap-analytics' );
        $lang_ja['service_ugc'] = esc_html__( 'Posting', 'qa-heatmap-analytics' );
        $lang_ja['ec_contents'] = esc_html__( 'Online content sales', 'qa-heatmap-analytics' );
        $lang_ja['general_ir'] = esc_html__( 'IR', 'qa-heatmap-analytics' );
        $lang_ja['media_other'] = esc_html__( 'Other information dissemination', 'qa-heatmap-analytics' );
        $lang_ja['service_membershi'] = esc_html__( 'SNS/Member services', 'qa-heatmap-analytics' );
        $lang_ja['ec_license'] = esc_html__( 'License sales', 'qa-heatmap-analytics' );
        $lang_ja['general_recruit'] = esc_html__( 'Recruitment', 'qa-heatmap-analytics' );
        $lang_ja['service_other'] = esc_html__( 'Other services', 'qa-heatmap-analytics' );
        $lang_ja['ec_other'] = esc_html__( 'Other sales', 'qa-heatmap-analytics' );

        $lang_ja['membership_question'] = esc_html__( 'Does the site have "member registration"?', 'qa-heatmap-analytics' );
        $lang_ja['payment_question'] = esc_html__( 'Does the site have "payment function"?', 'qa-heatmap-analytics' );
        $lang_ja['goal_monthly_access_question'] = esc_html__( 'Enter the target number for monthly sessions.', 'qa-heatmap-analytics' );

        $lang_ja['membership_yes'] = esc_html__( 'Yes.', 'qa-heatmap-analytics' );
        $lang_ja['membership_no'] = esc_html__( 'No.', 'qa-heatmap-analytics' );
        $lang_ja['next'] = esc_html__( 'Next', 'qa-heatmap-analytics' );
        $lang_ja['save'] = esc_attr__( 'Save', 'qa-heatmap-analytics' );

        $lang_ja['payment_no'] = esc_html__( 'No.', 'qa-heatmap-analytics' );
        $lang_ja['payment_yes'] = esc_html__( 'Yes, using original system.', 'qa-heatmap-analytics' );
        $lang_ja['payment_cart'] = esc_html__( 'Using external cart system.', 'qa-heatmap-analytics' );


        $lang_ja['month_later'] = esc_html__( 'month(s) later, reaching', 'qa-heatmap-analytics' );
        $lang_ja['session_goal'] = esc_html__( 'sessions/month is the goal.', 'qa-heatmap-analytics' );

        $goal        = esc_html__( 'Goal', 'qa-heatmap-analytics' );
        $goal_title  = esc_html__( 'Goal Name', 'qa-heatmap-analytics' );
        $required    = esc_html_x( '*', 'A mark that indicates it is required item.', 'qa-heatmap-analytics' );
        $goal_number = esc_html__( 'Completions Target in a Month', 'qa-heatmap-analytics' );
        $num_scale   = esc_html__( 'completion(s)', 'qa-heatmap-analytics' );
        $goal_value  = esc_html__( 'Goal Value (avg. monetary amount per goal)', 'qa-heatmap-analytics' );
        $val_scale   = esc_html_x( 'dollar(s)', 'Please put your currency. (This is only for a goal criterion.)', 'qa-heatmap-analytics' );
        $goal_sales  = esc_html__( 'Estimated Total Goal Value', 'qa-heatmap-analytics' );
        $goal_type   = esc_html__( 'Goal Type', 'qa-heatmap-analytics' );
        $goal_type_page  = esc_html__( 'Destination', 'qa-heatmap-analytics' );
        $goal_type_click = esc_html__( 'Click', 'qa-heatmap-analytics' );
        $goal_type_event = esc_html__( 'Event (Advanced)', 'qa-heatmap-analytics' );
        $goal_page   = esc_html__( 'Web page URL', 'qa-heatmap-analytics' );
        $click_page  = esc_html__( 'On which page?', 'qa-heatmap-analytics' );
        $eventtype   = esc_html__( 'Event Type', 'qa-heatmap-analytics' );
        $savechanges = esc_attr__( 'Save Changes', 'qa-heatmap-analytics' );
        $savegoal = esc_attr__( 'Save and Update', 'qa-heatmap-analytics' );
        $savesetting = esc_attr__( 'Settings saved.', 'qa-heatmap-analytics' );
        $clickselector = esc_html__( 'Click on the element shown below. (Its selector will be automatically detected and filled.)', 'qa-heatmap-analytics' );
        $eventselector = esc_html__( 'Hyperlink Reference (Regular Expression with delimiter)', 'qa-heatmap-analytics' );
        $example    = esc_html__( 'Example:', 'qa-heatmap-analytics' );
        $pagematch_complete = esc_html__( 'Equals to', 'qa-heatmap-analytics' );
        $pagematch_prefix   = esc_html__( 'Begins with', 'qa-heatmap-analytics' );
        $click_sel_load = esc_html__( 'Load the Page', 'qa-heatmap-analytics' );
        $click_sel_set  = esc_html__( 'Selector input completed.', 'qa-heatmap-analytics' );
        $unset_goal = esc_html_x( 'Unset', 'unset a goal', 'qa-heatmap-analytics' );


        //each event
        $event_click   = esc_html__( 'Link Click', 'qa-heatmap-analytics' );

        // iframe
        $click_iframe_url = esc_url( get_home_url() );



        //1st which panel will be oepn?
        /*
        $oepndetail = array_fill(1, 2, '' );
        if ( isset( $siteinfo_ary['session_goal'] ) || isset( $siteinfo_ary['sitetype'] ) ) {
            $oepndetail[2] = 'open';
        }else{
            $oepndetail[1] = 'open';
        }
*/
        $license_plan = $this->wrap_get_option( 'license_plans' );

		// Google API 認証情報
		$access_token = null;
		$credentials  = $qahm_google_api->get_credentials();
		if ( $credentials && isset($credentials['token']) && isset($credentials['token']['access_token']) ) {
			$access_token = $credentials['token']['access_token'];
		}
?>

		<div id="<?php echo esc_attr( basename( __FILE__, '.php' ) ); ?>" class="qahm-admin-page">
			<div class="wrap">
                <h1>QA <?php esc_html_e( 'Settings', 'qa-heatmap-analytics' ); ?></h1>
				<?php
				if ( $this->wrap_get_option( 'google_is_redirect' ) ) {
					if ( $qahm_google_api->is_auth() ) {
						echo $this->create_qa_announce_html( esc_html( __( 'Connected with Google API successfully.', 'qa-heatmap-analytics' ) ), 'success' );
					} else {
						echo $this->create_qa_announce_html( esc_html( __( 'Failed to connect with Google API.', 'qa-heatmap-analytics' ) ), 'error' );
					}
					$this->wrap_update_option( 'google_is_redirect', false );
				}
				
				$form_google_disabled = '';
				if ( $qahm_google_api->is_auth() ) {
				//	$form_google_disabled = ' disabled';
				}
				
				$err_ary = $qahm_google_api->test_search_console_connect();
				if ( $err_ary ) {
					$err_text  = esc_html( __( 'Failed to connect with Google API.', 'qa-heatmap-analytics' ) ) . '<br>';
					$err_text .= '<br>';
					$err_text .= 'error code: ' . $err_ary['code'] . '<br>';
					$err_text .= 'error message: ' . $err_ary['message'];
					echo $this->create_qa_announce_html( $err_text, 'error' );
				}
				?>
				<div class="tabs">
<!--mkdummy-->
					<input id="tab_plugin" type="radio" name="tab_item" value="plugin" checked>
					<label class="tab_item" for="tab_plugin"><span class="qahm_margin-right4" style="pointer-events: none;"><i class="fas fa-cog"></i> </span><?php echo esc_html( __( 'Plugin Options', 'qa-heatmap-analytics' ) ); ?></label>
					<input id="tab_google" type="radio" name="tab_item" value="google">
					<label class="tab_item" for="tab_google"><span class="qahm_margin-right4" style="pointer-events: none;"><i class="fab fa-google"></i> </span><?php esc_html_e( 'Google API', 'qa-heatmap-analytics' ); ?></label>
					<input id="tab_goal" type="radio" name="tab_item" value="goal">
					<label class="tab_item" for="tab_goal"><span class="qahm_margin-right4" style="pointer-events: none;"><i class="fas fa-crosshairs"></i> </span><?php esc_html_e( 'Goals', 'qa-heatmap-analytics' ); ?></label>
					<input id="tab_attribute" type="radio" name="tab_item" value="attribute">
					<label class="tab_item" for="tab_attribute"><span class="qahm_margin-right4" style="pointer-events: none;"><i class="far fa-address-card"></i> </span><?php echo esc_html( __( 'Site Category', 'qa-heatmap-analytics' ) ); ?></label>
<!--mkdummy-->

<?php
/** ----------------------------
 * "Plugin"
 */
					$is_subscribed = $this->is_subscribed();
					if ( $is_subscribed ) {
						$data_disabled = '';
					} else {
						$data_disabled = ' disabled';
					}
					$register    = esc_attr__( 'Register', 'qa-heatmap-analytics' );
					//$savechanges = esc_attr__( 'Save Changes', 'qa-heatmap-analytics' );
					$registerbtn   = $register;
					$already_registered = false;
					$email = get_option('admin_email');
					$popmail_cycle = '';
					$popmail_day = '';
					
					$cb_sup_mode = $this->wrap_get_option( 'cb_sup_mode' );
					if ( $cb_sup_mode === 'yes' ) {
						$cb_sup_mode_checked = ' checked';
					} else {
						$cb_sup_mode_checked = '';
					}
?>
					<div class="tab_content" id="tab_plugin_content">
						<div class="bl_whitediv">
                            <div style="width: 800px">

                                <div class="mail_config_section">
                                    <h2><?php esc_html_e( 'Data Retention Period', 'qa-heatmap-analytics' ); ?></h2>
                                    <div class="mail_config_inputpart">
                                        <p><?php echo esc_html( __( 'For premium plan users only: Set the number of days to retain data. Free plan users are limited to a fixed retention period of 90 days.', 'qa-heatmap-analytics' ) ); ?></p>
										<p><input type="number" name="data_retention_dur" id="data_retention_dur" value="<?php echo esc_attr( $this->wrap_get_option( 'data_retention_dur' ) ); ?>" min="1" max="30000" required<?php echo esc_attr( $data_disabled ); ?>> <?php echo esc_html( __( 'days', 'qa-heatmap-analytics' ) ); ?></p>
                                    </div>
                                </div>

                                <div class="mail_config_section">
									<h2><?php echo esc_html( __( 'Cookie Banner Compatibility Mode', 'qa-heatmap-analytics' ) ); ?><span class="qahm-tooltip" data-qahm-tooltip="You may want to configure your cookie banner tool. Click to view the guide."><a href="https://mem.quarka.org/en/manual/about-cookie-banner-tool/" target="_blank" rel="noopener" style="color: #1d2327; margin-left: 0.5em;"><i class="far fa-question-circle"></i></a></span></h2>
									<div class="mail_config_inputpart">
										<p><?php echo esc_html( __( 'Please check this box if your site is using a cookie banner. If left unchecked, the plugin will utilize cookie-based tracking for web traffic measurement.', 'qa-heatmap-analytics' ) ); ?></p>
										<p><input type="checkbox" name="cb_sup_mode" id="cb_sup_mode"<?php echo esc_attr( $cb_sup_mode_checked ); ?>></p>
									</div>
                                </div>

                                <div class="mail_config_section">
									<h2><?php echo esc_html( __( 'Email Notifications', 'qa-heatmap-analytics' ) ); ?></h2>
									<div class="mail_config_inputpart">
										<p><?php echo esc_html( __( 'Register an email address to receive notifications about your QA Analytics plugin. If you do not wish to receive notifications, leave this field blank. (This is not a newsletter. Notifications are automatically sent from your WordPress system.)', 'qa-heatmap-analytics' ) ); ?></p>
										<p><input type="email" name="send_email_address" id="send_email_address" value="<?php echo esc_attr( $this->wrap_get_option( 'send_email_address' ) ); ?>"></p>
									</div>
                                </div>


								<div>
									<p><button name="plugin-submit" id="plugin-submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'qa-heatmap-analytics' ); ?></button></p>
								</div>
                            </div>
                        </div>
					</div>



<?php
/** --------------------------------
 * "Google API"
 */
?>
					
					<div class="tab_content" id="tab_google_content">
						<h1><?php echo esc_html__( 'Connect to Google API', 'qa-heatmap-analytics' ); ?></h1>
						<p>
							<?php echo esc_html( __( 'Integrating with the Google API allows you to retrieve data from Google Search Console.', 'qa-heatmap-analytics' ) ); ?>
							<span class="qahm_hatena-mark"><i class="far fa-question-circle"></i></span>
							<a href="https://mem.quarka.org/en/manual/connect-to-gsc/" target="_blank" rel="noopener"><?php echo esc_html( __( 'How to connect with API', 'qa-heatmap-analytics' ) ); ?><span class="qahm_link-mark"><i class="fas fa-external-link-alt"></i></span></a>
						</p>
						<form method="post" action="">
							<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME, false ); ?>

							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row">
											<label for="client_id">
												<?php echo esc_html( __( 'Client ID', 'qa-heatmap-analytics' ) ); ?>
											</label>
										</th>
										<td>
											<input name="client_id" type="text" id="client_id" value="<?php echo esc_attr( $qahm_google_api->get_client_id() ); ?>" class="regular-text"<?php echo $form_google_disabled; ?>>
										</td>
									</tr>

									<tr>
										<th scope="row">
											<label for="client_secret">
												<?php echo esc_html( __( 'Client Secret', 'qa-heatmap-analytics' ) ); ?>
											</label>
										</th>
										<td>
											<input name="client_secret" type="text" id="client_secret" value="<?php echo esc_attr( $qahm_google_api->get_client_secret() ); ?>" class="regular-text"<?php echo $form_google_disabled; ?>>
											<?php
											if ( $form_google_disabled !== '' ) {
												echo '<span id="client_info_disabled_text" style="font size: 0.9em; color: #2271b1; cursor: pointer; text-decoration: underline;">&nbsp;' . esc_html( __( 'Unlock the button\'s disabled' , 'qa-heatmap-analytics' ) ) . '</span>';
											}
											?>
											</td>
									</tr>

									<tr>
										<th scope="row">
											<label for="redirect_uri">
												<?php echo esc_html( __( 'Redirect URI', 'qa-heatmap-analytics' ) ); ?>
											</label>
										</th>
										<td>
											<p><?php echo esc_attr( admin_url( 'admin.php?page=qahm-config' ) ); ?></p>
										</td>
									</tr>

									<tr>
										<td colspan="2">
											<p class="submit" style="text-align: center">
												<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Authenticate', 'qa-heatmap-analytics' ); ?>">
											</p>
										</td>
									</tr>
									
									<tr>
										<td colspan="2">
											<p style="font-size: 13px;">
												<?php
													if ( $access_token ) {
														echo esc_html__( 'Authentication is complete and the token has been obtained. If the integration isn\'t working properly, please click the "Authenticate" button again to re-authenticate.', 'qa-heatmap-analytics' );
													}
												?>
											</p>
										</td>
									</tr>
								</tbody>
							</table>
						</form>
						
					</div><!-- endof #tab_google_content -->


<?php
/** ----------------------------
 * "Goal"
 */
?>
					<div class="tab_content" id="tab_goal_content">
                        <div class="tab_content_description">
                            <p><?php esc_html_e( 'Your goals not only appear in the "Goals" report on the Home screen but also that relevant data is displayed in the goal metrics of each report. Set your goals to track your progress effectively!', 'qa-heatmap-analytics' ); ?></p>
                            <h2><?php esc_html_e( 'Goal setup', 'qa-heatmap-analytics' ); ?></h2>
                            <p class="el_caption"><?php esc_html_e( 'You can change these goals at any time. This will not affect the collected data. The value will be recalculated each time.', 'qa-heatmap-analytics' ); ?> <span class="qahm_hatena-mark"><i class="far fa-question-circle"></i></span><a href="https://mem.quarka.org/en/manual/goal-setting/" target="_blank" rel="noopener"><?php esc_html_e( 'How to set goal', 'qa-heatmap-analytics' ); ?><span class="qahm_link-mark"><i class="fas fa-external-link-alt"></i></span></a></p>
                             
                            <div id="step2">

                        <?php
                        $gtype_iframe_display  = array_fill(1, $GOALMAX,  'style="display: none"' );
                        for ( $iii = 1; $iii <= $GOALMAX; $iii++ ) {
                            $gtitle = isset( $goals_ary[$iii]['gtitle'] ) ? urldecode( $goals_ary[$iii]['gtitle'] ) : '';
                            $gnum_scale = isset( $goals_ary[$iii]['gnum_scale'] ) ? urldecode( $goals_ary[$iii]['gnum_scale'] ) : 0;
                            $gnum_value = isset( $goals_ary[$iii]['gnum_value'] ) ? urldecode( $goals_ary[$iii]['gnum_value'] ) : 0;
                            $gtype = isset( $goals_ary[$iii]['gtype'] ) ? urldecode( $goals_ary[$iii]['gtype'] ) : 'gtype_page';
                            $g_goalpage = isset( $goals_ary[$iii]['g_goalpage'] ) ? urldecode( $goals_ary[$iii]['g_goalpage'] ) : '';
                            $g_pagematch = isset( $goals_ary[$iii]['g_pagematch'] ) ? urldecode( $goals_ary[$iii]['g_pagematch'] ) : '';
                            $g_clickpage = isset( $goals_ary[$iii]['g_clickpage'] ) ? urldecode( $goals_ary[$iii]['g_clickpage'] ) : '';
                            $g_eventtype = isset( $goals_ary[$iii]['g_eventtype'] ) ? urldecode( $goals_ary[$iii]['g_eventtype'] ) : '';
                            $g_clickselector = isset( $goals_ary[$iii]['g_clickselector'] ) ? urldecode( $goals_ary[$iii]['g_clickselector'] ) : '';
                            $g_eventselector = isset( $goals_ary[$iii]['g_eventselector'] ) ? urldecode( $goals_ary[$iii]['g_eventselector'] ) : '';


                            $gtype_checked  = array_fill(0, 3, '');
                            $gtype_required = array_fill(0, 3, '');
                            $pagematch_checked  = array_fill(0, 2, '');
                            $gtype_display  = array_fill(0, 3, 'style="display: none"');

                            //set default
                            if ( ! $g_clickpage ) {
                                $g_clickpage = $click_iframe_url;
                            }
                            switch ($gtype) {
                                case 'gtype_click':
                                    $gtype_checked[1]  ='checked';
                                    $gtype_required[1] = 'required';
                                    $gtype_iframe_display[$iii]  = '';
                                    $gtype_display[1] = '';
                                    break;

                                case 'gtype_event':
                                    $gtype_checked[2]  ='checked';
                                    $gtype_required[2] = 'required';
                                    $gtype_display[2] = '';
                                    break;

                                default:
                                case 'gtype_page':
                                    $gtype_checked[0]  ='checked';
                                    $gtype_required[0] = 'required';
                                    $gtype_display[0] = '';
                                    break;
                            }
                            switch ($g_pagematch) {
                                case 'pagematch_prefix':
                                    $pagematch_checked[ 1 ] = 'checked';
                                    break;

                                default:
                                case 'pagematch_complete':
                                    $pagematch_checked[ 0 ] = 'checked';
                                    break;
                            }

                            //when goal type = "click", select box for limited event-measuring page.
                            $clicktype_page_input = '<input type="text" name="g'.$iii.'_clickpage" id="g'. $iii.'_clickpage" value="'.$g_clickpage.'" '. $gtype_required[1].' placeholder="'.$click_iframe_url.'" size="40">';
                            $attention_only_measuring_page = '';



                            $goalbox = <<<EOL
                                <div class="bl_goalbox" id="g{$iii}_goalbox">
                                    <h3>{$goal}{$iii}</h3>
                                    <form id="g{$iii}_form" onsubmit="saveChanges(this);return false">
                                    <table>
                                        <colgroup>
                                            <col style="width: 15%">
                                            <col style="width: 65%">
                                            <col style="width: 20%">
                                        <tbody>
                                        <tr>
                                            <td>{$goal_title}<span class="el_attention">{$required}</span></td>
                                            <td><input type="text" name="g{$iii}_title" id="g{$iii}_title" required value="{$gtitle}" size="30"></td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>{$goal_number}</td>
                                            <td><input type="number" name="g{$iii}_num" id="g{$iii}_num" value="{$gnum_scale}" onchange="calcSales(this)">{$num_scale}</td>
                                            <td>&nbsp&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>{$goal_value}</td>
                                            <td><input type="number" name="g{$iii}_val" id="g{$iii}_val" value="{$gnum_value}" onchange="calcSales(this)">{$val_scale}&nbsp;<p class="right">{$goal_sales} = <span id="g{$iii}_calcsales">0</span> {$val_scale}</p></td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>{$goal_type}<span class="el_attention">{$required}</span>&nbsp;<span class="el_loading">Loading<span></span></span></td>
                                            <td class="td_gtype_save" style="opacity: 0">
                                                <input type="radio" name="g{$iii}_type" id="g{$iii}_type_page" value="gtype_page" {$gtype_checked[0]}><label for="g{$iii}_type_page">{$goal_type_page}</label>
                                                <input type="radio" name="g{$iii}_type" id="g{$iii}_type_click" value="gtype_click" {$gtype_checked[1]}><label for="g{$iii}_type_click">{$goal_type_click}</label>&nbsp;
                                                <span><input type="radio" name="g{$iii}_type" id="g{$iii}_type_event" value="gtype_event" {$gtype_checked[2]}><label for="g{$iii}_type_event">{$goal_type_event}</label></span>&nbsp;
                                                <br>
                                                <div id="g{$iii}_page_goal" ${gtype_display[0]} class="bl_eachGtypeBox">
                                                    <label>{$goal_page}</label><br>
                                                    <input type="radio" name="g{$iii}_pagematch" id="g{$iii}_pagematch_prefix" value="pagematch_prefix" {$pagematch_checked[1]}><label for="g{$iii}_pagematch_prefix">{$pagematch_prefix}</label>
                                                    <input type="radio" name="g{$iii}_pagematch" id="g{$iii}_pagematch_complete" value="pagematch_complete" {$pagematch_checked[0]}><label for="g{$iii}_pagematch_complete">{$pagematch_complete}</label><br>
                                                    <input type="text" name="g{$iii}_goalpage" id="g{$iii}_goalpage" value="{$g_goalpage}" {$gtype_required[0]} size="60">
                                                    &nbsp;
                                                </div>
                                                <div id="g{$iii}_click_goal" ${gtype_display[1]} class="bl_eachGtypeBox">

                                                    <label>{$click_page}</label>{$clicktype_page_input}
                                                    <button id="g{$iii}_click_pageload" class="button button-secondary" type="button">{$click_sel_load}</button><br>
                                                    {$attention_only_measuring_page}
                                                    <label>{$clickselector}</label><br><input type="text" name="g{$iii}_clickselector" id="g{$iii}_clickselector" disabled value="{$g_clickselector}" {$gtype_required[1]} size="60">
                                                    <div id="g{$iii}_event-iframe-tooltip-right" class="event-iframe-tooltip-right">{$click_sel_set}</div>
                                                </div>
                                                <div id="g{$iii}_event_goal" ${gtype_display[2]} class="bl_eachGtypeBox">
                                                    <label>{$eventtype}</label><select name="g{$iii}_eventtype" id="g{$iii}_eventtype"><option value="onclick">{$event_click}</option></select> <br><br>
                                                    <label>{$eventselector}</label><br><input type="text" name="g{$iii}_eventselector" id="g{$iii}_eventselector" value="{$g_eventselector}" {$gtype_required[2]} size="80">
                                                    <div style="background-color: #eee; padding: 0 10px;"><p>{$example}<br>/.*ad-link.*/<br>/\/my-goal-link\//</p></div>
                                                </div>
                                            </td>
                                            <td class="td_gtype_save" style="opacity: 0"><input type="submit" name="submit" id="g{$iii}_submit" value="{$savegoal}" class="button button-primary"><p class="el_right"><a href="#g{$iii}_goalbox" onclick="deleteGoalX({$iii})">{$unset_goal}</a></p></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div id="g{$iii}_event-iframe-containar" class=".event-iframe-containar" {$gtype_iframe_display[$iii]}>
                                        <iframe id="g{$iii}_event-iframe" class="event-iframe" src="{$g_clickpage}" frameborder="0" width="1200" height="400" scrolling="yes"></iframe>
                                    </div>
                                    </form>
                                </div>
EOL;
                                echo $goalbox;
                        }
                        //end for
                        ?>

                            </div>
                        </div>
                    </div><!-- endof #tab_goal_content -->



<?php
/** --------------------------------
 * "Site Category"
 */
?>
                    <div class="tab_content" id="tab_attribute_content">
                        <div id="step1">
                            <?php
                                $formtags[0] = <<<EOL
                                <form id="siteinfo_form" onsubmit="siteinfoChanges(this);return false">
                                <h3>{$lang_ja['target_user_question']}</h3>
EOL;
                                echo $formtags[0];

                                $target_ary = ['target_individual', 'target_corporation'];
                                foreach ( $target_ary as $target ) {
                                    $checked = '';
                                    if ( isset( $siteinfo_ary['target_customer'] ) ) {
                                        if ( $target === $siteinfo_ary['target_customer'] ) {
                                            $checked = 'checked';
                                        }
                                    }
                                    $radio_target = <<< EOL
                                    <input type="radio" name="target_customer" id="{$target}" value="{$target}" $checked><label for="{$target}">{$lang_ja[$target]}</label>
EOL;
                                    echo $radio_target;
                                }

                                $formtags[1] = <<<EOL
                                <h3>{$lang_ja['select_sitetype_question']}</h3>
                                <table>
                                    <thead>
                                    <th>{$lang_ja['general']}</th>
                                    <th>{$lang_ja['media']}</th>
                                    <th>{$lang_ja['service']}</th>
                                    <th>{$lang_ja['ec_mall']}</th>
                                    </thead>
                                    <tbody>
                                        <tr>
EOL;
                                echo $formtags[1];
                                foreach ( $sitetype_ary as $lpcnt => $sitetype ) {
                                    $checked = '';
                                    if ( isset( $siteinfo_ary['sitetype'] ) ) {
                                        if ( $sitetype === $siteinfo_ary['sitetype'] ) {
                                            $checked = 'checked';
                                        }
                                    }
                                    $radio_sitetype = <<< EOL
                                    <td><input type="radio" name="sitetype" id="{$sitetype}" value="{$sitetype}" $checked><label for="{$sitetype}">{$lang_ja[$sitetype]}</label></td>
EOL;
                                    $nowtd = $lpcnt + 1;
                                    if ( $nowtd === 14 ) {
                                        echo '<td>&nbsp;</td>' . PHP_EOL;
                                    }
                                    if ( 14 <= $nowtd ) {
                                        $nowtd++;
                                    }
                                    echo $radio_sitetype;

                                    if ( $nowtd % 4 === 0 ) {
                                        echo '</tr>' . PHP_EOL;
                                        if ( $nowtd !== 16 ) {
                                            echo '<tr>' . PHP_EOL;
                                        }
                                    }
                                }
                                $formtags[2] = <<< EOL
                                    </tbody>
                                </table>
                                <h3>{$lang_ja['membership_question']}</h3>
EOL;
                                echo $formtags[2];
                                $membership_ary = ['membership_no', 'membership_yes'];
                                foreach ( $membership_ary as $membership ) {
                                    $checked = '';
                                    if ( isset( $siteinfo_ary['membership'] ) ) {
                                        if ( $membership === $siteinfo_ary['membership'] ) {
                                            $checked = 'checked';
                                        }
                                    }
                                    $radio_membership = <<< EOL
                                    <input type="radio" name="membership" id="{$membership}" value="{$membership}" $checked><label for="{$membership}">{$lang_ja[$membership]}</label>
EOL;
                                    echo $radio_membership;
                                }

                                $formtags[3] = <<< EOL
                                <h3>{$lang_ja['payment_question']}</h3>
EOL;
                                echo $formtags[3];
                                $payment_ary = ['payment_no', 'payment_yes', 'payment_cart'];
                                foreach ( $payment_ary as $payment ) {
                                    $checked = '';
                                    if ( isset( $siteinfo_ary['payment'] ) ) {
                                        if ( $payment === $siteinfo_ary['payment'] ) {
                                            $checked = 'checked';
                                        }
                                    }
                                    $radio_payment = <<< EOL
                                    <input type="radio" name="payment" id="{$payment}" value="{$payment}" $checked><label for="{$payment}">{$lang_ja[$payment]}</label>
EOL;
                                    echo $radio_payment;
                                }

                                $month_later  = isset( $siteinfo_ary['month_later'] )? $siteinfo_ary['month_later'] : '';
                                $session_goal = isset( $siteinfo_ary['session_goal'] )? $siteinfo_ary['session_goal'] : '';
                                $formtags[4] = <<< EOL
                                <h3>{$lang_ja['goal_monthly_access_question']}</h3>
                                <input type="number" name="month_later" id="month_later" value="{$month_later}"><label for="month_later">{$lang_ja['month_later']}</label>&nbsp;
                                <input type="number" name="session_goal" id="session_goal" value="{$session_goal}"><label for="session_goal">{$lang_ja['session_goal']}</label>
                                <p><input type="submit" value="{$lang_ja['save']}"></p>
                                </form>
EOL;
                                echo $formtags[4];
                            ?>
                        </div>
                    </div><!-- endof #tab_attribute_content -->

				</div>
			</div>
		</div>


<?php
	}

	/**
	 * 設定画面の項目をデータベースに保存する
	 */
	public function ajax_save_plugin_config() {
		$data_retention_dur = (int) $this->wrap_filter_input( INPUT_POST, 'data_retention_dur' );
		$cb_sup_mode        = $this->wrap_filter_input( INPUT_POST, 'cb_sup_mode' );
		if ( $cb_sup_mode === 'true' ) {
			$cb_sup_mode = 'yes';
		} else {
			$cb_sup_mode = 'no';
		}
		$send_email_address = $this->wrap_filter_input( INPUT_POST, 'send_email_address' );

		$this->wrap_update_option( 'data_retention_dur', $data_retention_dur );
		$this->wrap_update_option( 'cb_sup_mode', $cb_sup_mode );
		$this->wrap_update_option( 'send_email_address', $send_email_address );
	}
} // end of class
