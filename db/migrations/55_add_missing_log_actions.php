<?php
class AddMissingLogActions extends Migration
{
    static $log_actions = array(
        array(
            'name'        => 'USER_CHANGE_PASSWORD',
            'description' => 'Nutzerpasswort ge�ndert',
            'template'    => '%user �ndert/setzt das Passwort f�r %user(%affected)',
            'active'      => 0
        ), array(
            'name'        => 'SEM_CHANGE_CYCLE',
            'description' => 'Regelm��ige Zeit ge�ndert',
            'template'    => '%user hat in %sem(%affected) die regelm��ige Zeit %info ge�ndert',
            'active'      => 1
        )
    );

    function description()
    {
        return 'adds two missing log actions to the database';
    }

    function up()
    {
        $db = DBManager::get();
        $query = $db->prepare("INSERT INTO log_actions (action_id, name, description, info_template, active) VALUES (?, ?, ?, ?, ?)");

        foreach (self::$log_actions as $action) {
            $query->execute(array(md5($action['name']), $action['name'], $action['description'], $action['template'], $action['active']));
        }

        // fix misuse of %coaffected in SEM_ADD_CYCLE and SEM_DELETE_CYCLE
        $db->exec("UPDATE log_actions SET info_template = REPLACE(info_template, '<em>%coaffected</em>', '%info')
                   WHERE name IN ('SEM_ADD_CYCLE', 'SEM_DELETE_CYCLE')");

        $db->exec("UPDATE log_events SET info = coaffected_range_id WHERE action_id IN (MD5('SEM_ADD_CYCLE'), MD5('SEM_DELETE_CYCLE'))");
        $db->exec("UPDATE log_events SET coaffected_range_id = NULL WHERE action_id IN (MD5('SEM_ADD_CYCLE'), MD5('SEM_DELETE_CYCLE'))");
    }

    function down()
    {
        $db = DBManager::get();
        $query = $db->prepare("DELETE FROM log_actions WHERE action_id = ?");

        foreach (self::$log_actions as $action) {
            $query->execute(array(md5($action['name'])));
        }

        // restore misuse of %coaffected in SEM_ADD_CYCLE and SEM_DELETE_CYCLE
        $db->exec("UPDATE log_actions SET info_template = REPLACE(info_template, '%info', '<em>%coaffected</em>')
                   WHERE name IN ('SEM_ADD_CYCLE', 'SEM_DELETE_CYCLE')");

        $db->exec("UPDATE log_events SET coaffected_range_id = info WHERE action_id IN (MD5('SEM_ADD_CYCLE'), MD5('SEM_DELETE_CYCLE'))");
        $db->exec("UPDATE log_events SET info = NULL WHERE action_id IN (MD5('SEM_ADD_CYCLE'), MD5('SEM_DELETE_CYCLE'))");
    }
}
?>
