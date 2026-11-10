/**
 * 取得ページ（cap）の中で動く補助スクリプト。#1761
 *
 * cap は sandbox="allow-scripts"（allow-same-origin なし）の iframe で表示する。中のページは
 * 「どこでもないオリジン」になるので、親画面（リプレイ等）は iframe の中を直接さわれない。
 * 親画面とのやり取りは、ここで受け付ける決まったメッセージだけで行う。
 *
 * cap-view.php が配信するときに <head> の直後へ差し込む。data-parent-origin は親画面のオリジン。
 *
 * 受け付けるメッセージ（親 → 中）:
 *   { qahmCap: 1, type: 'scroll-to', top: 数値, duration: ミリ秒 }
 * 送るメッセージ（中 → 親）:
 *   { qahmCap: 1, type: 'ready' }
 *
 * 任意の処理の実行やページの書き換えを頼むメッセージは作らない。
 */
( function () {
	'use strict';

	var script       = document.currentScript;
	var parentOrigin = script ? script.getAttribute( 'data-parent-origin' ) : null;
	if ( ! parentOrigin || window.parent === window ) {
		return;
	}

	var DURATION_MAX = 5000;

	function send( type ) {
		try {
			window.parent.postMessage( { qahmCap: 1, type: type }, parentOrigin );
		} catch ( e ) {
			// 宛先の形式が不正などで送れない場合は何もしない（表示には影響させない）
		}
	}

	// <head> の中で動き始めるので、body がまだ無いことがある
	function getScrollTop() {
		return window.pageYOffset || document.documentElement.scrollTop || ( document.body ? document.body.scrollTop : 0 ) || 0;
	}

	function getScrollLeft() {
		return window.pageXOffset || document.documentElement.scrollLeft || ( document.body ? document.body.scrollLeft : 0 ) || 0;
	}

	// ページ側の CSS の scroll-behavior: smooth に引きずられないよう、即時で動かす
	function jumpTo( top ) {
		try {
			window.scrollTo( { left: getScrollLeft(), top: top, behavior: 'instant' } );
		} catch ( e ) {
			window.scrollTo( getScrollLeft(), top );
		}
	}

	// 最後に頼まれた位置。読み込みが終わる前はページが短く、頼まれた位置まで動けないことがあるので、
	// load の後にもう一度合わせる
	var lastTop = null;
	var animId  = null;

	function scrollToTop( top, duration ) {
		lastTop = top;
		if ( animId !== null ) {
			window.cancelAnimationFrame( animId );
			animId = null;
		}

		var startTop = getScrollTop();
		if ( duration <= 0 || startTop === top ) {
			jumpTo( top );
			return;
		}

		// 以前の jQuery.animate( 'swing' ) と同じ緩急
		var startTime = null;
		function step( ts ) {
			if ( startTime === null ) {
				startTime = ts;
			}
			var p    = Math.min( 1, ( ts - startTime ) / duration );
			var ease = 0.5 - Math.cos( p * Math.PI ) / 2;
			jumpTo( startTop + ( top - startTop ) * ease );
			animId = ( p < 1 ) ? window.requestAnimationFrame( step ) : null;
		}
		animId = window.requestAnimationFrame( step );
	}

	window.addEventListener( 'message', function ( e ) {
		// 親画面から、決まった形のメッセージだけを受け付ける
		if ( e.source !== window.parent || e.origin !== parentOrigin ) {
			return;
		}
		var d = e.data;
		if ( ! d || typeof d !== 'object' || d.qahmCap !== 1 ) {
			return;
		}

		switch ( d.type ) {
			case 'scroll-to':
				var top      = Number( d.top );
				var duration = Number( d.duration );
				if ( ! isFinite( top ) || top < 0 ) {
					return;
				}
				if ( ! isFinite( duration ) || duration < 0 ) {
					duration = 0;
				}
				scrollToTop( top, Math.min( duration, DURATION_MAX ) );
				break;
		}
	} );

	window.addEventListener( 'load', function () {
		if ( lastTop !== null && animId === null && getScrollTop() !== lastTop ) {
			jumpTo( lastTop );
		}
	} );

	send( 'ready' );
}() );
