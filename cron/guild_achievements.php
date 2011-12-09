<?
	#
	# $Id$
	#

	ini_set('memory_limit', '100M');

	include(dirname(__FILE__).'/../include/init.php');

	loadlib('bnet');

	#######################################################################################################

	#
	# grab possible achievements
	#

	echo "Catalog...";

	$ret = bnet_fetch_safe('us', '/data/character/achievements', 10);
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

	echo "Roster...";

	$players = array();

	$ret = bnet_fetch_safe('us', '/guild/hyjal/the%20eternal?fields=members', 1);
	if (!$ret['ok']){
		print_r($ret);
		exit;
	}

	foreach ($ret['data']['members'] as $member){

		$players[] = $member['character']['name'];
	}

	echo "ok\n";


	#######################################################################################################

	echo "Achievements...";

	$hashes = array();
	#$players = array_slice($players, 0, 5);

	foreach ($players as $player){

		$stats = fetch_player($player);

		foreach ($stats as $id => $ts){
			$hashes[] = array(
				'id' => intval($id),
				'player' => AddSlashes($player),
				'when' => intval($ts),
			);
			$catalog[$id]['num_players']++;
		}
	}

	db_query("DELETE FROM guild_achievements_data");
	db_insert_multi('guild_achievements_data', $hashes, 1000);

	echo "ok\n";

	#######################################################################################################

	echo "Updating catalog...";

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
	#######################################################################################################
	#######################################################################################################

	function fetch_player($player){

		$name_stub = str_replace("%27", "'", rawurlencode($player));
		$url = "/character/hyjal/{$name_stub}?fields=achievements";

		$ret = bnet_fetch_safe('us', $url, 1);
		if (!$ret['ok']){
			if ($ret['req']['status'] == 404) return array();
			print_r($ret);
			exit;
		}

		$out = array();

		foreach ($ret['data']['achievements']['achievementsCompleted'] as $k => $v){

			$out[$v] = substr($ret['data']['achievements']['achievementsCompletedTimestamp'][$k], 0, -3);
		}

		return $out;
	}
?>
