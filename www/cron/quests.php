<?
	#
	# $Id$
	#

	ini_set('memory_limit', '300M');

	include(dirname(__FILE__).'/../include/init.php');

	include($include_dir.'bnet.php');
	include($include_dir.'curl.php');

	#######################################################################################################

	#
	# fetch info for quests which don't have any
	#

	$ids = array();
	$result = db_query("SELECT id FROM guild_quests_key WHERE have_info=0");
	while ($row = db_fetch_hash($result)) $ids[] = $row['id'];

	echo "Fetching quest info...";

	$done = 0;
	$total = count($ids);

	output_progress($done, $total, 0);

	foreach ($ids as $id){

		$ret = bnet_fetch_safe($cfg['guild_region'], '/quest/'.$id, 1);
		if ($ret['ok']){

			db_update('guild_quests_key', array(
				'title'		=> AddSlashes($ret['data']['title']),
				'category'	=> AddSlashes($ret['data']['category']),
				'have_info'	=> 1,
				'last_fetched'	=> time(),
			), "id=$id");
		}

		$done++;
		output_progress($done, $total);
	}

	output_progress(0, 0);

	echo "ok                   \n";

	#######################################################################################################

	function output_progress($x, $y, $clear=1){

		if ($clear){
			echo str_repeat(chr(8), 12);
		}

		if ($y){
			echo ' '.str_pad($x, 5, ' ', STR_PAD_LEFT).'/'.str_pad($y, 5, ' ', STR_PAD_RIGHT);
		}
	}

	#######################################################################################################
?>
