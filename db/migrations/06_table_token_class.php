<?
class TableTokenClass extends DBMigration {

	function description () {
		return 'creates table for Token class';
	}

	function up () {
		set_time_limit(0);
		$this->announce(" creating table...");
		
		$this->db->query( "	CREATE TABLE IF NOT EXISTS `user_token` (
										`user_id` VARCHAR( 32 ) NOT NULL ,
										`token` VARCHAR( 32 ) NOT NULL ,
										`expiration` INT NOT NULL ,
										PRIMARY KEY ( `user_id` , `token` , `expiration` ),
										INDEX index_expiration (`expiration`),
										INDEX index_token (`token`),
										INDEX index_user_id (`user_id`)
									);");
		
		$this->announce("done.");
		
	}
	
	function down () {
		set_time_limit(0);
		$this->announce(" removing table...");
		$this->db->query("
      DROP TABLE `user_token` 
		");
		
		$this->announce("done.");
		
	}
}
?>
