<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

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


require_once 'lib/classes/LockRule.class.php';

/**
* LockRules.class.php
*
*
*
* @author     Mark Sievers <msievers@uos.de>
* @access     public
* @modulegroup
* @module
* @package
*/

class LockRules {

    private static $lockmap = array();
    private static $lockrules = array();


    public static function get($lock_id)
    {
        if(!array_key_exists($lock_id, self::$lockrules)) {
            self::$lockrules[$lock_id] = LockRule::find($lock_id);
        }
        return self::$lockrules[$lock_id];
    }

    public static function getObjectRule($object_id)
    {
        if(!array_key_exists($object_id, self::$lockmap)) {
                $object_type = get_object_type($object_id, words('sem inst user'));
                $methodmap = array('sem'  => 'Seminar',
                                   'inst' => 'Institute',
                                   'fak'  => 'Institute',
                                   'user' => 'User');
                $lr = call_user_func(array('LockRule', 'FindBy' . $methodmap[$object_type]), $object_id);
                if ($lr) {
                    self::$lockmap[$object_id] = $lr->getId();
                    self::$lockrules[$lr->getId()] = $lr;
                } else {
                    self::$lockmap[$object_id] = null;
                }
        }
        return self::$lockmap[$object_id] ? self::$lockrules[self::$lockmap[$object_id]] : null;
    }

    public static function Check($object_id, $attribute)
    {
        $lr = self::getObjectRule($object_id);
        if ($lr) {
            return isset($lr['attributes'][strtolower($attribute)]) && self::CheckLockRulePermission($object_id);
        } else {
            return false;
        }
    }

    public static function CheckLockRulePermission($object_id)
    {
        $perms = array('autor','tutor','dozent','admin','root','god');
        $lr = self::getObjectRule($object_id);

        if ($lr) {
            $pk = array_search($lr->permission, $perms);
            $check_perm = $perms[$pk + 1];
            if ($lr->object_type == 'sem') {
                return ($lr->permission == 'root' || !$GLOBALS['perm']->have_studip_perm($check_perm, $object_id));
            }
            if ($lr->object_type == 'inst') {
                return ($lr->permission == 'root' || !$GLOBALS['perm']->have_perm('root'));
            }
            if ($lr->object_type == 'user') {
                return ($lr->permission == 'root' || !$GLOBALS['perm']->have_perm($check_perm));
            }
        }
        return false;
    }

    public static function getLockRuleConfig($type)
    {
        $groups['basic'] = _("Grunddaten");
        $groups['personnel'] = _("Personen und Einordnung");
        $groups['misc'] = _("weitere Daten");
        $groups['room_time'] = _("Zeiten/R�ume");
        $groups['access'] = _("Zugangsberechtigungen");
        $groups['actions'] = _("spezielle Aktionen");

        $attributes['sem']['veranstaltungsnummer'] = array('name' => _("Veranstaltungsnummer"), 'group' => 'basic');
        $attributes['sem']['seminar_inst'] = array('name' => _("beteiligte Einrichtungen"), 'group' => 'basic');
        $attributes['sem']['name'] = array('name' => _("Name"), 'group' => 'basic');
        $attributes['sem']['untertitel'] = array('name' => _("Untertitel"), 'group' => 'basic');
        $attributes['sem']['status'] = array('name' => _("Status"), 'group' => 'basic');
        $attributes['sem']['beschreibung'] = array('name' => _("Beschreibung"), 'group' => 'basic');
        $attributes['sem']['ort'] = array('name' => _("Ort"), 'group' => 'basic');
        $attributes['sem']['art'] = array('name' => _("Veranstaltungstyp"), 'group' => 'basic');
        $attributes['sem']['ects'] = array('name' => _("ECTS-Punkte"), 'group' => 'basic');
        $attributes['sem']['admission_turnout'] = array('name' => _("Teilnehmerzahl"), 'group' => 'basic');
        $attributes['sem']['dozent'] = array('name' => _("DozentInnen"), 'group' => 'personnel');
        $attributes['sem']['tutor'] = array('name' => _("TutorInnen"), 'group' => 'personnel');
        $attributes['sem']['institut_id'] = array('name' => _("Heimateinrichtung"), 'group' => 'personnel');
        $attributes['sem']['sem_tree'] = array('name' => _("Studienbereiche"), 'group' => 'personnel');
        $attributes['sem']['participants'] = array('name' => _("Teilnehmer hinzuf�gen/l�schen"), 'group' => 'personnel');
        $attributes['sem']['groups'] = array('name' => _("Gruppen hinzuf�gen/l�schen"), 'group' => 'personnel');
        $attributes['sem']['sonstiges'] = array('name' => _("Sonstiges"), 'group' => 'misc');
        $attributes['sem']['teilnehmer'] = array('name' => _("Beschreibung des Teilnehmerkreises"), 'group' => 'misc');
        $attributes['sem']['voraussetzungen'] = array('name' => _("Teilnahmevoraussetzungen"), 'group' => 'misc');
        $attributes['sem']['lernorga'] = array('name' => _("Lernorganisation"), 'group' => 'misc');
        $attributes['sem']['leistungsnachweis'] = array('name' => _("Leistungsnachweis"), 'group' => 'misc');
        $attributes['sem']['room_time'] = array('name' => _("Zeiten/R�ume"), 'group' => 'room_time');
        $attributes['sem']['admission_endtime'] = array('name' => _("Zeit/Datum des Losverfahrens/Kontingentierung"), 'group' => 'access');
        $attributes['sem']['admission_disable_waitlist'] = array('name' => _("Aktivieren/Deaktivieren der Warteliste"), 'group' => 'access');
        $attributes['sem']['admission_binding'] = array('name' => _("Verbindlichkeit der Anmeldung"), 'group' => 'access');
        $attributes['sem']['admission_type'] = array('name' => _("Typ des Anmeldeverfahrens"), 'group' => 'access');
        $attributes['sem']['admission_prelim'] = array('name' => _("zugelassenene Studieng�nge"), 'group' => 'access');
        $attributes['sem']['admission_prelim_txt'] = array('name' => _("Vorl�ufigkeit der Anmeldungen"), 'group' => 'access');
        $attributes['sem']['admission_disable_waitlist'] = array('name' => _("Hinweistext bei Anmeldungen"), 'group' => 'access');
        $attributes['sem']['admission_starttime'] = array('name' => _("Startzeitpunkt der Anmeldem�glichkeit"), 'group' => 'access');
        $attributes['sem']['admission_endtime_sem'] = array('name' => _("Endzeitpunkt der Anmeldem�glichkeit"), 'group' => 'access');
        $attributes['sem']['lesezugriff'] = array('name' => _("Lesezugriff"), 'group' => 'access');
        $attributes['sem']['schreibzugriff'] = array('name' => _("Schreibzugriff"), 'group' => 'access');
        $attributes['sem']['passwort'] = array('name' => _("Passwort"), 'group' => 'access');
        $attributes['sem']['user_domain'] = array('name' => _("Veranstaltung kopieren"), 'group' => 'access');
        $attributes['sem']['seminar_copy'] = array('name' => _("Veranstaltung kopiere"), 'group' => 'actions');
        $attributes['sem']['seminar_archive'] = array('name' => _("Veranstaltung archivieren"), 'group' => 'actions');
        $attributes['sem']['seminar_visibility'] = array('name' => _("Veranstaltung sichtbar/unsichtbar schalten"), 'group' => 'actions');

        $attributes['inst']['name'] = array('name' => _("Name"), 'group' => 'basic');
        $attributes['inst']['fakultaets_id'] = array('name' => _("Fakult�t"), 'group' => 'basic');
        $attributes['inst']['type'] = array('name' => _("Bezeichnung"), 'group' => 'basic');
        $attributes['inst']['strasse'] = array('name' => _("Stra�e"), 'group' => 'basic');
        $attributes['inst']['plz'] = array('name' => _("Ort"), 'group' => 'basic');
        $attributes['inst']['telefon'] = array('name' => _("Telefonnummer"), 'group' => 'basic');
        $attributes['inst']['fax'] = array('name' => _("Faxnummer"), 'group' => 'basic');
        $attributes['inst']['email'] = array('name' => _("E-Mail-Adresse"), 'group' => 'basic');
        $attributes['inst']['url'] = array('name' => _("Homepage"), 'group' => 'basic');
        $attributes['inst']['participants'] = array('name' => _("Mitarbeiter hinzuf�gen/l�schen"), 'group' => 'personnel');
        $attributes['inst']['groups'] = array('name' => _("Gruppen hinzuf�gen/l�schen"), 'group' => 'personnel');

        $attributes['user']['name'] = array('name' => _("Vor- und Nachname"), 'group' => 'basic');
        $attributes['user']['username'] = array('name' => _("Nutzername"), 'group' => 'basic');
        $attributes['user']['passwort'] = array('name' => _("Passwort"), 'group' => 'basic');
        $attributes['user']['email'] = array('name' => _("E-Mail"), 'group' => 'basic');
        $attributes['user']['title'] = array('name' => _("Titel"), 'group' => 'basic');
        $attributes['user']['gender'] = array('name' => _("Geschlecht"), 'group' => 'basic');
        $attributes['user']['privatnr'] = array('name' => _("Telefon (privat)"), 'group' => 'basic');
        $attributes['user']['privatcell'] = array('name' => _("Mobiltelefon"), 'group' => 'basic');
        $attributes['user']['privadr'] = array('name' => _("Adresse (privat)"), 'group' => 'basic');
        $attributes['user']['hobby'] = array('name' => _("Hobbys"), 'group' => 'basic');
        $attributes['user']['lebenslauf'] = array('name' => _("Lebenslauf"), 'group' => 'basic');
        $attributes['user']['home'] = array('name' => _("Homepage"), 'group' => 'basic');
        $attributes['user']['publi'] = array('name' => _("Schwerpunkte"), 'group' => 'misc');
        $attributes['user']['schwerp'] = array('name' => _("Publikationen"), 'group' => 'misc');
        $attributes['user']['institute_data'] = array('name' => _("Einrichtungsdaten"), 'group' => 'misc');

        foreach(DataFieldStructure::getDataFieldStructures($type) as $df_id => $df) {
            $attributes[$type][$df_id] = array('name' => $df->data['name'], 'group' => 'misc');
        }

        return array('groups' => $groups,'attributes' => $attributes[$type]);
    }

}
