<ul>
<?
	include('../../include/init.php');

	$id = intval($_POST[id]);

	$limit = 10;

	$rows = array();
	$result = db_query("SELECT * FROM guild_achievements_data WHERE id=$id ORDER BY `when` ASC");
	while ($row = db_fetch_hash($result)){
		$rows[] = $row;
	}

	$more = 0;
	if (count($rows) > $limit + 1){
		$more = count($rows) - $limit;
		$rows = array_slice($rows, 0, $limit);
	}


	foreach ($rows as $row){

		$d = date('Y/m/d', $row[when]);

		echo "<li> ".HtmlSpecialChars($row[player])." ($d) </li>\n";
	}
	if ($more){
		echo "<li> <i>And $more others...</i> </li>\n";
	}

	if (!count($rows)){

		echo "<li> <i>No players have this achievement</i> </li>\n";
	}
?>
</ul>