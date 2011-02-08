<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
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
*/

interface StudienmodulManagementPlugin {
    
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
     * Gibt ein Objekt vom Typ Navigation zur�ck, das Titel, Link und Icon f�r
     * ein Modul enthalten kann, z.B. zur Darstellung eines Info Icons
     *
     * @param string $module_id $module_id eine ID aus der Tabelle sem_tree
     * @param string $semester_id eine ID aus der Tabelle semester_data
     * 
     * @return Navigation
     */
    function getModuleInfoNavigation($module_id, $semester_id = null);
    
    
}

?>
