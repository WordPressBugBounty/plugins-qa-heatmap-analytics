/*
■ 動作仕様
・人数の更新は10秒に1回
・テーブルの更新は1分に1回

1. ページを開いたタイミングで更新
2. タブがアクティブのとき上記時間経過したら更新
3. タブが非アクティブのとき上記時間経過後タブを戻したら更新
4. タブが非アクティブ中のときは更新関数を何度も実行しないよう対応
5. タブがアクティブのとき、かつウインドウのフォーカスが非アクティブの場合でも更新
*/

var qahm = qahm || {};

qahm.updateRealtimeListCnt = 0;
qahm.updateSessionNumCnt = 0;
qahm.nextRealtimeUpdate = 0;  // 次のテーブル更新時刻を保持
qahm.nextSessionUpdate = 0;   // 次の人数更新時刻を保持

let intervalSessionNum;

// 人数とテーブルの更新をチェックして実行
function checkAndUpdate() {
    const now = new Date().getTime();

    // 人数の更新 (10秒ごと)
    if (now >= qahm.nextSessionUpdate) {
        qahm.updateSessionNum();
        qahm.nextSessionUpdate = now + 10000; // 次回更新は10秒後
    }

    // テーブルの更新 (1分ごと)
    if (now >= qahm.nextRealtimeUpdate) {
        qahm.updateRealtimeList();
        qahm.nextRealtimeUpdate = now + 60000; // 次回更新は1分後
    }
}

function startIntervals() {
    if (!intervalSessionNum) {
        intervalSessionNum = setInterval(() => {
            if (document.visibilityState === 'visible') {
                checkAndUpdate();
            }
        }, 1000 * 10); // 10秒ごとにチェックして更新
    }
}

function stopIntervals() {
    if (intervalSessionNum) {
        clearInterval(intervalSessionNum);
        intervalSessionNum = null;
    }
}

// タブの可視状態変更時のイベント
function handleVisibilityChange() {
    const now = new Date().getTime();

    if (document.visibilityState === 'visible') {
        // 1分以上経過している場合は即時更新を実行
        if (now >= qahm.nextSessionUpdate || now >= qahm.nextRealtimeUpdate) {
            checkAndUpdate();
        }
        // 定期更新を再開
        startIntervals();
    } else {
        // タブが非アクティブのとき、定期更新を停止
        stopIntervals();
    }
}

// ページがロードされたときの初期処理
window.addEventListener('DOMContentLoaded', function() {
    qahm.openReplayView();

    // ページを開いた直後の即時更新
    checkAndUpdate();

    // 次回の更新時刻を設定し、定期更新を開始
    const now = new Date().getTime();
    qahm.nextSessionUpdate = now + 10000; // 次回の人数更新時刻は10秒後
    qahm.nextRealtimeUpdate = now + 60000; // 次回のテーブル更新時刻は1分後

    startIntervals();

    // 可視状態の変更を監視
    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('focus', handleVisibilityChange);  // フォーカス時もチェック
});


window.addEventListener('DOMContentLoaded', function() {
	// create session recoding table
	sesRecHeader = [
		{ key: 'tanmatsu', label: qahml10n['table_tanmatsu'], width: 7 },
		{ key: 'ridatsujikoku', label: qahml10n['table_ridatsujikoku'], width: 15, textAlign: 'center',
			formatter: function(value, row) {
				if (!value) return '';
				// Unixタイムスタンプは秒単位なので、ミリ秒に変換
				var date = new Date(value * 1000);
				
				// 各要素を2桁に揃える
				var y = date.getFullYear();
				var m = String(date.getMonth() + 1).padStart(2, '0');
				var d = String(date.getDate()).padStart(2, '0');
				var h = String(date.getHours()).padStart(2, '0');
				var min = String(date.getMinutes()).padStart(2, '0');
				var s = String(date.getSeconds()).padStart(2, '0');
				
				return y + '/' + m + '/' + d + ' ' + h + ':' + min + ':' + s;
			}
		},
		{ key: 'landing_page_url', hidden: true },
		{ key: 'landing_page', label: qahml10n['table_1page_me'], width: 20,
			formatter: function(value, row) {
				return `<a href="${row.landing_page_url}" target="_blank" rel="noopener">${value}</a>`;
			}
		},
		{ key: 'ridatsu_page_url', hidden: true },
		{ key: 'ridatsu_page', label: qahml10n['table_ridatsu_page'], width: 20,
			formatter: function(value, row) {
				return `<a href="${row.ridatsu_page_url}" target="_blank" rel="noopener">${value}</a>`;
			}
		},
		{ key: 'referrer_url', hidden: true },
		{ key: 'referrer', label: qahml10n['table_referrer'], width: 14, formatter: function(value, row) {
			if ( value !== 'direct' && value !== qahml10n['table_total'] ) {
				ret = `<a href="${row.referrer_url}" target="_blank" rel="noopener">${value}</a>`;
			} else {
				ret = value;
			}
			return ret;
    	} },
		{ key: 'pv', label: qahml10n['table_pv'], width: 7, type: 'integer' },
		{ key: 'site_taizaijikan', label: qahml10n['table_site_taizaijikan'], width: 10, type: 'duration' },
		{ key: 'saisei', label: qahml10n['table_saisei'], width: 7, sortable: false, exportable: false, filtering: false, formatter: function(value, row) {
			return `<div class="qa-table-replay-container">
					<span class="icon-replay" data-work_base_name="${value}"><span class="dashicons dashicons-format-video"></span></span>
				</div>`;
    	} },
	];
	sesRecOptions = {
		perPage: 100,
		pagination: true,
		exportable: true,
		sortable: true,
        filtering: true,
		maxHeight: 600,
		stickyHeader: true,
		initialSort: {
			column: 'ridatsujikoku',
			direction: 'desc'
		}
	};
	sesRecTable = qaTable.createTable('#tday_table', sesRecHeader, sesRecOptions);
	sesRecTable.showLoading();
});

qahm.openReplayView = function() {
	jQuery( document ).on( 'click', '.icon-replay', function(){
		qahm.showLoadIcon();

		let start_time = new Date().getTime();
		jQuery.ajax(
			{
				type: 'POST',
				url: qahm.ajax_url,
				dataType : 'text',
				data: {
					'action'        : 'qahm_ajax_create_replay_file_to_raw_data',
					'work_base_name': jQuery( this ).data( 'work_base_name' ),
					'replay_id'     : 1,
				},
			}
		).done(
			function( url ){
				if ( url.startsWith("http")) {
					// 最低読み込み時間経過後に処理実行
					let now_time  = new Date().getTime();
					let load_time = now_time - start_time;
					let min_time  = 400;

					if ( load_time < min_time ) {
						// ロードアイコンを削除して新しいウインドウを開く
						setTimeout(
							function(){
								window.open( url, '_blank' );
							},
							(min_time - load_time)
						);
					} else {
						window.open( url, '_blank' );
					}
				} else {
					AlertMessage.alert(
						qahml10n['realtime_replay_alert1'],
						qahml10n['realtime_replay_alert2'],
						'error',
						function(){}
					);
				}
			}
		).fail(
			function( jqXHR, textStatus, errorThrown ){
				qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
			}
		).always(
			function(){
				qahm.hideLoadIcon();
			}
		);
	});
}

qahm.updateSessionNum = function() {
	if ( jQuery('#session_num').length === 0 || qahm.updateSessionNumCnt > 0 ) {
		return;
	}
	qahm.updateSessionNumCnt++;

	jQuery.ajax(
		{
			type: 'POST',
			url: qahm.ajax_url,
			dataType : 'json',
			data: {
				'action' : 'qahm_ajax_get_session_num',
			},
		}
	).done(
		function( data ){
			if ( data ) {
                jQuery('#session_num').text(data['session_num']);
                jQuery('#session_num_1min').text(data['session_num_1min']);
            }
		}
	).fail(
		function( jqXHR, textStatus, errorThrown ){
			jQuery( '#session_num' ).text( 'please reload' );
			jQuery( '#session_num_1min' ).text( 'please reload' );
			qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
		}
	).always(
		function(){
			qahm.updateSessionNumCnt--;
		}
	);
}


qahm.updateRealtimeList = function() {
	if ( qahm.updateRealtimeListCnt > 0 ) {
		return;
	}
	qahm.updateRealtimeListCnt++;

	jQuery.ajax(
		{
			type: 'POST',
			url: qahm.ajax_url,
			dataType : 'json',
			data: {
				'action' : 'qahm_ajax_get_realtime_list',
			},
		}
	).done(
		function( data ){
			if ( ! data ) {
				sesRecTable.updateData([]);
				return;
			}
			if (typeof sesRecTable !== 'undefined' && sesRecTable !== '') {
				if ( data['realtime_list'].length > 0 ) {
					jQuery( '#update_time' ).hide().text(data['update_time']).fadeIn(4000,'swing');
					sesRecTable.updateData(data['realtime_list']);
				}
			}
		}
	).fail(
		function( jqXHR, textStatus, errorThrown ){
			jQuery( '#update_time' ).text( 'please reload' );
			qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
		}
	).always(
		function(){
			qahm.updateRealtimeListCnt--;
		}
	);
}


/**-------------------------------
 * to clear the chart
 */
 qahm.clearPreChart = function(chartVar) {
	if ( typeof chartVar !== 'undefined' ) {
		chartVar.destroy();
	}
}
qahm.resetCanvas = function(canvasId) {
  let container = document.getElementById(canvasId).parentNode;
	container.innerHTML = '&nbsp;';
	container.innerHTML = `<canvas id="${canvasId}"></canvas>`;
}