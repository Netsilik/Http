<?php
namespace Netsilik\Lib;

/**
 * @package Netsilik\Lib
 * @copyright (c) Netsilik (http://netsilik.nl)
 * @license EUPL-1.1 (European Union Public Licence, v1.1)
 */

class Http {
	 
	public static function getBasicAuthCredentials() {
		$username = null;
		$password = null;
		
		if (isset($_SERVER['PHP_AUTH_USER'])) { // mod_php
			$username = $_SERVER['PHP_AUTH_USER'];
			$password = $_SERVER['PHP_AUTH_PW'];
		} elseif (isset($_SERVER['HTTP_AUTHORIZATION']) && 0 === strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']), 'basic')) { // most other servers
			list($username, $password) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));	
		}
		
		return ['username' => $username, 'password' => $password];
	}
	 
	/**
	 * Display HTTP error 
	 * @param int $httpStatusCode The HTTP 1.1 response code to send (either 403: Forbidden, 404: Not Found, 405: Method Not Allowed, 500: Internal Server Error or 503: Service Unavailable)
	 * @note this will cause the script to halt.
	 */
	public static function error($httpStatusCode, $debug = false) {
		$setHeader = true;
		if ($debug) {
			$setHeader = false;
			$caller = debug_backtrace();
			$caller = array_slice ( $caller[0], 0, 2);
			$debugInfo = "<pre>Debug info: Http::error() called from {$caller['file']} on line {$caller['line']}.</pre>\n";
		} else {	
			$debugInfo = '';
		}
		
		if ($httpStatusCode == 401) {
			if ($setHeader) {
				header('HTTP/1.1 401 Unauthorized');
				header('WWW-Authenticate: Basic realm="Auth Required"');
			}
			echo "<!DOCTYPE html PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n";
			echo "<html><head>\n";
			echo "<title>401 Unauthorized</title>\n";
			echo "</head><body>\n";
			echo "<h1>Unauthorized</h1>\n";
			echo $debugInfo;
			echo "<p>This server could not verify that you are authorized to accessthe document requested. Either you supplied the wrong credentials (e.g., bad password), or your browser does't understand how to supply the credentials required.</p>\n";
			echo "<hr>\n";
			echo $_SERVER['SERVER_SIGNATURE']."\n";
			echo "</body></html>\n";
			exit(0);
		} elseif ($httpStatusCode == 403) {
			if ($setHeader) { header('HTTP/1.1 403 Forbidden'); }
			echo "<!DOCTYPE html PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n";
			echo "<html><head>\n";
			echo "<title>403 Forbidden</title>\n";
			echo "</head><body>\n";
			echo "<h1>Forbidden</h1>\n";
			echo $debugInfo;
			echo "<p>You don't have permission to access ".$_SERVER['REQUEST_URI']." on this server.</p>\n";
			echo "<hr>\n";
			echo $_SERVER['SERVER_SIGNATURE']."\n";
			echo "</body></html>\n";
			exit(0);
		} elseif ($httpStatusCode == 404) {
			if ($setHeader) { header('HTTP/1.1 404 Not Found'); }
			echo "<!DOCTYPE html PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n";
			echo "<html><head>\n";
			echo "<title>404 Not Found</title>\n";
			echo "</head><body>\n";
			echo "<h1>Not Found</h1>\n";
			echo $debugInfo;
			echo "<p>The requested URL ".$_SERVER['REQUEST_URI']." was not found on this server.</p>\n";
			echo "<hr>\n";
			echo $_SERVER['SERVER_SIGNATURE']."\n";
			echo "</body></html>\n";
			exit(0);
		} elseif ($httpStatusCode == 405) {
			if ($setHeader) { header('HTTP/1.1 405 Method Not Allowed'); }
			echo "<!DOCTYPE html PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n";
			echo "<html><head>\n";
			echo "<title>405 Method Not Allowed</title>\n";
			echo "</head><body>\n";
			echo "<h1>Method Not Allowed</h1>\n";
			echo $debugInfo;
			echo "<p>The method specified in the Request-Line is not allowed for the resource identified by the Request-URI.</p>\n";
			echo "<hr>\n";
			echo $_SERVER['SERVER_SIGNATURE']."\n";
			echo "</body></html>\n";
			exit(0);
		} elseif ($httpStatusCode == 500) {
			if ($setHeader) { header('HTTP/1.1 500 Internal Server Error'); }
			echo "<!DOCTYPE html PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n";
			echo "<html><head>\n";
			echo "<title>500 Internal Server Error</title>\n";
			echo "</head><body>\n";
			echo "<h1>Internal Server Error</h1>\n";
			echo $debugInfo;
			echo "<p>The server encountered an internal error or misconfiguration and was unable to complete your request.</p>\n";
			echo "<hr>\n";
			echo $_SERVER['SERVER_SIGNATURE']."\n";
			echo "</body></html>\n";
			exit(0);
		} elseif ($httpStatusCode == 503) {
			if ($setHeader) { header('HTTP/1.1 503 Service Unavailable'); }
			echo "<!DOCTYPE html PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n";
			echo "<html><head>\n";
			echo "<title>503 Service Unavailable</title>\n";
			echo "</head><body>\n";
			echo "<h1>Service Unavailable</h1>\n";
			echo $debugInfo;
			echo "<p>The server is currently unable to handle the request due to a temporary condition of the server.</p>\n";
			echo "<hr>\n";
			echo $_SERVER['SERVER_SIGNATURE']."\n";
			echo "</body></html>\n";
			exit(0);
		} else {
			trigger_error('Invalid http error code, valid redirect codes are: 401 (Unauthorized), 403 (Forbidden), 404 (Not Found), 405 (Method Not Allowed), 500 (Internal Server Error) and 503 (Service Unavailable).', E_USER_ERROR);
		}
	}
	
	/**
	 * Redirect to some other resource
	 * @param int $httpStatusCode The HTTP 1.1 response code to send (either 301: Moved Permanently or 303: See Other)
	 * @param string $redirectUri The URI to redirect to
	 * @note this will cause the script to halt.
	 */
	public static function redirect($httpStatusCode, $redirectUri, $debug = false) {
		$setHeader = true;
		if ($debug) {
			$setHeader = false;
			$caller = debug_backtrace();
			$caller = array_slice ( $caller[0], 0, 2);
			$debugInfo = "<pre>Debug info: Http::redirect() called from {$caller['file']} on line {$caller['line']}.</pre>\n";
		} else {	
			$debugInfo = '';
		}
		if ($httpStatusCode == 301) {
			if ($setHeader) { header('Location: '.$redirectUri, 301); }
			echo "<!DOCTYPE html PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n";
			echo "<html><head>\n";
			echo "<title>301 Moved Permanently</title>\n";
			echo "</head><body>\n";
			echo "<h1>Moved Permanently</h1>\n";
			echo $debugInfo;
			echo "<p>The document has moved <a href=\"".$redirectUri."\">here</a>.</p>\n";
			echo "<hr>\n";
			echo $_SERVER['SERVER_SIGNATURE']."\n";
			echo "</body></html>\n";
			exit(0);
		} elseif ($httpStatusCode == 303) {
			if ($setHeader) { header('Location: '.$redirectUri, 303); }
			echo "<!DOCTYPE html PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n";
			echo "<html><head>\n";
			echo "<title>303 See Other</title>\n";
			echo "</head><body>\n";
			echo "<h1>See Other</h1>\n";
			echo $debugInfo;
			echo "<p>The answer to your request is located <a href=\"".$redirectUri."\">here</a>.</p>\n";
			echo "<hr>\n";
			echo $_SERVER['SERVER_SIGNATURE']."\n";
			echo "</body></html>\n";
			exit(0);
		} else {
			trigger_error('Invalid http redirect code, valid redirect codes are: 301 (Moved Permanently) and 303 (See Other).', E_USER_ERROR,1);
		}
	}
}
