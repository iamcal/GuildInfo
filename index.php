<?
	$title = 'Photos';
	$sel = 'photos';

	include('head.txt');
?>

<table border="0" cellpadding="5" cellspacing="0" width="100%">
<?
	$files = array();

	if ($dh = opendir('images')){
		while (($file = readdir($dh)) !== false){
			if (preg_match('!(.*)\.jpg$!i', $file, $m)){

				$info = getimagesize('images/'.$file);

				if ($info[0] > $info[1]){

					$w = 100;
					$h = round(100 * ($info[1] / $info[0]));
				}else{
					$h = 100;
					$w = round(100 * ($info[0] / $info[1]));
				}

				$files[$file] = array(
					'src'	=> $file,
					'title'	=> str_replace('_', ' ', $m[1]),
					'w'	=> $w,
					'h'	=> $h,
				);

			}else{
				#echo "no match: $file<br />\n";
			}
		}
		closedir($dh);
	}

	ksort($files);

	$p = 0;
	$r_len = 8;

	foreach ($files as $file){

		if ($p == 0){
			echo "<tr valign=\"top\">\n";
		}

		$title = HtmlSpecialChars($file[title]);
		$src_url = urlencode($file[src]);

		$thumb_w = $file[w];
		$thumb_h = $file[h];

		echo "<td align=\"center\">\n";
		echo "<a href=\"images/$file[src]\" title=\"$title\"><img src=\"thumb.php?src=$src_url\" width=\"$thumb_w\" height=\"$thumb_h\" alt=\"$title\" class=\"thumb\" /></a><br />";
		echo $title;
		echo "</td>\n";

		$p++;
		if ($p == $r_len){
			echo "</tr>\n";
			$p = 0;
		}
	}

	#print_r($files);
?>
</table>

<?
	include('foot.txt');
?>