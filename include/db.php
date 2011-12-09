<?
	#
	# $Id: lib_db.php,v 1.3 2008/03/20 19:40:11 cal Exp $
	#

	db_connect();

	#################################################################

	function db_connect() {
		$GLOBALS[cfg][db_conn] = @mysql_connect($GLOBALS[cfg][db_host], $GLOBALS[cfg][db_user], $GLOBALS[cfg][db_pass]);

		if ($GLOBALS[cfg][db_conn]) {
			@mysql_select_db($GLOBALS[cfg][db_name], $GLOBALS[cfg][db_conn]);	
			return 1;
		}

		return 0;
	}

	#################################################################

	function db_query($qstring) {

		if ($_GET[debugsql]){
			echo "QUERY: ".HtmlSpecialChars($qstring)."<br />\n";
		}

		$result = mysql_query($qstring, $GLOBALS[cfg][db_conn]);

		if (!$result) {
			echo "<p>".db_errorno().' : '.db_error()."</p>";
		} else {
			return $result;
		}
	}

	#################################################################

	function db_insertid() {
		return mysql_insert_id($GLOBALS[cfg][db_conn]);
	}

	#################################################################

	function db_error() {
		return mysql_error($GLOBALS[cfg][db_conn]);
	}

	function db_errorno() {
		return mysql_errno($GLOBALS[cfg][db_conn]);
	}

	#################################################################

	function db_insert($tbl, $hash){
		$fields = array_keys($hash);
		db_query("INSERT INTO $tbl (`".implode('`,`',$fields)."`) VALUES ('".implode("','",$hash)."')");
		return db_insertid();
	}

	#################################################################

	function db_insert_multi($tbl, $hashes, $limit=0){
		$fields = array_keys($hashes[0]);
		$inserts = array();

		if ($limit){
			while (count($hashes)){
				$chunk = array_slice($hashes, 0, $limit);
				$hashes = array_slice($hashes, $limit);

				db_insert_multi($tbl, $chunk);
			}
			return;
		}

		foreach ($hashes as $hash){
			$values = array();
			foreach ($fields as $f){ $values[] = $hash[$f]; }
			$inserts[] = "('".implode("','", $values)."')";
		}

		db_query("INSERT INTO $tbl (`".implode('`,`',$fields)."`) VALUES ".implode(', ', $inserts));
		return db_insertid();
	}

	#################################################################

	function db_update($tbl, $hash, $where){
		$bits = array();
		foreach(array_keys($hash) as $k){
			$bits[] = "`$k`='$hash[$k]'";
		}
		db_query("UPDATE $tbl SET ".implode(', ',$bits)." WHERE $where");
	}

	#################################################################

	function db_insert_dupe($tbl, $hash, $hash2){

		$bits = array();
		foreach(array_keys($hash2) as $k){
			$bits[] = "`$k`='$hash2[$k]'";
		}

		$fields = array_keys($hash);
		db_query("INSERT INTO $tbl (`".implode('`,`',$fields)."`) VALUES ('".implode("','",$hash)."') ON DUPLICATE KEY UPDATE ".implode(', ',$bits));
	}

	#################################################################

	function db_num_rows($qhandle) {
		if ($qhandle) {
			return mysql_numrows($qhandle);
		} else {
			echo "no result set found";
			return 0;
		}
	}

	#################################################################

	function db_fetch_list($qhandle) {
		return mysql_fetch_array($qhandle, MYSQL_NUM);
	}

	#################################################################

	function db_fetch_hash($qhandle) {
		return mysql_fetch_array($qhandle, MYSQL_ASSOC);
	}

	#################################################################

	function db_escape_like($string){
		return str_replace(array('%','_'), array('\\%','\\_'), $string);
	}

	#################################################################

	function db_escape_rlike($string){
		return preg_replace("/([.\[\]*^\$()])/", '\\\$1', $string);
	}

	#################################################################

	function db_fetch_one($sql) {
		return db_fetch_array(db_query($sql));
	}

	#################################################################

	function db_fetch_all($sql) {
		$out = array();
		$result = db_query($sql);
		while($row = db_fetch_array($result)){
			$out[] = $row;
		}
		return $out;
	}

	#################################################################
?>
