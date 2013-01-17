<?
    require_once 'studip_cli_env.inc.php';
    require_once 'lib/classes/CronjobScheduler.class.php';

    CronjobScheduler::getInstance()->run();