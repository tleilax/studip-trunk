<?php
# Lifter010: TODO
/*
 * DocumentNavigation.php - navigation for document page
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Gerd Hoffmann
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2 or later
 * @category    Stud.IP
 * @version     3.0
 */

/**
 * Navigation for the document page used for user interaction.
 * It includes a filemanager for the user's personal disk-space in Stud.IP.
 */

class DocumentNavigation extends Navigation
 {
  public function __construct()
   {
    parent::__construct(_('Dokumente'));
    $documentinfo = "Zur Dateiverwaltung";
    $this -> setImage('header/files.png', array('title' => $documentinfo, "@2x" => TRUE));
   }

  /**
   * Initialize the subnavigation of this item. This method
   * is called once before the first item is added or removed.
   */
  public function initSubNavigation()
   {
    global $perm;

    parent::initSubNavigation();
    
    if (($GLOBALS['auth'] -> is_authenticated() || $GLOBALS['user'] -> id === 'nobody' 
         || $GLOBALS['perm'] -> have_perm('admin')) && Config::get() -> PERSONALDOCUMENT_ENABLE)  
      {
       $navigation = new Navigation(_('Dateien'), 'dispatch.php/document/dateien');
       $this -> addSubNavigation('dateien', $navigation);
      }
   }
 }