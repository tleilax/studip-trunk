<?
require_once 'lib/admission.inc.php';
class Step00150Studygroups extends Migration
{

	function description ()
	{
		return "This Migration is needed for Step 150. ";
	}

	function up ()
	{
	    // (1) Add a new dozent who is used as default dozent for all studygroups
		DBManager::get()->query("INSERT IGNORE INTO auth_user_md5 (user_id, username, password, perms, Vorname, Nachname, Email, locked) VALUES (MD5('studygroup_dozent'),'studygroup_dozent','0c6fe1b07e3aca7ee6387f87dc8370eb','dozent','','','',1)"); 
		DBManager::get()->query("INSERT IGNORE INTO user_info SET user_id =MD5('studygroup_dozent')");	
	
		// (2) Allocate some space in the config-table
		DBManager::get()->query("ALTER TABLE `config` CHANGE `value` 
			`value` TEXT CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL");

		// (3) Add some default-terms
		DBManager::get()->query("INSERT IGNORE INTO `config` (
			`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`,
			`range`, `section`, `position`, `mkdate`, `chdate`, `description`,
			`comment`, `message_template` )
			VALUES ( MD5( 'STUDYGROUPS_ENABLE' ) , '', 'STUDYGROUPS_ENABLE',
			'0', '1', 'boolean', 'global', '', '0', UNIX_TIMESTAMP( ) , UNIX_TIMESTAMP( ) ,
			'Studiengruppen', '', '')");
		
		$terms = "Mir ist bekannt, dass ich die Gruppe nicht zu rechtswidrigen Zwecken nutzen darf. Dazu z�hlen u.a. Urheberrechtsverletzungen, Beleidigungen und andere Pers�nlichkeitsdelikte.

Ich erkl�re mich damit einverstanden, dass AdministratorInnen die Inhalte der Gruppe zu Kontrollzwecken einsehen d�rfen.";
		Config::GetInstance()->setValue( $terms, 'STUDYGROUP_TERMS');

		// (4) Add default for allowed modules
		Config::GetInstance()->setValue( 'forum:1|documents:0|schedule:0|participants:1', 'STUDYGROUP_SETTINGS');
		
	}

	function down ()
	{
	    // (1) Remove studygroup_dozent
        DBManager::get()->query("DELETE FROM auth_user_md5 WHERE user_id = MD5('studygroup_dozent')"); 
		DBManager::get()->query("DELETE FROM user_info WHERE user_id =MD5('studygroup_dozent')");  

	}
}
?>
