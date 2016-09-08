<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
register1.php - Benutzerregistrierung in Stud.IP, Part I
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Oliver Brakel <obrakel@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/


require '../lib/bootstrap.php';

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
if (!$GLOBALS['ENABLE_SELF_REGISTRATION']){
    ob_start();
    PageLayout::postError(_("Registrierung ausgeschaltet"),
            [_("In dieser Installation ist die M�glichkeit zur Registrierung ausgeschaltet."),
             '<a href="index.php">' . _("Hier geht es zur Startseite."). '</a>']);

    $template = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();
    $template->infobox = $infobox ? array('content' => $infobox) : null;
    echo $template->render();
    page_close();
    die;
}



if ($auth->is_authenticated() && $user->id != "nobody") {
    ob_start();
    PageLayout::postError(_("Sie sind schon als BenutzerIn am System angemeldet!"), ['<a href="index.php">' . _("Hier geht es zur Startseite."). '</a>']);
    $template = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();
    $template->infobox = $infobox ? array('content' => $infobox) : null;
    echo $template->render();
    page_close();
    die;
} else {
    PageLayout::setHelpKeyword("Basis.AnmeldungRegistrierung");
    PageLayout::setTitle(_("Nutzungsbedingungen"));
    // Start of Output
    include 'lib/include/html_head.inc.php'; // Output of html head
    include 'lib/include/header.php';   // Output of Stud.IP head
    include 'lib/include/deprecated_tabs_layout.php';
    
    $auth->logout();
?>

<table width="100%" align="center" border=0 cellpadding=5 cellspacing=0>
<tr><td class="table_header_bold"><?= Icon::create('door-enter', 'info_alt')->asImg() ?><b>&nbsp;<?=_("Nutzungsbedingungen")?></b></td></tr>
<tr><td class="blank">
<br><br>
<?=_("Stud.IP ist ein Open Source Projekt und steht unter der Gnu General Public License (GPL). Das System befindet sich in der st�ndigen Weiterentwicklung.")?>

<? printf(_("F�r Vorschl�ge und Kritik findet sich immer ein Ohr. Wenden Sie sich hierzu entweder an die %sStud.IP Crew%s oder direkt an die %sEntwickler%s."),"<a href=\"mailto:studip-users@lists.sourceforge.net\">", "</a>", "<a href=\"dispatch.php/siteinfo/show\">", "</a>")?>
<br><br>
<?=_("Um den vollen Funktionsumfang von Stud.IP nutzen zu k�nnen, m�ssen Sie sich am System anmelden.")?><br>
<?=_("Das hat viele Vorz�ge:")?><br>
<blockquote>
    <ul>
        <li><?=_("Zugriff auf Ihre Daten von jedem internetf�higen Rechner weltweit,")?>
        <li><?=_("Anzeige neuer Mitteilungen oder Dateien seit Ihrem letzten Besuch,")?>
        <li><?=_("Eine eigenes Profil im System,")?>
        <li><?=_("die M�glichkeit anderen Personen Nachrichten zu schicken oder mit ihnen zu chatten,")?>
        <li><?=_("und vieles mehr.")?></li></blockquote><br>
    </ul>

<?=_("Mit der Anmeldung werden die nachfolgenden Nutzungsbedingungen akzeptiert:")?><br><br>

<?
include("locale/$_language_path/LC_HELP/pages/nutzung.html");
?>
<div style="text-align: center">
    <div class="button-group">
        <?= Studip\LinkButton::create(_('Ich erkenne die Nutzungsbedingungen an'), URLHelper::getLink('register2.php')) ?>
        <?= Studip\LinkButton::create(_('Registrierung abbrechen'), URLHelper::getLink('index.php')) ?>
    </div>
</div>
</td></tr>
<tr><td class="blank">&nbsp;</td></tr>
</table>
<?php
}

include ('lib/include/html_end.inc.php');
page_close();
