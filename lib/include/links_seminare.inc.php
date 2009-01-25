<?
# Lifter002: TODO
/*
links_seminare.inc.php - Navigation fuer die Uebersichtsseiten.
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de

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

require_once 'lib/include/reiter.inc.php';

$reiter = new reiter;

//Create Reitersystem

//Topkats
$structure = array();
if (!$GLOBALS['perm']->have_perm('root')){
	$structure['meine_veranstaltungen'] = array ('topKat' => '', 'name' => _("Meine&nbsp;Veranstaltungen"), 'link' => 'meine_seminare.php', 'active' => FALSE);
}
if (!$GLOBALS['perm']->have_perm('admin')){
	$structure['veranstaltungen_suche'] = array ('topKat' => '', 'name' => _("Veranstaltungen&nbsp;suchen / hinzuf&uuml;gen"), 'link' => 'sem_portal.php', 'active' => FALSE);
} else {
	$structure['veranstaltungen_suche'] = array ('topKat' => '', 'name' => _("Veranstaltungen&nbsp;suchen"), 'link' => 'sem_portal.php', 'active' => FALSE);
}
if ($GLOBALS['PLUGINS_ENABLE'] &&
$studienmodulmanagement = PluginEngine::getPluginPersistence('Core')->getPluginByNameIfAvailable('studienmodulmanagement')){
	$structure = array_merge($structure, (array)$studienmodulmanagement->getModuleCatalogNavigation());
}

//Bottomkats
$structure["_meine_veranstaltungen"] = array ('topKat' => 'meine_veranstaltungen', 'name' => _("&Uuml;bersicht"), 'link' => 'meine_seminare.php', 'active' => FALSE);
if (!$GLOBALS['perm']->have_perm('admin')) {
	$structure['meine_veranstaltungen_extendet'] = array ('topKat' => 'meine_veranstaltungen', 'name' => _("erweiterte&nbsp;&Uuml;bersicht"), 'link' => 'meine_seminare.php?view=ext', 'active' => FALSE);
	if ($GLOBALS['STM_ENABLE'] && $GLOBALS['perm']->have_perm('dozent')){
		$structure["my_stm"]=array ('topKat'=>"meine_veranstaltungen", 'name'=>_("meine&nbsp;Studienmodule"), 'link'=>"my_stm.php", 'active'=>FALSE);
	}
	if (is_object($studienmodulmanagement) && is_object($studienmodulmanagement->getMyModulesNavigation())){
		$structure["my_modules"]=array ("topKat"=>"meine_veranstaltungen", "name"=>$studienmodulmanagement->getMyModulesNavigation()->getDisplayName(), "link"=>$studienmodulmanagement->getMyModulesNavigation()->getLink(), "active"=>FALSE);
	}
	$structure['my_archiv'] = array ('topKat' => 'meine_veranstaltungen', 'name' => _("meine&nbsp;archivierten&nbsp;Veranstaltungen"), 'link' => 'my_archiv.php', 'active' => FALSE);
	if ($GLOBALS['EXPORT_ENABLE'])
		$structure['record_of_study'] = array ('topKat' => 'meine_veranstaltungen', 'name' => _("Druckansicht"), 'link' => 'recordofstudy.php', 'active' => FALSE);
}
if ($GLOBALS['perm']->have_perm('admin'))
	$structure['veranstaltungs_timetable'] = array ('topKat' => 'meine_veranstaltungen', 'name' => _("Veranstaltungs-Timetable"), 'link' => 'mein_stundenplan.php', 'active' => FALSE);
//
$structure['all'] = array ('topKat' => 'veranstaltungen_suche', 'name' => _("Alle"), 'link' => 'sem_portal.php?view=all&reset_all=TRUE', 'active' => FALSE);
foreach ($GLOBALS['SEM_CLASS'] as $key => $val)  {
	$structure['class_'.$key] = array ('topKat' => 'veranstaltungen_suche', 'name' => $val['name'], 'link' => 'sem_portal.php?view='.$key.'&reset_all=TRUE&cmd=qs', 'active' => FALSE);
}
if ($GLOBALS['STM_ENABLE']){
	$structure["mod"]=array ("topKat"=>"veranstaltungen_suche", "name"=>_("Studienmodule"), "link"=>"sem_portal.php?view=mod&reset_all=TRUE", "active"=>FALSE);
}
//

//View festlegen
switch ($GLOBALS['i_page']) {
	case 'meine_seminare.php' :
		if (isset($GLOBALS['view']) && ($GLOBALS['view'] == 'ext'))
			$reiter_view = 'meine_veranstaltungen_extendet';
		else
			$reiter_view = 'meine_veranstaltungen';
	break;
	case 'my_archiv.php':
		$reiter_view = 'my_archiv';
	break;
	case "sem_portal.php" :
		if ($GLOBALS['view']=="all") $reiter_view="all";
		elseif ($GLOBALS['view'] == 'mod')  $reiter_view="mod";
		else
			$reiter_view="class_".$GLOBALS['view'];
	break;
	case 'mein_stundenplan.php' :
		$reiter_view = 'veranstaltungs_timetable';
	break;
	case 'recordofstudy.php' :
		$reiter_view = 'record_of_study';
	break;
	case "my_stm.php":
		$reiter_view="my_stm";
	break;
	case "plugins.php":
		if(is_object($studienmodulmanagement)){
			$reiter_view = $studienmodulmanagement->getCurrentView();
		} else {
			$reiter_view = '';
		}
	break;
	default :
		$reiter_view = 'meine_seminare';
	break;
}

$reiter->create($structure, $reiter_view);
