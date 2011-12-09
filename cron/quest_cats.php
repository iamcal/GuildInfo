<?
	include(dirname(__FILE__).'/../include/init.php');

	#
	# fetch JS file
	#

	$url = escapeshellarg('http://wowjs.zamimg.com/js/locale_enus.js?'.time());
	$data = shell_exec("wget -q -O - $url");


	#
	# parse it out into variable assignments
	#

	$all = array();
	$chunks = explode('var ', $data);
	foreach ($chunks as $chunk){
		$chunk = preg_replace('!;\s*!', '', $chunk);
		list($name, $json) = explode('=', $chunk, 2);
		$all[$name] = $json;
	}


	#
	# reformat JSON to be valid
	#

	$json = $all['mn_quests'];
	$json = str_replace(',,', ',0,', $json);
	$json = str_replace('[,', '[0,', $json);

	$obj = JSON_decode($json);


	#
	# extract flat list of categories
	#

	$out_subs = array();
	$out_cats = array();

	foreach ($obj as $cat){

		if (is_array($cat[3]) && count($cat[3])){

			$sub_cats = array();
			foreach ($cat[3] as $sub){
				$sub_cats[$sub[0]] = $sub[1];
			}

			$out_subs[$cat[0]] = $sub_cats;
			$out_cats[$cat[0]] = $cat[1];
		}
	}


	#
	# get count of known, completed quests in each cat
	#

	$q_counts = array();

	$result = db_query("SELECT category, COUNT(id) AS num FROM guild_quests_key WHERE num_players>0 GROUP BY category");
	while ($row = db_fetch_hash($result)){
		$q_counts[$row['category']] = $row['num'];
	}


	#
	# insert into DB
	#

	db_query("DELETE FROM guild_quests_cats");
	$order = 1;
	foreach ($out_subs as $cat => $rows){

		db_insert('guild_quests_cats', array(
			'id'		=> $cat,
			'name'		=> AddSlashes($out_cats[$cat]),
			'in_order'	=> $order,
			'num_quests'	=> intval($q_counts[$out_cats[$cat]]),
		));
		$order++;

		foreach ($rows as $id => $name){

			db_insert('guild_quests_cats', array(
				'id'		=> intval($id),
				'name'		=> AddSlashes($name),
				'cat_id'	=> intval($cat),
				'cat_name'	=> AddSlashes($out_cats[$cat]),
				'in_order'	=> $order,
				'num_quests'	=> intval($q_counts[$name]),
			));
			$order++;
		}

		
	}




	$num = $order-1;
	echo "Inserted $num cats\n";


?>
