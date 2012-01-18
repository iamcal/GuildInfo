<?
	#
	# $Id$
	#

die("this is broken. there is no stats API");

	ini_set('memory_limit', '100M');

	include(dirname(__FILE__).'/../include/init.php');

	loadlib('xml');
	loadlib('curl');

	echo "Roster...";
	$tree = fetch_safe("http://www.wowarmory.com/guild-info.xml?r=Hyjal&cn=Bees&gn=The+Eternal");
	$players = $tree->findMulti('page/guildInfo/guild/members/character');
	$tree->cleanup();
	echo "ok\n";

	$last_update = time();


	foreach ($players as $node){

		$name = $node->attributes[name];

		echo "$name...";
		fetch_player($name);
		echo "ok\n";

	}

	function fetch_player($player){

		$player_enc = AddSlashes($player);
		$player_url = urlencode($player);

		$url = "http://www.wowarmory.com/character-statistics.xml?r=Hyjal&cn={$player_url}&gn=The+Eternal";

		$tree = fetch_safe($url);
		$cat_nodes = $tree->findMulti('page/statistics/rootCategories/category');
		$tree->cleanup();

		$cats = array();
		foreach($cat_nodes as $node){
			$cats[$node->attributes[id]] = $node->attributes[name];
		}

		$stats = array();
		$stats_h = array();
		$key = array();

		foreach ($cats as $id => $name){

			$url = "http://www.wowarmory.com/character-statistics.xml?r=Hyjal&n={$player_url}&c=$id";

			$tree = fetch_safe($url);
			$stats1 = $tree->findMulti('category/statistic');
			$stats2 = $tree->findMulti('category/category');
			$tree->cleanup();

			foreach ($stats1 as $node){

				process_node($node, $name, '', $stats, $key, $stats_h);
			}

			foreach ($stats2 as $snode){
				$stats3 = $snode->findMulti('statistic');
				foreach ($stats3 as $node){

					process_node($node, $name, $snode->attributes[name], $stats, $key, $stats_h);
				}
			}
		}

		foreach ($key as $id => $vals){

			$hash = array(
				'id'		=> $id,
				'cat'		=> AddSlashes($vals[0]),
				'subcat'	=> AddSlashes($vals[1]),
				'name'		=> AddSlashes($vals[2]),
			);

			$hash2 = $hash;
			unset($hash2[id]);

			db_insert_dupe('guild_stats_key', $hash, $hash2);
		}

		db_query("DELETE FROM guild_stats WHERE name='$player_enc'");

		foreach ($stats as $id => $value){

			db_insert('guild_stats', array(
				'stat_id' 	=> $id,
				'name'		=> $player_enc,
				'value'		=> AddSlashes($value),
				'value_i'	=> intval($value),
				'value_f'	=> floatval($value),
				'highest'	=> AddSlashes($stats_h[$id]),
				'last_update'	=> $GLOBALS[last_update],
			));
		}

	}

	db_query("UPDATE guild_stats SET hidden=1 WHERE last_update != $last_update");

	function process_node($node, $cat, $subcat, &$stats, &$key, &$stats_h){

		if (!$node->attributes[id]){
			#echo "no ID for $cat/$subcat/{$node->attributes[name]}...";
			$node->attributes[id] = create_stat_id($cat, $subcat, $node->attributes[name]);
			#echo "{$node->attributes[id]}\n";
		}

		$stats[$node->attributes[id]] = $node->attributes[quantity];
		$key[$node->attributes[id]] = array($cat, $subcat, $node->attributes[name]);
		if ($node->attributes[highest]){
			$stats_h[$node->attributes[id]] = $node->attributes[highest];
		}
	}


	function create_stat_id($cat, $subcat, $name){

		$cat_enc = AddSlashes($cat);
		$subcat_enc = AddSlashes($subcat);
		$name_enc = AddSlashes($name);


		#
		# does it exist?
		#

		$row = db_fetch_hash(db_query("SELECT id FROM guild_stats_key WHERE cat='$cat_enc' AND subcat='$subcat_enc' AND name='$name_enc' ORDER BY id ASC LIMIT 1 "));

		if ($row[id]) return $row[id];


		#
		# find a spare ID
		#

		list($last) = db_fetch_list(db_query("SELECT MAX(id) FROM guild_stats_key"));

		$id = ($last < 1000000) ? 1000000 : $last+1;

		db_insert('guild_stats_key', array(
			'id'		=> $id,
			'cat'		=> $cat_enc,
			'subcat'	=> $subcat_enc,
			'name'		=> $name_enc,
		));

		return $id;
	}

	function fetch_safe($url){

		while (1){
			$tree = xml_fetch($url);
			if ($tree) return $tree;
			echo '.'; flush();
			sleep(1);
		}
	}

?>
