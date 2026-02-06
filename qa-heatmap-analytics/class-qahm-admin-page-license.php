<?php
/**
 * 
 *
 * @package qa_heatmap
 */

$qahm_admin_page_license = new QAHM_Admin_Page_License();

class QAHM_Admin_Page_License extends QAHM_Admin_Page_Base {

	// スラッグ
	const SLUG = QAHM_NAME . '-license';

	// nonce
	const NONCE_ACTION = self::SLUG . '-nonce-action';
	const NONCE_NAME   = self::SLUG . '-nonce-name';

	private static $error_msg = array();

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_init', array( $this, 'save_config' ) );
		$this->regist_ajax_func( 'ajax_clear_license_message' );
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

		// enqueue_style
		$this->common_enqueue_style();
	
		// enqueue script
		$this->common_enqueue_script();
		wp_enqueue_script( QAHM_NAME . '-admin-page-license', plugins_url( 'js/admin-page-license.js', __FILE__ ), array( QAHM_NAME . '-effect' ), QAHM_PLUGIN_VERSION, false );
	
		// inline script
		$scripts = $this->get_common_inline_script();
		// 紙吹雪エフェクト。エフェクト実行後はajaxでupdateして紙吹雪フラグをオフにするのが適切だが、そこにかける時間ももったいないのでここでupdateしている
		$msg_ary = $this->wrap_get_option( 'license_message' );
		if ( ! empty( $msg_ary[0]['confetti'] ) ) {
			$scripts['license_confetti'] = $msg_ary[0]['confetti'];
			unset( $msg_ary[0]['confetti'] );
			$this->wrap_update_option( 'license_message', $msg_ary );
		}
		wp_add_inline_script( QAHM_NAME . '-common', 'var ' . QAHM_NAME . ' = ' . QAHM_NAME . ' || {}; let ' . QAHM_NAME . 'Obj = ' . $this->wrap_json_encode( $scripts ) . '; ' . QAHM_NAME . ' = Object.assign( ' . QAHM_NAME . ', ' . QAHM_NAME . 'Obj );', 'before' );

		// localize
		$localize = $this->get_common_localize_script();
		$localize['powerup_title'] = esc_html__( 'Success!', 'qa-heatmap-analytics' );
		$localize['powerup_text']  = esc_html__( 'Your license is now active and authenticated.', 'qa-heatmap-analytics' );
		wp_localize_script( QAHM_NAME . '-common', QAHM_NAME . 'l10n', $localize );
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

		global $qahm_time;
		$lic_authorized = $this->lic_authorized();
		$key       = $this->wrap_get_option( 'license_key' );
		$id        = $this->wrap_get_option( 'license_id' );
		$act_time  = $this->wrap_get_option( 'license_activate_time' );
		$act_time  = $qahm_time->unixtime_to_str( $act_time );
		$input_read = '';
		if ( $lic_authorized ) {
			$input_read = ' readonly';
		}
		?>

		<div id="<?php echo esc_attr( basename( __FILE__, '.php' ) ); ?>" class="qahm-admin-page" style="padding: 18px">
			<div class="wrap">
				<h1><?php esc_html_e( 'License Activation', 'qa-heatmap-analytics' ); ?></h1>

				<p><em>This current version does not require a license.</em><br>
				Paid plans that require license activation will be available in the future.</p>
			</div>
		</div>
		<?php

		// ライセンスページで1度のみ表示するメッセージであればこの段階で非表示設定にする
		$msg_ary = $this->wrap_get_option( 'license_message' );
		if ( $msg_ary ) {
			foreach ( $msg_ary as &$msg ) {
				if ( $msg['view'] === QAHM_License::MESSAGE_VIEW['license'] ) {
					$msg['view'] = QAHM_License::MESSAGE_VIEW['hidden'];
				}
			}
			$this->wrap_update_option( 'license_message', $msg_ary );
		}

		$this->create_footer_follow();
	}

	/**
	 * 設定画面の項目をデータベースに保存する
	 */
	public function save_config() {
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			$creds = false;

			$access_type = get_filesystem_method();
			if ( $access_type === 'ftpext' ) {
				$creds = request_filesystem_credentials( '', '', false, false, null );
			}
			if ( ! WP_Filesystem( $creds ) ) {
				throw new Exception( 'WP_Filesystem Invalid Credentials' );
			}
		}

		$post = array();
		// nonceで設定したcredentialのチェック
		if ( isset( $_POST[ self::NONCE_NAME ] ) ) {			
			// nonceチェック
			check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME ); // 失敗時は内部で停止するので分岐不要

			global $qahm_license;
			
			$cmd = $this->wrap_filter_input( INPUT_POST, 'license_cmd' );
			$key = $this->wrap_filter_input( INPUT_POST, 'license_key' );
			$id  = $this->wrap_filter_input( INPUT_POST, 'license_id' );
			$url = $this->wrap_filter_input( INPUT_POST, 'license_url' );

			// フォームの値を更新
			$this->wrap_update_option( 'license_key', $key );
			$this->wrap_update_option( 'license_id', $id );

			if ( 'check' === $cmd ) {
				// アクティベート
				$res = $qahm_license->activate( $key, $id, QAHM_License::MESSAGE_VIEW['license'], $url );

			} elseif ( 'deactivate' === $cmd ) {
				// ディアクティベート
				$res = $qahm_license->deactivate( $key, $id,QAHM_License::MESSAGE_VIEW['license'], $url );
			}
			
		}
	}

	/**
	* ライセンスメッセージのクリア
	*/
	public function ajax_clear_license_message() {
		$no  = (int) $this->wrap_filter_input( INPUT_POST, 'no' );

		$msg_ary  = $this->wrap_get_option( 'license_message' );
		if ( ! $msg_ary ) {
			die();
		}

		foreach ( $msg_ary as &$msg ) {
			if( $msg['no'] === $no ) {
				$msg['view'] = QAHM_License::MESSAGE_VIEW['hidden'];
			}
		}
		
		$this->wrap_update_option( 'license_message', $msg_ary );
		die();
	}
} // end of class
