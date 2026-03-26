var qahm = qahm || {};

qahm.assistantTalkNo = 0;
qahm.assistantProcessNo = 0;

createdAssistant = {};
const loadingElement = '<span class="el_loading">Loading<span></span></span>';
document.addEventListener("DOMContentLoaded", function() {
    qahm.createAssistant();
});

// キャラクターのクリックイベントを無効化するフラグ
qahm.isCharaClickDisabled = false;

qahm.createAssistant = function(assistantSlug = '', connectStep = 'start') {
    switch (connectStep) {
        case 'start':
            qahm.createAssistant(assistantSlug, 'getAllAssistant');
            break;

        case 'getAllAssistant':
            jQuery.ajax({
                type: 'POST',
                url: qahm.ajax_url,
                dataType: 'json',
                data: {
                    'action': 'qahm_ajax_get_assistant',
                    'nonce': qahm.nonce_api,
                }
            })
            .done(function(data) {
                if (data.success) {
                    qahm.allAssistants = data.data;
                    qahm.createAssistant(assistantSlug, 'renderAssistantSelector');
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.log('Error: ' + errorThrown);
            })
            .always(function() {
                // This function will always be called
            });
            break;

        case 'renderAssistantSelector':
            if (qahm.checkAssistantsPage()) {
                qahm.renderAssistantSelector();
            } else {
                qahm.createAssistant(assistantSlug, 'renderAssistantConversation');
            }
            break;

        case 'renderAssistantConversation':
            qahm.renderAssistantConversation(assistantSlug);
            qahm.createAssistant(assistantSlug, 'ajaxConnectAssistant');
            break;

        case 'ajaxConnectAssistant':
            qahm.ajaxConnectAssistant(assistantSlug, qahm.assistantTalkNo, 'start');
            qahm.assistantTalkNo++;
            break;
    }
}

qahm.checkAssistantsPage = function( ) {
    const element = document.getElementById('this_page_is_assistantpage');
    if (element) {
        return true;
    } else {
        return false;
    }
}

/**
 * アシスタント選択画面をレンダリングする
 * 
 * 利用可能なアシスタントプラグインのカードを表示し、ユーザーが選択できる
 * ようにする。アシスタントがインストールされていない場合は、空の状態画面を
 * 表示する。カードはSortable.jsでドラッグ&ドロップ可能で、順序はLocalStorageに
 * 保存される。
 * 
 * @returns {void}
 * 
 * @example
 * qahm.renderAssistantSelector();
 */
qahm.renderAssistantSelector = function() {
    const hasAssistants = Object.keys(qahm.allAssistants).length > 0;
    
    let html = `
	<div class="qa-zero-assistant-selector-inner">`;

    if (!hasAssistants) {
        const icon = `
        <svg viewBox="14 10 126 60" width="120" height="60" aria-hidden="true"
        fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
            <title>AI Assistant (Not Installed)</title>

            <!-- 吹き出し本体 -->
            <rect x="29" y="17" width="30" height="26" rx="7" ry="7"/>

            <!-- ちょん -->
            <path d="M55 43 L57 47 L51 43 Z"/>

            <!-- バツ印 -->
            <path d="M41 27 l7 7 M48 27 l-7 7"/>

            <!-- 丸い顔（右） -->
            <circle cx="92" cy="40" r="24"/>
            <circle cx="84" cy="36" r="2.4" fill="currentColor" stroke="none"/>
            <circle cx="100" cy="36" r="2.4" fill="currentColor" stroke="none"/>
            <path d="M82 47c6 4 14 4 20 0"/>
        </svg>`;

        // Differs between ZERO and QA - Start ----------
        if (qahm.type !== qahm.type_zero) {
            html += `  
            <div class="qa-zero-assistant-selector-empty-state">  
                <div class="qa-zero-assistant-selector-empty-icon">${icon}</div>  
                <h3>${qahml10n['no_assistant_installed_title']}</h3>  
                <p>${qahml10n['assistant_installation_required']}</p>  
                <a href="${qahml10n['download_assistant_url']}" class="qa-zero-assistant-selector-empty-download-button" target="_blank">  
                    ${qahml10n['download_first_assistant']}  
                </a>  
            </div>  
            `;
        }
        // Differs between ZERO and QA - End ----------
    } else {
        html += `
		<div class="qa-zero-assistant-selector-title">
			${qahml10n['select_agent']}
		</div>
		<div class="qa-zero-assistant-selector-cards">`;
        
        Object.keys(qahm.allAssistants).forEach((key) => {
		const assistant = qahm.allAssistants[key];
		html += `
			<div class="qa-zero-assistant-selector-card" data-assistant-slug="${assistant.slug}">
				<img class="qa-zero-assistant-selector-image" src="${assistant.images.default}" alt="${assistant.description}">
				<div class="qa-zero-assistant-selector-name" data-assistant-slug="${assistant.slug}">${assistant.name}</div>
				<div class="qa-zero-assistant-selector-description">${assistant.description}</div>
				<div class="qa-zero-assistant-selector-version">version ${assistant.version}</div>
			</div>`;
        });
        
        html += `
	</div>`;

        // Differs between ZERO and QA - Start ----------
        if (qahm.type !== qahm.type_zero) {
            html += `
	<a href="${qahml10n['download_assistant_url']}" 
	   class="qa-zero-assistant-selector-download-button" 
	   target="_blank">
		${qahml10n['download_more_assistants']}
	</a>`;
        }
        // Differs between ZERO and QA - End ----------
    }

    html += `
	</div>`;

    document.getElementById('qa-zero-assistant-selector').innerHTML = html;
	
	if (hasAssistants) {
		const container = document.querySelector('.qa-zero-assistant-selector-cards');
		const storageKey = 'assistant-selector-sort-order';

		function restoreOrder() {
			const savedOrder = JSON.parse(localStorage.getItem(storageKey) || '[]');
			const currentEls = Array.from(container.children);
			const currentIds = currentEls.map(el => el.getAttribute('data-assistant-slug'));

			const fragment = document.createDocumentFragment();

			savedOrder.forEach(slug => {
				if (currentIds.includes(slug)) {
				const el = container.querySelector(`[data-assistant-slug="${slug}"]`);
				if (el) fragment.appendChild(el);
				}
			});

			currentEls.forEach(el => {
				const id = el.getAttribute('data-assistant-slug');
				if (!savedOrder.includes(id)) {
				fragment.appendChild(el);
				}
			});

			container.appendChild(fragment);
		}

		function saveOrder() {
			const order = [...container.children].map(el => el.getAttribute('data-assistant-slug'));
			localStorage.setItem(storageKey, JSON.stringify(order));
		}
		
		new Sortable(container, {
			animation: 200,
			ghostClass: 'qa-zero-assistant-selector-drag-ghost',
			chosenClass: 'qa-zero-assistant-selector-drag-chosen',
			onEnd: saveOrder
		});

		restoreOrder();
	}

    // セルがクリックされたときの処理をセット
    let cards = document.querySelectorAll('.qa-zero-assistant-selector-card');
    cards.forEach(card => {
        card.addEventListener('click', function handler(event) {
            if (qahm.isCharaClickDisabled) {
                return;
            }

            qahm.isCharaClickDisabled = true;

            if (event.currentTarget.contains(event.target)) {
                let header = document.querySelector('.qa-zero-header__title');
                if ( header ) {
                    header.style.cursor = 'pointer';
                    header.addEventListener('click', function() {
                        location.reload();
                        exit;
                    });
                }

                let newAssistantSlug = card.dataset.assistantSlug;
                if (newAssistantSlug) {
                    qahm.createAssistant(newAssistantSlug, 'renderAssistantConversation');
                }
                document.getElementById('qa-zero-assistant-selector').classList.add('qa-zero-hide');
            }
        }, {once: false});

        card.addEventListener('mouseover', function() {
            card.classList.add('focused');
        });

        card.addEventListener('mouseout', function() {
            card.classList.remove('focused');
        });
    });
}

qahm.ajaxConnectAssistant = function(assistantSlug, assistantTalkNo, state = 'start', free = null, retryCount = 0) {
    let url = new URL(window.location.href);
    let params = url.searchParams;
    let tracking_id = params.get('tracking_id');

    let aiflag = false;
    if (state.substring(0, 2) === 'ai') {
        aiflag = true;
        const commandBox = document.querySelector('.qa-zero-assistant-command-box');
        if (commandBox) {
            commandBox.innerHTML = loadingElement;
        }
    }
    jQuery.ajax(
        {
            type: 'POST',
            url: qahm.ajax_url,
            dataType : 'json',
            data: {
                'action' : 'qahm_ajax_connect_assistant',
                'assistant_slug' : assistantSlug,
                'state' : state,
                'free' : free,
                'nonce': qahm.nonce_api,
                'tracking_id': tracking_id
            }
        }
    ).done(
        function( data ){
            if (data.success) {
                if (data.data.debug_logs && data.data.debug_logs.length > 0) {
                    data.data.debug_logs.forEach(function(log) {
                        const logMethod = log.level === 'error' ? 'error' : 
                                        log.level === 'warning' ? 'warn' : 'info';
                        
                        console.group(`🤖 [${log.class}] ${log.level.toUpperCase()} - ${log.state}`);
                        console[logMethod](`📝 ${log.message}`);
                        console.log(`⏰ ${new Date(log.timestamp * 1000).toLocaleString()}`);
                        
                        if (log.context && Object.keys(log.context).length > 0) {
                            console.log('📊 Context:', log.context);
                        }
                        
                        if (log.trace && log.trace.length > 0) {
                            console.log('🔍 Call Stack:', log.trace);
                        }
                        
                        console.groupEnd();
                    });
                }
                
                qahm.executeAssistant(assistantSlug, assistantTalkNo, data.data);
            } else {
                console.error("Assistant接続エラー (success=false):", data);
                if (data.data) {
                    console.error("エラー詳細:", data.data);
                    if (data.data.expected_class) {
                        console.error("期待クラス名:", data.data.expected_class);
                    }
                    if (data.data.plugin_slug) {
                        console.error("プラグインスラッグ:", data.data.plugin_slug);
                    }
                    if (data.data.error_type) {
                        console.error("エラータイプ:", data.data.error_type);
                    }
                }
            }
            if (aiflag) {
                const commandBox = document.querySelector('.qa-zero-assistant-command-box');
                if (commandBox) {
                    commandBox.innerHTML = data.success ? '' : 'Something went wrong. Retrying...';
                }
            }
        }
    ).fail(
        function( xhr, status, error ){
            console.error("Assistant接続エラー:", status, error);
            if (xhr.responseJSON && xhr.responseJSON.data) {
                console.error("エラー詳細:", xhr.responseJSON.data);
                console.error("期待クラス名:", xhr.responseJSON.data.expected_class);
                console.error("プラグインスラッグ:", xhr.responseJSON.data.plugin_slug);
            }
            if (aiflag) {
                const commandBox = document.querySelector('.qa-zero-assistant-command-box');
                if (commandBox) {
                    commandBox.innerHTML = 'Something went wrong. Retrying...';
                }
            }
            if (retryCount < 3) {
                qahm.ajaxConnectAssistant(assistantSlug, assistantTalkNo, state, null, retryCount + 1);
            } else {
                qahm.connectAssistantFailed();
                console.log('Failed to connect to assistant after 3 attempts.');
            }
        }
    ).always(
        function(){
            qahm.isCharaClickDisabled = false;
        }
    );
}

/**
 * アシスタント会話画面をレンダリングする
 * 
 * 指定されたアシスタントの会話UIを生成し、キャラクター画像、会話ウィンドウ、
 * アシスタント切り替えセレクトボックスを表示する。既存の会話コンテナがある
 * 場合は削除してから新しいものを作成する。
 * 
 * @param {string} [assistantSlug='official_robot'] - アシスタントのスラッグ
 * @returns {void}
 * 
 * @example
 * qahm.renderAssistantConversation('site-analyst');
 */
qahm.renderAssistantConversation = function( assistantSlug = 'official_robot' ) {

    // IDを指定して既存のnewDev要素を取得
    let element = document.getElementById(qahm.nowAssistantContainerId);
    if (element) {
        element.parentNode.removeChild(element);
    }

    qahm.assistantTalkNo = 0;
    qahm.assistantProcessNo = 0;
    qahm.assistantTable = undefined;

    let newDiv = document.createElement("div");
    let mainCharacterImage = '';
    let mainCharacterName = '';
    let mainCharacterVersion = '';
    let mainCharacterTooltip = '';
    let assistantSelectBoxHtml = '<select id="qa-zero-assistant-change"><option selected>-- ' + qahml10n['switch_agent'] + ' --</option>';
    for (let assistant in qahm.allAssistants) {
        if (qahm.allAssistants.hasOwnProperty(assistant)) {
            if ( assistantSlug === assistant ) {
                mainCharacterImage = qahm.allAssistants[assistant].images.default;
                mainCharacterName = qahm.allAssistants[assistant].name;
                mainCharacterVersion = qahm.allAssistants[assistant].version;
                if (typeof qahm.allAssistants[assistant].description !== 'undefined') {
                    mainCharacterTooltip = 'class="assistant-tooltip" tabindex="0" title="' + qahm.allAssistants[assistant].description + '"';
                }
            }
        }
        assistantSelectBoxHtml += '<option value="' + qahm.allAssistants[assistant].slug + '">' + qahm.allAssistants[assistant].name + '</option>';
    }
    assistantSelectBoxHtml += '<option value="reload"> ' + qahml10n['return_to_assistant_home'] + ' </option>';
    assistantSelectBoxHtml += '</select>';
    qahm.nowAssistantContainerId = `${assistantSlug}-container`;
    newDiv.innerHTML = `
<div id="${assistantSlug}-container">
<div class="qa-zero-assistant-container">
<div class="qa-zero-assistant-change-box">
${assistantSelectBoxHtml}
</div>
<div class="qa-zero-character-box">
<div class="qa-zero-main-character-box">
<div class="qa-zero-main-character-image"><img src="${mainCharacterImage}" alt="${mainCharacterName}" ${mainCharacterTooltip}></div>
</div>
</div>
<div class="qa-zero-assistant-talk-box">
<div class="qa-zero-assistant-talk-box-header">
    <div class="qa-zero-assistant-talk-box-title">
${mainCharacterName}
    </div>
</div>
<div id="qa-zero-assistant-talk-${qahm.assistantTalkNo}" class="qa-zero-assistant-dialogue-box">
</div>
</div>
</div>
</div>
`;

    // 直接新Divを対象要素に追加
    let container = document.getElementById('this_page_is_assistantpage');
    if (container) {
        container.appendChild(newDiv);
    }
    
    const selectBox = document.getElementById('qa-zero-assistant-change');
    selectBox.addEventListener('change', function(event) {
        if (qahm.isCharaClickDisabled) {
            return;
        }
        qahm.isCharaClickDisabled = true;
        let newAssistantSlug = event.target.value;
        if ( newAssistantSlug === 'reload' ) {
            location.reload();
            exit;
        }
        if (newAssistantSlug) {
            qahm.createAssistant(newAssistantSlug, 'renderAssistantConversation');
        }
    }, {once: false});
}

qahm.executeAssistant = function( assistantSlug = 'official_robot', assistantTalkNo, executeJson ) {
    processExecute(assistantSlug, assistantTalkNo, executeJson.execute);
}

const processExecute = async (assistantSlug = 'official_robot', assistantTalkNo, executeObj) => {
    const containerId = 'qa-zero-assistant-talk-' + assistantTalkNo;
    const talkElem = document.getElementById(containerId);
    
    if (talkElem === null || typeof talkElem === 'undefined') {
        console.error(`Talk element ${containerId} not found`);
        return;
    }
    
    try {
        await qahm.conversationUI.renderExecute(containerId, executeObj, {
            onCommandClick: async function(action) {
                if (action.next) {
                    if (action.next === 'start') {
                        clearConversationHistory(talkElem);
                    } else if (action.userMessage) {
                        await renderUserMessageWithDelay(action.userMessage, talkElem);
                    }
                    const free = action.free || null;
                    qahm.ajaxConnectAssistant(assistantSlug, assistantTalkNo, action.next, free);
                }
                if (action.link) {
                    window.location.href = action.link;
                }
                if (action.close === 'window') {
                    window.close();
                }
            },
            
            onNext: function(nextState) {
                qahm.ajaxConnectAssistant(assistantSlug, assistantTalkNo, nextState, null);
            },
            
            translations: {
                endCommandLabel: qahml10n['end_command_label']
            },
            
            tableRenderer: function(data, container) {
                if (typeof qahm.assistantTable === 'undefined') {
                    qahm.assistantTable = [];
                }
                
                const processNo = qahm.assistantProcessNo++;
                const tableKey = 'tb_assistant-' + processNo;
                
                const fragment = document.createDocumentFragment();
                const containerDiv = document.createElement('div');
                containerDiv.className = 'qa-zero-data-container';
                const zeroDataDiv = document.createElement('div');
                zeroDataDiv.className = 'qa-zero-data';
                containerDiv.appendChild(zeroDataDiv);
                
                if (data.title) {
                    const newTitle = document.createElement('div');
                    newTitle.textContent = data.title;
                    newTitle.id = tableKey + '-title';
                    newTitle.className = 'qa-zero-data__title';
                    zeroDataDiv.appendChild(newTitle);
                }
                
                const newDiv = document.createElement('div');
                newDiv.id = tableKey;
                zeroDataDiv.appendChild(newDiv);
                fragment.appendChild(containerDiv);
                container.appendChild(fragment);
                
                requestAnimationFrame(() => {
                    qahm.assistantTable[tableKey] = qaTable.createTable('#' + tableKey, data.header, data.option);
                    qahm.assistantTable[tableKey].updateData(data.body);
                });
            },
            
            onError: function(error, context) {
                console.error('Assistant conversation error:', error, context);
                qahm.connectAssistantFailed();
            },
            
            onMessageRendered: function(element, type) {
            },
            
            onBeforeRender: function(executeArray) {
            },
            
            onAfterRender: function() {
            }
        });
        
        // setupPxLinkEvents(talkElem);  // 一時的にコメントアウト
        
    } catch (error) {
        console.error('Failed to render assistant conversation:', error);
        qahm.connectAssistantFailed();
    }
}

qahm.connectAssistantFailed = function( ) {
    const commandBox = document.querySelector('.qa-zero-assistant-command-box');
    commandBox.innerHTML = 'Sorry, we tried 3 times but failed. Please select a Assistant again and try running it once more.';
}

qahm.getSelectorPath = function (element) {
    let path = [];
    while (element && element.parentNode) {
        let selector = element.nodeName.toLowerCase();
        if (element.id) {
            selector += '#' + element.id;
        } else if (element.className && typeof element.className === 'string') {
            selector += '.' + element.className.split(' ').join('.');
        }
        path.unshift(selector);
        element = element.parentNode;
    }
    return path.join(' > ');
}

/**
 * メッセージを会話ウィンドウに表示する
 * 
 * AIメッセージはタイプライター効果(15ms/文字)で表示され、ユーザーメッセージは
 * 瞬時に表示される。表示されたメッセージには適切なCSSクラスが適用される。
 * 
 * @param {string} html - 表示するHTMLメッセージ
 * @param {HTMLElement} dialogueBox - 会話ウィンドウのDOM要素
 * @param {boolean} [isCommand=false] - ユーザーコマンドかどうか(右寄せ表示)
 * @param {boolean} [skipTypewriter=false] - タイプライター効果をスキップするか
 * @returns {Promise<void>}
 * @throws {Error} dialogueBoxが見つからない場合
 * 
 * @example
 * // AIメッセージ(タイプライター効果あり)
 * await renderMessageWithTypewriter('<p>こんにちは</p>', talkElem, false, false);
 * 
 * // ユーザーメッセージ(瞬時表示)
 * await renderMessageWithTypewriter('トップページ', talkElem, true, true);
 */
// 一時的にコメントアウト - ヒートマップ連携機能は将来実装
/*
function convertPxToLink(text) {
    return text.replace(/(\d+)px/g, (match, p1) => {
        return `<span class="pxlink" data-scroll="${p1}" style="text-decoration: underline">${match}</span>`;
    });
}
*/

// 一時的にコメントアウト - ヒートマップ連携機能は将来実装
/*
function setupPxLinkEvents(targetElement) {
    const pxLinks = targetElement.querySelectorAll('.pxlink');
    pxLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            let iframe = document.getElementById('heatmap-iframe');
            let iframeWindow = iframe.contentWindow;
            const position = event.target.getAttribute('data-scroll');
            let windowHeight = iframeWindow.innerHeight;
            let scrollToPosition = position - (windowHeight / 2);
            iframeWindow.scrollTo({
                top: scrollToPosition,
                behavior: 'smooth'
            });
        });
    });
}
*/

/**
 * 会話履歴をクリアし、状態をリセットする
 * 
 * 会話ウィンドウの内容を削除し、トーク番号、プロセス番号、テーブル参照を
 * 初期化する。アシスタントを最初から開始する際に使用される。
 * 
 * @param {HTMLElement} dialogueBox - 会話ウィンドウのDOM要素
 * @returns {void}
 * 
 * @example
 * clearConversationHistory(talkElem);
 */
function clearConversationHistory(dialogueBox) {
    dialogueBox.innerHTML = '';
    qahm.assistantTalkNo = 0;
    qahm.assistantProcessNo = 0;
    qahm.assistantTable = undefined;
}

/**
 * ユーザーメッセージを表示し、指定時間待機する
 * 
 * コマンドボタンクリックまたは将来のチャット入力で使用される汎用関数。
 * ユーザー発言は瞬時に表示され(タイプライター効果なし)、0.3秒のフェードイン
 * アニメーションが適用される。表示後、0.3秒待機してからAI応答を取得する。
 * 
 * @param {string} userMessage - 表示するユーザーメッセージ
 * @param {HTMLElement} dialogueBox - 会話ウィンドウのDOM要素
 * @returns {Promise<void>}
 * @throws {Error} dialogueBoxが見つからない場合
 * 
 * @example
 * await renderUserMessageWithDelay('トップページ', talkElem);
 */
async function renderUserMessageWithDelay(userMessage, dialogueBox) {
    await qahm.conversationUI._displayText(
        userMessage,
        dialogueBox,
        true,  // isUser = true (ユーザーメッセージとして表示)
        {
            enableTypewriter: false,  // 瞬時表示
            onMessageRendered: function(element, type) {
            }
        }
    );
    
    await new Promise(resolve => setTimeout(resolve, 300));
}
