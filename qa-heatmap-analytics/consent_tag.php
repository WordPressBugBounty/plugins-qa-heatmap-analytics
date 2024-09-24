<?php

    define('SHORTINIT', true);

    require '../../../wp-load.php' ;
    require_once '../../../wp-settings.php' ;

    wp_plugin_directory_constants();
    $GLOBALS['wp_plugin_paths'] = array();
    require_once ABSPATH . WPINC . '/link-template.php';

    header('Content-Type: text/plain');

    $qa_consent_tag_content = file_get_contents('./qa_paste_consent_tag.html');
    $qa_tag_url = plugin_dir_url( __FILE__ );

    $cookie_consent = "no";

    if( array_key_exists('cookie_consent',$_GET) ){
        $cookie_consent = $_GET['cookie_consent'];
    }

    $qa_consent_tag_content  = str_replace('{qatag_url}', $qa_tag_url, $qa_consent_tag_content);
    $qa_consent_tag_content  = str_replace('{cookie_consent}', $cookie_consent, $qa_consent_tag_content);

    echo $qa_consent_tag_content;

?>