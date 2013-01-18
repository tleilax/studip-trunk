<?
    require_once 'studip_cli_env.inc.php';

    CronjobScheduler::getInstance()->run();