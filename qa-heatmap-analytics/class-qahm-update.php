<?php
/**
 * 
 * @package qa_heatmap
 */

class QAHM_Update extends QAHM_File_Data {

	public function __construct() {
	}

	public function check_version() {
		global $qahm_license;
		global $qahm_db;
		global $qahm_data_api;
		global $qahm_log;
		global $wpdb;
		$ver = $this->wrap_get_option( 'plugin_version' );
		if ( $ver === QAHM_PLUGIN_VERSION ) {
			$this->delete_maintenance_file();
			return;
		}

		// Differs between ZERO and QA - Start ----------
		// プラグインタイプによってアップデート処理を分ける
		if ( QAHM_TYPE === QAHM_TYPE_ZERO ) {
            if( version_compare( '2.5.1.0', $ver, '>' ) ) {
                $this->mod_table_add_ip_to_country();
                $this->wrap_update_option( 'plugin_version', '2.5.1.0' );
            }

            if( version_compare( '2.5.2.0', $ver, '>' ) ) {
                $qahm_activate = new QAHM_Activate();
                $qahm_activate->setup_config_file();
                $this->wrap_update_option( 'plugin_version', '2.5.2.0' );
            }

        } elseif ( QAHM_TYPE === QAHM_TYPE_WP ) {
            if( version_compare( '1.0.5.0', $ver, '>' ) ) {
                $this->wrap_update_option( 'is_first_heatmap_setting', '' );
                $this->wrap_update_option( 'plugin_version', '1.0.5.0' );
            }

            if( version_compare( '1.0.8.0', $ver, '>' ) ) {
                $this->wrap_update_option( 'heatmap_measure_max', 1 );
                $this->wrap_update_option( 'campaign_oneyear_popup', false );
                $this->wrap_update_option( 'plugin_version', '1.0.8.0' );
            }

            if( version_compare( '1.1.0.0', $ver, '>' ) ) {
                $this->wrap_update_option( 'data_save_month', 2 );
                $this->wrap_update_option( 'plugin_version', '1.1.0.0' );
            }

            if( version_compare( '2.9.0.0', $ver, '>' ) ) {
                $plan = (int) $this->wrap_get_option( 'license_plan' );
                if ( 0 < $plan ) {
                    // ライセンス情報を新しい形式に一新するので、この時点で強制的にライセンス認証を行う
                    $key = $this->wrap_get_option( 'license_key' );
                    $id  = $this->wrap_get_option( 'license_id' );
                    $qahm_license->activate( $key, $id );
                }
                $this->wrap_update_option( 'plugin_version', '2.9.0.0' );
            }

            if( version_compare( '3.3.0.0', $ver, '>' ) ) {
                $qahm_sql_table = new QAHM_Database_Creator();
                $check_exists   = -123454321;
                $ver = $this->wrap_get_option( 'qa_gsc_query_log_version', $check_exists );
                if ( $ver === $check_exists ) {
                    
                    $url = get_site_url();
                    $parse_url = wp_parse_url($url);
                    $domain_url = $this->to_domain_url($parse_url);
                    $tracking_id = $this->get_tracking_id($domain_url);
                    
                    $query = $qahm_sql_table->get_qa_gsc_query_log_create_table( $tracking_id );
                    if ( $query ) {
                        // queryのコメント、先頭末尾のスペースやTAB等を削除
                        $query_ary = explode( PHP_EOL, $query );
                        for ( $query_idx = 0, $query_max = $this->wrap_count( $query_ary ); $query_idx < $query_max; $query_idx++ ) {
                            $query_ary[$query_idx] = trim( $query_ary[$query_idx], " \t" );
                            if ( $this->wrap_substr( $query_ary[$query_idx], 0, 2 ) === '--' ) {
                                unset( $query_ary[$query_idx] );
                            }
                        }
                        $query = implode( '', $query_ary );

                        // クエリ実行
                        $query_ary = explode( ';', $query );
                        for ( $query_idx = 0, $query_max = $this->wrap_count( $query_ary ); $query_idx < $query_max; $query_idx++ ) {
                            if ( $query_ary[$query_idx] ) {
                                $qahm_db->query( $query_ary[$query_idx] );
                            }
                        }
                        $this->wrap_put_contents( 'qa_gsc_query_log_version', QAHM_DB_OPTIONS['qa_gsc_query_log_version'] );
                    }
                }

                $this->wrap_update_option( 'google_credentials', '' );
                $this->wrap_update_option( 'google_is_redirect', false );
                $this->wrap_update_option( 'plugin_version', '3.3.0.0' );
            }


            if( version_compare( '3.9.9.0', $ver, '>' ) ) {
                $this->wrap_update_option( 'cb_sup_mode', 'no' );
                $this->wrap_update_option( 'pv_limit_rate', 0 );
                $this->wrap_update_option( 'data_retention_dur', 90 );
                $this->wrap_update_option( 'license_option', null );
                $this->wrap_update_option( 'plugin_first_launch', false );
                $this->wrap_update_option( 'pv_limit_rate', 0 );
                $this->wrap_update_option( 'pv_warning_mail_month', null );
                $this->wrap_update_option( 'pv_over_mail_month', null );
                $this->wrap_update_option( 'plugin_version', '3.9.9.0' );
            }

            if( version_compare( '3.9.9.1', $ver, '>' ) ) {
                $this->wrap_update_option( 'send_email_address', get_option( 'admin_email' ) );
                $this->wrap_update_option( 'plugin_version', '3.9.9.1' );
            }

            if( version_compare( '3.9.9.3', $ver, '>' ) ) {
                $this->wrap_update_option( 'anontrack', 0 );
                $this->wrap_update_option( 'cb_init_consent', 'yes' );
                $this->wrap_update_option( 'plugin_version', '3.9.9.3' );
            }

            if( version_compare( '4.0.1.0', $ver, '>' ) ) {
                $this->wrap_update_option( 'announce_friend_plan', true );
                $this->wrap_update_option( 'plugin_version', '4.0.1.0' );
            }
           
            if( version_compare( '4.7.0.0', $ver, '>' ) ) {
				// QAアナリティクスからQAアシスタントへ＝データが見られない通知を表示するフラッグ
				$timestamp = time();
				$unavailable_state_array = array( 'pending'   => true, 'timestamp' => (string)$timestamp );
                $this->wrap_update_option( 'v5_data_unavailable_state', $unavailable_state_array );
                // 旧QA専用ユーザーロールを削除（該当ユーザーは "No role for this site" になります）
                remove_role( 'qahm-manager' );
                remove_role( 'qahm-viewer' );

                require_once dirname( __FILE__ ) . '/class-qahm-analytics-migration.php';  
                $migration = new QAHM_Analytics_Migration();
                $migration->convert_qa_analytics();
                $this->wrap_update_option( 'plugin_version', '4.7.0.0' );
            }
			if( version_compare( '4.8.0.0', $ver, '>' ) ) {
				$this->wrap_update_option( 'data_retention_days', 30 );
				$this->wrap_update_option( 'cb_sup_mode', 'yes' );
				$this->wrap_update_option( 'send_email_address', get_option( 'admin_email' ) );
				$this->wrap_update_option( 'pv_limit_rate', 0 );
				$this->wrap_update_option( 'pv_warning_mail_month', null );
				$this->wrap_update_option( 'pv_over_mail_month', null );
				$this->wrap_update_option( 'advanced_mode', false );
                $this->wrap_update_option( 'plugin_version', '4.8.0.0' );
			}
            if( version_compare( '4.8.4.0', $ver, '>' ) ) {
                $this->mod_table_add_ip_to_country();
                $this->wrap_update_option( 'plugin_version', '4.8.4.0' );
            }

            if( version_compare( '4.9.5.0', $ver, '>' ) ) {
                $sitemanage = $this->wrap_get_option('sitemanage');
                if ($sitemanage && is_array($sitemanage)) {
                    foreach ($sitemanage as &$site) {
                        $site['anontrack'] = 1;
                    }
                    unset($site);
                    $this->wrap_update_option('sitemanage', $sitemanage);
                }
                
                $qahm_activate = new QAHM_Activate();
                $qahm_activate->setup_config_file();

                $this->wrap_update_option( 'plugin_version', '4.9.5.0' );
            }

            if( version_compare( '5.1.0.0', $ver, '>' ) ) {
                $data_dir = $this->get_data_dir_path();
                
                // 進捗完了ファイルの存在確認
                $version_hist_completed = $this->wrap_exists( $data_dir . 'cleanup_version_hist_completed.php' );
                
                // version_histクリーンアップ
                if ( ! $version_hist_completed ) {
                    $completed = $this->cleanup_version_hist_duplicates();
                    if ( ! $completed ) {
                        return;  // 未完了なら即座に終了
                    }
                    // 完了ファイルを作成
                    $this->wrap_put_contents( $data_dir . 'cleanup_version_hist_completed.php', 'completed' );
                }
                
                // クリーンアップ完了ファイルを削除
                $this->wrap_delete( $data_dir . 'cleanup_version_hist_completed.php' );
                $this->wrap_update_option( 'plugin_version', '5.1.0.0' );
            }
		}
		// Differs between ZERO and QA - End ----------

		// 最終的にプラグインバージョンを現行のものに変更
		$this->wrap_update_option( 'plugin_version', QAHM_PLUGIN_VERSION );

		// プラグインバージョンに合わせてBrainsファイル更新のため、ライセンス認証
		$lic_authoirzed = $this->lic_authorized();
		if ( $lic_authoirzed ) {
			$key = $this->wrap_get_option( 'license_key' );
			$id  = $this->wrap_get_option( 'license_id' );
			$qahm_license->activate( $key, $id );
		}

		// メンテナンスファイルを削除
		$this->delete_maintenance_file();

		$qahm_log->info( 'Update process has completed.' );
	}

	
	/**
	 * メンテナンスファイルの削除
	 */
	private function delete_maintenance_file() {
		$maintenance_path = $this->get_temp_dir_path() . 'maintenance.php';
		if ( $this->wrap_exists( $maintenance_path ) ) {
			$this->wrap_delete( $maintenance_path );
		}
	}
    /**
     * DBのカラム変更
     */
    private function mod_table_add_ip_to_country() {
        global $wpdb;
        global $qahm_db;
        global $qahm_log;

        // qa_readersテーブルにcountry_codeカラムを追加
        $table_exists = $qahm_db->get_var("SHOW TABLES LIKE '{$wpdb->prefix}qa_readers'");
        if ( $table_exists ) {
            $column_exists = $qahm_db->get_var("SHOW COLUMNS FROM {$wpdb->prefix}qa_readers LIKE 'country_code'");
            if ( !$column_exists ) {
                $alt_query = "ALTER TABLE {$wpdb->prefix}qa_readers ADD COLUMN country_code CHAR(2) DEFAULT NULL AFTER is_reject";
                $result = $qahm_db->query($alt_query);
                if ($result === false) {
                    $qahm_log->info("(During plugin update) SQL Error: {$wpdb->last_error} in query: {$wpdb->last_query}");
                } else {
                    $qahm_log->info("(During plugin update) Successfully added country_code column to qa_readers table");
                }

                $index_exists = $qahm_db->get_var("SHOW INDEX FROM {$wpdb->prefix}qa_readers WHERE Key_name = 'idx_readers_country'");
                if ( !$index_exists ) {
                    $index_query = "CREATE INDEX idx_readers_country ON {$wpdb->prefix}qa_readers(country_code)";
                    $result = $qahm_db->query($index_query);
                    if ($result === false) {
                        $qahm_log->info("(During plugin update) SQL Error: {$wpdb->last_error} in query: {$wpdb->last_query}");
                    } else {
                        $qahm_log->info("(During plugin update) Successfully created idx_readers_country index");
                    }
                }
            }
        }

    }

    /**
     * バージョン5.1.0.0: qa_page_version_hist重複レコード削除
     * 各page_id+device_idの組み合わせにつき、最古のversion_id（値が最小）のレコードのみを残し、他を削除する
     * base_htmlとbase_selectorが存在する古いレコードを保持するため、version_id ASCでソートし最小を残す
     */
    private function cleanup_version_hist_duplicates() {
        global $wpdb;
        global $qahm_log;
        
        $start_time = time();
        $max_execution_time = 25;
        $batch_size = 100;
        
        $data_dir = $this->get_data_dir_path();
        $progress_file = $data_dir . 'cleanup_version_hist_progress.php';
        
        if ( $this->wrap_exists( $progress_file ) ) {
            $progress = $this->wrap_unserialize( $this->wrap_get_contents( $progress_file ) );
        } else {
            $table_name = $wpdb->prefix . 'qa_page_version_hist';
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safely constructed using $wpdb->prefix. Direct database call is necessary for counting distinct page_ids. Caching would not provide benefits in this context. (important-comment)
            $total_pages = $wpdb->get_var(
                "SELECT COUNT(DISTINCT page_id) FROM {$table_name}"
            );
            
            $progress = array(
                'last_processed_page_id' => 0,
                'total_pages' => (int)$total_pages,
                'processed_pages' => 0,
                'deleted_records' => 0,
                'start_time' => $start_time,
                'error_count' => array()
            );
            
            $qahm_log->info( "qa_page_version_hist cleanup started. Total pages: {$total_pages}" );
        }
        
        $table_name = $wpdb->prefix . 'qa_page_version_hist';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safely constructed using $wpdb->prefix. Direct database call is necessary for retrieving distinct page_ids. Caching would not provide benefits in this context. (important-comment)
        $page_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT page_id 
                 FROM {$table_name} 
                 WHERE page_id > %d
                 ORDER BY page_id ASC 
                 LIMIT %d",
                $progress['last_processed_page_id'],
                $batch_size
            )
        );
        
        if ( empty( $page_ids ) ) {
            $this->wrap_delete( $progress_file );
            $qahm_log->info(
                "qa_page_version_hist cleanup completed. " .
                "Processed: {$progress['processed_pages']} pages, " .
                "Deleted: {$progress['deleted_records']} records"
            );
            return true;  // 完了
        }
        
        $device_ids = array(
            QAHM_DEVICES['desktop']['id'],
            QAHM_DEVICES['tablet']['id'],
            QAHM_DEVICES['smartphone']['id']
        );
        
        foreach ( $page_ids as $page_id ) {
            if ( time() - $start_time > $max_execution_time ) {
                $this->wrap_put_contents( $progress_file, $this->wrap_serialize( $progress ) );
                $qahm_log->info(
                    "qa_page_version_hist cleanup paused (timeout). " .
                    "Progress: {$progress['processed_pages']}/{$progress['total_pages']}"
                );
                return false;  // 未完了
            }
            
            if ( isset( $progress['error_count'][$page_id] ) && $progress['error_count'][$page_id] >= 3 ) {
                $qahm_log->warning( "Skipping page_id {$page_id} due to repeated errors" );
                $progress['last_processed_page_id'] = $page_id;
                $progress['processed_pages']++;
                continue;
            }
            
            foreach ( $device_ids as $device_id ) {
                // version_noごとに処理するため、まず存在するversion_noを取得
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safely constructed using $wpdb->prefix. Direct database call is necessary for retrieving distinct version numbers. Caching would not provide benefits in this context. (important-comment)
                $version_nos = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT DISTINCT version_no 
                         FROM {$table_name} 
                         WHERE page_id = %d AND device_id = %d
                         ORDER BY version_no ASC",
                        $page_id,
                        $device_id
                    )
                );
                
                if ( empty( $version_nos ) ) {
                    continue;
                }
                
                // 各version_noごとに重複チェック
                foreach ( $version_nos as $version_no ) {
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safely constructed using $wpdb->prefix. Direct database call is necessary for retrieving version records. Caching would not provide benefits in this context. (important-comment)
                    $versions = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT version_id, version_no, insert_datetime 
                             FROM {$table_name} 
                             WHERE page_id = %d AND device_id = %d AND version_no = %d
                             ORDER BY version_id ASC",
                            $page_id,
                            $device_id,
                            $version_no
                        ),
                        ARRAY_A
                    );
                    
                    if ( empty( $versions ) || $this->wrap_count( $versions ) <= 1 ) {
                        continue;
                    }
                    
                    $min_version_id = (int)$versions[0]['version_id'];
                    
                    $delete_version_ids = array();
                    foreach ( $versions as $version ) {
                        if ( (int)$version['version_id'] !== $min_version_id ) {
                            $delete_version_ids[] = (int)$version['version_id'];
                        }
                    }
                    
                    if ( ! empty( $delete_version_ids ) ) {
                        $placeholders = implode( ',', array_fill( 0, $this->wrap_count( $delete_version_ids ), '%d' ) );
                        $query = $wpdb->prepare(
                            "DELETE FROM {$table_name} 
                             WHERE page_id = %d AND device_id = %d AND version_id IN ({$placeholders})",
                            array_merge( array( $page_id, $device_id ), $delete_version_ids )
                        );
                        
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name and placeholders are safely constructed. This SQL query uses placeholders and $wpdb->prepare(), but it may trigger warnings due to the dynamic construction of the SQL string. Direct database call is necessary in this case due to the complexity of the SQL query. Caching would not provide significant performance benefits in this context. (important-comment)
                        $result = $wpdb->query( $query );
                        
                        if ( $result === false ) {
                            $qahm_log->error(
                                "Failed to delete version_hist records for page_id={$page_id}, device_id={$device_id}, version_no={$version_no}. " .
                                "Error: {$wpdb->last_error}"
                            );
                            
                            if ( ! isset( $progress['error_count'][$page_id] ) ) {
                                $progress['error_count'][$page_id] = 0;
                            }
                            $progress['error_count'][$page_id]++;
                            
                            $this->wrap_put_contents( $progress_file, $this->wrap_serialize( $progress ) );
                            return false;  // 未完了
                        } else {
                            $progress['deleted_records'] += $result;
                            
                            // ファイルシステムからも削除
                            $version_hist_dir = $data_dir . 'view/all/version_hist/';
                            foreach ( $delete_version_ids as $vid ) {
                                $version_file = $version_hist_dir . $vid . '_version.php';
                                if ( $this->wrap_exists( $version_file ) ) {
                                    $this->wrap_delete( $version_file );
                                }
                            }
                        }
                    }
                }
            }
            
            $progress['last_processed_page_id'] = $page_id;
            $progress['processed_pages']++;
            
            if ( isset( $progress['error_count'][$page_id] ) ) {
                unset( $progress['error_count'][$page_id] );
            }
        }
        
        // バッチ処理完了後、次のバッチがあるか確認
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safely constructed using $wpdb->prefix. Direct database call is necessary for checking if more pages exist. Caching would not provide benefits in this context. (important-comment)
        $next_page_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT page_id 
                 FROM {$table_name} 
                 WHERE page_id > %d
                 ORDER BY page_id ASC 
                 LIMIT 1",
                $progress['last_processed_page_id']
            )
        );

        if ( empty( $next_page_ids ) ) {
            // 本当に完了
            $this->wrap_delete( $progress_file );
            $qahm_log->info(
                "qa_page_version_hist cleanup completed. " .
                "Processed: {$progress['processed_pages']} pages, " .
                "Deleted: {$progress['deleted_records']} records"
            );
            return true;
        }

        // まだ続きがある
        $this->wrap_put_contents( $progress_file, $this->wrap_serialize( $progress ) );
        $qahm_log->info(
            "qa_page_version_hist cleanup in progress. " .
            "Progress: {$progress['processed_pages']}/{$progress['total_pages']}"
        );
        return false;
    }
}
