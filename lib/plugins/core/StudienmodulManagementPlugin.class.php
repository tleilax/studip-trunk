<?php
# Lifter007: TODO
# Lifter003: TODO
/*
* StudienmodulManagementPlugin.class.php
*
* Copyright (C) 2008 - Andr� Noack <noack@data-quest.de>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as
* published by the Free Software Foundation; either version 2 of
* the License, or (at your option) any later version.
*/


/**
* StudienmodulManagementPlugin Interface.
*
* @author    anoack
* @copyright (c) Authors
* @version   $Id$
*/

interface StudienmodulManagementPlugin {
	
	/**
	 * Gibt die Navigationsstruktur f�r den Modulkatalog/Modulsuche zur�ck.
	 * Wird neben "Meine Veranstaltungen" und "Veranstaltungen suchen" eingeblendet
	 * Kann beliebiges Submen� enthalten
	 *
	 * @return StudipPluginNavigation
	 */
	function getModuleCatalogNavigation();
	
	/**
	 * Gibt das Navigationsobjekt f�r den Men�punkt "Meine Module" zur�ck.
	 * Wird unter der Topnavigation "Meine Veranstaltungen" eingeblendet
	 *
	 * @return StudipPluginNavigation
	 */
	function getMyModulesNavigation();
	
	/**
	 * Gibt die Bezeichnung f�r ein Modul zur�ck
	 *
	 * @param string $module_id eine ID aus der Tabelle sem_tree
	 * @param string $semester_id eine ID aus der Tabelle semester_data
	 * 
	 * @return string
	 */
	function getModuleTitle($module_id, $semester_id = null);
	
	/**
	 * Gibt die Kurzbeschreibung f�r ein Modul zur�ck
	 *
	 * @param string $module_id eine ID aus der Tabelle sem_tree
	 * @param string $semester_id eine ID aus der Tabelle semester_data
	 * 
	 * @return string
	 */
	function getModuleDescription($module_id, $semester_id = null);
	
	/**
	 * Gibt einen HTML Schnipsel zur�ck, der z.B. zur Darstellung eines Info Icons
	 * benutzt werden kann. Wird an verschiedenen Stellen direkt hinter dem Namen 
	 * ausgegeben, wenn es sich um ein Modul handelt
	 *
	 * @param string $module_id $module_id eine ID aus der Tabelle sem_tree
	 * @param string $semester_id eine ID aus der Tabelle semester_data
	 * 
	 * @return string
	 */
	function getModuleInfoHTML($module_id, $semester_id = null);
	
	/**
	 * Gibt eine Url zur�ck, die �ber eine entsprechende Plugin Aktion eine komplette
	 * Seite mit ausf�hrlicher Beschreibung des Moduls ergeben soll 
	 *
	 * @param string $module_id eine ID aus der Tabelle sem_tree
	 * @param string $semester_id eine ID aus der Tabelle semester_data
	 * 
	 * @return string
	 */
	function getModuleDescriptionUrl($module_id, $semester_id = null);
	
	/**
	 * Die Methode wird immer aufgerufen, wenn eine Veranstaltung einer Ebene im
	 * Bereichsbaum zugewiesen wurde, die vom Typ "Modul" ist
	 *
	 * @param string $module_id eine ID aus der Tabelle sem_tree
	 * @param string $course_id eine ID aus der Tabelle seminare
	 * 
	 * @return void
	 */
	function triggerCourseAddedToModule($module_id, $course_id);
	
	/**
	 * Die Methode wird immer aufgerufen, wenn eine Veranstaltung aus einer Ebene im
	 * Bereichsbaum entfernt wurde, die vom Typ "Modul" ist
	 *
	 * @param string $module_id eine ID aus der Tabelle sem_tree
	 * @param string $course_id eine ID aus der Tabelle seminare
	 * 
	 * @return void
	 */
	function triggerCourseRemovedFromModule($module_id, $course_id);
	
}

?>
