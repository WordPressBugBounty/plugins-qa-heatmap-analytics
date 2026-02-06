<?php
/**
 * 
 *
 * @package qa_heatmap
 */

$qahm_admin_page_help = new QAHM_Admin_Page_Help();

class QAHM_Admin_Page_Help extends QAHM_Admin_Page_Dataviewer {

	// スラッグ
	const SLUG = QAHM_NAME . '-help';

	// nonce
	const NONCE_ACTION = self::SLUG . '-nonce-action';
	const NONCE_NAME   = self::SLUG . '-nonce-name';

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		parent::__construct();
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

		// enqueue_style
		$this->common_enqueue_style();
		wp_enqueue_style( QAHM_NAME . '-admin-page-help-css', $css_dir_url . 'admin-page-help.css', array( QAHM_NAME . '-reset' ), QAHM_PLUGIN_VERSION );	


		// enqueue script
		$this->common_enqueue_script();
	
		// inline script
		$this->regist_inline_script();

		// localize
		$this->regist_localize_script();
	}

	/**
	 * ページの表示
	 */
	public function create_html() {
		$lang_set = get_bloginfo('language');
		$php_version = phpversion();
		$php_memory_limit = ini_get( 'memory_limit' );
		$php_max_execution_time = ini_get( 'max_execution_time' ); 
		global $wp_version;
		?>

		<div id="<?php echo esc_attr( basename( __FILE__, '.php' ) ); ?>" class="qahm-admin-page">
			<div class="qa-zero-content">
                <?php $this->create_header( esc_html__( 'Help', 'qa-heatmap-analytics' ) ); ?>

                <div id="qa-help">
                    <div class="help01">
                        <h2><?php esc_html_e( 'Site and Environment Info', 'qa-heatmap-analytics' ); ?></h2>
                        <p class="note-version"><?php esc_html_e( 'Installed Version', 'qa-heatmap-analytics' ); ?>: <?php echo esc_html( QAHM_PLUGIN_VERSION ); ?></p>
                        <table>
                            <thead>
                                <tr>
                                    <th></th>
                                    <td class="yours"><?php esc_html_e( 'Your Site', 'qa-heatmap-analytics' ); ?></td><td class="qas">/ <?php esc_html_e( 'QA Supported Environment', 'qa-heatmap-analytics' ); ?></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th><?php esc_html_e( 'WordPress version', 'qa-heatmap-analytics' ); ?></th>
                                    <td class="yours"><?php echo esc_html($wp_version); ?></td><td class="qas">/ <?php /* translators: placeholders represent the supported version number */ printf( esc_html__( '%s or higher', 'qa-heatmap-analytics' ), '6.0' ); ?></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'PHP version', 'qa-heatmap-analytics' ); ?></th>
                                    <td class="yours"><?php echo esc_html($php_version); ?></td><td class="qas">/ <?php /* translators: placeholders represent the supported version number */  printf( esc_html__( '%s or higher', 'qa-heatmap-analytics' ), '7.0' ); ?></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'PHP memory limit', 'qa-heatmap-analytics' ); ?></th>
                                    <td class="yours"><?php echo esc_html($php_memory_limit); ?></td><td class="qas">/ <?php esc_html_e( '1G+(1024M+) recommended', 'qa-heatmap-analytics' ); ?></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'PHP max execution time', 'qa-heatmap-analytics' ); ?></th>
                                    <td class="yours"><?php echo esc_html($php_max_execution_time); ?></td><td class="qas">/ <?php esc_html_e( '240 seconds recommended', 'qa-heatmap-analytics' ); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    
                    <h2><?php esc_html_e( 'Help', 'qa-heatmap-analytics' ); ?></h2>
                    <div class="h-frame help03">
                        <h3><a href="<?php echo esc_url( QAHM_DOCUMENTATION_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Documentation', 'qa-heatmap-analytics' ); ?></a></h3>					
                    </div>

                    <div class="h-frame help03">
                        <h3><a href="https://wordpress.org/support/plugin/qa-heatmap-analytics/" target="_blank" rel="noopener"><?php esc_html_e( 'WordPress Support Forum', 'qa-heatmap-analytics' ); ?></a></h3>
                        <p style="font-size: 14px;"><?php esc_html_e( 'You\'ll often find helpful answers from our community members. Our support team is also around if you need further assistance.
We also welcome your feedback or any insights you\'d like to share.', 'qa-heatmap-analytics' ); ?></p>
                    </div>

                </div>

                <hr>
                <div class="bl_news flex_item">
                <?php $this->view_rss_feed(); ?>
                </div>
			</div><!-- End qa-zero-content -->
		</div>

		
		
		
		<!-- Start debug -->
		<?php
			global $wpdb;
		
			$my_theme     = wp_get_theme();
			$site_plugins = get_plugins();
			$plugin_names = [];
		
			foreach( $site_plugins as $main_file => $plugin_meta ) {
				if ( ! is_plugin_active( $main_file ) ) {
					continue;
				}
				$plugin_names[] = sanitize_text_field( $plugin_meta['Name'] . ' ' . $plugin_meta['Version'] );
			}
			
			$options = '';
			/*
			foreach ( QAHM_OPTIONS as $key => $value ) {
				$options .= '<p><strong>' . $key . ':</strong><br>' . $this->wrap_get_option( $key ) . '</p>';
			}
			*/
			$options .= '<p><strong>Plugin version:</strong><br>' . QAHM_PLUGIN_VERSION . '</p>';

			$cron_status = $this->wrap_get_contents( $this->get_data_dir_path() . 'cron_status' );
		?>
		<div id="qahm-help-debug">
			<h3>Debug</h3>
			<hr>
			<p><strong>WordPress Server IP address:</strong><br><?php echo esc_html( $this->wrap_filter_input( INPUT_SERVER, 'SERVER_ADDR' ) ); ?></p>
			<p><strong>PHP version:</strong><br><?php echo esc_html($php_version); ?></p>
			<p><strong>PHP memory limit:</strong><br><?php echo esc_html($php_memory_limit); ?></p>
			<p><strong>max_execution_time:</strong><br><?php echo esc_html($php_max_execution_time); ?></p>
			<p><strong>PHP extensions:</strong><br><?php echo esc_html( implode( ', ', get_loaded_extensions() ) ); ?></p>			
			<p><strong>Database version:</strong><br>
			<?php
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query is required to get the database version.
			echo esc_html( $wpdb->get_var( "SELECT VERSION();" ) ); 
			?></p>
			<p><strong>InnoDB availability:</strong><br>
			<?php
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query is required to get the InnoDB availability.
			echo esc_html( $wpdb->get_var( "SELECT SUPPORT FROM INFORMATION_SCHEMA.ENGINES WHERE ENGINE = 'InnoDB';" ) ); 
			?></p>
			<p><strong>WordPress version:</strong><br><?php echo esc_html($wp_version); ?></p>
			<p><strong>Multisite:</strong><br>
			<?php
			 $is_multisite = ( function_exists( 'is_multisite' ) && is_multisite() ) ? 'Yes' : 'No';
			 echo esc_html( $is_multisite ); 
			?>
			</p>
			<p><strong>Active plugins:</strong><br><?php echo esc_html( implode( ', ', $plugin_names ) ); ?></p>
			<p><strong>Theme:</strong><br>
			<?php
			 $theme =  $my_theme->get( 'Name' ) . ' (' . $my_theme->get('Version') . ') by ' . $my_theme->get('Author');
			 echo esc_html($theme);
			?>
			</p>
			<?php echo wp_kses_post($options); ?>
			<p><strong>qalog.txt:</strong><br><?php echo esc_url( $this->get_data_dir_url( 'log' ) . 'qalog.txt' ); ?></p>
			<p><strong>cron_status:</strong><br><?php echo esc_html($cron_status); ?></p>
		</div>
		<!-- End debug -->

		<?php
        $this->create_footer_follow();
	}
} // end of class
