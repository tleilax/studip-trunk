<?php
/**
 * @author Thomas Hackl <thomas.hackl@uni-passau.de>
 * Script for changing the database engine for all tables.
 */

require_once __DIR__ . '/studip_cli_env.inc.php';

$old_engine = 'MyISAM';
$engine = 'InnoDB';
$ignore_tables=array();
$sql = "SELECT CONCAT('ALTER TABLE `".$DB_STUDIP_DATABASE."`.`', TABLE_NAME, '` ENGINE=".$engine.";') as query FROM `information_schema`.TABLES WHERE TABLE_SCHEMA='".$DB_STUDIP_DATABASE."' AND ENGINE='".$old_engine."' AND TABLE_NAME NOT IN ('".implode("', '", $ignore_tables)."')";

$db = DBManager::get();

$result = $db->query($sql);
foreach($result->fetchAll(PDO::FETCH_OBJ) as $row){
   $db->exec($row->query);
   fwrite(STDOUT, sprintf("Execute: %s \n",$row->query));
}
fwrite(STDOUT, "Finished\n");
