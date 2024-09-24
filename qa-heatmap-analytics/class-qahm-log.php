<?php
/**
 * プラグインのログを管理
 *
 * @package qa_heatmap
 */

$qahm_log = new QAHM_Log();

class QAHM_Log extends QAHM_Base {

	// 現在設定しているログの出力レベル
	const LEVEL = self::DEBUG;

	// ログの出力レベル一覧
	const ERROR = 0;                    // エラー
	const WARN  = 1;                    // エラーではないが例外的な事
	const INFO  = 2;                    // 記録したい情報
	const DEBUG = 3;                    // 開発時に必要な情報

	// ログを削除する際に残す最大行数
	const DELETE_LINE  = 10000;

	// wp-content直下のdebug.logにログを保存するフラグ。falseならプラグインのdataディレクトリ内
	const USE_WP_DEBUG_LOG = false;

	/**
	 * ログファイルのパスを取得
	 */
	public function get_log_file_path() {
		global $wp_filesystem;
		$path = $this->get_data_dir_path() . 'log/';
		if ( ! $wp_filesystem->exists( $path ) ) {
			$wp_filesystem->mkdir( $path );
		}

		$path .= 'qalog.txt';
		return $path;
	}

	/**
	 * ログの公開鍵ファイルのパスを取得
	 */
	public function get_key_file_path() {
		return plugin_dir_path( __FILE__ ) . 'key/qalog.pem';
	}

	/**
	 * 一定の行数まで溜まったログを削除
	 */
	public function delete() {
		if ( self::USE_WP_DEBUG_LOG ) {
			return;
		}

		$path_log = $this->get_log_file_path();
		if ( ! file_exists( $path_log ) ) {
			return;
		}

		$log_contents = file_get_contents( $path_log );
		$log_ary      = explode( PHP_EOL, $log_contents );

		if ( self::DELETE_LINE >= count( $log_ary ) ) {
			return;
		}

		array_splice( $log_ary, self::DELETE_LINE );
		$log_contents = implode( PHP_EOL, $log_ary );
		file_put_contents( $path_log, $log_contents, LOCK_EX );
	}

	/**
	 * ログ出力
	 */
	private function log( $log, $level, $backtrace ) {
		if ( self::LEVEL < $level ) {
			return '';
		}
		
		$path_log = $this->get_log_file_path();
		$path_key = $this->get_key_file_path();

		switch ( $level ) {
			case self::ERROR:
				$level = 'ERROR';
				break;
			case self::WARN:
				$level = 'WARNING';
				break;
			case self::INFO:
				$level = 'INFO';
				break;
			case self::DEBUG:
				$level = 'DEBUG';
				break;
		}

		// ファイル名
		$file = basename( $backtrace[0]['file'] );
		
		// 行数
		$line = $backtrace[0]['line'];

		// ログが配列なら文字列化
		if ( is_array( $log ) ){
			$log = print_r( $log, true );
		}

		if ( self::USE_WP_DEBUG_LOG ) {
			$log = sprintf( '%s, %s, %s:%s, %s', $level, QAHM_PLUGIN_VERSION, $file, $line, $log );
			error_log( $log );
		} else {
			global $qahm_time;
			if( method_exists( $qahm_time, 'now_str' ) ) {
				$time = '[' . $qahm_time->now_str() . ']';
			} else {
				$time = '[Unknown time]';
			}
			// maruyama:crypted only log messege not PHP_EOL
//			$log  = sprintf( '%s %s, %s:%s, %s' . PHP_EOL, $time, $level, $file, $line, $log );
			$log  = sprintf( '%s %s, %s, %s:%s, %s', $time, $level, QAHM_PLUGIN_VERSION, $file, $line, $log );

			if ( QAHM_DEBUG >= QAHM_DEBUG_LEVEL['debug'] || ( defined( 'QAHM_CONFIG_VIEW_LOG' ) && QAHM_CONFIG_VIEW_LOG === true ) ) {
				$this->file_put_contents_prepend( $path_log, $log.PHP_EOL );
				//file_put_contents( $path_log, $log.PHP_EOL, FILE_APPEND | LOCK_EX );
			} else {
				$key  = file_get_contents( $path_key );
				openssl_public_encrypt( $log, $crypted, $key );
				// maruyama:base 64 encode for crypted binary
				$crypted = base64_encode( $crypted );
				$this->file_put_contents_prepend( $path_log, $crypted.PHP_EOL );
				//file_put_contents( $path_log, $crypted.PHP_EOL, FILE_APPEND | LOCK_EX );
			}
		}
		return $log;
	}

	/**
	 * 先頭行にログを追加
	 */
	private function file_put_contents_prepend( $path, $data ) {
		if (!$fp = fopen($path, 'c+b')) { return false; }

		flock($fp, LOCK_EX);
		$data = $data . stream_get_contents($fp);
		rewind($fp);

		$result = fwrite($fp, $data);
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);

		return $result;
	}

	/**
	 * errorレベルのログを出力
	 */
	public function error( $log ) {
		$log = $this->log( $log, self::ERROR, debug_backtrace() );
		return $log;
	}

	/**
	 * warningレベルのログを出力
	 */
	public function warning( $log ) {
		$log = $this->log( $log, self::WARN, debug_backtrace() );
		return $log;
	}

	/**
	 * infoレベルのログを出力
	 */
	public function info( $log ) {
		$log = $this->log( $log, self::INFO, debug_backtrace() );
		return $log;
	}

	/**
	 * debugレベルのログを出力
	 */
	public function debug( $log ) {
		$log = $this->log( $log, self::DEBUG, debug_backtrace() );
		return $log;
	}
}
