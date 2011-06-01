<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* startup_checks.php
*
* checks if all requirements to create Veranstaltungen are set up. If evreything is fine, no output will be generated.
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @module       startup_checks.php
* @modulegroup      admin
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_modules.php
// ueberprueft, oba alle Voraussetzungen zum Anlegen von Veranstaltungen erf&uuml;llt sind. Wenn alles in Ordnung ist, wird keine Ausgabe erzeugt.
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

$perm->check("dozent");

require_once('lib/msg.inc.php');    //Ausgaben
require_once('lib/classes/StartupChecks.class.php');

$checks=new StartupChecks;
$list = $checks->getCheckList();

$problems_found = 0;

foreach ($list as $key=>$val) {
    if ($val)
        $problems_found++;
}

if ($problems_found) { ?>
    <table width="100%" border=0 cellpadding=0 cellspacing=0>
    <tr>
        <td class="topic" colspan=2><b>Startup Checks</b></td>
    </tr>
    <tr>
         <td class="blank" colspan=2>
            <?= MessageBox::info(_("Das Anlegen einer Veranstaltung ist leider zu diesem Zeitpunkt noch nicht m�glich, da zun�chst die folgenden Voraussetzungen geschaffen werden m&uuml;ssen."), ($problems_found > 1) ? array(_("(Beachten Sie bitte die angegebene Reihenfolge!)")) : ""); ?>
        </td>
    </tr>
    <tr>
    <td class="blank" colspan=2>
        <table width="99%" border=0 cellpadding=2 cellspacing=0 align="center">
        <tr <? $cssSw->switchClass() ?>>
            <td class="<? echo $cssSw->getClass() ?>" align="center" colspan="4">
                <a href="<?=$PHP_SELF?>"><?=makeButton("aktualisieren")?></a>
            </td>
        </tr>
        <?
        $i=0;
        foreach ($list as $key => $val) {
            if ($val) {
                if ($problems_found > 1)
                    $i++;
            ?>
            <tr <? $cssSw->switchClass() ?> rowspan=2>
                <td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
                    &nbsp;
                </td>
                <td class="<? echo $cssSw->getClass() ?>"  width="3%" align="left">
                    <?= Assets::img('icons/16/grey/info-circle.png') ?>
                </td>
                <td class="<? echo $cssSw->getClass() ?>"  width="2%" align="center" valign="top">
                    <font size="-1"><b><?=($i) ? $i."." : ""?></b></font>
                </td>
                <td class="<? echo $cssSw->getClass() ?>" width="91%" valign="top">
                    <font size="-1"><?if (($checks->registered_checks[$key]["msg_fak_admin"]) && ($perm->is_fak_admin())) print $checks->registered_checks[$key]["msg_fak_admin"]; else print $checks->registered_checks[$key]["msg"]; ?></font><br>
                    <font size="-1">Aktion:&nbsp;<?=formatReady("=)")?>&nbsp;
                        <a href="<?=(($checks->registered_checks[$key]["link_fak_admin"]) && ($perm->is_fak_admin())) ? $checks->registered_checks[$key]["link_fak_admin"] : $checks->registered_checks[$key]["link"]?>">
                            <?=(($checks->registered_checks[$key]["link_name_fak_admin"]) && ($perm->is_fak_admin())) ? $checks->registered_checks[$key]["link_name_fak_admin"] : $checks->registered_checks[$key]["link_name"]?>
                        </a>
                    </font><br>
                </td>
            </tr>
            <? }

        }
        ?>
        <tr>
            <td class="blank" colspan=3>&nbsp;
            </td>
        </tr>
    </table>
</td>
</tr>
</table>
<?php
include ('lib/include/html_end.inc.php');
page_close();
die;
}
