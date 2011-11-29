<?
	#
	# $Id$
	#

	ini_set('memory_limit', '100M');

	include(dirname(__FILE__).'/../include/init.php');

	loadlib('xml');
	loadlib('curl');

	#######################################################################################################

	#
	# grab categories xml
	#

	echo "Categories...";

	$tree = fetch_safe("http://www.wowarmory.com/character-achievements.xml?r=Hyjal&cn=Bees&gn=The+Eternal");
	$cat_nodes = $tree->findMulti('page/achievements/rootCategories/category');
	$tree->cleanup();


	#
	# flatten
	#

	$cats = array();
	$major_cat_ids = array();
	$c_num = 0;

	foreach ($cat_nodes as $node){

		$c_num++;

		$cats[] = array($node->attributes[id], 0, $node->attributes[name], $c_num);
		$major_cat_ids[] = $node->attributes[id];
		$num = 0;

		foreach ($node->children as $child){

			if (!$child->name == 'category') continue;
			$num++;

			$cats[] = array($node->attributes[id], $num, $child->attributes[name], $c_num);
		}
	}


	#
	# update keys table
	#

	db_query("DELETE FROM guild_achievements_cats");
	foreach ($cats as $cat){
		db_insert('guild_achievements_cats', array(
			'id'		=> intval($cat[0]),
			'sub_id'	=> intval($cat[1]),
			'in_order'	=> intval($cat[3]),
			'name'		=> AddSlashes($cat[2]),
		));
	}

	echo "ok\n";

	#######################################################################################################

	echo "Roster...";

	$players = array();

	$tree = fetch_safe("http://www.wowarmory.com/guild-info.xml?r=Hyjal&cn=Bees&gn=The+Eternal");
	$player_nodes = $tree->findMulti('page/guildInfo/guild/members/character');
	foreach ($player_nodes as $node){

		$players[] = $node->attributes[name];
	}

	$tree->cleanup();
	echo "ok\n";

	#######################################################################################################

	$catalog = array();
	$hashes = array();

	#$players = array_slice($players, 0, 5);

	foreach ($players as $player){

		$stats = fetch_player($player, $catalog);

		foreach ($stats as $id => $ts){
			$hashes[] = array(
				'id' => intval($id),
				'player' => AddSlashes($player),
				'when' => intval($ts),
			);
		}
	}

	db_query("DELETE FROM guild_achievements_data");
	db_insert_multi('guild_achievements_data', $hashes, 1000);

	#######################################################################################################

	echo "Catalog...";

	db_query("DELETE FROM guild_achievements_key");

	$hashes = array();
	foreach ($catalog as $k => $row){
		$hashes[] = array(
			'id'		=> intval($k),
			'cat_id'	=> intval($row[cat_id]),
			'sub_id'	=> intval($row[sub_id]),
			'title'		=> AddSlashes($row[title]),
			'desc'		=> AddSlashes($row[desc]),
			'icon'		=> AddSlashes($row[icon]),
			'points'	=> intval($row[points]),
			'num_players'	=> intval($row[num_players]),
		);
	}
	db_insert_multi('guild_achievements_key', $hashes);

	echo "ok\n";

	#######################################################################################################
	#######################################################################################################
	#######################################################################################################

	function fetch_safe($url){

		while (1){
			$tree = xml_fetch($url);
			if ($tree) return $tree;
			echo '.'; flush();
			sleep(1);
		}
	}

	#######################################################################################################

	function fetch_player($player, &$catalog){

		global $major_cat_ids;

		$player_url = urlencode($player);
		$stats = array();

		echo "$player... ";

		foreach ($major_cat_ids as $cat_id){

			echo "$cat_id ";

			$tree = fetch_safe("http://www.wowarmory.com/character-achievements.xml?r=Hyjal&n={$player_url}&c={$cat_id}");

			$a_nodes = $tree->findMulti('achievements/category/achievement');
			spider_nodes($stats, $catalog, $cat_id, 0, $a_nodes);

			$num = 0;

			$c_nodes = $tree->findMulti('achievements/category/category');
			foreach ($c_nodes as $c_node){

				$num++;

				$a_nodes = $c_node->findMulti('achievement');
				spider_nodes($stats, $catalog, $cat_id, $num, $a_nodes);
			}

			$tree->cleanup();
		}

		echo "ok\n";

		return $stats;
	}

	#######################################################################################################

	function spider_nodes(&$stats, &$catalog, $cat_id, $sub_id, $nodes){

		foreach ($nodes as $node){

			# this picks up nested achievements
			$sub_nodes = $node->findMulti('achievement');
			spider_nodes($stats, $catalog, $cat_id, $sub_id, $sub_nodes);

			$id = $node->attributes[id];

			if ($node->attributes[dateCompleted]){
				$when = strtotime($node->attributes[dateCompleted]);
				$stats[$id] = $when;
				$catalog[$id]['num_players']++;
			}

			$catalog[$id]['cat_id']	= $cat_id;
			$catalog[$id]['sub_id']	= $sub_id;
			$catalog[$id]['title']	= $node->attributes[title];
			$catalog[$id]['desc']	= $node->attributes[desc];
			$catalog[$id]['icon']	= $node->attributes[icon];
			$catalog[$id]['points']	= $node->attributes[points];
		}
	}

	#######################################################################################################
?>