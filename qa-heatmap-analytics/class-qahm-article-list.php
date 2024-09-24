<?php
/**
 *
 *
 * @package qa_heatmap
 */

$qahm_article_list = new QAHM_Article_List();

class QAHM_Article_List extends QAHM_File_Data {

	private $list_file;
	private $rec_table;

	/**
	 * コンストラクタ
	 * 今はこの機能を使っていないのでコメントアウト
	 */
	public function __construct() {
		// css, js読み込み
		//add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// 投稿ページ用
		//add_filter( 'manage_posts_columns', array( $this, 'add_columns' ) );
		//add_action( 'manage_posts_custom_column', array( $this, 'add_column_param' ), 10, 2 );
		
		// 固定ページ用
		//add_filter( 'manage_pages_columns', array( $this, 'add_columns' ) );
		//add_action( 'manage_pages_custom_column', array( $this, 'add_column_param' ), 10, 2 );
	}


	/**
	 * 初期化
	 */
	public function enqueue_scripts( $hook ) {
		if( $hook !== 'edit.php' ) {
			return;
		}

		// メンバ変数の初期化
		$cache_dir = $this->get_data_dir_path( 'cache' );
		$this->list_file = array();

		global $qahm_db;
		$table_name = $qahm_db->prefix . QAHM_RECTERM_TABLE;
		$query = "SELECT type,ids,rec_flag FROM {$table_name} WHERE rec_flag = 1";
		$this->rec_table = $qahm_db->get_results( $query, ARRAY_A );

		// 負荷軽減のため投稿一覧画面の種類により読み込むファイルを変更する
		$this->list_file['post'] = null;
		$this->list_file['page'] = null;
		$this->list_file['custom'] = null;
		$post_type = $this->wrap_filter_input( INPUT_GET, 'post_type' );
		if ( $post_type === null ) {
			$contents = $this->wrap_get_contents( $cache_dir . 'post_list.php' );
			if ( $contents ) {
				$this->list_file['post'] = $this->wrap_unserialize( $contents );
			}
		} elseif( $post_type === 'page' ) {
			$contents = $this->wrap_get_contents( $cache_dir . 'page_list.php' );
			if ( $contents ) {
				$this->list_file['page'] = $this->wrap_unserialize( $contents );
			}
		} else {
			$contents = $this->wrap_get_contents( $cache_dir . 'custom_list.php' );
			if ( $contents ) {
				$this->list_file['custom'] = $this->wrap_unserialize( $contents );
			}
		}

		// css, js読み込み
		wp_enqueue_style( QAHM_NAME . '-common', plugins_url( 'css/common.css', __FILE__ ), array(), QAHM_PLUGIN_VERSION );
		wp_enqueue_style( QAHM_NAME . '-article-list', plugins_url( 'css/article-list.css', __FILE__ ), array(), QAHM_PLUGIN_VERSION );

		wp_enqueue_script( QAHM_NAME . '-font-awesome', plugins_url( 'js/lib/font-awesome/all.min.js', __FILE__ ), null, QAHM_PLUGIN_VERSION, false );
	}

		
	/**
	 * 投稿一覧画面で使いやすい形のリストを作成
	 * 引数には文字列 post, page, custom のいずれかを入れる
	 */
	public function create_post_list( $wp_post_type, $period ) {
		global $qahm_db;

		if ( $wp_post_type !== 'post' && $wp_post_type !== 'page' && $wp_post_type !== 'custom' ) {
			return null;
		}

		$list = array();
		$list['head']['version'] = 1;
		
		$qa_type = 'p';
		if ( $wp_post_type === 'page' ) {
			$qa_type = 'page_id';
		}
		
		if( $wp_post_type === 'post' ) {
			$where = " WHERE post_status = 'publish' AND post_type = 'post'";
		} elseif( $wp_post_type === 'page' ) {
			$where = " WHERE post_status = 'publish' AND post_type = 'page'";
		} elseif( $wp_post_type === 'custom' ) {
			$in_search_post_types = get_post_types( array( 'exclude_from_search' => false ) );
			$in_search_post_types = array_diff( $in_search_post_types, array('page', 'post') );
			$in_search_post_types = array_values( $in_search_post_types );
			$where = " WHERE post_status = 'publish' AND post_type IN ('" . implode( "', '", array_map( 'esc_sql', $in_search_post_types ) ) . "')";
		}
		
		/*
		$posts = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => $get_posts_type,
			)
		);*/
		
		$table_name = $qahm_db->prefix . 'posts';
		$in_search_post_types = get_post_types( array( 'exclude_from_search' => false ) );
		$order    = ' ORDER BY post_date DESC';
		$query    = 'SELECT ID,post_type FROM ' . $table_name . $where . $order;
		$post_ary = $qahm_db->get_results( $query, ARRAY_A );

		foreach ( $post_ary as $post ) {
			$body = array();
			if ( $wp_post_type === 'custom' ) {
				if ( $post['post_type'] === 'post' || $post['post_type'] === 'page' ) {
					continue;
				}
				$body['type'] = $post['post_type'];
			}

			// DBからデータを取得
			global $qahm_db;
			$table_name = $qahm_db->prefix . 'qa_pages';
			$query      = 'select page_id,url from ' . $table_name . ' where wp_qa_type=%s and wp_qa_id=%d';
			$qa_pages = $qahm_db->get_results( $qahm_db->prepare( $query, $qa_type, $post['ID'] ) );
			if ( ! $qa_pages ) {
				continue;
			}
			
			$base_url = $this->get_base_url( $qa_type, $post['ID'] );
			if ( ! $base_url ) {
				continue;
			}

			$body['wp_qa_id'] = $post['ID'];
			foreach ( QAHM_DEVICES as $qahm_dev ) {
				$body[$qahm_dev['name']]['access_num']  = 0;
				$body[$qahm_dev['name']]['bounce_rate']  = 0;
				//$body[$qahm_dev['name']]['time_on_site'] = 0;
			}

			// 1週間以内のデータを取得
			foreach ( $qa_pages as $qa_page ) {
				if ( $base_url !== $qa_page->url ) {
					continue;
				};

				$table_name = $qahm_db->prefix . 'qa_pv_log';
				$query      = 'SELECT device_id,pv,browse_sec,is_last FROM ' . $table_name . ' WHERE page_id=%d AND access_time BETWEEN (CURDATE() - INTERVAL %d DAY) AND (CURDATE() + INTERVAL 1 DAY)';
				$qa_pv_log  = $qahm_db->get_results( $qahm_db->prepare( $query, $qa_page->page_id, $period ) );
				if ( ! $qa_pv_log ) {
					continue;
				}

				// 一時的に使用する配列の初期化
				$body_temp = array();
				foreach ( QAHM_DEVICES as $qahm_dev ) {
					$body_temp[$qahm_dev['name']]['access_num']       = 0;
					$body_temp[$qahm_dev['name']]['bounce_num']       = 0;
					$body_temp[$qahm_dev['name']]['landing_num']      = 0;
					$body_temp[$qahm_dev['name']]['time_on_site']     = 0;
					$body_temp[$qahm_dev['name']]['time_on_site_num'] = 0;
				}

				// 直帰率と滞在時間の平均を求める
				foreach ( $qa_pv_log as $log ) {
					$dev_name = $this->device_id_to_device_name( $log->device_id );

					if ( 0 < $log->browse_sec ) {
						$body_temp[$dev_name]['time_on_site'] += $log->browse_sec;
						$body_temp[$dev_name]['time_on_site_num']++;
					}
					if ( 1 == $log->pv ) {
						if( 1 == $log->is_last ) {
							$body_temp[$dev_name]['bounce_num']++;
						}
						$body_temp[$dev_name]['landing_num']++;
					}

					$body_temp[$dev_name]['access_num']++;
				}

				// $bodyに値を挿入。無駄なキャッシュデータを削減するため、アクセスがない場合にはbodyに値を入れない
				foreach ( QAHM_DEVICES as $qahm_dev ) {
					$dev_temp = $body_temp[$qahm_dev['name']];

					if ( 0 < $dev_temp['access_num'] ) {
						$body[$qahm_dev['name']][ 'access_num' ]  = $dev_temp['access_num'];

						if ( 0 < $dev_temp['bounce_num'] && 0 < $dev_temp['landing_num'] ) {
							$body[$qahm_dev['name']][ 'bounce_rate' ] = round( $dev_temp['bounce_num'] / $dev_temp['landing_num'] * 100 );
						} else {
							$body[$qahm_dev['name']][ 'bounce_rate' ] = 0;
						}

						/*
						if ( 0 < $dev_temp['time_on_site_num'] ) {
							$body[$qahm_dev['name']][ 'time_on_site' ] = round( $dev_temp['time_on_site'] / $dev_temp['time_on_site_num'] );
						} else {
							$body[$qahm_dev['name']][ 'time_on_site' ] = 0;
						}
						*/
					}
				}
			}
			
			$list['body'][] = $body;
		}

		return $list;
	}


	/*
	 * カラムを追加する
	 */
	public function add_columns( $columns ) {
		$columns['qa_access_num']  = 'QA ' . esc_html__( 'Number of Access', 'qa-heatmap-analytics' ) . '(7' . esc_html__( 'day(s)', 'qa-heatmap-analytics' ) . ')';
		$columns['qa_bounce_rate']  = 'QA ' . esc_html__( 'Bounce Rate', 'qa-heatmap-analytics' ) . '(7' . esc_html__( 'day(s)', 'qa-heatmap-analytics' ) . ')';
		//$columns['qa_time_on_site'] = 'QA ' . $this->qa_lang__( '滞在時間', 'qa-heatmap-analytics' ) . '(7' . $this->qa_lang__( '日', 'qa-heatmap-analytics' ) . ')';

		return $columns;
	}


	/*
	 * カラムの表示内容を定義
	 */
	public function add_column_param( $column_name, $post_id ) {
		if ( $column_name !== 'qa_access_num' &&
			 $column_name !== 'qa_bounce_rate' &&
			 $column_name !== 'qa_time_on_site' ) {
			return;
		}

		$cache_dir = $this->get_data_dir_path( 'cache' );
		
		global $post;
		
		$qa_type = 'p';
		if ( get_post_type( $post ) == 'post' ){
			$list_file = $this->list_file['post'];
		} elseif ( get_post_type( $post ) == 'page' ) {
			$list_file = $this->list_file['page'];
			$qa_type = 'page_id';
		} else {
			$list_file = $this->list_file['custom'];
		}

		$find = false;
		if ( $list_file && array_key_exists( 'body', $list_file ) ) {
			$list = $list_file['body'];
			foreach ( $list as $data ) {
				if ( $post_id == $data['wp_qa_id'] ) {
					switch ( $column_name ) {
						case 'qa_access_num':
							$dsk = esc_html( $data['dsk']['access_num'] );
							$tab = esc_html( $data['tab']['access_num' ] );
							$smp = esc_html( $data['smp']['access_num' ] );				
							$this->echo_device_data( $qa_type, $post_id, '', $dsk, $tab, $smp );
							break;

						case 'qa_bounce_rate':
							$dsk = esc_html( $data['dsk']['bounce_rate'] );
							$tab = esc_html( $data['tab']['bounce_rate' ] );
							$smp = esc_html( $data['smp']['bounce_rate' ] );				
							$this->echo_device_data( $qa_type, $post_id, '%', $dsk, $tab, $smp );
							break;

						case 'qa_time_on_site':
							$dsk = esc_html( $data['dsk']['time_on_site'] );
							$tab = esc_html( $data['tab']['time_on_site'] );
							$smp = esc_html( $data['smp']['time_on_site'] );	
							$this->echo_device_data( $qa_type, $post_id, esc_html__( 'second(s)', 'qa-heatmap-analytics' ), $dsk, $tab, $smp );
							break;
					}
					$find = true;
					break;
				}
			}
		}

		if ( ! $find ) {
			switch ( $column_name ) {
				case 'qa_access_num':
					$this->echo_device_data( $qa_type, $post_id, '', 0, 0, 0 );
					break;

				case 'qa_bounce_rate':
					$this->echo_device_data( $qa_type, $post_id, '%', 0, 0, 0 );
					break;

				case 'qa_time_on_site':
					$this->echo_device_data( $qa_type, $post_id, esc_html__( 'second(s)', 'qa-heatmap-analytics' ), 0, 0, 0 );
					break;
			}
		}
	}


	/*
	 * 各端末毎のデータをechoする
	 */
	private function echo_device_data( $qa_type, $post_id, $unit, $dsk, $tab, $smp ) {
		$dsk .= $unit;
		$tab .= $unit;
		$smp .= $unit;
		
		$license_plan = $this->wrap_get_option( 'license_plans' );
		if ( ! $license_plan ) {
			$find = false;
			foreach( $this->rec_table as $table ) {
				if ( $table['rec_flag'] && $table['type'] === $qa_type && (int) $table['ids'] === $post_id ) {
					$find = true;
					break;
				}
				$name = $table['rec_flag'];
			}
			if ( ! $find ) {
				$tooltip = esc_attr__( 'Data will be displayed when the page is selected to be measured in the "Heatmap Manager" screen of QA Analytics plugin.', 'qa-heatmap-analytics' );
				$tab = '<span class="qahm-tooltip" data-qahm-tooltip="' . $tooltip . '">--</span>';
				$smp = $tab;
			}
		}

		echo <<< EOH
		<table class="qahm-article-list-table">
			<tr>
				<td class="align-left"><i class="fas fa-desktop fa-fw"></i></td>
				<td class="align-right">{$dsk}</td>
			</tr>
			<tr>
				<td class="align-left"><i class="fas fa-tablet-alt fa-fw"></i></td>
				<td class="align-right">{$tab}</td>
			</tr>
			<tr>
				<td class="align-left"><i class="fas fa-mobile-alt fa-fw"></i></td>
				<td class="align-right">{$smp}</td>
			</tr>
		</table>
EOH;
	}
}
