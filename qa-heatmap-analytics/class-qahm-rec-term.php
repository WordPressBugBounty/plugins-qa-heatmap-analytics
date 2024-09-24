<?php
/**
 * rectermテーブルを操作するクラス
 *
 * @package qa_heatmap
 */

$qahm_recterm = new QAHM_Rec_Term();

class QAHM_Rec_Term extends QAHM_Base {

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		// 有効化時の処理
		register_activation_hook( $this->get_plugin_main_file_path(), array( $this, 'create_table' ) );

		// 更新時の処理
		add_action( 'upgrader_process_complete', array( $this, 'update' ), 10, 2 );
	}

	/**
	 * DBにテーブルを作成
	 */
	public function create_table() {
		global $wpdb;

		// 現在のDBバージョン取得
		$installed_ver = $this->wrap_get_option( 'recterm_version', '' );

		// DBバージョンが違ったら作成
		if ( $installed_ver !== '0.1.0.0' ) {
			// charsetを指定する
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} ";
			}

			// 照合順序を指定する（ある場合。通常デフォルトのutf8_general_ci）
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= "COLLATE {$wpdb->collate}";
			}

			// SQL文でテーブルを作る
			global $wpdb;
			$table_name = $wpdb->prefix . QAHM_RECTERM_TABLE;
			$sql        = "
				CREATE TABLE {$table_name} (
				autoid int UNSIGNED NOT NULL AUTO_INCREMENT,
				type varchar(30) NOT NULL,
				ids int UNSIGNED NOT NULL,
				rec_days smallint UNSIGNED NOT NULL,
				rec_flag tinyint(1),
				file_size int UNSIGNED,
				insert_date date,
				update_date date,
				start_date date,
				stop_date date,
				UNIQUE KEY autoid (autoid),
				UNIQUE KEY type_ids (type,ids)
			) {$charset_collate};";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			// バージョンアップ補正処理予定
			if ( $installed_ver ) {
				// if( version_compare( $installed_ver, '0.0.1', '>' ) ){
				// $sql = 'ALTER TABLE '.$table_name.' DROP PRIMARY KEY';
				// $wpdb->query($sql);
				// }
			}
			// 初めてインストールする時
			else {
				// set_record内のrecrefreshの関数が実行できないタイミングなのでbulk_insertで対応
				$this->bulk_insert(
					array(
						array(
							'type'     => 'home',
							'ids'      => 1,
							'rec_flag' => 1,
							'rec_days' => 30,
						),
					)
				);
			}

			$this->wrap_update_option( 'recterm_version', '0.1.0.0' );
		}
	}

	/**
	 * プラグインの更新
	 */
	public function update( $upgrader_object, $options ) {
		$current_plugin_path_name = plugin_basename( __FILE__ );

		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
			foreach ( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin == $current_plugin_path_name ) {
					$this->create_table();
				}
			}
		}
	}

	/**
	 * 全てのデータを取得
	 */
	public function get_all_record() {
		global $wpdb;
		$table_name = $wpdb->prefix . QAHM_RECTERM_TABLE;

		$query  = "SELECT * FROM {$table_name}";
		$result = $wpdb->get_results( $query, ARRAY_A );

		return $result ? $result : null;
	}

	/**
	 * レコードを取得
	 */
	public function get_record( $select, $type, $ids, $order_col = null, $sort = 'desc', $limit = null ) {
		global $wpdb;
		$table_name = $wpdb->prefix . QAHM_RECTERM_TABLE;

		$order_by = '';
		if ( $order_col && $sort ) {
			$order_by = ' ORDER BY ' . $order_col . ' ' . $sort;
		}
		if ( $limit ) {
			$limit = ' LIMIT ' . $limit;
		}

		$query  = 'SELECT ' . $select . " FROM {$table_name} WHERE type='" . $type . "' AND ids=" . $ids . $order_by . $limit;
		$result = $wpdb->get_results( $query, ARRAY_A );

		return $result ? $result : null;
	}

	/**
	 * フィールドを取得
	 */
	public function get_field( $select, $type, $ids ) {
		global $wpdb;
		$table_name = $wpdb->prefix . QAHM_RECTERM_TABLE;

		$query  = 'SELECT ' . $select . " FROM {$table_name} WHERE type='" . $type . "' AND ids=" . $ids;
		$result = $wpdb->get_var( $query );

		return $result;
	}

	/**
	 * デフォルト値を取得
	 */
	public function get_default_value( $col ) {
		switch ( $col ) {
			case 'rec_days':
				return 30;
			case 'rec_flag':
				return 1;
			case 'file_size':
				return 0;
			case 'insert_date':
				return date_i18n( 'Y-m-d' );
			case 'update_date':
				return date_i18n( 'Y-m-d' );
			case 'start_date':
				return null;
			case 'stop_date':
				return null;
			default:
				return null;
		}
	}

	/**
	 * フィールドのデータを設定
	 */
	public function bulk_insert( $data_ary ) {
		if ( ! $data_ary ) {
			return null;
		}

		global $wpdb;
		global $qahm_log;

		$table_name = $wpdb->prefix . QAHM_RECTERM_TABLE;

		// プレースホルダーとインサートするデータ配列
		$arrayValues   = array();
		$place_holders = array();

		// $data_aryはインサートするデータ配列が入っている
		// もし入っていない場合はデフォルト値を挿入
		foreach ( $data_ary as $data ) {
			// インサートするデータを格納
			$arrayValues[] = $data['type'];
			$arrayValues[] = $data['ids'];
			$arrayValues[] = isset( $data['rec_days'] ) ? $data['rec_days'] : $this->get_default_value( 'rec_days' );
			$arrayValues[] = 1;	// rec_flag
			$arrayValues[] = isset( $data['file_size'] ) ? $data['file_size'] : $this->get_default_value( 'file_size' );
			$arrayValues[] = isset( $data['insert_date'] ) ? $data['insert_date'] : $this->get_default_value( 'insert_date' );
			$arrayValues[] = isset( $data['update_date'] ) ? $data['update_date'] : $this->get_default_value( 'update_date' );
			$arrayValues[] = isset( $data['start_date'] ) ? $data['start_date'] : $this->get_default_value( 'start_date' );
			$arrayValues[] = isset( $data['stop_date'] ) ? $data['stop_date'] : $this->get_default_value( 'stop_date' );

			// プレースホルダーの作成
			$place_holders[] = '(%s, %d, %d, %d, %d, %s, %s, %s, %s)';
		}

		// SQLの生成
		$sql = 'INSERT INTO ' . $table_name . ' ' .
				'(type, ids, rec_days, rec_flag, file_size, insert_date, update_date, start_date, stop_date) ' .
				'VALUES ' . join( ',', $place_holders ) . ' ' .
				'ON DUPLICATE KEY UPDATE ' .
				'rec_days = VALUES(rec_days), ' .
				'rec_flag = VALUES(rec_flag), ' .
				'file_size = VALUES(file_size), ' .
				'insert_date = VALUES(insert_date), ' .
				'update_date = VALUES(update_date), ' .
				'start_date = VALUES(start_date), ' .
				'stop_date = VALUES(stop_date)';
		// SQL実行
		$result = $wpdb->query( $wpdb->prepare( $sql, $arrayValues ) );
		return $result;
	}

	/**
	 * フィールドのデータを設定
	 */
	public function set_field( $type, $ids, $col, $val ) {
		global $wpdb;
		$table_name = $wpdb->prefix . QAHM_RECTERM_TABLE;

		$result = null;

		// 既にデータが存在するかチェックし、データが存在すれば上書き
		$rec = $this->exist_record( $type, $ids );
		if ( $rec ) {
			switch ( $col ) {
				case 'rec_days':
				case 'rec_flag':
					$key = $col . '=%d';
					break;
				case 'insert_date':
				case 'update_date':
					$key = $col . '=%s';
					break;
			}

			if ( $key ) {
				// UPDATE
				$result = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$table_name}
					SET " . $key . ' WHERE type=%s AND ids=%d',
						$val,
						$type,
						$ids
					)
				);
			}
		}

		return $result;
	}

	/**
	 * レコードを設定
	 */
	public function set_record( $type, $ids, $rec_flag, $rec_days = null ) {
		global $wpdb;
		$table_name = $wpdb->prefix . QAHM_RECTERM_TABLE;

		if ( ! $rec_days ) {
			$rec_days = 30;
		}

		// 既にデータが存在するかチェックし、データが存在すれば上書き
		$insert_date = $this->get_field( 'insert_date', $type, $ids );
		if ( $insert_date ) {
			$update_date = date_i18n( 'Y-m-d' );

			// UPDATE
			$result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table_name}
				SET rec_days=%d, rec_flag=%d, insert_date=%s, update_date=%s
				WHERE type=%s AND ids=%d",
					$rec_days,
					$rec_flag,
					$insert_date,
					$update_date,
					$type,
					$ids
				)
			);
		}
		// データが存在しなければINSERT
		else {
			$insert_date = date_i18n( 'Y-m-d' );
			$update_date = $insert_date;

			// INSERT
			$result = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$table_name}
				(type, ids, rec_days, rec_flag, insert_date, update_date)
				VALUES (%s, %d, %d, %d, %s, %s)",
					$type,
					$ids,
					$rec_days,
					$rec_flag,
					$insert_date,
					$update_date
				)
			);
		}
		return $result ? $result : null;
	}

	/**
	 * 存在チェック
	 */
	public function exist_record( $type, $ids ) {
		global $wpdb;
		$table_name = $wpdb->prefix . QAHM_RECTERM_TABLE;

		$query = "SELECT 1 FROM {$table_name} WHERE type='" . $type . "' AND ids=" . $ids . ' LIMIT 1';
		return $wpdb->get_results( $query, ARRAY_A );
	}

	//used at admin-page-config
	public function get_recording_page() {
		global $wpdb;
		$table_name = $wpdb->prefix . QAHM_RECTERM_TABLE;

		$query = "SELECT type, ids FROM {$table_name} WHERE rec_flag = 1";
		return $wpdb->get_results( $query, ARRAY_A );
	}
}
