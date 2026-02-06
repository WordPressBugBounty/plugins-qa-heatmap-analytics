window.addEventListener( 'load', loadFinished );

function loadFinished() {

    let sm_table_all_cbx = document.getElementById("sm_table_all_cbx");
    sm_table_all_cbx.addEventListener("change",function(){

        let sm_table_cbxs = document.getElementsByClassName("sm_table_cbx");
        for(let i=0;i<sm_table_cbxs.length;i++){
            sm_table_cbxs[i].checked = this.checked;
        }

    }
    );

}

function SaveSitemanage( formobj ) {

    let domain_url = formobj['domain_url'].value;
    let domain_url_add_btn = document.getElementById("domain_url_add_btn");
    domain_url_add_btn.disabled = true;

    jQuery.ajax(
        {
            type: 'POST',
            url: qahm.ajax_url,
            dataType : 'json',
            data: {
                'action': 'qahm_ajax_set_sitemanage_domainurl',
                'url':   domain_url,
                'nonce' : qahm.nonce_api
            },
        }
    ).done(
        function( data ){
            let data_stringify = JSON.stringify(data);
            let data_json = JSON.parse(data_stringify);
            if(data_json["result"]!='success'){

                if(data_json["result"]=='registed_domainurl'){
					alert(qahml10n['registed_domainurl']);
				} else if(data_json["result"]!='success'){
                    alert(qahml10n['invalid_domainurl']);
                }else{

                }
            }
        }
    ).fail(
        function( jqXHR, textStatus, errorThrown ){
            alert(qahml10n['domainurl_add_failed']);
            qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
        }
    ).always(
        function(){
            location.reload();
        }
    );

}

function SetSmIgnoreParams( formobj ){

    let ignore_params = formobj['ignore_params'].value;
    let sid           = formobj['sid'].value;

    jQuery.ajax(
        {
            type: 'POST',
            url: qahm.ajax_url,
            dataType : 'json',
            data: {
                'action': 'qahm_ajax_set_sitemanage_ignore_params',
                'ignore_params':   ignore_params,
                'sid':   sid,
                'nonce' : qahm.nonce_api
            },
        }
    ).done(
        function( data ){
            let data_stringify = JSON.stringify(data);
            let data_json = JSON.parse(data_stringify);
            if(data_json["result"]!='success'){
                alert(qahml10n['ignore_param_add_failed']);
            }
        }
    ).fail(
        function( jqXHR, textStatus, errorThrown ){
            alert(qahml10n['ignore_param_add_failed']);
            qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
        }
    ).always(
        function(){
            location.reload();
        }
    );

}

function SetSmIgnoreIps( formobj ){

    let ignore_ip   = formobj['ignore_ip'].value;
    let sid          = formobj['sid'].value;

    let isValidIpAddress = /^([0-9]{1,3}\.){3}[0-9]{1,3}$/.test(ignore_ip);

    if(!isValidIpAddress){
        alert(qahml10n['invalid_ip_adress']);
        return;
    }

    jQuery.ajax(
        {
            type: 'POST',
            url: qahm.ajax_url,
            dataType : 'json',
            data: {
                'action': 'qahm_ajax_set_sitemanage_ignore_ips',
                'ignore_ip':   ignore_ip,
                'sid':   sid,
                'nonce' : qahm.nonce_api
            },
        }
    ).done(
        function( data ){
            let data_stringify = JSON.stringify(data);
            let data_json = JSON.parse(data_stringify);
            if(data_json["result"]!='success'){
                alert(qahml10n['ignore_ip_add_failed']);
            }
        }
    ).fail(
        function( jqXHR, textStatus, errorThrown ){
            alert(qahml10n['ignore_ip_add_failed']);
            qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
        }
    ).always(
        function(){
            location.reload();
        }
    );

}

function DeleteSitemanages(){

    let sm_table_cbxs = document.getElementsByClassName("sm_table_cbx");
    let sid_array     = [];

    for(let i=0;i<sm_table_cbxs.length;i++){
        if(sm_table_cbxs[i].checked){
            let sid = sm_table_cbxs[i].dataset.sid;
            sid_array.push(sid);
        }
    }
    
    if( sid_array.length > 0 ){

        let sids_json = JSON.stringify(sid_array);

        jQuery.ajax(
            {
                type: 'POST',
                url: qahm.ajax_url,
                dataType : 'json',
                data: {
                    'action': 'qahm_ajax_delete_sitemanages',
                    'sids_json':   sids_json,
                    'nonce' : qahm.nonce_api
                },
            }
        ).done(
            function( data ){
                let data_stringify = JSON.stringify(data);
                let data_json = JSON.parse(data_stringify);
                if(data_json["result"]!='success'){
                    alert(qahml10n['sitemaneges_delete_failed']);
                }
            }
        ).fail(
            function( jqXHR, textStatus, errorThrown ){
                alert(qahml10n['sitemaneges_delete_failed']);
                qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
            }
        ).always(
            function(){
                location.reload();
            }
        );
    }

}

function SetSmMemo( formobj ) {

    let sm_memo = formobj['sm_memo'].value;
    let sid     = formobj['sid'].value;
    
    
    jQuery.ajax(
        {
            type: 'POST',
            url: qahm.ajax_url,
            dataType : 'json',
            data: {
                'action': 'qahm_ajax_set_sitemanage_memo',
                'sm_memo':   sm_memo,
                'sid':    sid,
                'nonce' : qahm.nonce_api
            },
        }
    ).done(
        function( data ){
            let data_stringify = JSON.stringify(data);
            let data_json = JSON.parse(data_stringify);
            if(data_json["result"]!='success'){
                alert(qahml10n['memo_set_failed']);
            }
        }
    ).fail(
        function( jqXHR, textStatus, errorThrown ){
            alert(qahml10n['memo_set_failed']);
            qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
        }
    ).always(
        function(){
            location.reload();
        }
    );
}

function ClickSmbulkAction(){

    let bulk_selecter = document.getElementById("sm-bulk-act-selector");

    if( bulk_selecter.value == "delete" ){
        DeleteSitemanages();
    }

}



function UrlCaseRadioChange(event){

    let sid   = event.target.dataset.sid;
    let value = event.target.value;

    jQuery.ajax(
        {
            type: 'POST',
            url: qahm.ajax_url,
            dataType : 'json',
            data: {
                'action': 'qahm_ajax_set_sitemanage_url_case',
                'value':   value,
                'sid':    sid,
                'nonce' : qahm.nonce_api
            },
        }
    ).done(
        function( data ){
            let data_stringify = JSON.stringify(data);
            let data_json = JSON.parse(data_stringify);
            if(data_json["result"]!='success'){
                alert(qahml10n['url_case_sensitivity_set_failed']);
            }
        }
    ).fail(
        function( jqXHR, textStatus, errorThrown ){
            alert(qahml10n['url_case_sensitivity_set_failed']);
            qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
        }
    ).always(

    );

}

function GetBasehtmlPChange( event ){

    let sid   = event.target.dataset.sid;
    let value = event.target.value;

    jQuery.ajax(
        {
            type: 'POST',
            url: qahm.ajax_url,
            dataType : 'json',
            data: {
                'action': 'qahm_ajax_set_sitemanage_base_html_p',
                'value':   value,
                'sid':    sid,
                'nonce' : qahm.nonce_api
            },
        }
    ).done(
        function( data ){
            let data_stringify = JSON.stringify(data);
            let data_json = JSON.parse(data_stringify);
            if(data_json["result"]!='success'){
                alert(qahml10n['base_html_p_set_failed']);
            }
        }
    ).fail(
        function( jqXHR, textStatus, errorThrown ){
            alert(qahml10n['base_html_p_set_failed']);
            qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
        }
    ).always(

    );
}

function ChangeXdmcheck( event ){

    let sid   = event.target.dataset.sid;
    let t_tag_link = document.getElementById("t_tag_"+sid);
    let t_tagc_link = document.getElementById("t_tagc_"+sid);

    if(event.target.checked){
        t_tag_link.hidden = true;
        t_tagc_link.hidden = false;
    }else{
        t_tag_link.hidden = false;
        t_tagc_link.hidden = true;
    }

}

function ChangeCookieMcheck( event ){

    let sid   = event.target.dataset.sid;
    let t_tag_link = document.getElementById("t_tag_"+sid);
    let t_tagc_link = document.getElementById("t_tagc_"+sid);
    let url_t_tag = new URL(t_tag_link.href);
    let url_t_tagc = new URL(t_tagc_link.href);

    if (url_t_tag.searchParams.has('c_mode')) {
        url_t_tag.searchParams.set('c_mode', event.target.checked);
    } else {
        url_t_tag.searchParams.append('c_mode', event.target.checked);
    }

    if (url_t_tagc.searchParams.has('c_mode')) {
        url_t_tagc.searchParams.set('c_mode', event.target.checked);
    } else {
        url_t_tagc.searchParams.append('c_mode', event.target.checked);
    }
    
    // 更新されたURLをリンクのhref属性に設定
    t_tag_link.href = url_t_tag.toString();
    t_tagc_link.href = url_t_tagc.toString();

    let anontrack_rbox = document.getElementById("anontrack_radio_box_"+sid);

    if(event.target.checked){
        anontrack_rbox.hidden = false;
    }else{
        anontrack_rbox.hidden = true;
    }

}

function AnontrackChange( event ){

    let sid   = event.target.dataset.sid;
    let value = event.target.value;

    jQuery.ajax(
        {
            type: 'POST',
            url: qahm.ajax_url,
            dataType : 'json',
            data: {
                'action': 'qahm_ajax_set_sitemanage_anontrack',
                'value':   value,
                'sid':    sid,
                'nonce' : qahm.nonce_api
            },
        }
    ).done(
        function( data ){
            let data_stringify = JSON.stringify(data);
            let data_json = JSON.parse(data_stringify);
            if(data_json["result"]!='success'){
                alert(qahml10n['anontrack_set_failed']);
            }
        }
    ).fail(
        function( jqXHR, textStatus, errorThrown ){
            alert(qahml10n['anontrack_set_failed']);
            qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
        }
    ).always(

    );
}

function SetSmSearchParams( formobj ){

    let search_params = formobj['searchparam'].value;
    let sid          = formobj['sid'].value;

    jQuery.ajax(
        {
            type: 'POST',
            url: qahm.ajax_url,
            dataType : 'json',
            data: {
                'action': 'qahm_ajax_set_sitemanage_search_params',
                'search_params':   search_params,
                'sid':   sid,
                'nonce' : qahm.nonce_api
            },
        }
    ).done(
        function( data ){
            let data_stringify = JSON.stringify(data);
            let data_json = JSON.parse(data_stringify);
            if(data_json["result"]!='success'){
                alert(qahml10n['searchparam_update_failed']);
            }
        }
    ).fail(
        function( jqXHR, textStatus, errorThrown ){
            alert(qahml10n['searchparam_update_failed']);
            qahm.log_ajax_error( jqXHR, textStatus, errorThrown );
        }
    ).always(
        function(){
            location.reload();
        }
    );

}