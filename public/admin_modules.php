<?
# Lifter001: TODO
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/**
* admin_modules.php
*
* switch the modules (defines in Modules.class.php) on/off for Institutes or Veranstaltungen
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_modules.php
// Module fuer Veranstaltungen und Einrichtungen (definiert in Modules.class.php) an/abschalten
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$perm->check("tutor");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session
require_once('lib/msg.inc.php');    //Ausgaben
require_once('config.inc.php'); //Settings....
require_once 'lib/functions.php';   //basale Funktionen
require_once('lib/visual.inc.php'); //Darstellungsfunktionen
require_once('lib/messaging.inc.php');  //Nachrichtenfunktionen
require_once('lib/classes/AdminModules.class.php'); //Nachrichtenfunktionen
require_once 'lib/admin_search.inc.php';

$cssSw=new cssClassSwitcher;
$sess->register("admin_modules_data");
$sess->register("plugin_toggle");


$CURRENT_PAGE = _("Verwaltung verwendeter Module und Plugins");
if ($links_admin_data['topkat'] == 'sem') {
    Navigation::activateItem('/admin/course/modules');
} else {
    Navigation::activateItem('/admin/institute/modules');
}

//get ID
if ($SessSemName[1])
    $range_id=$SessSemName[1];

//Change header_line if open object
$header_line = getHeaderLine($range_id);
if ($header_line)
    $CURRENT_PAGE = $header_line." - ".$CURRENT_PAGE;

//Output starts here

include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
include 'lib/include/admin_search_form.inc.php';

$amodules=new AdminModules;

$admin_modules_plugins = PluginEngine::getPlugins('StandardPlugin'); // get all installed and enabled plugins

if ($perm->have_studip_perm("tutor", $admin_modules_data["range_id"])) {
    //Sicherheitscheck ob ueberhaupt was zum Bearbeiten gewaehlt ist.
    if ($default_x) {
        $admin_modules_data["changed_bin"] = $amodules->getDefaultBinValue($admin_modules_data["range_id"]);
    }
    //consistency: kill objects
    foreach ($amodules->registered_modules as $key => $val) {
        $moduleXxDeactivate = "module".$key."Deactivate";
        $delete_xx = "delete_".$key;

        if (($$delete_xx) && (method_exists($amodules,$moduleXxDeactivate))) {
            $amodules->$moduleXxDeactivate($admin_modules_data["range_id"]);
            $amodules->clearBit($admin_modules_data["changed_bin"], $amodules->registered_modules[$key]["id"]);
            unset($admin_modules_data["conflicts"][$key]);
            $resolve_conflicts = TRUE;
        }
    }
    //consistency: cancel kill objects
    foreach ($amodules->registered_modules as $key => $val) {
        $cancel_xx = "cancel_".$key;

        if ($$cancel_xx) {
            $amodules->setBit($admin_modules_data["changed_bin"], $amodules->registered_modules[$key]["id"]);
            unset($admin_modules_data["conflicts"][$key]);
            $resolve_conflicts = TRUE;
        }
    }

    if (($uebernehmen_x) || ($retry)) {
        $msg='';
        if ($uebernehmen_x){
            foreach ($amodules->registered_modules as $key => $val) {
                //after sending, set all "conflicts" to TRUE (we check them later)
                $admin_modules_data["conflicts"][$key] = TRUE;

                $tmp_key = $key."_value";
                if ($$tmp_key == "TRUE")
                    $$tmp_key = TRUE;
                else
                    $$tmp_key = FALSE;

                if ($$tmp_key) {
                    $amodules->setBit($admin_modules_data["changed_bin"], $amodules->registered_modules[$key]["id"]);
                } else {
                    $amodules->clearBit($admin_modules_data["changed_bin"], $amodules->registered_modules[$key]["id"]);
                }
            }
            // Setzen der Plugins
            foreach ($admin_modules_plugins as $plugin){
                $check = ( $_POST[ "plugin_" . $plugin->getPluginId() ] == "TRUE" );
                $setting = $plugin->isActivated($admin_modules_data['range_id']);
                if( $check != $setting ){
                    array_push( $plugin_toggle , $plugin->getPluginId() );
                }

            }
        }

        //consistency checks
        foreach ($amodules->registered_modules as $key => $val) {
            $delete_xx = "delete_".$key;
            $cancel_xx = "cancel_".$key;

            //checks for deactivating a module
            $getModuleXxExistingItems = "getModule".$key."ExistingItems";

            if (method_exists($amodules,$getModuleXxExistingItems)) {
                if (($amodules->isBit($admin_modules_data["orig_bin"],  $amodules->registered_modules[$key]["id"])) &&
                    (!$amodules->isBit($admin_modules_data["changed_bin"],  $amodules->registered_modules[$key]["id"])) &&
                    ($amodules->$getModuleXxExistingItems($admin_modules_data["range_id"])) &&
                    ($admin_modules_data["conflicts"][$key])) {

                    $msg.="info�".$amodules->registered_modules[$key]["msg_warning"];
                    $msg.="<br /><a href=\"". URLHelper::getLink("?delete_$key=TRUE&retry=TRUE") ."\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
                    $msg.="<a href=\"". URLHelper::getLink("?cancel_$key=TRUE&retry=TRUE") ."\">" . makeButton("nein", "img") . "</a>\n�";
                } else
                    unset($admin_modules_data["conflicts"][$key]);
            } else
                unset($admin_modules_data["conflicts"][$key]);

            //checks for activating a module
            $moduleXxActivate = "module".$key."Activate";

            if (method_exists($amodules,$moduleXxActivate)) {
                if ((!$amodules->isBit($admin_modules_data["orig_bin"],  $amodules->registered_modules[$key]["id"])) &&
                    ($amodules->isBit($admin_modules_data["changed_bin"],  $amodules->registered_modules[$key]["id"]))) {

                    $amodules->$moduleXxActivate($admin_modules_data["range_id"]);
                }
            }

        }

    }
    if( !count( $admin_modules_data["conflicts"] ) )  {
        $changes = false;
        // Module speichern
        if( $admin_modules_data["orig_bin"] != $admin_modules_data["changed_bin"] ){
            $amodules->writeBin($admin_modules_data["range_id"], $admin_modules_data["changed_bin"]);
            $admin_modules_data["orig_bin"] = $admin_modules_data["changed_bin"];
            $admin_modules_data["modules_list"] = $amodules->getLocalModules($admin_modules_data["range_id"]);
            $changes = true;
        }
        // Plugins speichern
        if( count( $plugin_toggle ) > 0 ){
            $context = $admin_modules_data['range_id'];
            $plugin_manager = PluginManager::getInstance();

            foreach ($admin_modules_plugins as $plugin){
                $plugin_id = $plugin->getPluginId();

                if( in_array( $plugin_id , $plugin_toggle ) ){
                    $activated = !$plugin_manager->isPluginActivated($plugin_id, $context);
                    $plugin_manager->setPluginActivated($plugin_id, $context, $activated);
                    $changes = true;
                    // logging
                    if ($activated) {
                        log_event('PLUGIN_ENABLE',$context,$plugin_id, $user->id);
                    }
                    else {
                        log_event('PLUGIN_DISABLE',$context,$plugin_id, $user->id);
                    }
                }
            }
            $plugin_toggle = array();
        }
        if( $changes ){
            $msg .= "msg�Die ver&auml;nderte Modulkonfiguration wurde &uuml;bernommen";
        }
    }
}

//wenn wir frisch reinkommen, werden benoetigte Daten eingelesen
if (($range_id) && (!$uebernehmen_x) && (!$delete_forum) && (!$delete_documents) && (!$resolve_conflicts)) {
    $admin_modules_data["modules_list"] = $amodules->getLocalModules($range_id);
    $admin_modules_data["orig_bin"] = $amodules->getBin($range_id);
    $admin_modules_data["changed_bin"] = $amodules->getBin($range_id);
    $admin_modules_data["range_id"] = $range_id;
    $admin_modules_data["conflicts"] = array();
    $plugin_toggle = array();
} else {
    //Sicherheitscheck ob ueberhaupt was zum Bearbeiten gewaehlt ist.
    if (!$admin_modules_data["range_id"]) {
        echo "</tr></td></table>";
        die;
    }
}

if ($admin_modules_data["range_id"])
{
?>
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="blank" valign="top">
            <?
            if (isset($msg)) {
            ?>
                <table border="0">
                <?parse_msg($msg);?>
                </table>
            <? } ?>
            <br />
            <blockquote>
            <b><?=_("Module konfigurieren") ?></b><br /><br />
            <?=_("Sie k&ouml;nnen hier einzelne Module nachtr&auml;glich aktivieren oder deaktivieren.")?> <br />
            <?=_("Mit &raquo;zur&uuml;cksetzten&laquo; k&ouml;nnen Sie die Ausgangs-Modulkonfiguration wieder herstellen.")?> <br /><br />
            </blockquote>
        </td>
        <td class="blank" align="right" valign="top"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" height="10" width="5" /><br />
            <img src="<?= $GLOBALS['ASSETS_URL'] ?>images/modules.jpg" border="0"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" height="10" width="10" />
        </td>
    </tr>
    <tr>
    <td class="blank" colspan="2">
    <form method="POST" name="modules" action="<?=URLHelper::getLink()?>">
        <table width="100%" border="0" cellpadding="2" cellspacing="0">
        <tr><? $cssSw->switchClass() ?>
            <td class="<?= $cssSw->getClass() ?>" align="center" colspan="3">
                <input type="IMAGE" name="uebernehmen" <?=makeButton("uebernehmen", "src")?> border=0 value="uebernehmen">
                &nbsp;<input type="IMAGE" name="default" <?=makeButton("zuruecksetzen", "src")?> border=0 value="uebernehmen">
                <? if ($admin_modules_data["orig_bin"] != $admin_modules_data["changed_bin"]) {
                    ?> <br /><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/ausruf_small2.gif" align="absmiddle" />&nbsp;<font size=-1><?=_("Diese Daten sind noch nicht gespeichert.")?></font><br /> <?
                    }
                ?>
            </td>
        </tr>
        <?
        foreach ($amodules->registered_modules as $key => $val)
        {
            if ($amodules->isEnableable($key, $admin_modules_data["range_id"]))
            {
                $pre_check = null;
                if (isset($val['preconditions'])){
                    $method = 'module' . $key . 'Preconditions';
                    if(method_exists($amodules, $method)) $pre_check = $amodules->$method($admin_modules_data["range_id"],$val['preconditions']);
                }

                ?>
            <tr><? $cssSw->switchClass() ?>
                <td class="<?= $cssSw->getClass() ?>"  width="15%" align="left">
                    <font size=-1><b><?=$val["name"]?></b><br /></font>
                </td>
                <td class="<?= $cssSw->getClass() ?>" width="15%">
                    <input type="RADIO" <?=($pre_check ? 'disabled' : '')?> name="<?=$key?>_value" value="TRUE" <?=($amodules->isBit($admin_modules_data["changed_bin"], $val["id"])) ? "checked" : "" ?>>
                    <font size=-1><?=_("an")?></font>
                    <input type="RADIO" <?=($pre_check ? 'disabled' : '')?> name="<?=$key?>_value" value="FALSE" <?=($amodules->isBit($admin_modules_data["changed_bin"], $val["id"])) ? "" : "checked" ?>>
                    <font size=-1><?=_("aus")?><br /></font>
                </td>
                <td class="<?= $cssSw->getClass() ?>" width="70%">
                    <font size=-1><?
                    $getModuleXxExistingItems = "getModule".$key."ExistingItems";

                    if (method_exists($amodules,$getModuleXxExistingItems)) {
                        if (($amodules->$getModuleXxExistingItems($admin_modules_data["range_id"])) && ($admin_modules_data["modules_list"][$key]))
                            printf ("<font color=\"red\">".$amodules->registered_modules[$key]["msg_pre_warning"]."</font>", $amodules->$getModuleXxExistingItems($admin_modules_data["range_id"]));
                        else
                            print ($admin_modules_data["modules_list"][$key]) ? $amodules->registered_modules[$key]["msg_deactivate"] : ($pre_check ? $pre_check : $amodules->registered_modules[$key]["msg_activate"]);
                    } else
                        print ($admin_modules_data["modules_list"][$key]) ? $amodules->registered_modules[$key]["msg_deactivate"] : ($pre_check ? $pre_check : $amodules->registered_modules[$key]["msg_activate"]);
                    ?></font>
                </td>
            </tr>
            <? }

        }
        foreach ($admin_modules_plugins as $plugin)
        {
            $plugin_activated = $plugin->isActivated($admin_modules_data['range_id']);
            ?>
            <tr><? $cssSw->switchClass() ?>
                <td class="<?= $cssSw->getClass() ?>"  width="15%" align="left">
                    <font size=-1><b><?=$plugin->getPluginname()?></b><br /></font>
                </td>
                <td class="<?= $cssSw->getClass() ?>" width="15%">
                    <!-- mark old state -->
                    <input type="RADIO" name="plugin_<?=$plugin->getPluginId()?>" value="TRUE" <?= $plugin_activated ? "checked" : "" ?>>
                    <font size=-1><?=_("an")?></font>
                    <input type="RADIO" name="plugin_<?=$plugin->getPluginId()?>" value="FALSE" <?= $plugin_activated ? "" : "checked" ?>>
                    <font size=-1><?=_("aus")?><br /></font>
                </td>
                <td class="<?= $cssSw->getClass() ?>" width="70%">
                    <font size="-1">
                    <? if (!$plugin_activated): ?>
                        <?= _('Dieses Plugin kann jederzeit aktiviert werden.') ?>
                    <? elseif ($warning = $plugin->deactivationWarning($admin_modules_data['range_id'])): ?>
                        <font color="red"><?= $warning ?></font>
                    <? else: ?>
                        <?= _('Dieses Plugin kann jederzeit deaktiviert werden.') ?>
                    <? endif ?>
                    </font>
                </td>
            </tr>
            <?php
        }
    ?>
    </table>
    </td>
</tr>
</table>

<?
}
else
{
    die;
}

include ('lib/include/html_end.inc.php');
page_close();
?>
