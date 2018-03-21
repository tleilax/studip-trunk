<?php

/**
 * mvv_modul_deskriptor_EN.php
 * Configures the permissions for Modul-Deskriptoren in english
 * (table mvv_modul_deskriptor)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */


/**
 * Permissions
 * ============
 * 
 * read: MVVPlugin::PERM_READ | 1
 * read && write: MVVPlugin::PERM_WRITE | 3
 * read && write && create && delete: MVVPlugin::PERM_CREATE | 7
 * 
 * Structure
 * ==========
 * 
 * ['default_table' => [name_of_role => permission]]
 * Permissions for the object itself regardless of its status.
 * Every tuple defines the permission for a different role (the role of a user
 * who wants to handle this object).
 * 
 * ['default_fields' => [name_of_role => permission]]
 * Default permissions for all fields of this object regardless of its status.
 * Maybe overwritten by an entry for a single field.
 * Every tuple defines the permission for a different role (the role of a user
 * who wants to handle this object).
 * 
 * ['fields' => ... ]
 * Permissions for a single field of this object (db_fields and relations of
 * the SORM-object). Overwites above declaration for this field.
 * 
 * ['fields' => name_of_field ['default' => [name_of_role => permission]]]
 * Default permission for one field for every given role regardless of
 * object's status.
 * 
 * ['fields' => name_of_field [name_of_status => [name_of_role => permission]]]
 * Permission for one field of the object with indicated status for every
 * given role. Overwrites above declaration.
 *
 */


// Tabelle mvv_modul_deskriptor_EN
// Es muss unterschieden werden zwischen dem Deskriptor für die Default-Sprache
// (Übersetzer hat keine Berechtigung) und den Deskriptoren in weiteren Sprachen
// (Übersetzer ist Schreibberechtigt)
$privileges = array(
    'lock_status' => array(
        'ausgelaufen'
    ),
    'default_table' => array(
        'MVVEntwickler' => 7,
        'MVVRedakteur'  => 3,
        'MVVTranslator' => 3,
        'MVVFreigabe'   => 7
    ),
    'table' => array(
        'planung' => array(
            'MVVEntwickler' => 7,
            'MVVRedakteur'  => 3,
            'MVVTranslator' => 3,
            'MVVFreigabe'   => 7
        ),
        'genehmigt' => array(
            'MVVEntwickler' => 3,
            'MVVRedakteur'  => 3,
            'MVVTranslator' => 3,
            'MVVFreigabe'   => 3
        ),
        'ausgelaufen' => array(
            'MVVEntwickler' => 1,
            'MVVRedakteur'  => 1,
            'MVVTranslator' => 1,
            'MVVFreigabe'   => 1
        )
    ),
    'default_fields' => array(
        'MVVEntwickler' => 1,
        'MVVRedakteur'  => 1,
        'MVVTranslator' => 1,
        'MVVFreigabe'   => 1
    ),
    'fields' => array(
        // wird beim Erstellen vorgegeben
        'sprache' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'modul_id' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 1,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'verantwortlich' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 1,
                'MVVFreigabe'   => 3
            )
        ),
        'bezeichnung' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'voraussetzung' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'kompetenzziele' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'inhalte' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'literatur' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'links' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'kommentar' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'turnus' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'kommentar_kapazitaet' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'kommentar_sws' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'kommentar_wl_selbst' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'kommentar_wl_pruef' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'kommentar_note' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'pruef_vorleistung' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'pruef_leistung' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'pruef_wiederholung' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 1,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'ersatztext' => array(
            'planung' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            ),
            'genehmigt' => array(
                'MVVEntwickler' => 3,
                'MVVRedakteur'  => 3,
                'MVVTranslator' => 3,
                'MVVFreigabe'   => 3
            )
        ),
        'datafields' => array(
            'default' => array(
                'planung' => array(
                    'MVVEntwickler' => 3,
                    'MVVRedakteur'  => 3,
                    'MVVTranslator' => 1,
                    'MVVFreigabe'   => 3
                ),
                'genehmigt' => array(
                    'MVVEntwickler' => 1,
                    'MVVRedakteur'  => 3,
                    'MVVTranslator' => 1,
                    'MVVFreigabe'   => 1
                )
            ),
            /* Use id of datafield as key :
            'da02d4d437c8bf08fd3f10d9974aca46' => array(
                'planung' => array(
                    'MVVEntwickler' => 3,
                    'MVVRedakteur'  => 1,
                    'MVVTranslator' => 1,
                    'MVVFreigabe'   => 3
                ),
                'genehmigt' => array(
                    'MVVEntwickler' => 1,
                    'MVVRedakteur'  => 3,
                    'MVVTranslator' => 1,
                    'MVVFreigabe'   => 1
                )
            )
             * 
             */
        )
    )
);
