<?
class AddOptionResourcesHidePastSingleDates extends Migration
{
    function description ()
    {
        return 'add config option RESOURCES_HIDE_PAST_SINGLE_DATES';
    }

    function up ()
    {
        $db = DBManager::get();
        $db->exec("
            INSERT IGNORE INTO `config` (
			`config_id` ,
			`parent_id` ,
			`field` ,
			`value` ,
			`is_default` ,
			`type` ,
			`range` ,
			`section` ,
			`position` ,
			`mkdate` ,
			`chdate` ,
			`description` ,
			`comment` ,
			`message_template`
			)
			VALUES (
			MD5( 'RESOURCES_HIDE_PAST_SINGLE_DATES' ) , '', 'RESOURCES_HIDE_PAST_SINGLE_DATES', '1', '1', 'boolean', 'global', '', '0', UNIX_TIMESTAMP( ) , UNIX_TIMESTAMP( ) , 'Schaltet in der Ressourcenverwaltung ein,ob bereits vergangene Terminen bei der Buchung und Planung br�cksichtigt werden sollen', '', ''
			)
			");
    }

    function down ()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM config WHERE field = 'RESOURCES_HIDE_PAST_SINGLE_DATES'");
    }
}
?>
