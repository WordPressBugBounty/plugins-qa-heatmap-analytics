<?php
/**
 * QAHM WordPress Base Class
 *
 * WordPress 環境に依存するラッパー関数（wrap_*）をまとめる基底クラス。
 *
 * 現時点では JSON 関連ラッパーのみを提供し、Core 層と役割分担を行う。
 * 将来的に get_option(), update_option(), wp_remote_get() など
 * WordPress 固有の安全ラッパーもここに集約する。
 *
 * @package qa_heatmap
 */
abstract class QAHM_WP_Base extends QAHM_Core_Base {

	/**
	 * WordPress 環境用の json_encode ラッパー（本体／static）
	 *
	 * @param mixed $value   エンコード対象.
	 * @param int   $options JSON オプション.
	 * @param int   $depth   最大深度.
	 * @return string|false JSON 文字列（エラー時は false）.
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
}
