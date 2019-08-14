<?php

$db = new SQLite3(dirname(__FILE__)."/your/db.sqlite");
$db->busyTimeout(5000);

$sql="";

$tables=$db->query("SELECT name FROM sqlite_master WHERE type ='table' AND name NOT LIKE 'sqlite_%';");

while ($table=$tables->fetchArray(SQLITE3_NUM)) {
	$sql.=$db->querySingle("SELECT sql FROM sqlite_master WHERE name = '{$table[0]}'").";\n\n";
	$rows=$db->query("SELECT * FROM {$table[0]}");
	$sql.="INSERT INTO {$table[0]} (";
	$columns=$db->query("PRAGMA table_info({$table[0]})");
	$fieldnames=array();
	while ($column=$columns->fetchArray(SQLITE3_ASSOC)) {
		$fieldnames[]=$column["name"];
	}
	$sql.=implode(",",$fieldnames).") VALUES";
	while ($row=$rows->fetchArray(SQLITE3_ASSOC)) {
		foreach ($row as $k=>$v) {
			$row[$k]="'".SQLite3::escapeString($v)."'";
		}
		$sql.="\n(".implode(",",$row)."),";
	}
	$sql=rtrim($sql,",").";\n\n";
}
file_put_contents("sqlitedump.sql",$sql);
