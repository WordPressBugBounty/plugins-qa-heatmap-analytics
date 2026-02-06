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
		add_action( 'load-toplevel_page_qahm-config', array( $this, 'admin_init' ) );

		// AJAX関数の登録
	    add_action('wp_ajax_qahm_ajax_save_plugin_config', array($this, 'ajax_save_plugin_config'));
	}
	
	// 管理画面の初期化
	public function admin_init(){
		if( defined('DOING_AJAX') && DOING_AJAX ){
			return;
		}
		if ( $this->is_redirect() ) {
			return;
		}
	
		global $qahm_google_api;
		global $qahm_data_api;

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		$tracking_id_raw = isset( $_GET['tracking_id'] ) ? $this->sanitize_tracking_id( wp_unslash( $_GET['tracking_id'] ) ) : 'all';
		$tracking_id     = $this->get_safe_tracking_id( $tracking_id_raw );
		$scope   = array( 'https://www.googleapis.com/auth/webmasters.readonly' );
		
		$sitemanage = $qahm_data_api->get_sitemanage();
		if ( $sitemanage ) {
			$url = null;
			foreach ( $sitemanage as $site ) {
				if ( $tracking_id === $site['tracking_id'] ) {
					$url = $site['url'];
					break;
				}
			}

			if ( isset( $_POST[ self::NONCE_NAME ] ) ) {
				// フォーム送信時
				// どのフォームが送信されたかを確認
				$form_type = isset( $_POST['form_type'] )
					? sanitize_key( wp_unslash( $_POST['form_type'] ) )
					: '';

				// Google API 設定フォームの場合のみ処理
				if ( 'save_google_credentials' === $form_type ) {

					// nonceチェック
					check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME ); // 失敗時は内部で停止するので分岐不要

					// wrap_filter_inputではなく、WordPressの unslash → sanitize を使う
					$client_id     = isset( $_POST['client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) : '';
					$client_secret = isset( $_POST['client_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['client_secret'] ) ) : '';

					$qahm_google_api->set_credentials( $client_id, $client_secret, null, $tracking_id );
					$qahm_google_api->set_tracking_id( $tracking_id, $url );
					$qahm_google_api->init_for_admin(
						'Google API Integration',
						$scope,
						admin_url( 'admin.php?page=qahm-config' ),
						true
					);
				}
			} else {
				// 通常表示
				$qahm_google_api->set_tracking_id( $tracking_id, $url );
				$qahm_google_api->init_for_admin(
					'Google API Integration',
					$scope,
					admin_url( 'admin.php?page=qahm-config' )
				);
			}

		}
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

		if ( $this->is_redirect() ) {
			return;
		}
	
		global $qahm_time;
		$js_dir       = $this->get_js_dir_url();
		$data_dir     = $this->get_data_dir_url();
		$css_dir_url = $this->get_css_dir_url();

		$GOALMAX = 10;

		// enqueue_style
		$this->common_enqueue_style();
		wp_enqueue_script( QAHM_NAME . '-admin-page-config', $js_dir . 'admin-page-config.js', array( QAHM_NAME . '-effect' ), QAHM_PLUGIN_VERSION ); //QA ZERO add
	
		if ( QAHM_TYPE === QAHM_TYPE_ZERO ) {
			wp_enqueue_style( QAHM_NAME . '-admin-page-config', $css_dir_url . 'admin-page-config-zero.css', array( QAHM_NAME . '-reset' ), QAHM_PLUGIN_VERSION );
		} elseif ( QAHM_TYPE === QAHM_TYPE_WP ) {
			wp_enqueue_style( QAHM_NAME . '-admin-page-config', $css_dir_url . 'admin-page-config-wp.css', array( QAHM_NAME . '-reset' ), QAHM_PLUGIN_VERSION );
		}
	
		// enqueue script
		$this->common_enqueue_script();

		// g_clickpage の変数作成
        global $qahm_data_api;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		$tracking_id_raw = isset( $_GET['tracking_id'] ) ? $this->sanitize_tracking_id( wp_unslash( $_GET['tracking_id'] ) ) : 'all';
		$tracking_id     = $this->get_safe_tracking_id( $tracking_id_raw );
		$goals_ary = $qahm_data_api->get_goals_preferences($tracking_id);
		$click_iframe_url = esc_url( get_home_url() );
		$measuring_page_url = array();
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
		wp_add_inline_script( QAHM_NAME . '-common', 'var ' . QAHM_NAME . ' = ' . QAHM_NAME . ' || {}; let ' . QAHM_NAME . 'Obj = ' . $this->wrap_json_encode( $scripts ) . '; ' . QAHM_NAME . ' = Object.assign( ' . QAHM_NAME . ', ' . QAHM_NAME . 'Obj );', 'before' );

		// localize
		$localize = $this->get_common_localize_script();
		$localize['data_save_month_title']     = esc_html__( 'Data Storage Period', 'qa-heatmap-analytics' );
		$localize['settings_saved']           = esc_attr__( 'Settings saved.', 'qa-heatmap-analytics' );
		$localize['cnv_couldnt_saved']        = esc_html__( 'Could not be saved. The value is same as before or is incorrect.', 'qa-heatmap-analytics' );
		$localize['cnv_delete_confirm']       = esc_html__( 'Are you sure to delete this goal?', 'qa-heatmap-analytics' );
		$localize['cnv_couldnt_delete']       = esc_html__( 'Could not delete. The value is incorrect.', 'qa-heatmap-analytics' );
		$localize['cnv_page_set_alert']       = esc_html__( 'You are trying to set all the pages.', 'qa-heatmap-analytics' );
		$localize['cnv_goal_numbering_alert'] = esc_html__( 'There is a skip in goal numbers. Please set goals sequentially.', 'qa-heatmap-analytics' );
		/* translators: placeholders are for a goal ID */
        $localize['cnv_saved_1']               = esc_html__( 'Goal %d saved successfully.', 'qa-heatmap-analytics' );
		/* translators: placeholders are for a goal ID */
		$localize['cnv_deleted']			  = esc_html__( 'Goal %d deleted.', 'qa-heatmap-analytics' );
		$localize['cnv_deleted2'] = esc_html__( 'Press OK to reload the page.', 'qa-heatmap-analytics' );
		$localize['cnv_reaching_goal_notice'] = esc_attr__( 'There are goals that have been achieved in the past 30 days.', 'qa-heatmap-analytics' );
		$localize['cnv_saving']               = esc_attr__( 'Saving...', 'qa-heatmap-analytics' );
		$localize['cnv_load_page']            = esc_html__( 'Load the Page', 'qa-heatmap-analytics' );
		$localize['cnv_loading']              = esc_html__( 'Loading...', 'qa-heatmap-analytics' );
		$localize['cnv_in_progress']     = esc_html__( 'Generating goal data for the past 30 days.', 'qa-heatmap-analytics' );
		$localize['cnv_estimated_time']  = esc_html__( '(Estimated time) About', 'qa-heatmap-analytics' );
		$localize['x_minutes']           = esc_html__( 'minutes', 'qa-heatmap-analytics' );
		$localize['x_seconds']           = esc_html__( 'seconds', 'qa-heatmap-analytics' );
		$localize['cnv_estimated_time2']	  = esc_html__( 'The report may take a few minutes to update after processing is complete.', 'qa-heatmap-analytics' );
		$localize['cnv_save_failed']		  = esc_html__( 'Failed to save the goal.', 'qa-heatmap-analytics' );
        $localize['nothing_page_id']           = esc_html__( 'Sorry, a post or page that is either newly created or never visited cannot be set as a goal. Please allow at least one day.', 'qa-heatmap-analytics' );
        $localize['nothing_page_id2']          = esc_html__( 'Or, please ensure the URL belongs to this WordPress site.', 'qa-heatmap-analytics' );
        $localize['wrong_regex_delimiter']     = esc_html__( 'The pattern does not have a valid starting or ending delimiter.', 'qa-heatmap-analytics' );
		$localize['no_pvterm']				  = esc_html__( 'Analytics data may not yet be available. Please wait a few days or review your settings.', 'qa-heatmap-analytics' );
		$localize['failed_iframe_load']		  = esc_html__( 'Failed to load the page. Please check the URL.', 'qa-heatmap-analytics' );
		$localize['mail_btn_updating']        = esc_html__( 'Updating...', 'qa-heatmap-analytics' );
		$localize['mail_alert_update_failed'] = esc_html__( 'Failed updating. Please retry again.', 'qa-heatmap-analytics' );
		$localize['please_try_again'] 		  = esc_html__( 'Please try again.', 'qa-heatmap-analytics' );

		// プラグイン設定用のローカライズテキスト
		$localize['data_save_month_title'] = esc_html__('Data Storage Period', 'qa-heatmap-analytics');
		$localize['setting_option_saved'] = esc_html__('Plugin options saved successfully.', 'qa-heatmap-analytics');
		$localize['setting_option_failed'] = esc_html__('Failed saving plugin options.', 'qa-heatmap-analytics');
		$localize['alert_message_success'] = esc_html__('Success', 'qa-heatmap-analytics');
		$localize['alert_message_failed'] = esc_html__('Failed to update settings', 'qa-heatmap-analytics');
		$localize['nonce_qahm_options'] = wp_create_nonce('qahm-config-nonce-action-qahm-options');

		wp_localize_script( QAHM_NAME . '-common', QAHM_NAME . 'l10n', $localize );

		$this->localize_ary = $localize;

	}

	/**
	 * ページの表示
	 */
	public function create_html() {
		if ( $this->is_redirect() ) {
			return;
		}
        
		if( ! $this->is_enqueue_jquery() ) {
			$this->print_not_enqueue_jquery_html();
			return;
		}

		if( $this->is_maintenance() ) {
			$this->print_maintenance_html();
			return;
		}

		// データを取得
		global $qahm_data_api;
		global $qahm_google_api;

		$GOALMAX = 10;

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		$tracking_id_raw = isset( $_GET['tracking_id'] ) ? $this->sanitize_tracking_id( wp_unslash( $_GET['tracking_id'] ) ) : 'all';
		$tracking_id     = $this->get_safe_tracking_id( $tracking_id_raw );

		$siteinfo_ary = $qahm_data_api->get_siteinfo_preferences($tracking_id);

		$goals_ary = $qahm_data_api->get_goals_preferences($tracking_id);

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
		$lang_ja['save'] = esc_html__( 'Save', 'qa-heatmap-analytics' );

		$lang_ja['payment_no'] = esc_html__( 'No.', 'qa-heatmap-analytics' );
		$lang_ja['payment_yes'] = esc_html__( 'Yes, using original system.', 'qa-heatmap-analytics' );
		$lang_ja['payment_cart'] = esc_html__( 'Using external cart system.', 'qa-heatmap-analytics' );


		$lang_ja['month_later'] = esc_html__( 'month(s) later, reaching', 'qa-heatmap-analytics' );
		$lang_ja['session_goal'] = esc_html__( 'sessions/month is the goal.', 'qa-heatmap-analytics' );

		$goal_noun   = esc_html__( 'Goal', 'qa-heatmap-analytics' );
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
		$savegoal = esc_attr__( 'Save Goal', 'qa-heatmap-analytics' );
		$savesetting = esc_attr__( 'Settings saved.', 'qa-heatmap-analytics' );
		$clickselector = esc_html__( 'Click the object. (auto-fill)', 'qa-heatmap-analytics' );
		$eventselector = esc_html__( 'Hyperlink Reference (Regular Expression with delimiter)', 'qa-heatmap-analytics' );
		$example    = esc_html__( 'Example:', 'qa-heatmap-analytics' );
		$pagematch_complete = esc_html__( 'Equals to', 'qa-heatmap-analytics' );
		$pagematch_prefix   = esc_html__( 'Begins with', 'qa-heatmap-analytics' );
		$click_sel_load = esc_html__( 'Load the Page', 'qa-heatmap-analytics' );
		$click_sel_set  = esc_html__( 'Selector input completed.', 'qa-heatmap-analytics' );
		$unset_goal = esc_html_x( 'Unset', 'unset a goal', 'qa-heatmap-analytics' );


		//each event
		$event_click   = esc_html__( 'on click', 'qa-heatmap-analytics' );
		$event_value_click   = 'onclick';

		// iframe
		$click_iframe_url = '';
		$sitemanage = $qahm_data_api->get_sitemanage();
		if ( $sitemanage ) {
			foreach( $sitemanage as $site ) {
				if ( $site['tracking_id'] === $tracking_id ) {
					$click_iframe_url = "https://" . $site['url'];
					break;
				}
			}
		}


		//1st which panel will be oepn?
		$oepndetail = array_fill(1, 2, '' );
		if ( isset( $siteinfo_ary['session_goal'] ) || isset( $siteinfo_ary['sitetype'] ) ) {
			$oepndetail[2] = 'open';
		}else{
			$oepndetail[1] = 'open';
		}

		//event measuring page
		$measuring_page_url = array();
	
		// Google API 認証情報
		$access_token = null;
		$credentials  = $qahm_google_api->get_credentials( $tracking_id );
		if ( $credentials && isset($credentials['token']) && isset($credentials['token']['access_token']) ) {
			$access_token = $credentials['token']['access_token'];
		}

		
?>

		<!-- このページはZEROのスタイルを適用していない。タイトルのみ無理やりZEROのスタイルを適用している。 -->
		<div id="<?php echo esc_attr( basename( __FILE__, '.php' ) ); ?>" class="qahm-admin-page">
			<div class="wrap">
				<h1 style="color: var(--SubColor-02, #02926F); font-feature-settings: 'clig' off, 'liga' off; font-family: Helvetica Neue; font-size: 32px; font-style: normal; font-weight: 700; line-height: 40px; letter-spacing: -0.5px; padding: 18px;"><?php esc_html_e( 'Settings', 'qa-heatmap-analytics' ); ?></h1>

				<?php
				if ( $this->wrap_get_option( 'google_is_redirect' ) ) {
					if ( $qahm_google_api->is_auth() ) {
						$this->print_qa_announce_html( esc_html( __( 'Connected with Google API successfully.', 'qa-heatmap-analytics' ) ), 'success' );
					} else {
						$this->print_qa_announce_html( esc_html( __( 'Failed to connect with Google API.', 'qa-heatmap-analytics' ) ), 'error' );
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
					$this->print_qa_announce_html( $err_text, 'error' );
				}
				?>
				<div class="tabs">

					<?php
						$goal_checked = 'checked';
						if ( QAHM_TYPE === QAHM_TYPE_WP ) {
							$goal_checked = '';
							?>
							<input id="tab_plugin" type="radio" name="tab_item" value="plugin" checked>
							<label class="qahm-config__tab-item" for="tab_plugin"><span class="qahm_margin-right4" style="pointer-events: none;"><i class="fas fa-cog"></i> </span><?php echo esc_html( __( 'General Settings', 'qa-heatmap-analytics' ) ); ?></label>
							<?php
						}
					?>
					<input id="tab_goal" type="radio" name="tab_item" value="goal" <?php echo esc_attr($goal_checked); ?>>
					<label class="qahm-config__tab-item" for="tab_goal"><span class="qahm_margin-right4" style="pointer-events: none;"><i class="fas fa-crosshairs"></i> </span><?php esc_html_e( 'Goals', 'qa-heatmap-analytics' ); ?></label>
					<input id="tab_site_attr" type="radio" name="tab_item" value="google">
					<?php if( false ) : // サイトの属性を非表示 ?>
					<label class="qahm-config__tab-item" for="tab_site_attr"><span class="qahm_margin-right4" style="pointer-events: none;"><i class="far fa-address-card"></i> </span><?php esc_html_e( 'Site Profile', 'qa-heatmap-analytics' ); ?></label>
					<?php endif; ?>
					<input id="tab_google" type="radio" name="tab_item" value="google">
					<label class="qahm-config__tab-item" for="tab_google"><span class="qahm_margin-right4" style="pointer-events: none;"><i class="fab fa-google"></i> </span><?php esc_html_e( 'Google Integration', 'qa-heatmap-analytics' ); ?></label>
<!--mkdummy-->

					<?php
					
					$advanced_mode = $this->wrap_get_option( 'advanced_mode' );
					if ( $advanced_mode == true ) {
						$advanced_mode = ' checked';
					} else {
						$advanced_mode = '';
					}
					
					$cb_sup_mode = $this->wrap_get_option( 'cb_sup_mode' );
					if ( $cb_sup_mode === 'yes' ) {
						$cb_sup_mode_checked = ' checked';
					} else {
						$cb_sup_mode_checked = '';
					}

					/** ----------------------------
					 * プラグイン設定
					 */
					if( QAHM_TYPE === QAHM_TYPE_WP || QAHM_TYPE === QAHM_TYPE_ZERO ) { ?>
						<div class="qahm-config__tab-content" id="tab_plugin_content">
							<div style="width: 800px">
								<div class="qahm-config__general-section">
									<h2><?php echo esc_html__( 'Advanced Mode', 'qa-heatmap-analytics' ); ?></h2>
									<div class="qahm-config__general-section-content">
										<p>
											<?php echo esc_html__(
												'Advanced Mode enables access to detailed reports, including Audience, Acquisition, Behavior, and Goals. If you prefer a simpler interface, disable it to only see the essential metrics.',
												'qa-heatmap-analytics'
											); ?>
										</p>
										<p>
											<input type="checkbox" name="advanced_mode" id="advanced_mode"<?php echo esc_attr( $advanced_mode ); ?>>
										</p>
									</div>
								</div>


								<div class="qahm-config__general-section">
									<h2>
										<?php echo esc_html__( 'Enable Cookieless Tracking', 'qa-heatmap-analytics' ); ?>
									</h2>
									<div class="qahm-config__general-section-content">
										<p>
											<?php echo esc_html__( 'This plugin uses cookieless tracking by default. If a cookie banner is present, it will respect the visitor\'s consent and adjust tracking behavior accordingly. Uncheck this if you want to always use cookies for tracking (not recommended).', 'qa-heatmap-analytics' ); ?>
										</p>
										<p>
											<input type="checkbox" name="cb_sup_mode" id="cb_sup_mode"<?php echo esc_attr( $cb_sup_mode_checked ); ?>>
										</p>
										<p><?php echo esc_html__( 'If you are using a cookie banner tool, you may need to configure it to work properly with cookieless tracking. For more details, visit our documentation site.', 'qa-heatmap-analytics' ); ?><br>
										<a href="<?php echo esc_url( QAHM_DOCUMENTATION_URL ); ?>" target="_blank" rel="noopener"><?php echo esc_html( QAHM_PLUGIN_NAME ); ?> Documentation</a></p>
									</div>
								</div>

								<div>
									<p><button name="plugin-submit" id="plugin-submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'qa-heatmap-analytics' ); ?></button></p>
								</div>

								<hr>
								<?php 
								$retention_days = $this->get_data_retention_days();
								$monthly_pv_limit = QAHM_CONFIG_LIMIT_PV_MONTH;
								?>
								<div class="qahm-config-note" style="margin-top:12px;padding:12px 14px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
									<h3><?php esc_html_e('Data retention & limits', 'qa-heatmap-analytics'); ?></h3>

									<ul style="margin:0 0 .6em 1.1em;list-style:disc;">
										<li style="margin-bottom:.4em;">
										<?php esc_html_e('Data retention', 'qa-heatmap-analytics'); ?>:
										<strong><?php echo esc_html( number_format_i18n( $retention_days ) ); ?></strong>
										<?php esc_html_e('days', 'qa-heatmap-analytics'); ?>
										</li>
										<li style="margin-bottom:.4em;">
										<?php esc_html_e('Monthly PV limit', 'qa-heatmap-analytics'); ?>:
										<strong><?php echo esc_html( number_format_i18n( $monthly_pv_limit ) ); ?></strong>
										</li>
									</ul>

									<p style="margin:1.5em 0 0; color:#4b5563;">
										<?php										
										$text = sprintf(
											/* translators: 1: opening <code> tag, 2: closing </code> tag. Example output: Defined in <code>qa-config.php</code>. You can change them by editing this file. */
											__( 'Defined in %1$sqa-config.php%2$s. You can change them by editing this file.', 'qa-heatmap-analytics' ),
											'<code>',
											'</code>'
										);
										echo wp_kses( $text, array( 'code' => array() ) );
										?>
									<br>
									<a href="<?php echo esc_url( 'https://docs.quarka.org/docs/user-manual/getting-started/configure-qa-config/' ); ?>" target="_blank" rel="noopener" class="button button-link">
										<?php esc_html_e('How to configure qa-config.php (Documentation)', 'qa-heatmap-analytics'); ?>
									</a>
									</p>
								</div>

				                <?php $this->create_footer_follow(); ?>

							</div>
						</div>
					<?php } ?>

<?php
/** ----------------------------
 * "Goal"
 */
?>
					<div class="qahm-config__tab-content" id="tab_goal_content">
                        <div class="qahm-config__tab-content-description">
                            <p><?php esc_html_e( 'Goals help you monitor your website’s success.', 'qa-heatmap-analytics' ); ?><br>
							<?php if ( QAHM_TYPE === QAHM_TYPE_WP ) { ?>
                            <strong><?php esc_html_e( 'Basic goal metrics appear in Audience. For the full Goals report, enable Advanced Mode.', 'qa-heatmap-analytics' ); ?></strong> — <?php esc_html_e( 'they will appear across various reports, including the detailed “Goals” report.', 'qa-heatmap-analytics' ); ?><br>
                            <?php } ?>
							<?php esc_html_e( 'You can update your goals at any time. This will not affect the collected data.', 'qa-heatmap-analytics' ); ?></p>
                             
                            <div id="step2">

                            <?php
                            $gtype_iframe_display = array_fill(1, $GOALMAX, 'display: none');
                            for ($iii = 1; $iii <= $GOALMAX; $iii++) {
                                $gtitle = isset($goals_ary[$iii]['gtitle']) ? esc_html(urldecode($goals_ary[$iii]['gtitle'])) : '';
                                $gnum_scale = isset($goals_ary[$iii]['gnum_scale']) ? esc_attr(urldecode($goals_ary[$iii]['gnum_scale'])) : 0;
                                $gnum_value = isset($goals_ary[$iii]['gnum_value']) ? esc_attr(urldecode($goals_ary[$iii]['gnum_value'])) : 0;
                                $gtype = isset($goals_ary[$iii]['gtype']) ? esc_attr(urldecode($goals_ary[$iii]['gtype'])) : 'gtype_page';
                                $g_goalpage = isset($goals_ary[$iii]['g_goalpage']) ? esc_url(urldecode($goals_ary[$iii]['g_goalpage'])) : '';
                                $g_pagematch = isset($goals_ary[$iii]['g_pagematch']) ? esc_attr(urldecode($goals_ary[$iii]['g_pagematch'])) : '';
                                $g_clickpage = isset($goals_ary[$iii]['g_clickpage']) ? esc_url(urldecode($goals_ary[$iii]['g_clickpage'])) : '';
                                $g_eventtype = isset($goals_ary[$iii]['g_eventtype']) ? esc_attr(urldecode($goals_ary[$iii]['g_eventtype'])) : '';
                                $g_clickselector = isset($goals_ary[$iii]['g_clickselector']) ? esc_attr(urldecode($goals_ary[$iii]['g_clickselector'])) : '';
                                $g_eventselector = isset($goals_ary[$iii]['g_eventselector']) ? esc_attr(urldecode($goals_ary[$iii]['g_eventselector'])) : '';

                                $gtype_checked = array_fill(0, 3, '');
                                $gtype_required = array_fill(0, 3, '');
                                $pagematch_checked = array_fill(0, 2, '');
                                //$gtype_display = array_fill(0, 3, 'style="display: none"');
                                $gtype_display = array_fill(0, 3, 'display: none');

                                if (!$g_clickpage) {
                                    $g_clickpage = esc_url($click_iframe_url);
                                }

                                switch ($gtype) {
                                    case 'gtype_click':
                                        $gtype_checked[1] = 'checked';
                                        $gtype_required[1] = 'required';
                                        $gtype_iframe_display[$iii] = '';
                                        $gtype_display[1] = '';
                                        break;
                                    case 'gtype_event':
                                        $gtype_checked[2] = 'checked';
                                        $gtype_required[2] = 'required';
                                        $gtype_display[2] = '';
                                        break;
                                    default:
                                    case 'gtype_page':
                                        $gtype_checked[0] = 'checked';
                                        $gtype_required[0] = 'required';
                                        $gtype_display[0] = '';
                                        break;
                                }

                                switch ($g_pagematch) {
                                    case 'pagematch_prefix':
                                        $pagematch_checked[1] = 'checked';
                                        break;
                                    default:
                                    case 'pagematch_complete':
                                        $pagematch_checked[0] = 'checked';
                                        break;
                                }
                            ?>
                            <div class="qahm-config__goal-box" id="<?php echo esc_attr( 'g'.$iii.'_goalbox' ); ?>">
                                <h3><?php echo esc_html( $goal_noun . $iii ); ?></h3>
                                <form id="<?php echo esc_attr( 'g'.$iii.'_form' ); ?>" onsubmit="saveChanges(this);return false">
                                <table>
                                    <colgroup>
                                        <col style="width: 15%">
                                        <col style="width: 65%">
                                        <col style="width: 20%">
                                    </colgroup>
                                    <tbody>
                                    <tr>
                                        <td><?php echo esc_html( $goal_title ); ?><span class="el_attention">*</span></td>
                                        <td><input type="text" name="<?php echo esc_attr( 'g'.$iii.'_title' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_title' ); ?>" required value="<?php echo esc_attr( $gtitle ); ?>" size="30"></td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html( $goal_number ); ?></td>
                                        <td><input type="number" name="<?php echo esc_attr( 'g'.$iii.'_num' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_num' ); ?>" value="<?php echo esc_attr( $gnum_scale ); ?>" onchange="calcSales(this)"><?php echo esc_html( $num_scale ); ?></td>
                                        <td>&nbsp;&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html( $goal_value ); ?></td>
                                        <td><input type="number" name="<?php echo esc_attr( 'g'.$iii.'_val' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_val' ); ?>" value="<?php echo esc_attr( $gnum_value ); ?>" onchange="calcSales(this)"><?php echo esc_html( $val_scale ); ?>&nbsp;<p class="right"><?php echo esc_html( $goal_sales ); ?> = <span id="<?php echo esc_attr( 'g'.$iii.'_calcsales' ); ?>">0</span> <?php echo esc_html( $val_scale ); ?></p></td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html( $goal_type ); ?><span class="el_attention">*</span>&nbsp;<span class="el_loading">Loading<span></span></span></td>
                                        <td class="td_gtype_save" style="opacity: 0">
                                            <input type="radio" name="<?php echo esc_attr( 'g'.$iii.'_type' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_type_page' ); ?>" value="gtype_page" <?php echo esc_attr( $gtype_checked[0] ); ?>><label for="<?php echo esc_attr( 'g'.$iii.'_type_page' ); ?>"><?php echo esc_html( $goal_type_page ); ?></label>
                                            <input type="radio" name="<?php echo esc_attr( 'g'.$iii.'_type' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_type_click' ); ?>" value="gtype_click" <?php echo esc_attr( $gtype_checked[1] ); ?>><label for="<?php echo esc_attr( 'g'.$iii.'_type_click' ); ?>"><?php echo esc_html( $goal_type_click ); ?></label>&nbsp;
                                            <span style="display:none;"><input type="radio" name="<?php echo esc_attr( 'g'.$iii.'_type' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_type_event' ); ?>" value="gtype_event" <?php echo esc_attr( $gtype_checked[2] ); ?>><label for="<?php echo esc_attr( 'g'.$iii.'_type_event' ); ?>"><?php echo esc_html( $goal_type_event ); ?></label></span>&nbsp;
                                            <br>
                                            <div id="<?php echo esc_attr( 'g'.$iii.'_page_goal' ); ?>" style="<?php echo esc_attr( $gtype_display[0] ); ?>" class="qahm-config__goal-type-box">
                                                <label><?php echo esc_html( $goal_page ); ?></label><br>
                                                <input type="radio" name="<?php echo esc_attr( 'g'.$iii.'_pagematch' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_pagematch_prefix' ); ?>" value="pagematch_prefix" <?php echo esc_attr( $pagematch_checked[1] ); ?>><label for="<?php echo esc_attr( 'g'.$iii.'_pagematch_prefix' ); ?>"><?php echo esc_html( $pagematch_prefix ); ?></label>
                                                <input type="radio" name="<?php echo esc_attr( 'g'.$iii.'_pagematch' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_pagematch_complete' ); ?>" value="pagematch_complete" <?php echo esc_attr( $pagematch_checked[0] ); ?>><label for="<?php echo esc_attr( 'g'.$iii.'_pagematch_complete' ); ?>"><?php echo esc_html( $pagematch_complete ); ?></label><br>
                                                <input type="text" name="<?php echo esc_attr( 'g'.$iii.'_goalpage' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_goalpage' ); ?>" value="<?php echo esc_attr( $g_goalpage ); ?>" <?php echo esc_attr( $gtype_required[0] ); ?> size="60">
                                                &nbsp;
                                            </div>
                                            <div id="<?php echo esc_attr( 'g'.$iii.'_click_goal' ); ?>" style="<?php echo esc_attr( $gtype_display[1] ); ?>" class="qahm-config__goal-type-box">
                                                <label><?php echo esc_html( $click_page ); ?></label><input type="text" name="<?php echo esc_attr('g'.$iii.'_clickpage'); ?>" id="<?php echo esc_attr('g'.$iii.'_clickpage'); ?>" value="<?php echo esc_url($g_clickpage); ?>" <?php echo esc_attr($gtype_required[1]); ?> placeholder="<?php echo esc_url($click_iframe_url); ?>" size="40">

                                                <button id="<?php echo esc_attr( 'g'.$iii.'_click_pageload' ); ?>" class="button button-secondary" type="button"><?php echo esc_html( $click_sel_load ); ?></button><br>
                                                <label><?php echo esc_html( $clickselector ); ?></label><br><input type="text" name="<?php echo esc_attr( 'g'.$iii.'_clickselector' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_clickselector' ); ?>" disabled value="<?php echo esc_attr( $g_clickselector ); ?>" <?php echo esc_attr( $gtype_required[1] ); ?> size="60">
                                                <div id="<?php echo esc_attr( 'g'.$iii.'_event-iframe-tooltip-right' ); ?>" class="qahm-config__event-tooltip--right"><?php echo esc_html( $click_sel_set ); ?></div>
                                            </div>
                                            <div id="<?php echo esc_attr( 'g'.$iii.'_event_goal' ); ?>" style="<?php echo esc_attr( $gtype_display[2] ); ?>;  display:none;" class="qahm-config__goal-type-box">
                                                <label><?php echo esc_html( $eventtype ); ?></label><select name="<?php echo esc_attr( 'g'.$iii.'_eventtype' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_eventtype' ); ?>"><option value="onclick"><?php echo esc_html( $event_click ); ?></option></select> <br><br>
                                                <label><?php echo esc_html( $eventselector ); ?></label><br><input type="text" name="<?php echo esc_attr( 'g'.$iii.'_eventselector' ); ?>" id="<?php echo esc_attr( 'g'.$iii.'_eventselector' ); ?>" value="<?php echo esc_attr( $g_eventselector ); ?>" <?php echo esc_attr( $gtype_required[2] ); ?> size="80">
                                                <div style="background-color: #eee; padding: 0 10px;"><p><?php echo esc_html( $example ); ?><br>/.*ad-link.*/<br>/\/my-goal-link\//</p></div>
                                            </div>
                                        </td>
                                        <td class="td_gtype_save" style="opacity: 0"><input type="submit" name="submit" id="<?php echo esc_attr( 'g'.$iii.'_submit' ); ?>" value="<?php echo esc_html( $savegoal ); ?>" class="button button-primary"><p class="el_right"><a href="#<?php echo esc_attr( 'g'.$iii.'_goalbox' ); ?>" onclick="deleteGoalX(<?php echo esc_attr( $iii ); ?>)"><?php echo esc_html( $unset_goal ); ?></a></p></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div id="<?php echo esc_attr( 'g'.$iii.'_event-iframe-containar' ); ?>" class="qahm-config__event-iframe-container" style="<?php echo esc_attr( $gtype_iframe_display[$iii] ); ?>">
                                    <iframe id="<?php echo esc_attr( 'g'.$iii.'_event-iframe' ); ?>" class="event-iframe" src="" frameborder="0" width="1200" height="400" scrolling="yes"></iframe>
                                </div>
                                </form>
                            </div>

                            <?php
									// ※メモ
									//  開発環境等セキュリティの高いページをiframeで参照させた場合、g_clickpageでエラーが発生する可能性がある。そのためにsrcを空白にする暫定措置を施した
									// 	<iframe id="g{$iii}_event-iframe" class="event-iframe" src="{$g_clickpage}" frameborder="0" width="1200" height="400" scrolling="yes"></iframe>
                            }  //end for
                            ?>


                            </div>
                        </div>
                    </div><!-- endof #tab_goal_content -->



<?php
/** --------------------------------
 * "Site Profile"
 */
?>
						<?php if ( false ) : // 非表示 ?>
						<div class="qahm-config__tab-content" id="tab_site_attr_content">
							<form id="siteinfo_form" onsubmit="siteinfoChanges(this);return false">

								<h3><?php echo esc_html( __( 'Which type of users do you want meet the goal?', 'qa-heatmap-analytics' ) ); ?></h3>
								<?php
								$target_options = [
									'target_individual'   => __( 'Personal', 'qa-heatmap-analytics' ),
									'target_corporation'  => __( 'Corporations/Organizations', 'qa-heatmap-analytics' ),
								];

								foreach ( $target_options as $key => $label ) {
								?>
									<input type="radio"
										name="target_customer"
										id="<?php echo esc_attr( $key ); ?>"
										value="<?php echo esc_attr( $key ); ?>"
										<?php checked( $siteinfo_ary['target_customer'] ?? '', $key ); ?> />

									<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
								<?php } ?>

								<h3><?php echo esc_html( __( 'Choose a category that describes your site best.', 'qa-heatmap-analytics' ) ); ?></h3>
								<table>
									<thead>
										<tr>
											<th><?php echo esc_html( __( 'General', 'qa-heatmap-analytics' ) ); ?></th>
											<th><?php echo esc_html( __( 'Media', 'qa-heatmap-analytics' ) ); ?></th>
											<th><?php echo esc_html( __( 'Providing services', 'qa-heatmap-analytics' ) ); ?></th>
											<th><?php echo esc_html( __( 'EC/Mall', 'qa-heatmap-analytics' ) ); ?></th>
										</tr>
									</thead>
									<tbody>
										<tr>
										<?php
										$sitetype_options = [
											'general_company'   => __( 'About a company/services', 'qa-heatmap-analytics' ),
											'media_affiliate'   => __( 'Affiliate blogs/Media', 'qa-heatmap-analytics' ),
											'service_matching'  => __( 'Matching', 'qa-heatmap-analytics' ),
											'ec_ec'             => __( 'Product sales', 'qa-heatmap-analytics' ),
											'general_shop'      => __( 'About stores/facilities', 'qa-heatmap-analytics' ),
											'media_owned'       => __( 'Owned media', 'qa-heatmap-analytics' ),
											'service_ugc'       => __( 'Posting', 'qa-heatmap-analytics' ),
											'ec_contents'       => __( 'Online content sales', 'qa-heatmap-analytics' ),
											'general_ir'        => __( 'IR', 'qa-heatmap-analytics' ),
											'media_other'       => __( 'Other information dissemination', 'qa-heatmap-analytics' ),
											'service_membershi' => __( 'SNS/Member services', 'qa-heatmap-analytics' ),
											'ec_license'        => __( 'License sales', 'qa-heatmap-analytics' ),
											'general_recruit'   => __( 'Recruitment', 'qa-heatmap-analytics' ),
											'service_other'     => __( 'Other services', 'qa-heatmap-analytics' ),
											'ec_other'          => __( 'Other sales', 'qa-heatmap-analytics' ),
										];

										foreach ( $sitetype_options as $key => $label ) :
										?>
											<td>
												<input type="radio" 
													name="sitetype" 
													id="<?php echo esc_attr( $key ); ?>"
													value="<?php echo esc_attr( $key ); ?>"
													<?php checked( $siteinfo_ary['sitetype'] ?? '', $key ); ?> />
												<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
											</td>
											<?php
											$nowtd = array_search( $key, array_keys( $sitetype_options ), true ) + 1;
											if ( $nowtd === 14 ) {
												echo '<td>&nbsp;</td>' . PHP_EOL;
											}
											if ( $nowtd >= 14 ) {
												$nowtd++;
											}
											if ( $nowtd % 4 === 0 ) {
												echo '</tr>' . PHP_EOL;
												if ( $nowtd !== 16 ) {
													echo '<tr>' . PHP_EOL;
												}
											}
										endforeach;
										?>
									</tbody>
								</table>

								<h3><?php echo esc_html( __( 'Does the site have "member registration"?', 'qa-heatmap-analytics' ) ); ?></h3>
								<?php
								$membership_options = [
									'membership_no'  => __( 'No.', 'qa-heatmap-analytics' ),
									'membership_yes' => __( 'Yes.', 'qa-heatmap-analytics' ),
								];
								foreach ( $membership_options as $key => $label ) :
								?>
									<input type="radio" 
										name="membership" 
										id="<?php echo esc_attr( $key ); ?>"
										value="<?php echo esc_attr( $key ); ?>"
										<?php checked( $siteinfo_ary['membership'] ?? '', $key ); ?> />
									<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
								<?php endforeach; ?>

								<h3><?php echo esc_html( __( 'Does the site have "payment function"?', 'qa-heatmap-analytics' ) ); ?></h3>
								<?php
								$payment_options = [
									'payment_no'   => __( 'No.', 'qa-heatmap-analytics' ),
									'payment_yes'  => __( 'Yes, using original system.', 'qa-heatmap-analytics' ),
									'payment_cart' => __( 'Using external cart system.', 'qa-heatmap-analytics' ),
								];
								foreach ( $payment_options as $key => $label ) :
								?>
									<input type="radio" 
										name="payment" 
										id="<?php echo esc_attr( $key ); ?>"
										value="<?php echo esc_attr( $key ); ?>"
										<?php checked( $siteinfo_ary['payment'] ?? '', $key ); ?> />
									<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
								<?php endforeach; ?>

								<?php
								$month_later   = isset( $siteinfo_ary['month_later'] ) ? $siteinfo_ary['month_later'] : '';
								$session_goal  = isset( $siteinfo_ary['session_goal'] ) ? $siteinfo_ary['session_goal'] : '';
								?>
								<h3><?php echo esc_html( __( 'Enter the target number for monthly sessions.', 'qa-heatmap-analytics' ) ); ?></h3>
								<input type="number" name="month_later" id="month_later" value="<?php echo esc_attr( $month_later ); ?>" />
								<label for="month_later"><?php echo esc_html( __( 'month(s) later, reaching', 'qa-heatmap-analytics' ) ); ?></label>&nbsp;
								<input type="number" name="session_goal" id="session_goal" value="<?php echo esc_attr( $session_goal ); ?>" />
								<label for="session_goal"><?php echo esc_html( __( 'sessions/month is the goal.', 'qa-heatmap-analytics' ) ); ?></label>

								<p>
									<input type="submit" value="<?php echo esc_attr( __( 'Save', 'qa-heatmap-analytics' ) ); ?>" />
								</p>
							</form>
						</div><!-- endof #tab_site_attr_content -->
						<?php endif; // end if false ?>



<?php
/** --------------------------------
 * "Google API"
 */
?>
						<div class="qahm-config__tab-content" id="tab_google_content">
							<?php if( QAHM_TYPE === QAHM_TYPE_WP ) { ?>
								<p><em>Coming soon</em></p>
								<p>Google Integration will be available in a future release.</p>
							
							<?php } else { ?>
							<h1><?php echo esc_html__( 'Connect with Google API', 'qa-heatmap-analytics' ); ?></h1>
							<p>
								<?php echo esc_html( __( 'API integration with Google allows you to retrieve data from Google Search Console and Google Analytics.', 'qa-heatmap-analytics' ) ); ?>
								<span class="qahm_hatena-mark"><i class="far fa-question-circle"></i></span>
								<a href="https://mem.quarka.org/manual/connect-to-gsc/" target="_blank" rel="noopener"><?php echo esc_html( __( 'How to connect with API', 'qa-heatmap-analytics' ) ); ?><span class="qahm_link-mark"><i class="fas fa-external-link-alt"></i></span></a>
							</p>
							<form method="post" action="">
								<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME, false ); ?>
								<input type="hidden" name="form_type" value="save_google_credentials">

								<table class="form-table">
									<tbody>
										<tr>
											<th scope="row">
												<label for="client_id">
													<?php echo esc_html( __( 'Client ID', 'qa-heatmap-analytics' ) ); ?>
												</label>
											</th>
											<td>
												<input name="client_id" type="text" id="client_id" value="<?php echo esc_attr( $qahm_google_api->get_client_id() ); ?>" class="regular-text"<?php echo esc_attr($form_google_disabled); ?>>
											</td>
										</tr>

										<tr>
											<th scope="row">
												<label for="client_secret">
													<?php echo esc_html( __( 'Client Secret', 'qa-heatmap-analytics' ) ); ?>
												</label>
											</th>
											<td>
												<input name="client_secret" type="text" id="client_secret" value="<?php echo esc_attr( $qahm_google_api->get_client_secret() ); ?>" class="regular-text"<?php echo esc_attr($form_google_disabled); ?>>
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
						   <?php } ?>
						</div><!-- endof #tab_google_content -->
			</div>
		</div>
	</div>


<?php
	}

	private function is_redirect() {
		// tracking_id is used only for display switching (no state changes). wp_unslash() and sanitize_text_field() are applied inside sanitize_tracking_id().
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		$tracking_id_raw = isset( $_GET['tracking_id'] ) ? $this->sanitize_tracking_id( wp_unslash( $_GET['tracking_id'] ) ) : 'all';
		$tracking_id     = $this->get_safe_tracking_id( $tracking_id_raw );
		if ( $this->wrap_get_option( 'google_is_redirect' ) && $tracking_id === '' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 設定画面の項目をデータベースに保存する
	 */
	public function ajax_save_plugin_config() {
		if (!check_ajax_referer('qahm-config-nonce-action-qahm-options', 'security', false)) {
			wp_send_json_error('Invalid nonce');
			wp_die();
		}
		/*
		if (!$this->check_qahm_access_cap('qahm_manage_settings')) {
			wp_send_json_error('Permission denied');
			wp_die();
		}
		*/

		$advanced_mode = $this->wrap_filter_input(INPUT_POST, 'advanced_mode');
		if ($advanced_mode === 'true') {
			$advanced_mode = true;
		} else {
			$advanced_mode = false;
		}
		$cb_sup_mode = $this->wrap_filter_input(INPUT_POST, 'cb_sup_mode');
		if ($cb_sup_mode === 'true') {
			$cb_sup_mode = 'yes';
		} else {
			$cb_sup_mode = 'no';
		}

		$this->wrap_update_option('advanced_mode', $advanced_mode);
		$this->wrap_update_option('cb_sup_mode', $cb_sup_mode);
		
		wp_send_json_success();
	}
} // end of class
