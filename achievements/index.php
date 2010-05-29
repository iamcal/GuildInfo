<?
	include('../../include/init.php');

	list($id, $sub_id) = explode('-', $_GET[id]);
	$id = intval($id);
	$sub_id = intval($sub_id);

	$title = 'Achievements';
	$sel = 'achievements';

	include('../head.txt');
?>

<table border="0" cellpadding="0" cellspacing="0">
	<tr valign="top">
		<td>
			<div id="index">
<?
	#
	# show cats
	#

	$result = db_query("SELECT * FROM guild_achievements_cats ORDER BY in_order ASC, sub_id ASC");
	while ($row = db_fetch_hash($result)){

		if ($row[sub_id]){
			$prefix = '&nbsp;&nbsp;&nbsp;';
			$url = "./?id={$row[id]}-{$row[sub_id]}";
		}else{
			$prefix = '';
			$url = "./?id={$row[id]}";
		}

		$row[name] = str_replace('10-Player Raid', '(10)', $row[name]);
		$row[name] = str_replace('25-Player Raid', '(25)', $row[name]);
		$name = str_replace(' ', '&nbsp;', HtmlSpecialChars($row[name]));

		if ($row[id]==$id && $row[sub_id]==$sub_id){
			echo "$prefix<b>$name</b><br />";
		}else{
			echo "$prefix<a href=\"$url\">$name</a><br />";
		}
	}
?>

			</div>
		</td>
		<td>
			<div id="listing">

<?
	if ($id){
?>
	<table border="0">
<?
		$result = db_query("SELECT * FROM guild_achievements_key WHERE cat_id=$id AND sub_id=$sub_id ORDER BY num_players DESC");
		while ($row = db_fetch_hash($result)){
?>
		<tr>
			<td><img src="http://static.wowhead.com/images/wow/icons/medium/<?=$row[icon]?>.jpg" width="36" height="36" /></td>
			<td><?=$row[desc]?></td>
			<td><?=$row[num_players]?></td>
		</tr>
<?
		}
?>
	</table>
<?
	}
?>

			</div>
		</td>
	</tr>
</table>


<?
	include('../foot.txt');
?>