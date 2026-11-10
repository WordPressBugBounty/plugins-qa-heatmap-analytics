<?php
defined( 'ABSPATH' ) || exit;
/**
 * QAHM WordPress Base Class
 *
 * WordPress 環境に依存するラッパー関数（wrap_*）をまとめる基底クラス。
 *
 * 役割（Core / WP / Base の分離方針）
 * - QAHM_Core_Base : PHP標準関数の安全ラップ（wrap_*_static など）と、WP非依存の基盤処理
 * - QAHM_WP_Base   : WordPress API / WP_Filesystem / wp_remote_get など、WP環境に依存する処理の吸収
 * - QAHM_Base      : プロダクト共通の状態・ロジック（WP環境上の共通基底）
 *
 * 補足（歴史的経緯）
 * - 以前 QAHM_File_Base に存在していた「WP_Filesystem/remote/ファイルI/O系」のメソッドを、
 *   役割の明確化のため本クラスへ段階的に移管している。
 * - そのため、本クラスは「static関数ラッパーのみ」ではなく、WP依存のI/Oラッパーも提供する。
 *
 * @package qa_heatmap
 */
abstract class QAHM_WP_Base extends QAHM_Core_Base {

	/**
	 * wrap_remote_get_site_page() が追うリダイレクトの上限。wp_remote_get() の既定値と同じ。
	 */
	const SITE_PAGE_MAX_REDIRECTS = 5;

	/**
	 * WordPress 環境用の json_encode ラッパー（static実装）
	 *
	 * QAHM_Core_Base 側の instance ラッパー（wrap_json_encode）から呼ばれることを想定。
	 * WP環境では wp_json_encode() を利用して、WordPress流のエンコードを行う。
	 *
	 * @param mixed $value   エンコード対象。
	 * @param int   $options JSON オプション。
	 * @param int   $depth   最大深度。
	 * @return string|false  JSON 文字列。エラー時は false。
	 */
	protected static function wrap_json_encode_static( $value, $options = 0, $depth = 512 ) {

		if ( $value === null ) {
			return 'null';
		}

		// WordPress 提供の wp_json_encode() を利用
		$result = wp_json_encode( $value, $options, $depth );

		if ( false === $result ) {
			return false;
		}

		return $result;
	}

	/**
	 * WP_Filesystem の exists() ラッパー
	 *
	 * @param string $path パス。
	 * @return bool        存在する場合 true。
	 */
	protected function wrap_exists( $path ) {
		global $wp_filesystem;
		return $wp_filesystem->exists( $path );
	}

	/**
	 * ディレクトリ作成ラッパー（存在していれば true）
	 *
	 * - 既に存在する場合は true を返す（mkdirを呼ばない）
	 * - 存在しない場合のみ mkdir を実行し、結果を返す
	 *
	 * @param string $path ディレクトリパス。
	 * @return bool        既存/作成成功なら true。
	 */
	protected function wrap_mkdir( $path ) {
		global $wp_filesystem;
		if ( $wp_filesystem->exists( $path ) ) {
			return true;
		} else {
			return $wp_filesystem->mkdir( $path );
		}
	}

	/**
	 * ディレクトリ内ファイル一覧を取得する（最終更新時刻・サイズ付き）
	 *
	 * 返却形式は、配列の各要素が以下の連想配列：
	 * - name        : ファイル名
	 * - lastmodunix : 更新時刻（UNIX）
	 * - size        : ファイルサイズ
	 *
	 * FS_METHOD が ftpext の場合：
	 * - $wp_filesystem->dirlist() を使用（ただし更新時刻は filemtime で補完）
	 *
	 * それ以外：
	 * - scandir() + filemtime/filesize を使用
	 *
	 * @param string $path ディレクトリパス（末尾スラッシュ前提の箇所があるため、呼び出し側で統一すること）。
	 * @return array|false 取得できた場合は配列、空の場合は false。
	 */
	public function wrap_dirlist( $path ) {
		global $wp_filesystem;

		$ret_ary = array();

		$path = trailingslashit( $path );
		if ( is_readable( $path ) ) {

			if ( defined( 'FS_METHOD' ) ) {

				switch ( FS_METHOD ) {

					case 'ftpext':
						$files = $wp_filesystem->dirlist( $path );
						foreach ( $files as $file ) {
							// 「.」「..」以外のファイルを出力
							if ( preg_match( '/^(\.|\.\.)$/', $file['name'] ) ) {
								continue; }

							$lastmodunix = filemtime( $path . $file['name'] );
							$ret_ary[]   = array(
								'name'        => $file['name'],
								'lastmodunix' => $lastmodunix,
								'size'        => $file['size'],
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
									'size'        => $filesize,
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
							'size'        => $filesize,
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

	/**
	 * wp_remote_get を QAHM 用にラップした関数
	 *
	 * - User-Agent に QAHM bot 情報を付与する
	 * - デバイス種別（smp/tab/dsk）によって UA を切り替える
	 *
	 * @param string $url      取得URL。
	 * @param string $dev_name デバイス種別（smp/tab/dsk）。
	 * @param int    $timeout  応答待ちの秒数。既定 60。短い待ちで十分な用途（サムネイル取得等）だけが明示的に指定する。
	 * @return array|\WP_Error WP HTTP API の戻り値。失敗時は WP_Error。
	 */
	protected function wrap_remote_get( $url, $dev_name = 'dsk', $timeout = 60 ) {
		return wp_remote_get( $url, $this->get_remote_get_args( $dev_name, $timeout ) );
	}

	/**
	 * 計測対象サイトのページを取得する（#1761）
	 *
	 * リプレイ・ヒートマップの表示用 HTML など、取得した本文を画面に出す用途はこちらを使う。
	 * 取得先の URL は計測で受け取った値（外部から送られうる）なので、wrap_remote_get() とは次の点が違う。
	 *
	 * - 登録サイト（is_registered_site_url()）以外の URL は取りに行かない
	 * - リダイレクトは自分で追い、行き先ごとに同じ照合をする
	 *   （登録サイトのオープンリダイレクトを経由して外へ出ないように）
	 * - wp_safe_remote_get() を使う。ただし登録サイトのホストは、内部アドレスに解決されても
	 *   標準外のポートでも許可する（自社環境で計測対象が内部ネットワークにある場合があるため）
	 *
	 * @param string $url      取得URL。
	 * @param string $dev_name デバイス種別（smp/tab/dsk）。
	 * @param int    $timeout  応答待ちの秒数。既定 60。
	 * @return array|\WP_Error WP HTTP API の戻り値。登録サイト以外への取得・リダイレクトは WP_Error。
	 */
	protected function wrap_remote_get_site_page( $url, $dev_name = 'dsk', $timeout = 60 ) {
		$args = $this->get_remote_get_args( $dev_name, $timeout );
		// リダイレクトは WP に任せず、下のループで1回ずつ照合してから追う
		$args['redirection'] = 0;

		$hosts = $this->get_registered_site_hosts();

		$allow_host = function ( $is_external, $host ) use ( $hosts ) {
			if ( in_array( mb_strtolower( (string) $host, 'UTF-8' ), $hosts, true ) ) {
				return true;
			}
			return $is_external;
		};
		$allow_port = function ( $ports, $host, $url ) use ( $hosts ) {
			if ( in_array( mb_strtolower( (string) $host, 'UTF-8' ), $hosts, true ) ) {
				$port = wp_parse_url( $url, PHP_URL_PORT );
				if ( $port ) {
					$ports[] = (int) $port;
				}
			}
			return $ports;
		};
		add_filter( 'http_request_host_is_external', $allow_host, 10, 2 );
		add_filter( 'http_allowed_safe_ports', $allow_port, 10, 3 );

		try {
			$current = $url;
			for ( $hop = 0; ; $hop++ ) {
				if ( ! $this->is_registered_site_url( $current ) ) {
					return new WP_Error( 'qahm_unregistered_site_url', 'The URL is not a registered site.' );
				}

				$response = wp_safe_remote_get( $current, $args );
				if ( is_wp_error( $response ) ) {
					return $response;
				}

				$code     = (int) wp_remote_retrieve_response_code( $response );
				$location = wp_remote_retrieve_header( $response, 'location' );
				if ( is_array( $location ) ) {
					$location = end( $location );
				}
				if ( $code < 300 || $code >= 400 || ! is_string( $location ) || '' === $location ) {
					return $response;
				}

				if ( $hop >= self::SITE_PAGE_MAX_REDIRECTS ) {
					return new WP_Error( 'qahm_too_many_redirects', 'Too many redirects.' );
				}
				$current = WP_Http::make_absolute_url( $location, $current );
			}
		} finally {
			remove_filter( 'http_request_host_is_external', $allow_host, 10 );
			remove_filter( 'http_allowed_safe_ports', $allow_port, 10 );
		}
	}

	/**
	 * 計測対象として登録されているサイトのホスト名の一覧を返す（#1761）
	 *
	 * qahm_sitemanage の domain のうち、削除済み（status 255）を除いたもの。
	 * domain は登録時に小文字で保存している（#1624）が、比較のためここでもそろえる。
	 * qahm-ajax.php（受信側）の qahm_get_domains_with_cache() と同じ条件で選ぶ。
	 *
	 * @return string[] 小文字のホスト名。登録が無ければ空配列。
	 */
	protected function get_registered_site_hosts() {
		$sitemanage = get_option( 'qahm_sitemanage' );
		if ( ! is_array( $sitemanage ) ) {
			return array();
		}

		$hosts = array();
		foreach ( $sitemanage as $site ) {
			if ( ! is_array( $site ) || ! isset( $site['status'] ) || 255 === (int) $site['status'] ) {
				continue;
			}
			if ( ! isset( $site['domain'] ) || ! is_string( $site['domain'] ) || '' === $site['domain'] ) {
				continue;
			}
			$hosts[] = mb_strtolower( $site['domain'], 'UTF-8' );
		}

		// QA Assistants の登録サイトは「WordPress アドレス」（siteurl）から作られるが、
		// 訪問者が見るページは「サイトアドレス」（home）側にある。両者のホストが違う設定でも
		// 計測・表示が成り立つよう、サイトアドレスのホストも加える（管理者が設定する値なので外からは変えられない）。
		// QA ZERO のサイトアドレスは ZERO サーバー自身で計測対象ではないので加えない。
		// qahm-ajax.php（受信側）でも同じ扱いにしている。
		if ( defined( 'QAHM_TYPE' ) && QAHM_TYPE === QAHM_TYPE_WP ) {
			$home_host = wp_parse_url( (string) get_option( 'home' ), PHP_URL_HOST );
			if ( is_string( $home_host ) && '' !== $home_host ) {
				$hosts[] = mb_strtolower( $home_host, 'UTF-8' );
			}
		}

		return array_values( array_unique( $hosts ) );
	}

	/**
	 * URL が登録サイトのものかを判定する（#1761）
	 *
	 * http/https で、ホストが登録サイトの domain と一致するときだけ true。
	 * 計測で受け取った URL を取得・保存する前の照合に使う。
	 *
	 * @param string $url 判定する URL。
	 * @return bool 登録サイトの URL なら true。
	 */
	protected function is_registered_site_url( $url ) {
		if ( ! is_string( $url ) || '' === $url ) {
			return false;
		}

		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return false;
		}
		$scheme = strtolower( $parts['scheme'] );
		if ( 'http' !== $scheme && 'https' !== $scheme ) {
			return false;
		}
		// user:pass@host の形は、表示上のホストと実際の接続先を取り違えやすいので受け付けない
		if ( isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
			return false;
		}

		return in_array( mb_strtolower( $parts['host'], 'UTF-8' ), $this->get_registered_site_hosts(), true );
	}

	/**
	 * wrap_remote_get() / wrap_remote_get_site_page() 共通の取得オプションを作る
	 *
	 * @param string $dev_name デバイス種別（smp/tab/dsk）。
	 * @param int    $timeout  応答待ちの秒数。
	 * @return array wp_remote_get() に渡す引数。
	 */
	private function get_remote_get_args( $dev_name, $timeout ) {
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

		return array(
			'user-agent' => $ua,
			'timeout'    => $timeout,
			'sslverify'  => false,
		);
	}

	/**
	 * WP_Filesystem の delete() ラッパー
	 *
	 * @param string $file 対象ファイルパス。
	 * @return bool        成功時 true。
	 */
	function wrap_delete( $file ) {
		global $wp_filesystem;
		return $wp_filesystem->delete( $file );
	}

	/**
	 * WP_Filesystem の put_contents() ラッパー（QAHM専用ヘッダ付与）
	 *
	 * 保存するファイルの先頭に PHP 実行コード（404+exit）を付与する。
	 * これにより、作業用ディレクトリのファイルが直接アクセスされた場合でも内容が漏れない想定。
	 *
	 * @param string $file 保存先ファイルパス。
	 * @param string $data 保存するデータ（ヘッダはここに含めない）。
	 * @return bool        成功時 true。
	 */
	function wrap_put_contents( $file, $data ) {
		global $wp_filesystem;
		$newstr  = '<?php http_response_code(404);exit; ?>' . PHP_EOL;
		$newstr .= $data;
		return $wp_filesystem->put_contents( $file, $newstr );
	}

	/**
	 * WP_Filesystem の get_contents() ラッパー（QAHM専用ヘッダ除去）
	 *
	 * wrap_put_contents() で付与した 1行目のヘッダ（404+exit）を除去して返す。
	 * 前提：ヘッダが存在する場合は必ずファイルの1行目に存在する。
	 *
	 * @param string $file 読み込み対象ファイルパス。
	 * @return string|false 読み込み成功時は内容文字列（ヘッダ除去済み）、失敗時 false。
	 */
	function wrap_get_contents( $file ) {
		global $wp_filesystem;

		$string = $wp_filesystem->get_contents( $file );
		if ( $string === false || $string === '' ) {
			return false;
		}

		// 1行目のみ確認（高速・安全）
		$pos = $this->wrap_strpos( $string, "\n" );
		if ( $pos !== false ) {
			$first_line = $this->wrap_substr( $string, 0, $pos );

			// <?php が付いていても OK。1行目に http_response_code(404) が含まれていれば除去
			if ( $this->wrap_strpos( $first_line, 'http_response_code(404)' ) !== false ) {

				$rest = $this->wrap_substr( $string, $pos + 1 );

				// \r\n 対応（\n の直後に \r が残る場合がある）
				if ( $rest !== '' && $this->wrap_substr( $rest, 0, 1 ) === "\r" ) {
					$rest = $this->wrap_substr( $rest, 1 );
				}

				return $rest;
			}
		}

		return $string;
	}
}
