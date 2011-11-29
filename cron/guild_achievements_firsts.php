<?
	#
	# $Id$
	#

	ini_set('memory_limit', '100M');

	include(dirname(__FILE__).'/../include/init.php');

	#######################################################################################################

	echo "Firsts ";

	$result = db_query("SELECT id FROM guild_achievements_key");
	while ($row = db_fetch_hash($result)){

		$temp = db_fetch_hash(db_query("SELECT * FROM guild_achievements_data WHERE id=$row[id] ORDER BY `when` ASC LIMIT 1"));
		if ($temp[id]){
			$player_enc = AddSlashes($temp[player]);
			db_query("UPDATE guild_achievements_data SET first=1 WHERE id=$temp[id] AND player='$player_enc'");
		}

		echo '.'; flush();
	}

	echo " done\n";

	#######################################################################################################
?>