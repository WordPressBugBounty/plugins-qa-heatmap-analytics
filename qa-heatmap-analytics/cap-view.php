<?php
/**
 * ヒートマップ・リプレイの iframe が読む「取得 HTML（cap）」を配信する。T143b
 *
 * 以前は cap を `-cap.php`（先頭に権限チェックの PHP 1 行＋取得 HTML）としてデータ領域
 * （wp-content/qa-zero-data/）に置き、データ領域で PHP を実行させていた。これがデータ領域の
 * PHP 実行を許す唯一の正当な理由だった。T143b で cap を `-cap.html` にし、権限チェックと
 * 配信をこのファイル（プラグイン領域）へ移した。これでデータ領域の .php は丸ごと実行禁止に
 * できる（Apache: qa-zero-data/.htaccess、nginx: docs/infra/data-dir-no-exec.md）。
 *
 * パラメータ:
 *   kind=heatmap & file_base_name={version_id}_{YmdHis}_{YmdHis}_{0|1}_{tracking_id}
 *   kind=replay  & work_base_name={readers 名 or pv_id} & replay_id=N
 *   qahm_view_mode=1 は iframe 内の qtag.js が読むために付いている（ここでは使わない）
 *
 * 失敗はすべて 404（権限なし・形式不正・ファイルなしを区別しない。旧 -cap.php と同じ振る舞い）。
 */
if ( ! defined( 'ABSPATH' ) ) {
	// Standalone file: loaded directly by the heatmap / replay iframe. Do not exit.
}

$qahm_cap_kind = filter_input( INPUT_GET, 'kind' );

$config_path = dirname( __DIR__, 3 ) . '/wp-content/qa-zero-data/qa-config.php';
if ( file_exists( $config_path ) ) {
	require_once $config_path;
}
if ( defined( 'QAHM_CONFIG_WP_ROOT_PATH' ) && file_exists( QAHM_CONFIG_WP_ROOT_PATH . 'wp-load.php' ) ) {
	require_once QAHM_CONFIG_WP_ROOT_PATH . 'wp-load.php';
	require_once QAHM_CONFIG_WP_ROOT_PATH . 'wp-settings.php';
} else {
	require_once '../../../wp-load.php';
	require_once '../../../wp-settings.php';
}

global $wp_filesystem;
global $qahm_view_heatmap;
global $qahm_view_replay;

$qahm_cap_path = '';

if ( 'heatmap' === $qahm_cap_kind && $qahm_view_heatmap ) {
	if ( ! $qahm_view_heatmap->check_access_role( 'qahm_analytics' ) ) {
		http_response_code( 404 );
		exit;
	}
	$qahm_cap_name = filter_input( INPUT_GET, 'file_base_name' );
	// 作業ファイル名に入る値は、作る側（create_heatmap_file）の形式だけを受け付ける
	if ( ! $qahm_view_heatmap->is_valid_heatmap_file_base_name( $qahm_cap_name ) ) {
		http_response_code( 404 );
		exit;
	}
	$qahm_cap_path = $qahm_view_heatmap->get_data_dir_path( 'heatmap-view-work' ) . $qahm_cap_name . '-cap.html';
} elseif ( 'replay' === $qahm_cap_kind && $qahm_view_replay ) {
	if ( ! $qahm_view_replay->check_access_role( 'qahm_analytics' ) ) {
		http_response_code( 404 );
		exit;
	}
	$qahm_cap_base = filter_input( INPUT_GET, 'work_base_name' );
	$qahm_cap_id   = (int) filter_input( INPUT_GET, 'replay_id' );
	if ( ! $qahm_view_replay->is_valid_replay_work_base_name( $qahm_cap_base ) || $qahm_cap_id < 1 ) {
		http_response_code( 404 );
		exit;
	}
	$qahm_cap_path = $qahm_view_replay->get_data_dir_path( 'replay-view-work' ) . $qahm_cap_base . '_' . $qahm_cap_id . '-cap.html';
} else {
	http_response_code( 404 );
	exit;
}

if ( ! $wp_filesystem->exists( $qahm_cap_path ) ) {
	http_response_code( 404 );
	exit;
}

// cap は純粋な HTML なので、PHP ヘッダを剥がす wrap_get_contents ではなく素の get_contents で読む
$qahm_cap_html = $wp_filesystem->get_contents( $qahm_cap_path );
if ( false === $qahm_cap_html ) {
	http_response_code( 404 );
	exit;
}

// #1761: リプレイは、取得ページを別オリジン（sandbox）に閉じ込めて表示する。
// 親画面は iframe の中を直接さわれなくなるので、やり取りをする補助スクリプト（cap-bridge.js）を差し込む。
// ヒートマップは親画面の作り直しが済むまで今のまま（CSP の sandbox を先に付けると画面が壊れる）。
if ( 'replay' === $qahm_cap_kind ) {
	$qahm_cap_plugin_url = plugin_dir_url( __FILE__ );
	$qahm_cap_url_parts  = wp_parse_url( $qahm_cap_plugin_url );
	// 親画面（replay-view.php）も plugin_dir_url() から開かれるので、親画面のオリジンはこれと同じ
	$qahm_cap_parent_origin = '';
	if ( isset( $qahm_cap_url_parts['scheme'], $qahm_cap_url_parts['host'] ) ) {
		$qahm_cap_parent_origin = $qahm_cap_url_parts['scheme'] . '://' . $qahm_cap_url_parts['host']
			. ( isset( $qahm_cap_url_parts['port'] ) ? ':' . $qahm_cap_url_parts['port'] : '' );
	}
	// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Standalone response outside the WP page lifecycle; the helper script must be inserted into the fetched HTML.
	$qahm_cap_bridge_tag = '<script src="' . esc_url( $qahm_cap_plugin_url . 'js/cap-bridge.js?ver=' . QAHM_PLUGIN_VERSION ) . '" data-parent-origin="' . esc_attr( $qahm_cap_parent_origin ) . '"></script>';

	// ページのスクリプトより先に動くよう、<head> の直後に入れる。<head> が無ければ <html> の直後、
	// それも無ければ <!doctype> の直後（先頭に入れると doctype より前になり、互換モードで表示が変わる）
	$qahm_cap_inserted = false;
	foreach ( array( '/<head\b[^>]*>/i', '/<html\b[^>]*>/i', '/^\s*<!doctype\b[^>]*>/i' ) as $qahm_cap_pattern ) {
		$qahm_cap_replaced = preg_replace_callback(
			$qahm_cap_pattern,
			function ( $m ) use ( $qahm_cap_bridge_tag ) {
				return $m[0] . $qahm_cap_bridge_tag;
			},
			$qahm_cap_html,
			1,
			$qahm_cap_count
		);
		if ( null !== $qahm_cap_replaced && $qahm_cap_count > 0 ) {
			$qahm_cap_html     = $qahm_cap_replaced;
			$qahm_cap_inserted = true;
			break;
		}
	}
	if ( ! $qahm_cap_inserted ) {
		$qahm_cap_html = $qahm_cap_bridge_tag . $qahm_cap_html;
	}

	// iframe の sandbox 属性と同じ制限を、応答そのものにもかける（URL を直接開いた場合も別オリジンになる）
	header( 'Content-Security-Policy: sandbox allow-scripts' );
}

header( 'Content-Type: text/html; charset=utf-8' );
header( 'X-Content-Type-Options: nosniff' );
header( 'X-Robots-Tag: noindex, nofollow' );
header( 'Cache-Control: private, no-store' );
// 計測対象ページの取得 HTML をそのまま iframe に出す（画面の本体。escape すると壊れる）
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $qahm_cap_html;
exit;
