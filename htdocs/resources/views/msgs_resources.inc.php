<?
/**
* msgs_resources.inc.php
* 
* library for the messages (error, info and other)
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	resources
* @module		msgs_resources.inc.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// msgs_resources.inc.php
// Alle Meldungen, die in der Ressourcenverwaltung ausgegeben werden
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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


$this->msg[1] = array (
		"mode" => "error",
		"titel" => _("Fehlende Berechtigung"),
		"msg"=> _("Sie haben leider keine Berechtigung, das Objekt zu bearbeiten!"));
$this->msg[2] = array (
		"mode" => "error",
		"titel" => _("Nutzer hat keine Berechtigung")	,
		"msg"=> _("Sie versuchen, einen Nutzer einzutragen, der nicht selbst in der Lage ist, die Belegung zu bearbeiten oder zu l&ouml;schen. Sie k&ouml;nnen diesen Nutzer leider nicht eintragen"));
$this->msg[3] = array (
		"mode" => "msg",
		"titel" => _("Belegung eingetragen"),
		"msg"=> _("Die Belegung wurde eingetragen"));
$this->msg[4] = array (
		"mode" => "msg",
		"titel" => _("Belegung ver&auml;ndert"),
		"msg"=> _("Die Belegung wurde ver&auml;ndert"));
$this->msg[5] = array (
		"mode" => "msg",
		"titel" => _("Belegung gel&ouml;scht"),
		"msg"=> _("Die Belegung wurde gel&ouml;scht"));
$this->msg[6] = array (
		"mode" => "msg",
		"titel" => _("Eigenschaften ver&auml;ndert"),
		"msg"=> _("Die Eigenschaften der Ressource wurden ver&auml;ndert"));
$this->msg[7] = array (
		"mode" => "msg",
		"titel" => _("Ressource gel&ouml;scht"),
		"msg"=> _("Die Ressource wurde gel&ouml;scht"));
$this->msg[8] = array (
		"mode" => "msg",
		"titel" => _("Berechtigungen ver&auml;ndert"),
		"msg"=> _("Die Berechtigungseinstellungen der Ressource wurden ver&auml;ndert"));
$this->msg[9] = array (
		"mode" => "msg",
		"titel" => _("Ressource verschoben"),
		"msg"=> _("Die Ressource wurde verschoben"));
$this->msg[10] = array (
		"mode" => "error",
		"msg"=> _("Bitte geben Sie einen Nutzer f&uuml;r die Belegung an, um diese Belegung zu speichern!"));
$this->msg[11] = array (
		"mode" => "error",
		"msg"=> _("Die Belegung konnte nicht gespeichert werden, da sie sich mit einer anderen Belegung &uuml;berschneidet!"));
$this->msg[12] = array (
		"mode" => "info",
		"msg"=> _("Es existieren keine Ressourcen oder Ebenen, auf die Sie Zugriff haben.")."<br />"._("Um Ressourcen anzulegen, erzeugen sie zun&auml;chst eine neue Ebene, indem sie &raquo;Neue Hierarchie erzeugen&laquo; anw&auml;hlen.")." <br />"._("Anschliessend k&ouml;nnen auf dieser Ebene Ressourcen anlegen"));
$this->msg[13] = array (
		"mode" => "info",
		"msg"=> _("Es existieren keine Ressourcen, die Sie in im Rahmen dieser Veranstaltung belegen d&uuml;rfen."));
$this->msg[14] = array (
		"mode" => "info",
		"msg"=> _("Sie haben keine Ebene ausgew&auml;hlt. Daher kann keine Liste erzeugt werden.")." <br />"._("Benutzen Sie die Suchfunktion oder w&auml;hlen Sie unter &raquo;&Uuml;bersicht&laquo; einen Ebene bzw. Ressource in der Hierachie aus."));
$this->msg[15] = array (
		"mode" => "info",
		"msg"=> _("Sie haben keine Objekt zum Bearbeiten ausgew&auml;hlt.")." <br />"._("Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus."));
$this->msg[16] = array (
		"mode" => "info",
		"msg"=> _("Sie haben keine Objekt zum Anzeigen ausgew&auml;hlt.")." <br />"._("Bitte w&auml;hlen Sie zun&auml;chst ein Objekt aus."));
$this->msg[17] = array (
		"mode" => "error",
		"msg"=> _("Bitte geben Sie g&uuml;ltige Werte f&uuml;r Datum, Beginn und Ende der Belegung an!"));
$this->msg[18] = array (
		"mode" => "error",
		"msg"=> _("Bitte geben Sie einen g&uuml;ltigen Wert f&uuml;r das Ende der Wiederholung an!"));
$this->msg[19] = array (
		"mode" => "error",
		"msg"=> _("Das Ende der Wiederholung darf nicht vor dem ersten Termin der Wiederholung liegen!"));
$this->msg[20] = array (
		"mode" => "error",
		"msg"=> _("Die Startzeit darf nicht vor der Endzeit liegen!"));
$this->msg[21] = array (
		"mode" => "error",
		"msg"=> _("Die j&auml;hrliche Wiederholung darf maximal 10 Jahre dauern!"));
$this->msg[22] = array (
		"mode" => "error",
		"msg"=> _("Die monatliche Wiederholung darf maximal 12 Monate dauern!"));
$this->msg[23] = array (
		"mode" => "error",
		"msg"=> _("Die w&ouml;chentliche Wiederholung darf maximal 50 Wochen dauern!"));
$this->msg[24] = array (
		"mode" => "error",
		"msg"=> _("Die t&auml;gliche Wiederholung darf maximal 30 Tage dauern!"));
$this->msg[25] = array (
		"mode" => "error",
		"titel" => _("Fehlende Berechtigung"),
		"msg"=> _("Sie haben leider keine Berechtigung, diese Funktion zu benutzen!"));		
?>