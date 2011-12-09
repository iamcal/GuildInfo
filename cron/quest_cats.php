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

	$out = array();

	foreach ($obj as $cat){

		if (is_array($cat[3]) && count($cat[3])){

			$sub_cats = array();
			foreach ($cat[3] as $sub){
				$sub_cats[$sub[0]] = $sub[1];
			}

			$out[$cat[1]] = $sub_cats;
		}
	}


	#
	# insert into DB
	#

	db_query("DELETE FROM guild_quests_cats");
	$order = 1;
	foreach ($out as $cat => $rows){
		foreach ($rows as $id => $name){

			db_insert('guild_quests_cats', array(
				'id'		=> intval($id),
				'name'		=> AddSlashes($name),
				'cat_name'	=> AddSlashes($cat),
				'in_order'	=> $order,
			));

			$order++;
		}
	}

	$num = count($out);
	echo "Inserted $num cats\n";


?>
