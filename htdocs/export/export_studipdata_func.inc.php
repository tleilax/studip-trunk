<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// export_studipdata_func.inc.php
// exportfunctions for the Stud.IP database
// 
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
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

function string_to_unicode ($xml_string) 
{
	for ($x=0; $x<strlen($xml_string); $x++) 
	{
		$char = substr($xml_string, $x, 1);
		$dosc = ord($char);
		$ret .= ($dosc > 127) ? "&#".$dosc.";" : $char;
	}
	return $ret;
}

function output_data($object_data, $output_mode = "file")
{
	global $xml_file;
	if (($output_mode == "file") OR ($output_mode == "processor") OR ($output_mode == "passthrough") OR ($output_mode == "choose"))
		fputs($xml_file, string_to_unicode( $object_data ));
	elseif ($output_mode == "direct")
		echo string_to_unicode( $object_data );
}

function export_range($range_id)
{
	global $db, $o_mode, $range_name;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;

//    Ist die Range-ID eine Einrichtungs-ID?
	$db->query('SELECT * FROM Institute WHERE Institut_id = "' . $range_id . '"');
	if (($db->next_record()) And ($db->f("Name") != "")) 
	{
		$range_name = $db->f("Name");
		output_data ( xml_header(), $o_mode);
		export_inst( $range_id );
		
	}

//	Ist die Range-ID eine Fakultaets-ID? Dann auch untergeordnete Institute exportieren!
	$db2->query('SELECT * FROM Institute WHERE fakultaets_id = "' . $range_id . '" ');
	while ($db2->next_record())
		if (($db2->f("Name") != "") And ($db2->f("Institut_id") != $range_id)) 
		{
//			output_data ( xml_header(), $o_mode);
			export_inst( $db2->f("Institut_id") );		
		}

//    Ist die Range-ID eine Seminar-ID?
	$db->query('SELECT * FROM seminare WHERE Seminar_id = "' . $range_id . '"');
	if (($db->next_record()) And ($db->f("Name") != ""))
	{
		$range_name = $db->f("Name");
		output_data ( xml_header(), $o_mode);
		export_inst( $db->f("Institut_id"), $db->f("Seminar_id") );
	}


//    Ist die Range-ID ein Range-Tree-Item?
	$tree_object = new RangeTreeObject($range_id);
	$range_name = $tree_object->item_data["name"];

//    Tree-Item ist ein Institut:
	if ($tree_object->item_data['studip_object'] == 'inst')
	{
		output_data ( xml_header(), $o_mode);
		export_inst( $tree_object->item_data['studip_object_id'] );
	}
//    Tree-Item hat Institute als Kinder:
	$inst_array = $tree_object->GetInstKids();
	if (sizeof($inst_array) > 0)
	{
		output_data ( xml_header(), $o_mode);
		while (list($key, $inst_ids) = each($inst_array))
		{
			export_inst($inst_ids);
		}
	}
	output_data ( xml_footer(), $o_mode);
}


function export_inst($inst_id, $ex_sem_id = "all")
{
	global $db, $ex_type, $o_mode, $xml_file, $xml_names_inst, $xml_groupnames_inst, $INST_TYPE;

	$db=new DB_Seminar;

	$db->query('SELECT * FROM Institute WHERE Institut_id = "' . $inst_id . '"');
	$db->next_record();
	$data_object .= xml_open_tag($xml_groupnames_inst["object"], $db->f("Name"));
	while ( list($key, $val) = each($xml_names_inst))
	{
		if ($val == "") $val = $key;
		if (($key == "type") AND ($INST_TYPE[$db->f($key)]["name"] != ""))
			$data_object .= xml_tag($val, $INST_TYPE[$db->f($key)]["name"]);
		elseif ($db->f($key) != "") 
			$data_object .= xml_tag($val, $db->f($key));
	}
	reset($xml_names_inst);
	$db->query('SELECT Name FROM Institute WHERE Institut_id = "' . $db->f("fakultaets_id") . '" AND fakultaets_id = "' . $db->f("fakultaets_id") . '"');
	$db->next_record();
	{
		if ($db->f("Name") != "") 
			$data_object .= xml_tag($xml_groupnames_inst["childobject"], $db->f("Name"));
	}
//	echo nl2br(htmlentities($data_object));
	output_data( $data_object, $o_mode );
	$data_object = "";

	switch ($ex_type)
	{
	case "veranstaltung": 
		export_sem($inst_id, $ex_sem_id); 
		break;
	case "person": 
		if ($ex_sem_id == "all")
			export_pers($inst_id); 
		elseif (($o_mode != "passthrough") AND ($o_mode != "direct"))
			export_teilis($inst_id, $ex_sem_id); 
		else
			$data_object .= xml_tag("message", _("KEINE BERECHTIGUNG!"));
		break;
	default: 
		echo "</td></tr>";
		my_error(_("Der gew�hlte Exportmodus wird nicht unterst�tzt."));
		echo "</table></td></tr></table>";
		die();
	}

	$data_object .= xml_close_tag($xml_groupnames_inst["object"]);

	output_data($data_object, $o_mode);
	$data_object = "";
}

function export_sem($inst_id, $ex_sem_id = "all")
{
	global $db, $db2, $range_id, $xml_file, $o_mode, $xml_names_lecture, $xml_groupnames_lecture, $object_counter, $SEM_TYPE, $SEM_CLASS, $filter, $SEMESTER, $ex_sem, $ex_class_array;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;

	switch ($filter)
	{
		case "seminar":
			$order = " seminare.Name";
		break;
		case "status":
			$order = "seminare.status, seminare.Name";
			$group = "FIRSTGROUP";
			$group_tab_zelle = "status";
//			$subgroup = "FIRSTGROUP";
//			$subgroup_tab_zelle = "name";
			$do_group = true;
//			$do_subgroup = true;
		break;
		default:
			$order = "seminare.status, seminare.Name";
			$group = "FIRSTGROUP";
			$group_tab_zelle = "status";
			$do_group = true;
	}
	if (isset($SEMESTER[ $ex_sem]["beginn"] ) )
		$addquery = " AND seminare.start_time <=".$SEMESTER[$ex_sem]["beginn"]." AND (".$SEMESTER[$ex_sem]["beginn"]." <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1) ";
	if ($ex_sem_id != "all")
		$addquery .= " AND seminare.Seminar_id = '" . $ex_sem_id . "' ";
	$db->query('SELECT * FROM seminar_inst
				LEFT JOIN seminare USING (Seminar_id)
				WHERE seminar_inst.Institut_id = "' . $inst_id . '" ' . $addquery . '
				ORDER BY ' . $order);

	$data_object .= xml_open_tag( $xml_groupnames_lecture["group"] );

	while ($db->next_record()) 
		if (($ex_class_array == "") OR ($ex_class_array[$SEM_TYPE[$db->f("status")]["class"]] == true))
		// Nur gew&auml;hlte Veranstaltungsklassen exportieren
		{
			$group_string = "";
			if (($do_group) AND ($group != $db->f($group_tab_zelle)))
			{
				if ($group != "FIRSTGROUP")
					$group_string .= xml_close_tag($xml_groupnames_lecture["subgroup1"]);
				if ($group_tab_zelle == "status") 
					$group_string .= xml_open_tag($xml_groupnames_lecture["subgroup1"], $SEM_TYPE[$db->f($group_tab_zelle)]["name"]);
				else
					$group_string .= xml_open_tag($xml_groupnames_lecture["subgroup1"], $db->f($group_tab_zelle));
				$group = $db->f($group_tab_zelle);
				if (($do_subgroup) AND ($subgroup == $db->f($subgroup_tab_zelle)))
					$subgroup = "NEXTGROUP";
			}
			if (($do_subgroup) AND ($subgroup != $db->f($subgroup_tab_zelle)))
			{
				if ($subgroup != "FIRSTGROUP")
					$group_string = xml_close_tag($xml_groupnames_lecture["subgroup2"]) . $group_string;
				$group_string .= xml_open_tag($xml_groupnames_lecture["subgroup2"], $db->f($subgroup_tab_zelle));
				$subgroup = $db->f($subgroup_tab_zelle);
			}
			$data_object .= $group_string;
			$object_counter++;
			$data_object .= xml_open_tag($xml_groupnames_lecture["object"], $db->f("Name"));
			while ( list($key, $val) = each($xml_names_lecture))
			{
				if ($val == "") $val = $key;
				if ($key == "status") 
					$data_object .= xml_tag($val, $SEM_TYPE[$db->f($key)]["name"]);
				elseif ($key == "ort") 
					$data_object .= xml_tag($val, getRoom($db->f("Seminar_id")));
				elseif (($key == "bereich") AND (($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"])))
				{
					$data_object .= xml_open_tag($xml_groupnames_lecture["childgroup3"]);
					$pathes = get_sem_tree_path($db->f("Seminar_id"));
					if (is_array($pathes)){
						foreach ($pathes as $sem_tree_id => $path_name)
							$data_object .= xml_tag($val, $path_name);
					} else {
						$data_object .= xml_tag($val, "n.a.");
					}
					$data_object .= xml_close_tag($xml_groupnames_lecture["childgroup3"]);
				}
				elseif ($key == "metadata_dates") 
				{
					$data_object .= xml_open_tag( $xml_groupnames_lecture["childgroup1"] );
					$vorb = vorbesprechung($db->f("Seminar_id"));
					if ($vorb != false) 
						$data_object .= xml_tag($val[0], $vorb);
					$data_object .= xml_tag($val[1], veranstaltung_beginn($db->f("Seminar_id")));
					$data_object .= xml_tag($val[2], view_turnus($db->f("Seminar_id")));
					$data_object .= xml_close_tag( $xml_groupnames_lecture["childgroup1"] );
				}
				elseif ($db->f($key) != "") 
					$data_object .= xml_tag($val, $db->f($key));
			}
			$db2->query('SELECT auth_user_md5.Vorname, auth_user_md5.Nachname, user_info.title_front, user_info.title_rear FROM seminar_user 
						LEFT JOIN user_info USING(user_id) 
						LEFT JOIN auth_user_md5 USING(user_id) 
						WHERE (seminar_user.status = "dozent") AND (seminar_user.Seminar_id = "' . $db->f("Seminar_id") . '")');
			$data_object .= "<" . $xml_groupnames_lecture["childgroup2"] . ">\n";
			while ($db2->next_record()) 
				{
					$content_string = $db2->f("Vorname") . " " . $db2->f("Nachname");
					if ($db2->f("title_front") != "") 
						$content_string = $db2->f("title_front") . " " . $content_string;
					if ($db2->f("title_rear") != "") 
						$content_string = $content_string . ", " . $db2->f("title_rear");
					$data_object .= xml_tag($xml_groupnames_lecture["childobject2"], $content_string);
				}
			$data_object .= xml_close_tag( $xml_groupnames_lecture["childgroup2"] );
			$data_object .= xml_close_tag( $xml_groupnames_lecture["object"] );
			reset($xml_names_lecture);
			output_data($data_object, $o_mode);
			$data_object = "";
		}

	if (($do_subgroup) AND ($subgroup != "FIRSTGROUP"))
		$data_object .= xml_close_tag($xml_groupnames_lecture["subgroup2"]);
	if (($do_group) AND ($group != "FIRSTGROUP"))
		$data_object .= xml_close_tag($xml_groupnames_lecture["subgroup1"]);

	$data_object .= xml_close_tag($xml_groupnames_lecture["group"]);
	output_data($data_object, $o_mode);
	$data_object = "";
}

function export_teilis($inst_id, $ex_sem_id = "no")
{
	global $db, $db2, $range_id, $xml_file, $o_mode, $xml_names_person, $xml_groupnames_person, $object_counter, $filter, $SEM_CLASS, $SEM_TYPE, $SessSemName;

	$db=new DB_Seminar;

	if ($filter == "status")
	{
		$db->query ("SELECT name, statusgruppe_id FROM statusgruppen WHERE range_id = '$ex_sem_id' ORDER BY position ASC");
		while ($db->next_record()) 
		{
			$gruppe[$db->f("statusgruppe_id")] = $db->f("name");
		}
		$gruppe["no"] = _("keiner Funktion oder Gruppe zugeordnet");
	}
	else
	{	
		$db->query ("SELECT name, studiengaenge.studiengang_id FROM studiengaenge LEFT JOIN admission_seminar_studiengang USING(studiengang_id) WHERE seminar_id = '$ex_sem_id' ");
		while ($db->next_record()) 
		{
			$studiengang[$db->f("studiengang_id")] = $db->f("name");
		}
		$studiengang["all"] = _("Alle Studieng&auml;nge");
		if (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"])
			$gruppe = array ("dozent" => _("DozentInnen"),
				  "tutor" => _("TutorInnen"),
				  "autor" => _("AutorInnen"),
				  "user" => _("LeserInnen"));
		else
			$gruppe = array ("dozent" => _("LeiterInnen"),
				  "tutor" => _("Mitglieder"),
				  "autor" => _("AutorInnen"),
				  "user" => _("LeserInnen"));
	}

	$data_object = xml_open_tag( $xml_groupnames_person["group"] );

	while (list ($key1, $val1) = each ($gruppe)) 
	{
		if ($filter == "status") // Gruppierung nach Statusgruppen / Funktionen
		{	
			if ($key1 == "no")
				$db->query ("SELECT * FROM user_info 
					LEFT JOIN auth_user_md5 USING ( user_id ) 
					LEFT JOIN seminar_user USING ( user_id ) 
					WHERE seminar_id = '$ex_sem_id' ORDER BY Nachname");
			else	
				$db->query ("SELECT DISTINCT * FROM statusgruppe_user  
					LEFT JOIN user_info USING ( user_id ) 
					LEFT JOIN auth_user_md5 USING ( user_id ) 
					WHERE statusgruppe_id = '" . $key1 . "'  ORDER BY Nachname");
//					LEFT JOIN seminar_user USING ( user_id ) 
		}
		else // Gruppierung nach Status in der Veranstaltung / Einrichtung
			$db->query ("SELECT * FROM seminar_user  
				LEFT JOIN user_info USING(user_id) 
				LEFT JOIN auth_user_md5 USING(user_id) 
				WHERE seminar_id = '$ex_sem_id' AND seminar_user.status = '" . $key1 . "'  ORDER BY Nachname");
		if ($db->num_rows())
		{
			$data_object .= xml_open_tag($xml_groupnames_person["subgroup1"], $val1);
			while ($db->next_record()) 
				if (($key1 != "no") OR ($person_out[$db->f("user_id")] != true)) // Nur Personen ausgeben, die entweder einer Gruppe angehoeren oder zur Veranstaltung gehoeren und noch nicht ausgegeben wurden.
				{
					$object_counter++;
					$data_object .= xml_open_tag($xml_groupnames_person["object"], $db->f("username"));
					while ( list($key, $val) = each($xml_names_person))
					{
						if ($val == "") $val = $key;
						if (($key == "admission_studiengang_id") AND ($db->f($key) != ""))
							$data_object .= xml_tag($val, $studiengang[$db->f($key)]);
						elseif ($db->f($key) != "") 
							$data_object .= xml_tag($val, $db->f($key));
					}
					$data_object .= xml_close_tag( $xml_groupnames_person["object"] );
					reset($xml_names_person);
					$person_out[$db->f("user_id")] = true;
				}
			$data_object .= xml_close_tag($xml_groupnames_person["subgroup1"]);
		}	
	}

	$data_object .= xml_close_tag( $xml_groupnames_person["group"]);
	output_data($data_object, $o_mode);
	$data_object = "";
}

function export_pers($inst_id)
{
	global $db, $db2, $range_id, $xml_file, $o_mode, $xml_names_person, $xml_groupnames_person, $object_counter, $filter;

	$db=new DB_Seminar;

	switch ($filter)
	{
		case "no":
			$order = "statusgruppen.name";
		break;
		case "status":
			$order = "statusgruppen.name";
			$group = "FIRSTGROUP";
			$group_tab_zelle = "name";
			$do_group = true;
		break;
		default:
			$order = "statusgruppen.name";
			$group = "FIRSTGROUP";
			$group_tab_zelle = "name";
			$do_group = true;
	}

	$data_object = xml_open_tag( $xml_groupnames_person["group"] );

	$db->query('SELECT statusgruppen.name, 
		aum.Nachname, aum.Vorname, ui.inst_perms, ui.raum,
		ui.sprechzeiten, ui.Telefon, ui.Fax, aum.Email, 
		aum.username, info.Home, info.geschlecht, info.title_front, info.title_rear FROM statusgruppen
		LEFT JOIN statusgruppe_user sgu USING(statusgruppe_id)
		LEFT JOIN user_inst ui ON (ui.user_id = sgu.user_id AND ui.Institut_id = range_id AND ui.inst_perms!="user")
		LEFT JOIN auth_user_md5 aum USING(user_id) 
		LEFT JOIN user_info info USING(user_id) 
		WHERE range_id = "' . $inst_id . ' "
		ORDER BY ' . $order);

	while ($db->next_record()) 
	{
		$group_string = "";
		if (($do_group) AND ($group != $db->f($group_tab_zelle)))
		{
			if ($group != "FIRSTGROUP")
				$group_string .= xml_close_tag($xml_groupnames_person["subgroup1"]);
			$group_string .= xml_open_tag($xml_groupnames_person["subgroup1"], $db->f($group_tab_zelle));
			$group = $db->f($group_tab_zelle);
		}
		$data_object .= $group_string;
		$object_counter++;
		$data_object .= xml_open_tag($xml_groupnames_person["object"], $db->f("username"));
		while ( list($key, $val) = each($xml_names_person))
		{
			if ($val == "") $val = $key;
			if ($db->f($key) != "") 
				$data_object .= xml_tag($val, $db->f($key));
		}
		$data_object .= xml_close_tag( $xml_groupnames_person["object"] );
		reset($xml_names_person);
		output_data($data_object, $o_mode);
		$data_object = "";
	}

	if ($do_group)
		$data_object .= xml_close_tag($xml_groupnames_person["subgroup1"]);

	$data_object .= xml_close_tag( $xml_groupnames_person["group"]);
	output_data($data_object, $o_mode);
	$data_object = "";
}

?>
