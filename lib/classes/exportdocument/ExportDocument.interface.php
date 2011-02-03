<?php

/**
 * ExportDocument.interface.php - create and export or save a pdf with simple HTML-Data
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse & Peter Thienel
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


interface ExportDocument {

    public function addPage();

    /**
     * adding an area of Stud.IP formatted content:
     */
    public function addContent($content);

    //outputs the content as a file with MIME-type and aborts any other output:
    public function dispatch($filename);

    //saves the content as a file in the filesystem and returns a Stud.IP document_id
    public function save($filename, $folder_id = null);

}



/*
class ExportSpreadsheet implements ExportDocument {

    //represents table-sheets:
    public function addPage();

    //adds a 10-column formatted content like desciptional text:
    public function addContent();

    //object of a 2-dimensional table
    public function addTable($table);

    //does nothing?
    public function dispatch($filename);

    public function save($folder_id = null);
}

class ExportXLS extends ExportSpreadsheet {

    public function dispatch($filename);

    public function save($folder_id = null);
}

class ExportODS extends ExportSpreadsheet {

    public function dispatch($filename);

    public function save($folder_id = null);
}


*/

//Das Rahmenlayout wird �ber die globale Config gesteuert
//(Schriftfarbe, Schriftart, Schriftgr��e, Headerlogo, Headerschriftzug, etc.)