<ul>
<?
	include('include/init.php');

	$id = intval($_POST[id]);

	$limit = 10;

	$rows = array();
	$result = db_query("SELECT * FROM guild_quests_data WHERE id=$id ORDER BY player ASC");
	while ($row = db_fetch_hash($result)){
		$rows[] = $row;
	}

	$more = 0;
	if (count($rows) > $limit + 1){
		$more = count($rows) - $limit;
		$rows = array_slice($rows, 0, $limit);
	}


	foreach ($rows as $row){

		echo "<li> ".HtmlSpecialChars($row[player])."</li>\n";
	}
	if ($more){
		echo "<li> <i>And $more others...</i> </li>\n";
	}

	if (!count($rows)){

		echo "<li> <i>No players have completed this quest</i> </li>\n";
	}
?>
</ul>
