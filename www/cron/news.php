<?
	#
	# $Id$
	#

	ini_set('memory_limit', '300M');

	include(dirname(__FILE__).'/../include/init.php');

	include($include_dir.'bnet.php');
	include($include_dir.'curl.php');

	#######################################################################################################

	echo "News feed...";

	$realm_stub = str_replace("%27", "'", rawurlencode($GLOBALS['cfg']['guild_realm']));
	$guild_stub = str_replace("%27", "'", rawurlencode($GLOBALS['cfg']['guild_name']));

	$ret = bnet_fetch_safe($cfg['guild_region'], "/guild/{$realm_stub}/{$guild_stub}?fields=news", 1);
	if (!$ret['ok']){
		print_r($ret);
		exit;
	}

	$num = 0;

	foreach ($ret['data']['news'] as $row){

		db_insert_dupe('guild_news', array(
			'timestamp'	=> AddSlashes($row['timestamp']),
			'type'		=> AddSlashes($row['type']),
			'data'		=> AddSlashes(serialize($row)),
		), array(
			'type'		=> AddSlashes($row['type']),
			'data'		=> AddSlashes(serialize($row)),			
		));

		$num++;
	}

	echo "done ($num)\n";
