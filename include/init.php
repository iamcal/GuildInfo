<?
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	putenv('TZ=PST8PDT');
	date_default_timezone_set('America/Los_Angeles');

	$include_dir = dirname(__FILE__).'/';

	include($include_dir.'config.php');
	include($include_dir.'db.php');

	function stub($x){
		return str_replace("%27", "'", rawurlencode(mb_strtolower($x)));
	}

	function stub_pub($x){
		return rawurlencode(str_replace(' ', '-', str_replace("'", '', mb_strtolower($x))));
	}
?>
