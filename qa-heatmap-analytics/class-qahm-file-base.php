<?php
/**
 *
 *
 * @package qa_heatmap
 */

class QAHM_File_Base extends QAHM_Base {
	protected function wrap_exists( $path ) {
		global $wp_filesystem;
		return $wp_filesystem->exists( $path );
	}

	/**
	 * ディレクトリの存在チェック＆ディレクトリを作成
	 * ディレクトリが既に存在した場合や新規作成した場合にtrueを返す
	 */
	protected function wrap_mkdir( $path ) {
		global $wp_filesystem;
		if ( $wp_filesystem->exists( $path ) ) {
			return true;
		} else {
			return $wp_filesystem->mkdir( $path );
		}
	}

	public function wrap_dirlist( $path ) {
		global $wp_filesystem;
		$ret_ary = array();
		if ( is_readable( $path ) ) {

			if ( defined( 'FS_METHOD' ) ) {
				switch ( FS_METHOD ) {
					case 'ftpext':
						$files = $wp_filesystem->dirlist( $path );
						foreach ( $files as $file ) {
							// 「.」「..」以外のファイルを出力
							$lastmodunix = filemtime( $path . $file['name'] );
							$ret_ary[]   = array(
								'name'        => $file['name'],
								'lastmodunix' => $lastmodunix,
								'size'        => $file['size']
							);
						}
						break;

					default:
						// ディレクトリ内のファイルを取得
						$files = scandir( $path );
						foreach ( $files as $file_name ) {
							// 「.」「..」以外のファイルを出力
							if ( ! preg_match( '/^(\.|\.\.)$/', $file_name ) ) {
								$lastmodunix = filemtime( $path . $file_name );
								$filesize    = filesize( $path . $file_name );
								$ret_ary[]   = array(
									'name'        => $file_name,
									'lastmodunix' => $lastmodunix,
									'size'        => $filesize
								);
							}
						}
						break;
				}
			} else {
				$files = scandir( $path );
				foreach ( $files as $file_name ) {
					// 「.」「..」以外のファイルを出力
					if ( ! preg_match( '/^(\.|\.\.)$/', $file_name ) ) {
						$lastmodunix = filemtime( $path . $file_name );
						$filesize    = filesize( $path . $file_name );
						$ret_ary[]   = array(
							'name'        => $file_name,
							'lastmodunix' => $lastmodunix,
							'size'        => $filesize
						);
					}
				}
			}
		}
		if ( ! empty( $ret_ary ) ) {
			return $ret_ary;
		} else {
			return false;
		}
	}

	//QA ZERO ADD START

	/**
	 * lsコマンドの結果をwrap_dirlistの戻り値と同じ形式で返します。
	 * 
	 * 引数：
	 * $dir       string : 必須。対象となるディレクトリのパスを指定
	 * $wildcard  string : 省略化。lsに渡すファイル抽出条件を指定。
	 * 
	 * 備考：
	 * ・ファイルパスにスペースが入る場合は無視されます。
	 * ・wrap_dirlistはアルファベット順の”自然順”で返しますが、
	 * 　当関数はls結果をそのまま（アルファベット順）で返します。
	 * 　自然順で返すことを期待して使用しないでください。
	 * ・「ls -l --time-style=full-iso」
	 * 　が標準的な列名
	 * 　パーミッション, ハードリンクの数, ファイルの所有者名, 
	 * 　ファイルの所有グループ名, ファイルサイズ（バイト単位）, 
	 * 　ファイルの最終更新日時（ISO 8601形式）, ファイル名、
	 * 　で返ることを想定していますので、OSや設定によっては使用不可です。
	 * 　(使用する場合は、オプションで切り替え可能とすること)
	 * ・osコマンドインジェクションの可能性がある場合は使用しないでください
	 * 　（POSTされた値をそのままチェックせず入力することは不可）
	 */
	public function listfiles_ls($dir, $wildcard = "*") {

		$output = array();
		exec("ls -l --time-style=full-iso ".$dir.$wildcard, $files);

		foreach ($files as $file) {
			#$fileInfo = preg_split('/\s+/', $file, null, PREG_SPLIT_NO_EMPTY);
			$fileInfo  = explode(' ', $file);
			if ($this->wrap_count($fileInfo) != 9 || $this->wrap_substr($fileInfo[0], 0, 1) == "d") {
				continue;
			}

			$fileName = basename($fileInfo[8]);
			$lastModUnix = filemtime($fileInfo[8]);
			#$lastModUnix = strtotime($fileInfo[5] . " " . $fileInfo[6]. " " .$fileInfo[7]);
			$fileSize = intval($fileInfo[4]);
			$output[] = array("name" => $fileName, "lastmodunix" => $lastModUnix, "size" => $fileSize);
		}
		return $output;

	}
	//QA ZERO ADD END

	/**
	 * wp_remote_getをqahm用にラップした関数
	 * 失敗した場合WP_Errorが返る
	 */
	protected function wrap_remote_get( $url, $dev_name='dsk' ) {
		$bot = QAHM_NAME . 'bot/' . QAHM_PLUGIN_VERSION;
		
		// デバイスによるユーザーエージェント指定
		switch ( $dev_name ) {
			case 'smp':
				$ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1' . ' ' . $bot;
				break;
			case 'tab':
				$ua = 'Mozilla/5.0 (iPad; CPU OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1' . ' ' . $bot;
				break;
			default:
				$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36' . ' ' . $bot;
				break;
		}
		$args = array(
			'user-agent' => $ua,
			'timeout'    => 60,
			'sslverify'  => false,
		);

		return wp_remote_get( $url, $args );
	}

	/**
	 * qahm共通のfile_get_contentsのstream_context_create内で使用するオプションを取得
	 * ここは後々remote_getを使う形に変更
	 */
	protected function get_stream_options( $dev_name ) {
		$bot = QAHM_NAME . 'bot/' . QAHM_PLUGIN_VERSION;

		// デバイスによるユーザーエージェント指定
		switch ( $dev_name ) {
			case 'smp':
				$ua = 'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1' . ' ' . $bot;
				break;
			case 'tab':
				$ua = 'User-Agent: Mozilla/5.0 (iPad; CPU OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1' . ' ' . $bot;
				break;
			default:
				$ua = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36' . ' ' . $bot;
				break;
		}

		$options = array(
			'http' => array(
				'method'           => 'GET',
				'header'           => $ua,
				'timeout'          => 10,
				'ignore_errors'    => true,
			),
			'ssl' => array(
				'verify_peer'      => false,
				'verify_peer_name' => false,
			)
		);

		return $options;
	}
	

	/**
	 * rawデータのディレクトリのパスを取得
	 */

	//QA ZERO start
	public function get_raw_dir_path( $tracking_id, $url_hash ){

		$dir = $this->get_data_dir_path();
		
		$dir .= $tracking_id . '/';
		if ( ! $this->wrap_mkdir( $dir ) ) {
			return false;
		}

		if ( $url_hash ) {
			$dir .= $url_hash . '/';
			if ( ! $this->wrap_mkdir( $dir ) ) {
				return false;
			}
		}

		return $dir;
	}

	/**
	 * セキュリティを強化するためのトラッキングハッシュ配列を取得する。なければ作成
	 */
	public function get_tracking_hash_array( $tracking_id = null, $hash_update = true ) {
	
		if( !$tracking_id ){
			$tracking_id = $this->get_tracking_id( );
		}
		//$tracking_id = $this->get_tracking_id( $url );
		$data_dir    = $this->get_data_dir_path();
		$thash_file  = $data_dir . $tracking_id . '_tracking_hash.php';

		$new_thash_ary = [];
		//get now hash
		global $wp_filesystem;
		global $qahm_time;
		$now_utime = $qahm_time->now_unixtime();
		// 旧: $newhash = hash('fnv164', (string) mt_rand());
		$newhash = hash('fnv164', (string) random_int(0, mt_getrandmax()));
		if ( $wp_filesystem->exists( $thash_file ) ) {
			$th_serial = $this->wrap_get_contents( $thash_file );
			$thash_ary = $this->wrap_unserialize( $th_serial );

			$recent_utime = $thash_ary[0]['create_utime'];
			$th_interval  = $now_utime - $recent_utime;
			if ( 3600 * 24 < $th_interval && $hash_update ) {
				$new_thash_ary[0] = ['create_utime' => $now_utime, 'tracking_hash' => $newhash];
				$new_thash_ary[1] = $thash_ary[0];
				$new_th_serial    = $this->wrap_serialize( $new_thash_ary );
				$this->wrap_put_contents( $thash_file, $new_th_serial );
			} else {
				$new_thash_ary = $thash_ary;
			}
		} else {
				$new_thash_ary[0] = ['create_utime' => $now_utime, 'tracking_hash' => $newhash];
				$new_th_serial   = $this->wrap_serialize( $new_thash_ary );
				$this->wrap_put_contents( $thash_file, $new_th_serial );
		}
		return $new_thash_ary;
	}

	/**
	 * hash値があればtrue。なければfalse
	 */
	public function check_tracking_hash( $checkhash, $tracking_id ) {

		$hash_ary = $this->get_tracking_hash_array( $tracking_id, false );
		$is_in    = false;
		foreach ( $hash_ary as $hash ) {
			if ( $checkhash === $hash['tracking_hash'] ) {
				$is_in = true;
			}
		}
		return $is_in;
	}

	/**
	 * tracking_id毎のqtag.jsを作成する
	 */
	public function create_qtag( $tracking_id, $exists_ok = true ){

		if( empty( $tracking_id ) ){
			return false;
		}

		$qtag_file_name   = "qtag.js";
		$js_dir_path      = $this->get_js_dir_path();

		$qtag_subdir_path = $this->get_qtag_dir_path( $tracking_id );
		$qtag_file_path   = $qtag_subdir_path.$qtag_file_name;

		//すでに存在していれば作り直さない
		if( !$exists_ok && file_exists( $qtag_file_path ) ){
			return $qtag_file_path;
		}

		$qtag_tmp_file_path = $js_dir_path.$qtag_file_name;
		$qtag_content       = file_get_contents( $qtag_tmp_file_path );

		if( !$qtag_content ){
			return false;
		}

		$tracking_hash = $this->get_tracking_hash_array( $tracking_id )[0]['tracking_hash'];
		$ajax_url      = plugin_dir_url( __FILE__ )."qahm-ajax.php";

		$qtag_content = str_replace('{tracking_hash}', $tracking_hash, $qtag_content);
		$qtag_content = str_replace('{ajax_url}', $ajax_url, $qtag_content);

		if ( !file_put_contents( $qtag_file_path , $qtag_content ) ){
			return false;
		}

		return $qtag_file_path;

	}

	/**
	 * tracking_id毎のqtag.jsの保存先ディレクトリを取得。なければ作成。
	 */
	public function get_qtag_dir_path( $tracking_id, $mkdir = true ){

		$data_dir_path  = $this->get_data_dir_path();

		$qtag_dir_name  = "qtag_js";
		$qtag_dir_path  = $data_dir_path.$qtag_dir_name;

		if( $mkdir ){
			$this->wrap_mkdir( $qtag_dir_path );
		}

		$qtag_subdir_path   = $qtag_dir_path."/".$tracking_id;

		if( $mkdir ){
			$this->wrap_mkdir( $qtag_subdir_path );
		}

		return $qtag_subdir_path."/";

	}

    /**  
     * tracking_id毎のqtag.jsのURL取得  
     *   
     * 指定されたtracking_idに対応するqtag.jsファイルが格納される  
     * ディレクトリのWebアクセス可能なURLを取得します。  
     *   
     * @param string $tracking_id トラッキングID  
     * @return string qtagディレクトリのURL（末尾にスラッシュ付き）  
     */ 
    public function get_qtag_dir_url( $tracking_id ) {  
        $data_dir_url = $this->get_data_dir_url();  
        $qtag_dir_name = "qtag_js";  
        $qtag_dir_url = $data_dir_url . $qtag_dir_name;  
        $qtag_subdir_url = $qtag_dir_url . "/" . $tracking_id;  
        return $qtag_subdir_url . "/";  
    }

	/**
	 * tracking_id毎のqtag.jsを削除する
	 */
	function delete_qtag( $tracking_id ) {

		if ( empty( $tracking_id ) ) {
			return false;
		}

		$qtag_file_name = 'qtag.js';
		$qtag_dir_path  = $this->get_qtag_dir_path( $tracking_id, false );
		$qtag_file_path = $qtag_dir_path . $qtag_file_name;

		if ( $this->wrap_exists( $qtag_file_path ) ) {
			$this->wrap_delete( $qtag_file_path );
		}

		global $wp_filesystem;
		return $wp_filesystem->rmdir( $qtag_dir_path );
	}


	//QA ZERO end

	/*
	protected function get_raw_dir_path( $type, $id, $dev_name, $tracking_id = null ) {
		// $wp_filesystemオブジェクトの呼び出し
		global $wp_filesystem;

		$dir = $this->get_data_dir_path();
		if ( ! $this->wrap_mkdir( $dir ) ) {
			return false;
		}
		
		// トラッキングIDがnullなら今使用しているWPのトラッキングID
		if ( ! $tracking_id ) {
			$tracking_id = $this->get_tracking_id();
		}
		$dir .= $tracking_id . '/';
		if ( ! $this->wrap_mkdir( $dir ) ) {
			return false;
		}

		if ( $type ) {
			$dir .= $type . '/';
			if ( ! $this->wrap_mkdir( $dir ) ) {
				return false;
			}
		}

		$dir .= $id . '/';
		if ( ! $this->wrap_mkdir( $dir ) ) {
			return false;
		}

		$dir .= 'temp/';
		if ( ! $this->wrap_mkdir( $dir ) ) {
			return false;
		}

		$dir .= $dev_name . '/';
		if ( ! $this->wrap_mkdir( $dir ) ) {
			return false;
		}

		return $dir;
	} QA ZERO del */

	/**
	 * ディレクトリのURL or パスから要素を求める
	 */
	protected function get_raw_dir_elem( $url ) {
		$url_exp = explode( '/', $url );

		$data_num = null;
		for ( $i = 0; $i < $this->wrap_count( $url_exp ); $i++ ) {
			// dataフォルダの位置を求める
			if ( $url_exp[ $i ] === 'data' ) {
				$data_num = $i;
				break;
			}
		}
		if ( $data_num === null || ! isset( $url_exp[ $i + 4 ] ) ) {
			return null;
		}
		if ( ! $url_exp[ $i + 5 ] ) {
			return null;
		}

		$data         = array();
		$data['type'] = $url_exp[ $i + 2 ];
		$data['id']   = $url_exp[ $i + 3 ];
		$data['ver']  = $url_exp[ $i + 4 ];
		$data['dev']  = $url_exp[ $i + 5 ];
		return $data;
	}

	/**
	 * タイプとIDから元URLを取得
	 */
	protected function get_base_url( $type, $id ) {
		switch ( $type ) {
			case 'home':
				return home_url( '/' );
			case 'page_id':
			case 'p':
				return get_permalink( $id );
			case 'cat':
				return get_category_link( $id );
			case 'tag':
				return get_tag_link( $id );
			case 'tax':
				return get_term_link( $id );
			default:
				return null;
		}
	}

	/** ------------------------------
	 * 容量計算ルーチン一式
	 */

	//DB
	public function count_db() {
		//calc db
		global $qahm_db;
		global $wpdb;
		$alldbsize_ary = [];
		$alltb_ary = $qahm_db->alltable_name();
		foreach ( $alltb_ary as $tablename ) {
			//1行だけとる
			$rowsize = 0;
			$query = 'SELECT * from '. $tablename . ' LIMIT 1';
			$res   = $qahm_db->get_results( $query,'ARRAY_A' );
			$line = $res[0];
			if ($line !== null ) {
				foreach ($line as $val ) {
					if ( is_string( $val ) ) {
						if ( is_numeric( $val ) ) {
							$num = (int)$val;
							if ( $num <= 255 ) {
								$rowsize += 1;
							} else if ( $num <= 65535 ) {
								$rowsize += 2;
							} else {
								$rowsize += 4;
							}
						} else {
							$rowsize += $this->wrap_strlen( $val );
						}
					}
				}
			}
			if ( $rowsize === 0 ) {
				$rowsize = 100;
			}

			$query = 'SELECT $this->wrap_count(*) from ' . $tablename;
			$res   = $qahm_db->get_results( $query );
			$count = (int)$res[0]->{'$this->wrap_count(*)'};
			$byte = $rowsize * $count;
			$alldbsize_ary[] =  [ 'tablename' => $tablename, 'count' => $count, 'byte' => $byte ];
		}
		$allcount = 0;
		$allbyte  = 0;
		foreach ( $alldbsize_ary as $table ) {
			$allcount += $table['count'];
			$allbyte  += $table['byte'];
		}
		$alldbsize_ary[] = ['tablename' => 'all', 'count' => $allcount, 'byte' => $allbyte ];
		return $alldbsize_ary;
	}

	//file
	public function count_files() {
		global $qahm_time;
		global $wp_filesystem;
		$data_dir = $this->get_data_dir_path();

		// データディレクトリの再帰検索を行い、ファイル数と総容量を求める
		$search_dirs =  array( $data_dir );
		$allfile_cnt = 0;
		$allfilesize = 0;
		for ( $iii = 0; $iii < $this->wrap_count( $search_dirs ); $iii++ ) {   // 再帰のためループ毎にcount関数を実行しなければならない
			$dir = $search_dirs[ $iii ];
			if ( $wp_filesystem->is_dir( $dir ) && $wp_filesystem->exists( $dir ) ) {

				// ディレクトリ内に存在するファイルのリストを取得
				$file_list = $this->wrap_dirlist( $dir );
				if ( $file_list ) {
					// ディレクトリ内のファイルを全てチェック
					foreach ( $file_list as $file ) {
						// ディレクトリなら再帰検索用の配列にディレクトリを登録
						if ( is_dir( $dir . $file['name'] ) ) {
							$search_dirs[] = $dir . $file['name'] . '/';
						} else {
							// ファイルをカウントしサイズを取得
							++$allfile_cnt;
							$allfilesize += $file['size'];
						}
					}
				}
			}
		}
		return [ 'filecount' => $allfile_cnt, 'size' => $allfilesize ];
	}

	//days pv
	public function count_this_month_pv($tracking_id = 'all') {
		$ret_count = 0;

		global $qahm_db;
		global $qahm_time;

		$data_dir = $this->get_data_dir_path();
		$view_dir          = $data_dir . 'view/';
		$myview_dir        = $view_dir . $tracking_id . '/';
		$vw_summary_dir     = $myview_dir . 'summary/';

		if ( $this->wrap_exists($vw_summary_dir . 'days_access.php' ) ) {
			$daysum_ary = $this->wrap_unserialize($qahm_db->wrap_get_contents($vw_summary_dir . 'days_access.php'));
			if (!is_array($daysum_ary)) {
				return $ret_count; // 0を返す
			}

			$month = $qahm_time->month();
			if ((int)$month < 10 ) {
				$month = '0' . (string)$month;
			} else {
				$month = (string)$month;
			}

			$this_month_1st = $qahm_time->year() . '-' . $month . '-01 00:00:00';
			$this_month_1st_unix = $qahm_time->str_to_unixtime( $this_month_1st );
			
			foreach ($daysum_ary as $val ) {
				$nowunixtime = $qahm_time->str_to_unixtime( $val['date'] . ' 00:00:00' );
				if ($this_month_1st_unix <= $nowunixtime) {
					$ret_count +=  $val['pv_count'];
				}
			}
		}
		return $ret_count;
	}

	//days pv
	public function get_pvterm_start_date($tracking_id = "all") {

		global $qahm_db;
		global $qahm_time;
		$ret_day = $qahm_time->now_str('Y-m-d');

		$data_dir = $this->get_data_dir_path();
		$view_dir          = $data_dir . 'view/';
		$myview_dir        = $view_dir . $tracking_id . '/';
		$vw_summary_dir     = $myview_dir . 'summary/';

		if ( $this->wrap_exists($vw_summary_dir . 'days_access.php' ) ) {
			$daysum_ary = $this->wrap_unserialize($qahm_db->wrap_get_contents($vw_summary_dir . 'days_access.php'));
			if ( isset ( $daysum_ary[0] ) ) {
				$ret_day = $daysum_ary[0]['date'];
			}
		}
		return $ret_day;
	}

	public function get_pvterm_latest_date($tracking_id = "all") {

		global $qahm_db;
		global $qahm_time;
		$ret_day = $qahm_time->now_str('Y-m-d');

		$data_dir = $this->get_data_dir_path();
		$view_dir          = $data_dir . 'view/';
		$myview_dir        = $view_dir . $tracking_id . '/';
		$vw_summary_dir     = $myview_dir . 'summary/';

		if ( $this->wrap_exists($vw_summary_dir . 'days_access.php' ) ) {
			$daysum_ary = $this->wrap_unserialize($qahm_db->wrap_get_contents($vw_summary_dir . 'days_access.php'));
			$last_index = $this->wrap_count( $daysum_ary ) - 1;
			if ( isset ( $daysum_ary[$last_index] ) ) {
				$ret_day = $daysum_ary[$last_index]['date'];
			}
		}
		return $ret_day;
	}

	public function get_pvterm_both_end_date($tracking_id = "all") {

		global $qahm_db;
		$ret_days = [];

		$data_dir = $this->get_data_dir_path();
		$view_dir          = $data_dir . 'view/';
		$myview_dir        = $view_dir . $tracking_id . '/';
		$vw_summary_dir     = $myview_dir . 'summary/';

		if ( $this->wrap_exists($vw_summary_dir . 'days_access.php' ) ) {
			$daysum_ary = $this->wrap_unserialize($qahm_db->wrap_get_contents($vw_summary_dir . 'days_access.php'));
			if ( is_array( $daysum_ary ) ) {
				$last_index = $this->wrap_count( $daysum_ary ) - 1;
				if ( isset ( $daysum_ary[0] ) ) {
					$ret_days[ 'start' ] = $daysum_ary[0]['date'];
					$ret_days[ 'latest' ] = $daysum_ary[$last_index]['date'];
				}
			}
		}
		return $ret_days;
	}

	//days heatmap
	public function get_hmterm_start_date($tracking_id = "all") {
		global $qahm_time;

		$data_dir = $this->get_data_dir_path();
		$view_dir          = $data_dir . 'view/';
		$myview_dir        = $view_dir . $tracking_id . '/view_pv';
		$raw_p_dir         = $myview_dir . '/raw_p/';

		$allfiles = $this->wrap_dirlist( $raw_p_dir );
		$minunixt = $qahm_time->now_unixtime();
		if ($allfiles) {
			foreach ( $allfiles as $file ) {
				$filename = $file[ 'name' ];
				if ( is_file( $raw_p_dir . $filename ) ) {
					$f_date = $this->wrap_substr( $filename, 0, 10 );
					$f_datetime = $f_date . ' 00:00:00';
				}
				$f_unixt = $qahm_time->str_to_unixtime( $f_datetime );
				if ( $f_unixt < $minunixt && $f_unixt !== 0 ) {
					$minunixt = $f_unixt;
				}
			}
		}
		$mindate = $qahm_time->unixtime_to_str( $minunixt );
		$ret_day = $this->wrap_substr( $mindate, 0, 10 );
		return $ret_day;
	}

	/** ------------------------------
	 * ユーザーエージェントからデバイス名に変換
	 */
	public function user_agent_to_device_name( $ua ) {
		// スマホからのアクセス
		if ( stripos( $ua, 'iphone' ) !== false || // iphone
			stripos( $ua, 'ipod' ) !== false || // ipod
			( stripos( $ua, 'android' ) !== false && stripos( $ua, 'mobile' ) !== false ) || // android
			( stripos( $ua, 'windows' ) !== false && stripos( $ua, 'mobile' ) !== false ) || // windows phone
			( stripos( $ua, 'firefox' ) !== false && stripos( $ua, 'mobile' ) !== false ) || // firefox phone
			( stripos( $ua, 'bb10' ) !== false && stripos( $ua, 'mobile' ) !== false ) || // blackberry 10
			( stripos( $ua, 'blackberry' ) !== false ) // blackberry
			) {
			return 'smp';
		}
		// タブレット
		// mobileという文字が含まれていないAndroid端末はすべてタブレット
		elseif ( stripos( $ua, 'android' ) !== false || stripos( $ua, 'ipad' ) !== false ) {
			return 'tab';
		} else {
			return 'dsk';
		}
	}

	/**
	 * ユーザーエージェントからOS名に変換
	 */
	public function user_agent_to_os_name( $ua ) {
		if (preg_match('/Windows NT 10.0/', $ua)) {
			return 'Windows 10';
		} elseif (preg_match('/Windows NT 6.3/', $ua)) {
			return 'Windows 8.1';
		} elseif (preg_match('/Windows NT 6.2/', $ua)) {
			return 'Windows 8';
		} elseif (preg_match('/Windows NT 6.1/', $ua)) {
			return 'Windows 7';
		} elseif (preg_match('/Mac OS X ([0-9\._]+)/', $ua, $matches)) {
			return 'Mac OS X ' . str_replace('_', '.', $matches[1]);
		} elseif (preg_match('/Linux ([a-z0-9_]+)/', $ua, $matches)) {
			return 'Linux ' . $matches[1];
		} elseif (preg_match('/OS ([a-z0-9_]+)/', $ua, $matches)) {
			return 'iOS ' . str_replace('_', '.', $matches[1]);
		} elseif (preg_match('/Android ([a-z0-9\.]+)/', $ua, $matches)) {
			return 'Android ' . $matches[1];
		} else {
			return 'Unknown';
		}
	}

	/**
	 * ユーザーエージェントからブラウザ名に変換
	 */
	public function user_agent_to_browser_name( $ua ) {
		if (preg_match('/(Iron|Sleipnir|Maxthon|Lunascape|SeaMonkey|Camino|PaleMoon|Waterfox|Cyberfox)\/([0-9\.]+)/', $ua, $matches)) {
			return $matches[1] . $matches[2];
		} elseif (preg_match('/Edge\/([0-9\.]+)/', $ua, $matches)) {
			return 'Edge' . ' ' . $matches[2];
		} elseif (preg_match('/(^Opera|OPR).*\/([0-9\.]+)/', $ua, $matches)) {
			return 'Opera' . ' ' . $matches[2];
		} elseif (preg_match('/Chrome\/([0-9\.]+)/', $ua, $matches)) {
			return 'Chrome' . ' ' . $matches[1];
		} elseif (preg_match('/Firefox\/([0-9\.]+)/', $ua, $matches)) {
			return 'Firefox' . ' ' . $matches[1];
		} elseif (preg_match('/(MSIE\s|Trident.*rv:)([0-9\.]+)/', $ua, $matches)) {
			return 'Internet Explorer' . ' ' . $matches[2];
		} elseif (preg_match('/\/([0-9\.]+)(\sMobile\/[A-Z0-9]{6})?\sSafari/', $ua, $matches)) {
			return 'Safari' . ' ' . $matches[1];
		} else {
			return 'Unknown';
		}
	}

	/**
	 * デバイスIDをデバイス名に変換
	 */
	protected function device_id_to_device_name( $id ) {
		foreach ( QAHM_DEVICES as $qahm_dev ) {
			if ( $qahm_dev['id'] === (int) $id ) {
				return $qahm_dev['name'];
			}
		}

		return false;
	}

	/**
	 * デバイス名をデバイスIDに変換
	 */
	protected function device_name_to_device_id( $name ) {
		foreach ( QAHM_DEVICES as $qahm_dev ) {
			if ( $qahm_dev['name'] === $name ) {
				return $qahm_dev['id'];
			}
		}

		return false;
	}


	/**
	 * $wp_filesystem->put_contentsのラップ関数。ファイル書込用
	 */
	function wrap_put_contents( $file, $data) {
		global $wp_filesystem;
		$newstr  = '<?php http_response_code(404);exit; ?>'.PHP_EOL;
		$newstr .= $data;
		return $wp_filesystem->put_contents( $file, $newstr );
	}

	/**
	 * $wp_filesystem->get_contentsのラップ関数。ファイル読み出し用。1行目の 404を抜く
	 */

	function wrap_get_contents( $file ) {
		global $wp_filesystem;
		$string = $wp_filesystem->get_contents( $file );
		if ( $string ) {
			if ( strpos ( $string, 'http_response_code(404)' ) ) {
				return $this->wrap_substr(strstr( $string, PHP_EOL ),1);
			} else {
				return $string;
			}
		} else {
			return false;
		}
	}

	/**
	 * $wp_filesystem->get_contents_arrayのラップ関数。ファイル読み出し用。1行目の 404を抜く
	 */
	function wrap_get_contents_array( $file ) {
		global $wp_filesystem;
		$ary    = $wp_filesystem->get_contents_array( $file );
		if ( $ary ) {
			$retary = array();
			$maxcnt = $this->wrap_count( $ary );
			if ( strpos( $ary[0],'http_response_code(404)' ) ) {
				$startn = 1;
			} else {
				$startn = 0;
			}
			for ( $iii = $startn; $iii < $maxcnt; $iii++ ) {
				$retary[] = $ary[$iii];
			}
			return $retary;
		} else {
			return false;
		}
	}

	/**
	 * $wp_filesystem->deleteのラップ関数
	 */
	function wrap_delete( $file ) {
		global $wp_filesystem;
		return $wp_filesystem->delete( $file );
	}
	
	/**
	 * tsv形式の文字列データを二次元配列に変換して返す
	 */
	protected function convert_tsv_to_array( $tsv ) {
		$tsv_ary = array();
		$tsv_col = explode( PHP_EOL, $tsv );

		foreach ( $tsv_col as $tsv_row ) {
			$tsv_row_ary = explode( "\t", $tsv_row );
			$tsv_ary[]   = $tsv_row_ary;
		}

		return $tsv_ary;
	}

	
	/**
	 * 二次元配列をtsv形式の文字列データに変換して返す
	 */
	protected function convert_array_to_tsv( $ary ) {
		$tsv = '';

		for ( $i = 0, $col_cnt = $this->wrap_count( $ary ); $i < $col_cnt; $i++ ) {
			for ( $j = 0, $raw_cnt = $this->wrap_count( $ary[$i] ); $j < $raw_cnt; $j++ ) {
				// 値にPHP_EOLや\tが入っていた場合はtsvの形が崩れる可能性があるので無視
				$replace = str_replace( PHP_EOL, '', $ary[ $i ][ $j ] );
				$replace = str_replace( "\t", '', $replace );
				$tsv .= $replace;

				if ( $j === $raw_cnt - 1 ) {
					if ( $i !== $col_cnt -1 ) {
						$tsv .= PHP_EOL;
					}
				} else {
					$tsv .= "\t";
				}
			}
		}

		return $tsv;
	}
}
