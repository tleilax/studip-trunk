<?php

class Step00196Config extends Migration
{
    function description()
    {
        return 'new configuration view';
    }

    function up()
    {
        $db = DBManager::get();

        $db->exec("UPDATE config SET section = 'resources' WHERE field LIKE '%resources%'");
        $db->exec("UPDATE config SET section = 'studygroups' WHERE field LIKE '%studygroup%'");
        $db->exec("UPDATE config SET section = 'modules' WHERE field IN ('BANNER_ADS_ENABLE','CALENDAR_ENABLE','CHAT_ENABLE','ELEARNING_INTERFACE_ENABLE','EXPORT_ENABLE','EXTERN_ENABLE','FOAF_ENABLE','LITERATURE_ENABLE','LOG_ENABLE','SCM_ENABLE','SEMESTER_ADMINISTRATION_ENABLE','SMILEYADMIN_ENABLE','STM_ENABLE','VOTE_ENABLE','WIKI_ENABLE')");
        $db->exec("UPDATE config SET section = 'allow_views' WHERE field IN ('ALLOW_ADMIN_USERACCESS','ALLOW_DOZENT_ARCHIV','ALLOW_DOZENT_VISIBILITY','ALLOW_SELFASSIGN_INSTITUTE','AUX_RULE_ADMIN_PERM','LOCK_RULE_ADMIN_PERM','INST_FAK_ADMIN_PERMS','RANGE_TREE_ADMIN_PERM','RESTRICTED_USER_MANAGEMENT','SEM_CREATE_PERM','SEM_TREE_ADMIN_PERM','SEM_TREE_SHOW_EMPTY_AREAS_PERM','SEM_VISIBILITY_PERM')");
        $db->exec("UPDATE config SET section = 'privacy' WHERE field IN ('ACCESSKEY_ENABLE','DOZENT_ALWAYS_VISIBLE','CHAT_USE_AJAX_CLIENT','ENABLE_SKYPE_INFO','FOAF_SHOW_IDENTITY','FORUM_ANONYMOUS_POSTINGS','HOMEPAGE_VISIBILITY_DEFAULT','MESSAGE_PRIORITY','SHOWSEM_ENABLE','USER_VISIBILITY_UNKNOWN')");
        $db->exec("UPDATE config SET section = 'global' WHERE field IN ('ADMISSION_ALLOW_DISABLE_WAITLIST','ADMISSION_PRELIM_COMMENT_ENABLE','AUTO_ARCHIVIERUNG','AUTO_ARCHIVIERUNG_LETZTE_AKTIVIT�T','AUTO_ARCHIVIERUNG_SEMESTER','DOCUMENTS_EMBEDD_FLASH_MOVIES','EMAIL_DOMAIN_RESTRICTION','ENABLE_PROTECTED_DOWNLOAD_RESTRICTION','EVAL_AUSWERTUNG_CONFIG_ENABLE','EVAL_AUSWERTUNG_GRAPH_FORMAT','EXTERNAL_FLASH_MOVIE_EMBEDDING','EXTERNAL_HELP','EXTERNAL_HELP_LOCATIONID','EXTERNAL_HELP_URL','EXTERNAL_IMAGE_EMBEDDING','HTML_HEAD_TITLE','MAIL_NOTIFICATION_ENABLE','MAINTENANCE_MODE_ENABLE','NEWS_DISABLE_GARBAGE_COLLECT','NEWS_RSS_EXPORT_ENABLE','ONLINE_NAME_FORMAT','SEMESTER_TIME_SWITCH','SEM_TREE_ALLOW_BRANCH_ASSIGN','SENDFILE_LINK_MODE','WANTED_DEFAULT_VALUES','ZIP_DOWNLOAD_MAX_FILES','ZIP_DOWNLOAD_MAX_SIZE','ZIP_UPLOAD_ENABLE','ZIP_UPLOAD_MAX_DIRS','ZIP_UPLOAD_MAX_FILES')");
        $db->exec("UPDATE config SET value = REPLACE (value, '|', ' ') WHERE field = 'STUDYGROUP_SETTINGS'");
    }

    function down()
    {
        $db = DBManager::get();

        $db->exec("UPDATE config SET section = '' WHERE field LIKE '%resources%'");
        $db->exec("UPDATE config SET section = '' WHERE field LIKE '%studygroup%'");
        $db->exec("UPDATE config SET section = '' WHERE field IN ('BANNER_ADS_ENABLE','CALENDAR_ENABLE','CHAT_ENABLE','ELEARNING_INTERFACE_ENABLE','EXPORT_ENABLE','EXTERN_ENABLE','FOAF_ENABLE','LITERATURE_ENABLE','LOG_ENABLE','SCM_ENABLE','SEMESTER_ADMINISTRATION_ENABLE','SMILEYADMIN_ENABLE','STM_ENABLE','VOTE_ENABLE','WIKI_ENABLE')");
        $db->exec("UPDATE config SET section = '' WHERE field IN ('ALLOW_ADMIN_USERACCESS','ALLOW_DOZENT_ARCHIV','ALLOW_DOZENT_VISIBILITY','ALLOW_SELFASSIGN_INSTITUTE','AUX_RULE_ADMIN_PERM','LOCK_RULE_ADMIN_PERM','INST_FAK_ADMIN_PERMS','RANGE_TREE_ADMIN_PERM','RESTRICTED_USER_MANAGEMENT','SEM_CREATE_PERM','SEM_TREE_ADMIN_PERM','SEM_TREE_SHOW_EMPTY_AREAS_PERM','SEM_VISIBILITY_PERM')");
        $db->exec("UPDATE config SET section = '' WHERE field IN ('ACCESSKEY_ENABLE','DOZENT_ALWAYS_VISIBLE','CHAT_USE_AJAX_CLIENT','ENABLE_SKYPE_INFO','FOAF_SHOW_IDENTITY','FORUM_ANONYMOUS_POSTINGS','HOMEPAGE_VISIBILITY_DEFAULT','MESSAGE_PRIORITY','SHOWSEM_ENABLE','USER_VISIBILITY_UNKNOWN')");
        $db->exec("UPDATE config SET section = '' WHERE field IN ('ADMISSION_ALLOW_DISABLE_WAITLIST','ADMISSION_PRELIM_COMMENT_ENABLE','AUTO_ARCHIVIERUNG','AUTO_ARCHIVIERUNG_LETZTE_AKTIVIT�T','AUTO_ARCHIVIERUNG_SEMESTER','DOCUMENTS_EMBEDD_FLASH_MOVIES','EMAIL_DOMAIN_RESTRICTION','ENABLE_PROTECTED_DOWNLOAD_RESTRICTION','EVAL_AUSWERTUNG_CONFIG_ENABLE','EVAL_AUSWERTUNG_GRAPH_FORMAT','EXTERNAL_FLASH_MOVIE_EMBEDDING','EXTERNAL_HELP','EXTERNAL_HELP_LOCATIONID','EXTERNAL_HELP_URL','EXTERNAL_IMAGE_EMBEDDING','HTML_HEAD_TITLE','MAIL_NOTIFICATION_ENABLE','MAINTENANCE_MODE_ENABLE','NEWS_DISABLE_GARBAGE_COLLECT','NEWS_RSS_EXPORT_ENABLE','ONLINE_NAME_FORMAT','SEMESTER_TIME_SWITCH','SEM_TREE_ALLOW_BRANCH_ASSIGN','SENDFILE_LINK_MODE','WANTED_DEFAULT_VALUES','ZIP_DOWNLOAD_MAX_FILES','ZIP_DOWNLOAD_MAX_SIZE','ZIP_UPLOAD_ENABLE','ZIP_UPLOAD_MAX_DIRS','ZIP_UPLOAD_MAX_FILES')");
        $db->exec("UPDATE config SET value = REPLACE (value, ' ', '|') WHERE field = 'STUDYGROUP_SETTINGS'");
    }
}
