<?php
/**
 * boostrap_globals.php - A bootstrap file to be run before tests.
 *
 * This file tries to be minimalistic while enabling a maximum of
 * Stud.IP functionality for unit tests.
 *
 * It is meant to be used with PHPunit. You can either include it in
 * your test file with "require_once 'bootstrap_globals.php';" or 
 * provide it by a command line switch to PHPunit: "phpunit --bootstrap
 * bootstrap_globals.php" (don't forget to prefix the correct path).
 *
 * If it becomes necessary to edit this file, then take care to unset 
 * any variables defined by this file before it exits. Implement helper
 * functions as lambdas to be able to unset them, too. Have a look at
 * existing code ($joindirs and $studip_root) as an example.
 *
 * NOTE i decided to rather create a new one than modify the existing
 *      bootstrap.php, since that one's used by many other tests which
 *      don't depend on a running Stud.IP environment
 *
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2013 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */

// it would be better to keep "notice" and "strict" switched on,
// but currently a lot of Stud.IP code doesn't exactly follow rules...
// TODO fix Stud.IP code that raises "notice" and "strict" errors
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

// augment PHP include paths
$joindirs = function(){ return join(DIRECTORY_SEPARATOR, func_get_args()); };
$studip_root = realpath($joindirs(dirname(__FILE__), '..', '..'));
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $studip_root);
unset($joindirs);
unset($studip_root);

// explicitely declare globals, as PHPunit won't put them into $GLOBALS
// if they're used without being declared as global, first
//
// NOTE various forum entries claim that "phpunit --no-globals-backup"
//      fixes this problem. it didn't for me, though. also, i'd prefer
//      to have PHPunit backup globals.
//
// to get a list of globals, create a Stud.IP page containing the line:
//     echo implode(',', array_keys($GLOBALS));
global $PHP_SELF, $STUDIP_BASE_PATH, $UNI_NAME_CLEAN, $STUDIP_INSTALLATION_ID,
    $DB_STUDIP_HOST, $DB_STUDIP_USER, $DB_STUDIP_PASSWORD, $DB_STUDIP_DATABASE, 
    $ABSOLUTE_PATH_STUDIP, $CANONICAL_RELATIVE_PATH_STUDIP, 
    $ABSOLUTE_URI_STUDIP, $php_errormsg, $ASSETS_URL, $PLUGINS_PATH, 
    $UPLOAD_PATH, $ARCHIV_PATH, $EXTERN_CONFIG_FILE_PATH, 
    $DYNAMIC_CONTENT_PATH, $DYNAMIC_CONTENT_URL, $TMP_PATH, $ZIP_USE_INTERNAL, 
    $ZIP_PATH, $ZIP_OPTIONS, $UNZIP_PATH, $MEDIA_CACHE_PATH, 
    $MEDIA_CACHE_MAX_LENGTH, $MEDIA_CACHE_LIFETIME, $MEDIA_CACHE_MAX_FILES, 
    $RELATIVE_PATH_RESOURCES, $RELATIVE_PATH_CALENDAR, 
    $RELATIVE_PATH_ADMIN_MODULES, $RELATIVE_PATH_EXTERN, 
    $RELATIVE_PATH_ELEARNING_INTERFACE, $RELATIVE_PATH_SOAP, $PATH_EXPORT, 
    $CACHING_ENABLE, $CACHING_FILECACHE_PATH, $CALENDAR_DRIVER, $XSLT_ENABLE, 
    $FOP_ENABLE, $FOP_SH_CALL, $EXTERN_SERVER_NAME, $EXTERN_SRI_ENABLE, 
    $EXTERN_SRI_ENABLE_BY_ROOT, $EXTERN_ALLOW_ACCESS_WITHOUT_CONFIG, 
    $SOAP_ENABLE, $SOAP_USE_PHP5, $WEBSERVICES_ENABLE, 
    $ELEARNING_INTERFACE_MODULES, $PLUGINS_UPLOAD_ENABLE, $PLUGIN_REPOSITORIES, 
    $SMILEY_COUNTER, $MAIL_TRANSPORT, $MAIL_HOST_NAME, $MAIL_LOCALHOST, 
    $MAIL_CHARSET, $MAIL_ENV_FROM, $MAIL_FROM, $MAIL_ABUSE, 
    $MAIL_BULK_DELIVERY, $MAIL_VALIDATE_HOST, $MAIL_VALIDATE_BOX, 
    $MESSAGING_FORWARD_AS_EMAIL, $MESSAGING_FORWARD_DEFAULT, 
    $ENABLE_EMAIL_TO_STATUSGROUP, $ENABLE_EMAIL_ATTACHMENTS, 
    $ALLOW_GROUPING_SEMINARS, $ALLOW_SELFASSIGN_STUDYCOURSE, 
    $SHOW_TERMS_ON_FIRST_LOGIN, $USER_VISIBILITY_CHECK, $CONVERT_IDNA_URL, 
    $USERNAME_REGULAR_EXPRESSION, $DEFAULT_TIMEZONE, $INSTALLED_LANGUAGES, 
    $DEFAULT_LANGUAGE, $_language_domain, $_lit_search_plugins, 
    $STUDIP_AUTH_PLUGIN, $STUDIP_AUTH_CONFIG_STANDARD, $ALLOW_CHANGE_USERNAME, 
    $ALLOW_CHANGE_EMAIL, $ALLOW_CHANGE_NAME, $ALLOW_CHANGE_TITLE, 
    $ENABLE_SELF_REGISTRATION, $ENABLE_REQUEST_NEW_PASSWORD_BY_USER, 
    $REQUEST_NEW_PASSWORD_SECRET, $ENABLE_FREE_ACCESS, $UNI_NAME, 
    $CALENDAR_MAX_EVENTS, $export_ex_types, $export_icon, $export_o_modes, 
    $FLASHPLAYER_DEFAULT_CONFIG_MIN, $FLASHPLAYER_DEFAULT_CONFIG_MAX, 
    $INST_ADMIN_DATAFIELDS_VIEW, $INST_MODULES, $INST_STATUS_GROUPS, 
    $INST_TYPE, $LIT_LIST_FORMAT_TEMPLATE, $NAME_FORMAT_DESC, $output_formats, 
    $PERS_TERMIN_KAT, $record_of_study_templates, $SCM_PRESET, 
    $SEM_STATUS_GROUPS, $skip_page_3, $SMILE_SHORT, $SYMBOL_SHORT, $TERMIN_TYP, 
    $TIME_PRESETS, $TITLE_FRONT_TEMPLATE, $TITLE_REAR_TEMPLATE, $UNI_CONTACT, 
    $UNI_INFO, $UNI_LOGIN_ADD, $UNI_LOGOUT_ADD, $UNI_URL, $UPLOAD_TYPES, 
    $username_prefix, $xml_filename, $xslt_filename_default, $SEM_TREE_TYPES, 
    $NOT_HIDEABLE_FIELDS, $TEILNEHMER_IMPORT_DATAFIELDS, 
    $DEFAULT_TITLE_FOR_STATUS, $TEILNEHMER_VIEW, $LIT_IMPORT_PLUGINS, 
    $wiki_keyword_regex, $wiki_link_regex, $wiki_extended_link_regex, 
    $template_factory;
