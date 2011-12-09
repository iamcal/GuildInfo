<?
	#
	# $Id$
	#

	ini_set('memory_limit', '100M');

	include(dirname(__FILE__).'/../include/init.php');

	include($include_dir.'bnet.php');
	include($include_dir.'curl.php');

	#######################################################################################################

	#
	# grab possible achievements
	#

	echo "Achievements Catalog...";

	$ret = bnet_fetch_safe($cfg['guild_region'], '/data/character/achievements', 10);
	if (!$ret['ok']){
		print_r($ret);
		exit;
	}

	$groups = array();
	$catalog = array();

	foreach ($ret['data']['achievements'] as $group){

		$groups[] = array($group['id'], 0, $group['name']);

		if (is_array($group['achievements']))
		foreach ($group['achievements'] as $row){
			$row['group_id'] = $group['id'];
			$row['num_players'] = 0;
			$catalog[$row['id']] = $row;
		}

		if (is_array($group['categories']))
		foreach ($group['categories'] as $cat){
			$groups[] = array($group['id'], $cat['id'], $cat['name']);

			foreach ($cat['achievements'] as $row){

				$row['group_id'] = $group['id'];
				$row['cat_id'] = $cat['id'];
				$row['num_players'] = 0;
				$catalog[$row['id']] = $row;
			}
		}
	}


	#
	# update cats table
	#

	$c = 0;
	db_query("DELETE FROM guild_achievements_cats");
	foreach ($groups as $row){
		$c++;
		db_insert('guild_achievements_cats', array(
			'id'		=> intval($row[0]),
			'sub_id'	=> intval($row[1]),
			'in_order'	=> intval($c),
			'name'		=> AddSlashes($row[2]),
		));
	}

	echo "ok\n";

	#######################################################################################################

	echo "Guild Roster...";

	$players = array();

	$realm_stub = str_replace("%27", "'", rawurlencode($GLOBALS['cfg']['guild_realm']));
	$guild_stub = str_replace("%27", "'", rawurlencode($GLOBALS['cfg']['guild_name']));

	$ret = bnet_fetch_safe($cfg['guild_region'], "/guild/{$realm_stub}/{$guild_stub}?fields=members", 1);
	if (!$ret['ok']){
		print_r($ret);
		exit;
	}

	foreach ($ret['data']['members'] as $member){

		$players[] = $member['character']['name'];
	}

	$num = count($players);

	echo "ok ($num)\n";


	#######################################################################################################

	echo "Player Data...";

	$a_hashes = array();
	$q_hashes = array();
	$players = array_slice($players, 0, 5);

	$q_catalog = array();

	$done = 0;
	$total = count($players);

	output_progress($done, $total, 0);

	foreach ($players as $player){

		$stats = fetch_player($player);

		foreach ($stats['a'] as $id => $ts){
			$a_hashes[] = array(
				'id' => intval($id),
				'player' => AddSlashes($player),
				'when' => intval($ts),
			);
			$catalog[$id]['num_players']++;
		}

		foreach ($stats['q'] as $id){
			$q_hashes[] = array(
				'id' => intval($id),
				'player' => AddSlashes($player),
			);
			$q_catalog[$id]['num_players']++;
		}

		$done++;
		output_progress($done, $total);
	}

	db_query("DELETE FROM guild_achievements_data");
	db_insert_multi('guild_achievements_data', $a_hashes, 1000);

	db_query("DELETE FROM guild_quests_data");
	db_insert_multi('guild_quests_data', $q_hashes, 1000);

	output_progress(0, 0); # this will delete it

	echo "ok\n";

	function output_progress($x, $y, $clear=1){

		if ($clear){
			echo str_repeat(chr(8), 12);
		}

		if ($y){
			echo ' '.str_pad($x, 5, ' ', STR_PAD_LEFT).'/'.str_pad($y, 5, ' ', STR_PAD_RIGHT);
		}
	}

	#######################################################################################################

	echo "Updating Achievements Catalog...";

	#
	# store the catalog
	#

	db_query("DELETE FROM guild_achievements_key");

	$hashes = array();
	foreach ($catalog as $row){
		$hashes[] = array(
			'id'		=> intval($row['id']),
			'cat_id'	=> intval($row['group_id']),
			'sub_id'	=> intval($row['cat_id']),
			'title'		=> AddSlashes($row['title']),
			'desc'		=> AddSlashes($row['description']),
			'icon'		=> AddSlashes($row['icon']),
			'points'	=> intval($row['points']),
			'num_players'	=> intval($row['num_players']),
		);
	}
	db_insert_multi('guild_achievements_key', $hashes);


	echo "ok\n";

	#######################################################################################################

	echo "Updating Quests Catalog...";

	foreach ($q_catalog as $k => $row){
		db_insert_dupe('guild_quests_key', array(
			'id'		=> intval($k),
			'num_players'	=> intval($row['num_players']),
		), array(
			'num_players'	=> intval($row['num_players']),
		));
	}

	echo "ok\n";

	#######################################################################################################
	#######################################################################################################
	#######################################################################################################

	function fetch_player($player){

		$realm_stub = str_replace("%27", "'", rawurlencode($GLOBALS['cfg']['guild_realm']));
		$name_stub = str_replace("%27", "'", rawurlencode($player));
		$url = "/character/{$realm_stub}/{$name_stub}?fields=achievements,quests";

		$ret = bnet_fetch_safe($GLOBALS['cfg']['guild_region'], $url, 1);
		if (!$ret['ok']){
			if ($ret['req']['status'] == 404) return array();
			print_r($ret);
			exit;
		}

		$out = array(
			'a'	=> array(),
			'q'	=> array(),
		);

		foreach ($ret['data']['achievements']['achievementsCompleted'] as $k => $v){

			$out['a'][$v] = substr($ret['data']['achievements']['achievementsCompletedTimestamp'][$k], 0, -3);
		}

		foreach ($ret['data']['quests'] as $id){
			$out['q'][] = $id;
		}

		return $out;
	}
?>
