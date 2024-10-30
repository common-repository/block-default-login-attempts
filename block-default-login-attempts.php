<?php
/*
Plugin Name: Block Default Login Attempts
Description: Completely block default admin user login attempts in WordPress.
Version: 1.3.1
Author: Ben Yates
Author URI: http://bayates.host-ed.me/wordpress/
*/

if (!function_exists('bdla_check_logs')) {
	function bdla_check_logs($log_dir) {
		$max_backup_files = 30;
		$arr_php = glob("$log_dir/*.log.php");
		if (count($arr_php) > $max_backup_files) {
			array_multisort(array_map('filemtime', $arr_php), SORT_NUMERIC, SORT_ASC, $arr_php);
			for ($i = 0; $i < count($arr_php) - $max_backup_files; $i++) {
				@unlink($arr_php[$i]);
			}
		}
	}
}

if (!function_exists('bdla_get_log_dir')) {
	function bdla_get_log_dir() {
		$data_dir = dirname(dirname(dirname(__FILE__))) . '/plugin-data';
		$log_dir = $data_dir . '/block-default-login-attempts';
		if (@is_dir($log_dir)) return $log_dir;
		if (!@is_dir($data_dir)) @mkdir($data_dir);
		if (!@is_dir($log_dir)) @mkdir($log_dir);
		if (!@is_file($log_dir . '/index.html')) {
			@copy(dirname(__FILE__) . '/index.html', $log_dir . '/index.html');
		}
		if (@is_dir($log_dir)) return $log_dir;
	}
}

if (!function_exists('bdla_write_log')) {
	function bdla_write_log($text) {
		if (!$log_dir = bdla_get_log_dir()) return;	
		if (!date_default_timezone_get()) date_default_timezone_set('America/Los_Angeles');
		$info = pathinfo(__FILE__);
		$name_pre = $info['filename'] . '_' . date('m-d-y');
		$log_name = $name_pre . '.log.php';
		$log_path = $log_dir . '/' . $log_name;
		$s = '';
		clearstatcache();
		if (!@filesize($log_path)) {
			$s .= '<?php exit; ?' . '>' . PHP_EOL;
		}
		$s .= date('M d Y H:i:s') . PHP_EOL;
		$s .= $text . PHP_EOL . PHP_EOL;
		$log = @fopen($log_path, 'a');
		@fwrite($log, $s);
		@fclose($log);
		bdla_check_logs($log_dir);
	}
}

// check admin login attempts:
if (isset($_REQUEST['log']) and isset($_REQUEST['pwd'])) {
	if ($_REQUEST['log'] == 'admin') {
		$attempts = get_option('block_default_login_attempts', 0);
		update_option('block_default_login_attempts', $attempts + 1);
		bdla_write_log('Default login attempt IP: ' . $_SERVER['REMOTE_ADDR']
			. ', HTTP_X_FORWARDED_FOR: ' . $_SERVER['HTTP_X_FORWARDED_FOR']
			. "\nREQUEST URI: " . $_SERVER['REQUEST_URI']
			. "\nREFERER: " . $_SERVER['HTTP_REFERER'] . "\nREQUEST:\n" . trim(print_r($_REQUEST, true)));
		header_remove();
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
}

// just in case:
if (!function_exists('bdla_auth_cookie_bad_username')) {
	function bdla_auth_cookie_bad_username($cookie_elements) {
		if ($cookie_elements['username'] == 'admin') {
			$attempts = get_option('block_default_login_attempts', 0);
			update_option('block_default_login_attempts', $attempts + 1);
			bdla_write_log('Default login attempt (cookie) IP: ' . $_SERVER['REMOTE_ADDR']
				. ', HTTP_X_FORWARDED_FOR: ' . $_SERVER['HTTP_X_FORWARDED_FOR']
				. "\nREQUEST URI: " . $_SERVER['REQUEST_URI']
				. "\nREFERER: " . $_SERVER['HTTP_REFERER'] . "\nREQUEST:\n" . trim(print_r($_REQUEST, true)));
			header_remove();
			header('HTTP/1.1 403 Forbidden');
			exit;
		}
	}
	add_action('auth_cookie_bad_username', 'bdla_auth_cookie_bad_username');
}

if ((defined('DOING_AJAX') and DOING_AJAX) or (defined('DOING_CRON') and DOING_CRON)) return;

if (!function_exists('bdla_admin_notices')) {
	function bdla_admin_notices() {
		$user = get_user_by('login', 'admin');
		if (empty($user) or empty($user->ID)) return;
		?>
			<div class="error">
				<p>
					The default user &quot;admin&quot; still exists. Either replace it, or
					deactivate the plugin &quot;Block Default Login Attempts&quot; until you
					do so. Otherwise, you may not be able to log in again!
				</p>
			</div>
		<?php
	}
	add_action('admin_notices', 'bdla_admin_notices');
}

if (!function_exists('bdla_activation')) {
	function bdla_activation() {
		add_option('block_default_login_attempts', 0, '', 'no');
	}
	register_activation_hook(__FILE__, 'bdla_activation');
}

if (!function_exists('bdla_plugin_row_meta')) {
	function bdla_plugin_row_meta($links, $file) {
		if (strpos($file, 'block-default-login-attempts.php') === false) return $links;
		$attempts = get_option('block_default_login_attempts', 0);
		if ($attempts) {
			$links_new = array('<b>' . number_format($attempts) . '</b> attempts blocked');
		} else {
			$links_new = array('No attempts encountered');
		}
		$links = array_merge($links, $links_new);
		return $links;
	}
	add_filter('plugin_row_meta', 'bdla_plugin_row_meta', 10, 2);
}

?>
