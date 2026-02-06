/**
 * AIレポート画面のJavaScript
 * Handles AI report UI functionality and CSV queue callbacks
 */

jQuery(document).ready(function($) {
    'use strict';

    if (typeof qahm !== 'undefined' && typeof qahm.initDateSetting === 'function' && typeof qahm.setDateRangePicker === 'function') {
        qahm.initDateSetting();
        qahm.setDateRangePicker();
    }

    if (typeof qahm !== 'undefined' && typeof qahm.setDateRangePicker === 'function') {
        initializeFreeExtractionDatePicker();
    }

    if (typeof qahm !== 'undefined' && qahm.ajax_url && qahm.nonce_api) {
        QAHMReportManager.initializeCompletedReportsTable();
        QAHMReportManager.refreshQueueDisplay();

        setInterval(function() {
            QAHMReportManager.refreshQueueDisplay();
        }, 30000);
    }

    jQuery('.query-button').on('click', function() {
        var buttonId = jQuery(this).attr('id');
        var buttonText = jQuery(this).find('.query-text').text();
        
        var dateRange = getDateRangeFromPicker('#datepicker-base-textbox');
        if (!dateRange) {
            alert('日付範囲を選択してください。');
            return;
        }
        
        var reportTypeMap = {
            'query-seo-pages': 'seo',
            'query-ad-visitors': 'ads',
            'query-popular-pages': 'popular',
            'query-cv-pages': 'cv',
            'query-repeat-visitors': 'repeat'
        };
        
        var reportType = reportTypeMap[buttonId];
        if (!reportType) {
            alert('不明なレポートタイプです。');
            return;
        }
        
        var message = 'クエリ: ' + buttonText + '\n';
        message += '期間: ' + dateRange.start + ' 〜 ' + dateRange.end + '\n';
        message += 'このクエリを実行しますか？';
        
        if (confirm(message)) {
            generateAIReport(reportType, dateRange.start, dateRange.end, buttonText);
        }
        
        jQuery(this).addClass('clicked');
        setTimeout(function() {
            jQuery('.query-button').removeClass('clicked');
        }, 200);
    });

    jQuery('#generate-report').on('click', function() {
        var dateRange = getDateRangeFromPicker('#datepicker-free-extraction-textbox');
        if (!dateRange) {
            alert('日付範囲を選択してください。');
            return;
        }
        
        var dataSources = [];
        jQuery('input[name="data_source"]:checked').each(function() {
            dataSources.push(jQuery(this).val());
        });
        
        var pageFilter = jQuery('#page-filter').val();
        var sourceFilter = jQuery('#source-filter').val();
        var userFilter = jQuery('#user-filter').val();
        
        if (dataSources.length === 0) {
            alert('データソースを選択してください。');
            return;
        }
        
        var message = '自由抽出レポート\n';
        message += '期間: ' + dateRange.start + ' 〜 ' + dateRange.end + '\n';
        message += 'データソース: ' + dataSources.join(', ') + '\n';
        message += 'このレポートを生成しますか？';
        
        if (confirm(message)) {
            generateAIReport('free_extraction', dateRange.start, dateRange.end, '自由抽出レポート');
        }
        
        jQuery(this).addClass('clicked');
        setTimeout(function() {
            jQuery('#generate-report').removeClass('clicked');
        }, 200);
    });

    jQuery('input[name="data_source"]').on('change', function() {
        var checkedCount = jQuery('input[name="data_source"]:checked').length;
        
        if (checkedCount === 0) {
            jQuery(this).prop('checked', true);
            alert('データソースは最低1つ選択してください。');
        }
    });

    jQuery('#start-date, #end-date').on('change', function() {
        var startDate = new Date(jQuery('#start-date').val());
        var endDate = new Date(jQuery('#end-date').val());
        
        if (startDate && endDate && startDate > endDate) {
            alert('開始日は終了日より前の日付を選択してください。');
            
            if (jQuery(this).attr('id') === 'start-date') {
                jQuery(this).val(jQuery('#end-date').val());
            } else {
                jQuery(this).val(jQuery('#start-date').val());
            }
        }
    });

    jQuery('.filter-input').on('focus', function() {
        jQuery(this).addClass('focused');
    }).on('blur', function() {
        jQuery(this).removeClass('focused');
    });

    function initializeAiReport() {
        var today = new Date();
        var firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        var lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        var formatDate = function(date) {
            var year = date.getFullYear();
            var month = String(date.getMonth() + 1).padStart(2, '0');
            var day = String(date.getDate()).padStart(2, '0');
            return year + '-' + month + '-' + day;
        };
        
        if (!jQuery('#start-date').val()) {
            jQuery('#start-date').val(formatDate(firstDay));
        }
        if (!jQuery('#end-date').val()) {
            jQuery('#end-date').val(formatDate(lastDay));
        }
        
        console.log('AI Report page initialized');
    }

    initializeAiReport();

    function initializeFreeExtractionDatePicker() {
        var rangeStartDate = moment().subtract(30, 'days');
        var rangeEndDate = moment();
        
        var daterangeOpt = {
            startDate: rangeStartDate,
            endDate: rangeEndDate,
            locale: {
                format: 'YYYY/MM/DD',
                separator: ' - ',
                applyLabel: '適用',
                cancelLabel: 'キャンセル',
                fromLabel: '開始',
                toLabel: '終了',
                customRangeLabel: 'カスタム',
                weekLabel: 'W',
                daysOfWeek: ['日', '月', '火', '水', '木', '金', '土'],
                monthNames: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
                firstDay: 1
            },
            ranges: {
                '今日': [moment(), moment()],
                '昨日': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '過去7日間': [moment().subtract(6, 'days'), moment()],
                '過去30日間': [moment().subtract(29, 'days'), moment()],
                '今月': [moment().startOf('month'), moment().endOf('month')],
                '先月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        };

        function updateFreeExtractionDateRangeTextbox(start, end) {
            jQuery('#datepicker-free-extraction-textbox').val(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'));
        }

        jQuery('#datepicker-free-extraction-textbox').daterangepicker(daterangeOpt, updateFreeExtractionDateRangeTextbox);
        jQuery('#datepicker-free-extraction-textbox').on('apply.daterangepicker', function(ev, picker) {
            updateFreeExtractionDateRangeTextbox(picker.startDate, picker.endDate);
        });
        updateFreeExtractionDateRangeTextbox(rangeStartDate, rangeEndDate);
    }

    function getDateRangeFromPicker(selector) {
        var dateRangeText = jQuery(selector).val();
        if (!dateRangeText) {
            return null;
        }
        
        var dates = dateRangeText.split(' - ');
        if (dates.length !== 2) {
            return null;
        }
        
        return {
            start: moment(dates[0], 'YYYY/MM/DD').format('YYYY-MM-DD'),
            end: moment(dates[1], 'YYYY/MM/DD').format('YYYY-MM-DD')
        };
    }

    function generateAIReport(buttonType, startDate, endDate, reportName) {
        var $button = jQuery('button:contains("' + reportName + '")');
        var originalText = $button.html();
        
        $button.prop('disabled', true).html('<span class="loading-spinner">⏳</span> 処理中...');
        
        var ajaxData = {
            action: 'qahm_ajax_generate_ai_report',
            button_type: buttonType,
            start_date: startDate,
            end_date: endDate,
            tracking_id: qahm_ajax_obj.tracking_id || 'all',
            nonce: qahm_ajax_obj.nonce_api
        };
        
        jQuery.ajax({
            url: qahm_ajax_obj.ajax_url,
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showReportCompletionNotification(reportName + 'の生成を開始しました');
                    setTimeout(function() {
                        QAHMReportManager.refreshQueueDisplay();
                    }, 1000);
                } else {
                    alert('エラー: ' + (response.message || 'レポート生成に失敗しました'));
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAXエラー", status, error);
                console.error("レスポンス本文:", xhr.responseText);
                alert('通信エラーが発生しました: ' + error);
            },
            complete: function() {
                jQuery(button).prop('disabled', false).html(originalText);
            }
        });
    }

    function showReportCompletionNotification(message) {
        var notification = jQuery('<div class="report-notification">')
            .text(message)
            .css({
                'position': 'fixed',
                'top': '20px',
                'right': '20px',
                'background': '#4CAF50',
                'color': 'white',
                'padding': '15px',
                'border-radius': '5px',
                'z-index': '9999'
            });
        
        jQuery('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(function() {
                jQuery(this).remove();
            });
        }, 5000);
    }

    jQuery(document).on('click', '#select-all-reports', function() {
        QAHMReportManager.toggleSelectAll();
    });

    jQuery(document).on('click', '#delete-selected-reports', function() {
        QAHMReportManager.deleteSelectedReports();
    });
});

jQuery(document).ready(function($) {
    var clickedStyle = `
        <style>
        .query-button.clicked,
        .report-output-button.clicked {
            transform: scale(0.98) !important;
            transition: transform 0.1s ease !important;
        }
        </style>
    `;
    jQuery('head').append(clickedStyle);
});

function qahmReportComplete(reportType, period) {
    console.log('Report completed:', reportType, period);
    
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            var message = 'レポート生成が完了しました: ' + reportType + ' (' + period + ')';
            
            var notification = jQuery('<div class="qahm-report-notification">')
                .text(message)
                .css({
                    'position': 'fixed',
                    'top': '20px',
                    'right': '20px',
                    'background': '#00a32a',
                    'color': 'white',
                    'padding': '10px 20px',
                    'border-radius': '4px',
                    'z-index': '9999',
                    'box-shadow': '0 2px 5px rgba(0,0,0,0.2)'
                });
            
            jQuery('body').append(notification);
            
            setTimeout(function() {
                notification.fadeOut(500, function() {
                    notification.remove();
                });
            }, 5000);
            
            jQuery(document).trigger('qahmReportCompleted', {
                reportType: reportType,
                period: period
            });
        });
    }
}

var QAHMReportManager = {
    completedReportsTable: null,
    
    initializeCompletedReportsTable: function() {
        const columns = [
            { key: 'check', label: '', type: 'check', width: 5, typeOptions: { maxSelections: 100 }, exportable: false, filtering: false },
            { key: 'title', label: 'タイトル', type: 'string', width: 40, sortable: true, filterable: true },
            { key: 'created_at', label: '作成日', type: 'date', width: 15, sortable: true, filterable: true },
            { key: 'download', label: 'ダウンロード', type: 'html', width: 15, exportable: false, filtering: false },
            { key: 'spreadsheet', label: 'レポート生成', type: 'html', width: 20, exportable: false, filtering: false }
        ];
        
        const options = {
            pagination: true,
            perPage: 10,
            sortable: true,
            filtering: true,
            exportable: false,
            maxHeight: 400,
            initialSort: {
                column: 'created_at',
                direction: 'desc'
            }
        };
        
        this.completedReportsTable = qaTable.init('#completed-reports-table-container', [], columns, options);
    },
    checkQueueStatus: function(queueId) {
        console.log('Checking queue status for:', queueId);
    },
    
    downloadReport: function(queueId) {
        console.log('Downloading report:', queueId);
    },

    refreshQueueDisplay: function() {
        this.loadProcessingQueues();
        this.loadCompletedQueues();
    },

    loadProcessingQueues: function() {
        var self = this;
        jQuery.ajax({
            url: qahm.ajax_url,
            type: 'POST',
            data: {
                action: 'qahm_ajax_get_processing_queues',
                tracking_id: qahm.tracking_id || 'all',
                nonce: qahm.nonce_api
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    self.displayProcessingQueues(response.data);
                } else {
                    self.displayProcessingQueues([]);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAXエラー", status, error);
                console.error("レスポンス本文:", xhr.responseText);
                self.displayProcessingQueues([]);
            }
        });
    },

    loadCompletedQueues: function() {
        var self = this;
        jQuery.ajax({
            url: qahm.ajax_url,
            type: 'POST',
            data: {
                action: 'qahm_ajax_get_completed_queues',
                tracking_id: qahm.tracking_id || 'all',
                nonce: qahm.nonce_api
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    self.displayCompletedQueues(response.data);
                } else {
                    self.displayCompletedQueues([]);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAXエラー", status, error);
                console.error("レスポンス本文:", xhr.responseText);
                self.displayCompletedQueues([]);
            }
        });
    },

    displayProcessingQueues: function(queues) {
        var $container = jQuery('#processing-queue-list');
        var $noMessage = jQuery('#no-processing-message');
        
        $container.empty();
        
        if (queues.length === 0) {
            $noMessage.show();
            return;
        }
        
        $noMessage.hide();
        
        queues.forEach(function(queue) {
            var progress = queue.progress || 0;
            var $item = jQuery('<div class="queue-processing-item">');
            
            $item.html(
                '<div class="queue-item-header">' +
                    '<span class="queue-item-title">' + (queue.title || queue.type || 'レポート') + '</span>' +
                    '<button class="cancel-queue-btn" data-queue-id="' + queue.id + '">キャンセル</button>' +
                '</div>' +
                '<div class="queue-progress-container">' +
                    '<div class="queue-progress-bar">' +
                        '<div class="queue-progress-fill" style="width: ' + progress + '%"></div>' +
                    '</div>' +
                    '<span class="queue-progress-text">' + progress + '%</span>' +
                '</div>' +
                '<div class="queue-item-details">' +
                    '<span class="queue-date-range">' + (queue.start_date || '') + ' 〜 ' + (queue.end_date || '') + '</span>' +
                '</div>'
            );
            
            $container.append($item);
        });
        
        jQuery('.cancel-queue-btn').on('click', function() {
            var queueId = jQuery(this).data('queue-id');
            QAHMReportManager.cancelQueue(queueId);
        });
    },

    displayCompletedQueues: function(queues) {
        if (!this.completedReportsTable) {
            this.initializeCompletedReportsTable();
        }
        
        const tableData = queues.map(function(queue) {
            const createdDate = queue.created_at ? new Date(queue.created_at).toLocaleDateString('ja-JP') : '';
            
            return {
                id: queue.id,
                check: queue.id, // Used for checkbox identification
                title: queue.title || queue.type || 'レポート',
                created_at: createdDate,
                download: '<a href="#" class="download-link" data-queue-id="' + queue.id + '">ダウンロード</a>',
                spreadsheet: '<button type="button" class="spreadsheet-open-btn" data-queue-id="' + queue.id + '">スプレッドシートで開く</button>'
            };
        });
        
        this.completedReportsTable.updateData(tableData);
        
        this.attachCompletedReportsEventHandlers();
    },
    
    attachCompletedReportsEventHandlers: function() {
        jQuery('.download-link').off('click').on('click', function(e) {
            e.preventDefault();
            var queueId = jQuery(this).data('queue-id');
            QAHMReportManager.downloadReport(queueId);
        });
        
        jQuery('.spreadsheet-open-btn').off('click').on('click', function(e) {
            e.preventDefault();
            var queueId = jQuery(this).data('queue-id');
            console.log('Open in spreadsheet clicked for queue:', queueId);
            alert('スプレッドシート機能は今後実装予定です。');
        });
    },

    cancelQueue: function(queueId) {
        if (!confirm('このレポート生成をキャンセルしますか？')) {
            return;
        }
        
        var self = this;
        jQuery.ajax({
            url: qahm.ajax_url,
            type: 'POST',
            data: {
                action: 'qahm_ajax_cancel_queue',
                queue_id: queueId,
                nonce: qahm.nonce_api
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    self.refreshQueueDisplay();
                } else {
                    alert('キャンセルに失敗しました: ' + (response.message || ''));
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAXエラー", status, error);
                console.error("レスポンス本文:", xhr.responseText);
                alert('通信エラーが発生しました');
            }
        });
    },

    deleteSelectedReports: function() {
        if (!this.completedReportsTable) {
            return;
        }
        
        const selectedData = this.completedReportsTable.getCheckedData('check');
        const selectedIds = selectedData.map(function(item) {
            return item.id;
        });
        
        if (selectedIds.length === 0) {
            alert('削除するレポートを選択してください。');
            return;
        }
        
        if (!confirm(selectedIds.length + '件のレポートを削除しますか？')) {
            return;
        }
        
        var self = this;
        jQuery.ajax({
            url: qahm.ajax_url,
            type: 'POST',
            data: {
                action: 'qahm_ajax_delete_reports',
                queue_ids: selectedIds,
                nonce: qahm.nonce_api
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    self.refreshQueueDisplay();
                } else {
                    alert('削除に失敗しました: ' + (response.message || ''));
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAXエラー", status, error);
                console.error("レスポンス本文:", xhr.responseText);
                alert('通信エラーが発生しました');
            }
        });
    },

    toggleSelectAll: function() {
        if (!this.completedReportsTable) {
            return;
        }
        
    }
};
