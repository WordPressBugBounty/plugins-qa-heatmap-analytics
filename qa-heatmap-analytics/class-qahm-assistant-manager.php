<?php
$qahm_assistant_manager = new QAHM_Assistant_Manager();
class QAHM_Assistant_Manager extends QAHM_File_Data {
    public const NONCE_API  = 'api';
    public function __construct() {
        // Retrieve a list of assistants
		add_action( 'init', array( $this, 'get_assistant' ) );

        // Register AJAX functions
        $this->regist_ajax_func( 'ajax_get_assistant' );
        $this->regist_ajax_func( 'ajax_connect_assistant' );
    }

    public function ajax_get_assistant() {
        if ( ! is_user_logged_in() ) {
            wp_die('you don not have privilege to access this page.');
        }
        // Verify nonce and check maintenance status
        $nonce = $this->wrap_filter_input( INPUT_POST, 'nonce' );
        if ( ! wp_verify_nonce( $nonce, self::NONCE_API ) || $this->is_maintenance() ) {
            http_response_code( 400 );
            die( 'nonce error' );
        }
        // Retrieve the assistantSlug from the POST data
        $assistant_slug = $this->wrap_filter_input( INPUT_POST, 'assistant_slug' );
        $response = $this->get_assistant( $assistant_slug );
        // Return the JSON response
        wp_send_json_success($response);
    }
    public function get_assistant( $assistant_slug = null ) {

        $assistant_dir = WP_PLUGIN_DIR . '/';
        $assistant_ary = array();

        // Ensure get_plugins function is loaded  
        if (!function_exists('get_plugins')) {  
            require_once ABSPATH . 'wp-admin/includes/plugin.php';  
        }  

        // Get the list of assistant directories
        $dirs = glob( $assistant_dir . '*', GLOB_ONLYDIR );

        foreach ( $dirs as $dir ) {
            $slug = basename( $dir );

            // Skip if not a qa-assistant-* pattern  
            if ( strpos( $slug, 'qa-assistant-' ) !== 0 ) {  
                continue;  
            }  
    
            // Check if the plugin is active  
            $plugin_file = $slug . '/' . $slug . '.php';  
            if ( ! is_plugin_active( $plugin_file ) ) {  
                continue;  
            }

            // Skip if a specific assistant slug is requested and this is not it
            if ( $assistant_slug && $slug !== $assistant_slug ) {
                continue;
            }
            
            $assistant_file = $dir . '/main.php';
            if ( ! file_exists( $assistant_file ) ) {
                continue;
            }

            // Get plugin data from header instead of config.json  
            $plugin_data = get_plugin_data( $dir . '/' . $slug . '.php', false, true );
            
            // Get the config file for Assistant-specific settings only  
            $config_file = $dir . '/config.json';  
            if ( ! file_exists( $config_file ) ) {  
                continue;
            }

            $config = json_decode( file_get_contents( $config_file ), true );  
            if ( ! $config ) {
                continue;
            }
            
            // Create the assistant array using plugin header data  
            $assistant = array(
                'assistant_file'  => $assistant_file,
                'slug'        => $slug,  
                'name'        => $config['name'] ?? $plugin_data['Name'],  
                'description' => $config['description'] ?? $plugin_data['Description'],  
                'author'      => $config['author'] ?? $plugin_data['Author'],  
                'version'     => $plugin_data['Version'] ?? '',
                'images'      => $config['images'] ?? '',
            );

            // Get the character images
            if ( $config['images'] ) {
                $relative_dir = str_replace( WP_CONTENT_DIR, '', $dir );
                foreach ( $config['images'] as $key => $image ) {
                    $assistant['images'][$key] = content_url($relative_dir . '/' . $image );
                }
            }

			// translations.jsonの読み込み
			$translations = null;
			$locale = get_locale(); // 例: en_US
			$file_path = $dir . "/translations-{$locale}.json";

			// フォールバック（例: en だけ指定された場合に translations-en.json を見る）
			if ( ! file_exists($file_path) ) {
				$lang = $this->wrap_substr($locale, 0, 2);
				$file_path = $dir . "/translations-{$lang}.json";
			}

			// さらにフォールバック（translations.json）
			if ( ! file_exists($file_path) ) {
				$file_path = $dir . "/translations.json";
			}

			// JSON をデコードして連想配列に
			if ( file_exists($file_path) ) {
				$json = file_get_contents( $file_path );
				$translations = json_decode( $json, true );
			}

			// エラー対策
			if ( ! is_array( $translations ) ) {
				$translations = [];
			}

			$assistant['name'] = $this->resolve_translation($assistant['name'], $translations);
			$assistant['description'] = $this->resolve_translation($assistant['description'], $translations);
			$assistant['author'] = $this->resolve_translation($assistant['author'], $translations);
            $assistant_ary[ $slug ] = $assistant;
        }

        return $assistant_ary;
    }

    public function ajax_connect_assistant() 	{
        if ( ! is_user_logged_in() ) {
            wp_die('you don not have privilege to access this page.');
        }
        // Verify nonce and check maintenance status
        $nonce = $this->wrap_filter_input( INPUT_POST, 'nonce' );
        if ( ! wp_verify_nonce( $nonce, self::NONCE_API )  ) {
            http_response_code( 401 );
            die( 'nonce error' );
        }
        session_start();

        $assistant_slug = $this->wrap_filter_input(INPUT_POST, 'assistant_slug');
        $state = $this->wrap_filter_input(INPUT_POST, 'state');
        $free = $this->wrap_filter_input(INPUT_POST, 'free');
        $tracking_id = $this->wrap_filter_input(INPUT_POST, 'tracking_id');

        if ( $state === 'start' ) {
            session_unset();
        }
        if ( !$state ) {
            $state = 'start';
        }

		if ( $free ) {
			$class_name = $this->make_assistant_class_name($assistant_slug);
			$_SESSION[$class_name]['session_free'] = $free;
		}

        // Return the JSON response
        $response = $this->connect_assistant( $assistant_slug, $state, $tracking_id );
        if ( ! $response ) {
            http_response_code( 404 );
            die( 'assistant not found' );
        }
        wp_send_json_success($response);
    }
    public function connect_assistant( $assistant_slug, $state, $tracking_id = 'all' ) {
        $assistant_config = $this->get_assistant( $assistant_slug );
        if ( ! isset( $assistant_config[$assistant_slug]['assistant_file'] ) ) {
            return false;
        }
        require_once $assistant_config[$assistant_slug]['assistant_file'];
        $assistant_dir_name = dirname( $assistant_config[$assistant_slug]['assistant_file'] );
        $class_name  = $this->make_assistant_class_name( $assistant_slug );
        
        // Check if class exists and provide detailed error information
        if ( ! class_exists( $class_name ) ) {
            $error_info = array(
                'error_type' => 'class_not_found',
                'expected_class' => $class_name,
                'plugin_slug' => $assistant_slug,
                'plugin_directory' => $assistant_dir_name,
                'message' => "Assistant class '{$class_name}' not found"
            );
            wp_send_json_error( $error_info );
        }
        
        if ( isset( $_SESSION[$class_name] )) {
            $assistant_class = new $class_name( $tracking_id, $assistant_dir_name, $state, $_SESSION[ $class_name ] );
        } else {
            $assistant_class = new $class_name( $tracking_id, $assistant_dir_name, $state );
        }
        $assistant_class->progress_story();

        $response = array(
            'execute' => $assistant_class->execute,
            'debug_logs' => $assistant_class->debug_logs ?? []
        );
        //save variables to session
        foreach ( get_object_vars( $assistant_class ) as $key => $value) {
            if ( strpos( $key, 'session_' ) === 0 ) {
                $_SESSION[ $class_name ][ $key ] = $value;
            }
        }
        return $response;
    }
    public function make_assistant_class_name ( $assistant_slug ) {    
        // ハイフンをアンダースコアに変換    
        $class_name = str_replace('-', '_', $assistant_slug);    
          
        $class_name = str_replace('qa_assistant_', 'QAHM_Assistant_', $class_name);    
          
        // その後で各単語の最初を大文字に変換    
        $class_name = ucwords($class_name, '_');    
          
        return $class_name;    
    }

	private function resolve_translation($key, $translations) {
		if (strpos($key, '.') !== false) {
			list($section, $subkey) = explode('.', $key, 2);
			if (isset($translations[$section][$subkey])) {
				return $translations[$section][$subkey];
			}
		}
		return $key; // 翻訳が見つからない場合はキーをそのまま返す
	}
}
