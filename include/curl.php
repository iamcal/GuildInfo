<?php
	#
	# $Id: lib_curl.php,v 1.5 2008/04/05 00:28:40 cal Exp $
	#

	$GLOBALS['http_status_code'] = 0;
	$GLOBALS['http_headers'] = array();

	$GLOBALS['debug_http_request_time'] = 0;
	$GLOBALS['debug_http_request_count'] = 0;



	#
	# Args
	#	 url		- the url to request
	#	 method		- POST or GET or HEAD
	#	 timeout	- time in seconds to timeout for response
	#	 headers	- headers to pass
	#	 post		- the request is a POST -- compat with old http_request
	#	 rawBody	- preq args[post] - call rawBody
	#	 options	- add extra cURL options
	#	
	# Return: ARRAY	 array($code, $head, $body);
	#

	function curl_http_request($args){

		if ($_GET['debugsql']){
			echo "CURL:";
			dumper($args);
		}


		#
		# disconnect from databases incase http traffic burps
		#

		$create_args = array(
			'timeout'	=> $GLOBALS['cfg']['http_conn_timeout'],
			'io_timeout'	=> $GLOBALS['cfg']['http_io_timeout'],
		);

		$pass_args = array('method', 'timeout', 'io_timeout');

		foreach($pass_args as $arg){
			if ($args[$arg]){ $create_args[$arg] = $args[$arg]; }
		}


		#
		# set up the handler
		#

		$curl_handler = curl_init();

		curl_setopt($curl_handler, CURLOPT_URL, $args['url']);
		curl_setopt($curl_handler, CURLOPT_CONNECTTIMEOUT, $create_args['timeout']);
		curl_setopt($curl_handler, CURLOPT_TIMEOUT, $create_args['io_timeout']);
		curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handler, CURLOPT_FAILONERROR, false);

		if (!$args['ignore_redirect']){
			curl_setopt($curl_handler, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl_handler, CURLOPT_MAXREDIRS, 3);
		}


		#
		# The following are attempts to see if curl is causing load spikes
		# and if these settings fix it
		#

		curl_setopt($curl_handler, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl_handler, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl_handler, CURLOPT_MAXCONNECTS, 2);


		#
		# ignore invalid certs
		#

		curl_setopt($curl_handler, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl_handler, CURLOPT_SSL_VERIFYHOST, FALSE);


		#
		# callback function to process the headers from return
		#

		curl_setopt($curl_handler, CURLOPT_HEADERFUNCTION, 'curl_http_process_headers');


		#
		# support other passed-in cURL options, optionally.
		#

		if ($args['options']){
			foreach($args['options'] as $option => $value){
				curl_setopt($curl_handler, $option, $value);
			}
		}


		#
		# support for debugging
		#

		if ($GLOBALS['cfg']['curl_debug']){

			curl_setopt($curl_handler, CURLOPT_VERBOSE, 1);
			$fp = fopen("/tmp/curl_debug.log", 'a+');
			curl_setopt($curl_handler, CURLOPT_STDERR, $fp);
		}

		if ($args['verbose']){
			curl_setopt($curl_handler, CURLOPT_VERBOSE, 1);
			echo "in verbose mode...<br />";
		}


		#
		# to support the old method of POSTING data which is
		# HTTP_Request()->addRawPostData complient
		#

		$headers = array();

		if ($args['post'] && $args['rawBody']){

			#
			# add custom headers
			#

			if ($args[headers]){
				foreach($args[headers] as $var => $value){
					$headers[] = "$var: $value";
				}
			}

			curl_setopt($curl_handler, CURLOPT_POST,1);
			curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $args[rawBody]);
			
			curl_setopt($curl_handler, CURLOPT_HTTPHEADER, $headers);

		} elseif (is_array($args[headers])){

			foreach($args[headers] as $var => $value){
				$headers[] = "$var: $value";
			}

			if ($_GET[debugsql]){
				echo "CURL headers:";
				dumper($headers);
			}

			curl_setopt($curl_handler, CURLOPT_HTTPHEADER, $headers);
		}


		#
		# new method of posting data. Note: Default is Get
		#

		if ($args[method] == 'POST' &&  is_array($args[post_data])){

			echo "setting postfields with".count($args[post_data])." fields...<br />";

			$params = $args[post_data];
			curl_setopt($curl_handler, CURLOPT_POST,1);
			curl_setopt($curl_handler, CURLOPT_POSTFIELDS,$params);
		}


		#
		# HEAD, PURGE, DELETE request CUSTOMREQUEST
		#

		if ($args[method] && ($args[method] != 'POST' && $args[method] != 'GET')){

			curl_setopt($curl_handler, CURLOPT_CUSTOMREQUEST, $args[method]);
		}


		#
		# send the request
		#
		
		$GLOBALS[http_headers] = array(); // clear the previous request
		
		$body = @curl_exec($curl_handler);
		$info = @curl_getinfo($curl_handler);

		#dumper($info);

		$code = $info['http_code'];
		$head = $GLOBALS[http_headers];

		if (!$body && ($error_str = curl_error($curl_handler)) && (curl_errno($curl_handler) != 52)){ 

			return array(0, array(), '');
		}

		#
		#	close the connection
		#
		
		curl_close($curl_handler);

		$GLOBALS[http_status_code] = $code;

		if ($_GET[debugsql]){
			echo "CURL:";
			dumper($code);
			dumper($head);
			dumper($body);
		}


		#
		# process request
		#

		#curl_http_log_excesstime();

		return array($code, $head, $body);
	}

	#############################################################################

	function curl_http_process_headers($ch, $string){


		$lines = split("\n", $string);

		foreach ($lines as $str){
			list($key, $value) = split(": ", $str);
			if ($key){
				$GLOBALS[http_headers][$key] = trim($value);
				$GLOBALS[http_headers][strtolower($key)] = trim($value);
			}
		}

		return strlen($string);
	}

	#############################################################################
	
	function curl_http_log_excesstime(){

		$time = microtime_float() - $GLOBALS[cfg][req_start_time];

		if ($time >= $GLOBALS[cfg][curl_http_excesstime] && !$GLOBALS['cfg']['silence_excesstime']){
			$trace = debug_backtrace();
			while (preg_match('/db_|http_|curl_/', $trace[0]['function'])){
				array_shift($trace);
			}

			$function = $trace[0]['function'] ? $trace[0]['function'].'()' : '_global_';
			$msg = sprintf("[EXCESSTIME] Request from %s function took %.3f seconds", $function, $time);

			trigger_error($msg, E_USER_WARNING);
		}

	}

	#############################################################################
?>
