<?
	include('include/init.php');

	list($id, $sub_id) = explode('-', $_GET[id]);
	$id = intval($id);
	$sub_id = intval($sub_id);

	if ($_GET[id] == 'rare') $id = 'rare';

	$title = 'Achievements';
	$sel = 'achievements';

	include('head.txt');
?>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr valign="top">
		<td>
			<div id="index">
<?
	#
	# show cats
	#

	if ($id === 'rare'){
		echo "<b>Rarest Achievements</b><br />\n";
	}else{
		echo "<a href=\"./?id=rare\">Rarest Achievements</a><br />\n";
	}
	if ($_GET[id] === 'firsts'){
		echo "<b>Guild Firsts</b><br />\n";
	}else{
		echo "<a href=\"./?id=firsts\">Guild Firsts</a><br />\n";
	}
	echo "<br />\n";

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
		<td width="100%">
			<div id="listing">

<?
	if ($id){

		if ($id == 'rare'){

			$rows = array();
			$result = db_query("SELECT * FROM guild_achievements_key WHERE cat_id!=81 AND num_players>0 ORDER BY num_players ASC LIMIT 50");
			while ($row = db_fetch_hash($result)){
				$rows[] = $row;
			}
?>

				<div style="padding: 20px; font-size: 18px; background-color: #ffffee; margin: 20px; text-align: center">
					These achievements have been earned by the fewest players in the guild.
				</div>

<?

		}else{
			$rows = array();
			$result = db_query("SELECT * FROM guild_achievements_key WHERE cat_id=$id AND sub_id=$sub_id ORDER BY num_players DESC");
			while ($row = db_fetch_hash($result)){
				$rows[] = $row;
			}
		}


?>
	<table border="0" cellpadding="4" cellspacing="8" class="achievements" width="100%">
<?
		foreach ($rows as $row){
			$row[icon] = str_replace("'", '-', $row[icon]);
?>
		<tr valign="top">
			<td onclick="toggle(<?=$row[id]?>);" style="cursor: hand; cursor: pointer">
				<div class="ahicon"><img src="http://static.wowhead.com/images/wow/icons/medium/<?=$row[icon]?>.jpg" width="36" height="36" /></div>

				<div class="pcount"><?=$row[num_players]?></div>

				<b><?=$row[title]?></b><br />
				<?=$row[desc]?>
				<div id="expand-<?=$row[id]?>"></div>
			</td>
		</tr>
<?
		}
?>
	</table>
<?
	}else if ($_GET[id] == 'firsts'){

?>

	<p>These players have earned the most 'guild first' achievements:</p>

	<table border="0" cellpadding="4" cellspacing="8">
<?

		$result = db_query("SELECT player, COUNT(first) AS num FROM guild_achievements_data WHERE first=1 GROUP BY player ORDER BY num DESC");
		while ($row = db_fetch_hash($result)){
?>
		<tr>
			<td><a href="./?p=<?=urlencode($row[player])?>"><?=HtmlSpecialChars($row[player])?></a></td>
			<td><?=$row[num]?></td>
		</tr>
<?
		}
?>
	</table>

<?
	}else if ($_GET[p]){

		$region = StrToLower($cfg['guild_region']);
		$realm = stub_pub($cfg['guild_realm']);
		$name = stub($_GET['p']);

		$p_html = HtmlSpecialChars($_GET['p']);
		$player_enc = AddSlashes($_GET['p']);

		$a_url = "http://{$region}.battle.net/wow/en/character/{$realm}/{$name}/";

		$result = db_query("SELECT * FROM guild_achievements_data WHERE player='$player_enc' AND first=1 ORDER BY `when` DESC");
		$firsts_num = db_num_rows($result);

		if ($firsts_num){
?>
	<div class="tblblock"><?=$p_html?> was the first player in the guild to get these <?=$firsts_num?> achievements:</div>

	<table border="0" cellpadding="4" cellspacing="8" class="achievements" width="100%">
<?
			while ($temp = db_fetch_hash($result)){
				$row = db_fetch_hash(db_query("SELECT * FROM guild_achievements_key WHERE id=$temp[id]"));
				$row[icon] = str_replace("'", '-', $row[icon]);
?>
		<tr valign="top">
			<td>
				<div class="ahicon"><img src="http://static.wowhead.com/images/wow/icons/medium/<?=$row[icon]?>.jpg" width="36" height="36" /></div>

				<b><?=$row[title]?></b><br />
				<?=$row[desc]?><br />
				<i>Earned <?=date('Y/m/d',$temp[when])?></i>
			</td>
		</tr>
<?
			}
?>
	</table>
<?
		}


		list($c) = db_fetch_list(db_query("SELECT COUNT(*) FROM guild_achievements_data WHERE player='$player_enc' AND first=0 ORDER BY `when` DESC"));

		echo "<div class=\"tblblock\">";

		if ($firsts_num){
			if ($c){
				echo "$p_html also has <a href=\"$a_url\">$c other achievements</a>.";
			}else{
				echo "$p_html doesn't have any other achievements.";
			}
		}else{
			if ($c){
				echo "$p_html has <a href=\"$a_url\">$c other achievements</a>.";
			}else{
				echo "$p_html doesn't have any achievements.";
			}
		}

		echo "</div>";

	}else{
?>
				<div style="padding: 40px; font-size: 22px; background-color: #ffffcc; margin: 20px; text-align: center">
					Choose a category on the left to see which achievements the guild has earned.
				</div>
<?
	}
?>

			</div>
		</td>
	</tr>
</table>

<script>

var keys_open = {};

function toggle(id){

	if (keys_open[id]){
		var d = document.getElementById('expand-'+id);
		d.style.display = 'none';
		delete keys_open[id];
		return;
	}

	keys_open[id] = 1;

	var d = document.getElementById('expand-'+id);
	d.innerHTML = '...';
	d.style.display = 'block';

	ajaxify('./api.php', {id: id}, function(o){

		//console.log(o);
		var d = document.getElementById('expand-'+id);

		d.innerHTML = o;
		d.style.display = 'block';
	});
}

function ajaxify(url, args, handler){

	var req = new XMLHttpRequest();

	req.onreadystatechange = function(){

		var l_f = handler;

		if (req.readyState == 4){
			if (req.status == 200){

				this.onreadystatechange = null;
				l_f(req.responseText);
			}else{
				// error
			}
		}
	}

	req.open('POST', url, 1);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

	var args2 = [];
	for (i in args){
		args2[args2.length] = escape(i)+'='+encodeURIComponent(args[i]);
	}

	req.send(args2.join('&'));
}

</script>


<?
	include('foot.txt');
?>
