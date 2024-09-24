<?php

    header('Content-Type: application/x-javascript; charset=utf-8');
    // キャッシュを完全に無効にする
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');

    $file_name   = './js/cookie-consent-qtag.js';
    $cookie_consent = "false";

    $cookie_consent_setting = $_GET['cookie_consent'];

    if( $cookie_consent_setting == "yes" ){
        $cookie_consent = "true";
    }

    if( file_exists( $file_name )){
        echo str_replace('"{cookie_consent}"', $cookie_consent, file_get_contents( $file_name ));
    }

?>