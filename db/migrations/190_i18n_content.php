<?php

class I18nContent extends Migration
{
    public function description()
    {
        return 'Add database table for multi-language content';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec('CREATE TABLE IF NOT EXISTS `i18n` (
                   `object_id` varchar(32) NOT NULL,
                   `table` varchar(255) NOT NULL,
                   `field` varchar(255) NOT NULL,
                   `lang` varchar(32) NOT NULL,
                   `value` text,
                   PRIMARY KEY (`object_id`,`table`,`field`,`lang`)
                   )');

        // Write config
        if (!$GLOBALS['CONTENT_LANGUAGES']) {
            $config_file = $GLOBALS['STUDIP_BASE_PATH'].DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config_local.inc.php';
            if (is_writable($config_file)) {
                $newlines[] = PHP_EOL."// Content languages".PHP_EOL;
                $newlines[] = '$CONTENT_LANGUAGES[\'de_DE\'] = array(\'picture\' => \'lang_de.gif\', \'name\' => \'Deutsch\');'.PHP_EOL;
                $newlines[] = '//$CONTENT_LANGUAGES[\'en_GB\'] = array(\'picture\' => \'lang_en.gif\', \'name\' => \'English\');'.PHP_EOL;
                file_put_contents($config_file, $newlines, FILE_APPEND | LOCK_EX);
            } else {
                PageLayout::postMessage(MessageBox::error(_('config_local.inc.php kann nicht beschrieben werden. Bitte fügen Sie "$CONTENT_LANGUAGES[\'de_DE\'] = array(\'picture\' => \'lang_de.gif\', \'name\' => \'Deutsch\');" selbst hinzu.')));
            }
        }
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec('DROP TABLE IF EXISTS i18n');
    }
}
