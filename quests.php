<?
	include('include/init.php');

	$cat = $_GET['cat'];

	$title = 'Quests';
	$sel = 'quests';

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

	if ($cat === 'rare'){
		echo "<b>Rarest Quests</b><br />\n";
	}else{
		echo "<a href=\"./?cat=rare\">Rarest Quests</a><br />\n";
	}
	echo "<br />\n";


	#
	# only show cats which have some quests
	#

        $q_counts = array();
        $result = db_query("SELECT category, COUNT(id) AS num FROM guild_quests_key WHERE num_players>0 GROUP BY category");
        while ($row = db_fetch_hash($result)){
                $q_counts[$row['category']] = $row['num'];
        }


	$result = db_query("SELECT * FROM guild_quests_cats ORDER BY in_order ASC");
	while ($row = db_fetch_hash($result)){

		$url = "./?cat=".urlencode($row['name']);
		$name = str_replace(' ', '&nbsp;', HtmlSpecialChars($row['name']));

		if (!$row['cat_name']){
			echo "$name<br />";
			continue;
		}

		if (!$q_counts[$row['name']]) continue;

		$prefix = '&nbsp;&nbsp;&nbsp;';

		if ($row['name'] == $cat){
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
	if ($cat){

		if ($cat == 'rare'){

			$rows = array();
			$result = db_query("SELECT * FROM guild_quests_key WHERE num_players>0 ORDER BY num_players ASC LIMIT 50");
			while ($row = db_fetch_hash($result)){
				$rows[] = $row;
			}
?>

				<div style="padding: 20px; font-size: 18px; background-color: #ffffee; margin: 20px; text-align: center">
					These quests have been completed by the fewest players in the guild.
				</div>

<?

		}else{
			$cat_enc = AddSlashes($cat);

			$rows = array();
			$result = db_query("SELECT * FROM guild_quests_key WHERE category='$cat_enc' AND num_players>0 ORDER BY num_players DESC");
			while ($row = db_fetch_hash($result)){
				$rows[] = $row;
			}
		}


?>
	<table border="0" cellpadding="4" cellspacing="8" class="achievements" width="100%">
<?
		foreach ($rows as $row){
?>
		<tr valign="top">
			<td onclick="toggle(<?=$row[id]?>);" style="cursor: hand; cursor: pointer">

				<div class="pcount"><?=$row[num_players]?></div>

				<b><?=$row[title]?></b><br />
				<div id="expand-<?=$row[id]?>"></div>
			</td>
		</tr>
<?
		}
?>
	</table>
<?
	}else{
?>
	<div style="padding: 40px; font-size: 22px; background-color: #ffffcc; margin: 20px; text-align: center">
        	Choose a category on the left to see which quests the guild has completed.
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
