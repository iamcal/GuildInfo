<?
	include('include/init.php');

	$title = 'Stats';
	$sel = 'stats';

	include('head.txt');

	if ($_GET[id]){

		$id = intval($_GET[id]);

		$key = db_fetch_hash(db_query("SELECT * FROM guild_stats_key WHERE id=$id"));

		echo "<p><a href=\"./\">&laquo; Back</a></p>\n";

		if ($key[subcat]){
			echo "<h2>$key[cat] / $key[subcat] / $key[name]</h2>\n";
		}else{
			echo "<h2>$key[cat] / $key[name]</h2>\n";
		}

		echo "<table border=1>\n";

		$result = db_query("SELECT * FROM guild_stats WHERE stat_id=$id AND hidden=0 ORDER BY value_f DESC");
		while ($row = db_fetch_hash($result)){

			if ($row[value] == floatval($row[value])) $row[value] = number_format($row[value]);

			echo "<tr>\n";
			echo "<td>$row[name]</td>\n";
			if ($row[highest]){
				echo "<td>$row[highest] ($row[value])</td>\n";
			}else{
				echo "<td>$row[value]</td>\n";
			}
			echo "</tr>\n";
		}

		echo "</table>\n";


	}else{

	$rows = array();
	$result = db_query("SELECT * FROM guild_stats_key ORDER BY cat ASC, subcat ASC, id ASC");
	while ($row = db_fetch_hash($result)){
		$rows[] = $row;
	}

	$last_cat = 'x';
	$last_subcat = 'x';

	foreach ($rows as $row){

		if ($last_cat != $row[cat]){
			$last_subcat = 'x';
			if ($last_cat != 'x'){
				echo "</div>\n";
				echo "</div>\n";
			}
			echo "<div class=\"cat\">\n";
			echo "<h2>$row[cat]</h2>";
		}

		if ($last_subcat != $row[subcat]){
			if ($last_subcat != 'x'){
				echo "</div>\n";
			}
			echo "<div class=\"subcat\">\n";
			echo "<h3>$row[subcat]</h3>\n";
		}

		echo "<a href=\"./?id=$row[id]\">$row[name]</a><br />";

		$last_cat = $row[cat];
		$last_subcat = $row[subcat];
	}

	echo "</div>";
	echo "</div>";

	}


	include('foot.txt');
?>
