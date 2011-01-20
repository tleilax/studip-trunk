<?php

class Ticket1702LogEvents extends Migration {
    function description() {
        return 'add separate log-events for adding and removing an insitute from a seminar';
    }
    
    function up() {
        DBManager::get()->query("INSERT INTO `log_actions`
            (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES
            ('75c90fe19770d6578b6db87d9232a362', 'SEM_DEL_INSTITUTE', 'Veranstaltung - Institut gel�scht', '%user hat in Veranstaltung %sem(%affected) das Institut %inst(%coaffected) gel�scht.', 1, 0),
            ('d29b739add0ecfbc3c1f939dd5f13db8', 'SEM_ADD_INSTITUTE', 'Veranstaltung - Institut hinzugef�gt', '%user hat in Veranstaltung %sem(%affected) das Institut %inst(%coaffected) hinzugef�gt.', 1, 0);");
    }
}