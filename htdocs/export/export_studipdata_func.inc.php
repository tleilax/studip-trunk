<?

function output_data($object_data, $output_mode = "file")
{
	global $xml_file, $o_mode;
	if (($o_mode == "file") OR ($o_mode == "processor") OR ($o_mode == "passthrough"))
		fputs($xml_file, $object_data);
	elseif ($o_mode == "direct")
		echo $object_data;
}

function export_range($range_id)
{

}

function export_inst($inst_id)
{
	global $db, $ex_type, $o_mode, $xml_file, $xml_names_inst, $xml_groupnames_inst;

	$db->query('SELECT * FROM Institute WHERE Institut_id = "' . $inst_id . '"');
	$db->next_record();
	$data_object .= xml_open_tag($xml_groupnames_inst["object"], $db->f("Name"));
	while ( list($key, $val) = each($xml_names_inst))
	{
		if ($val == "") $val = $key;
		if ($db->f($key) != "") 
			$data_object .= xml_tag($val, $db->f($key));
	}
	reset($xml_names_inst);
	$db->query('SELECT Fakultaeten.Name FROM Fakultaeten LEFT JOIN Institute USING(fakultaets_id) WHERE Institut_id = "' . $inst_id . '"');
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
		export_sem($inst_id); 
		break;
	case "person": 
		export_pers($inst_id); 
		break;
	default: 
		echo "</td></tr>";
		my_error("Der gew&auml;hlte Exportmodus wird nicht unterst&uuml;tzt.");
		echo "</table></td></tr></table>";
		die();
	}

	$data_object .= xml_close_tag($xml_groupnames_inst["object"]);

	output_data($data_object, $o_mode);
	$data_object = "";
}

function export_sem($inst_id)
{
	global $db, $db2, $range_id, $xml_file, $o_mode, $xml_names_lecture, $xml_groupnames_lecture, $object_counter;

	$data_object .= xml_open_tag( $xml_groupnames_lecture["group"] );
	$db->query('SELECT * FROM seminar_inst
				LEFT JOIN seminare USING (Seminar_id) LEFT JOIN seminar_bereich USING(Seminar_id) 
				LEFT JOIN bereiche USING(bereich_id) 
				WHERE seminar_inst.Institut_id = "' . $inst_id . '" 
				ORDER BY bereiche.name, seminare.Name');

	while ($db->next_record()) 
	{
		$object_counter++;
		$data_object .= xml_open_tag($xml_groupnames_lecture["object"], $db->f("Name"));
		while ( list($key, $val) = each($xml_names_lecture))
		{
			if ($val == "") $val = $key;
			if ($key == "metadata_dates") 
			{
				$data_object .= xml_open_tag( $xml_groupnames_lecture["childgroup1"] );
				$vorb = vorbesprechung($db->f("Seminar_id"));
				if ($vorb != false) 
					$data_object .= xml_tag($val[0], $vorb);
				$data_object .= xml_tag($val[1], veranstaltung_beginn($db->f("Seminar_id")));
				$data_object .= xml_tag($val[2], view_turnus($db->f("Seminar_id")));
				$data_object .= xml_close_tag( $xml_groupnames_lecture["childgroup1"] );
			}
			else if ($db->f($key) != "") 
				$data_object .= xml_tag($val, $db->f($key));
		}
		$db2->query('SELECT * FROM auth_user_md5 
					LEFT JOIN user_info USING(user_id) 
					LEFT JOIN seminar_user USING(user_id) 
					LEFT JOIN seminare USING(seminar_id) 
					WHERE (seminar_user.status = "dozent") AND (seminare.Seminar_id = "' . $db->f("Seminar_id") . '")');
		$data_object .= "<" . $xml_groupnames_lecture["childgroup2"] . ">\n";
		while ($db2->next_record()) 
			{
				$data_object .= xml_tag($xml_groupnames_lecture["childobject2"], $db2->f("Vorname") . " " . $db2->f("Nachname"));
			}
		$data_object .= xml_close_tag( $xml_groupnames_lecture["childgroup2"] );
		$data_object .= xml_close_tag( $xml_groupnames_lecture["object"] );
		reset($xml_names_lecture);
		output_data($data_object, $o_mode);
		$data_object = "";
	}

	$data_object = xml_close_tag($xml_groupnames_lecture["group"]);
	output_data($data_object, $o_mode);
	$data_object = "";
}

function export_pers($inst_id)
{
	global $db, $db2, $range_id, $xml_file, $o_mode, $xml_names_person, $xml_groupnames_person, $object_counter;

	$data_object = xml_open_tag( $xml_groupnames_person["group"] );

	$db->query('SELECT statusgruppen.name, aum.Nachname, aum.Vorname, ui.inst_perms, ui.raum,
		ui.sprechzeiten, ui.Telefon, ui.Fax, aum.Email, 
		aum.username, info.Home, info.geschlecht FROM statusgruppen 
		LEFT JOIN statusgruppe_user USING(statusgruppe_id) 
		LEFT JOIN auth_user_md5 aum USING(user_id) 
		LEFT JOIN user_info info USING(user_id) 
		LEFT JOIN user_inst ui USING(user_id)
		WHERE ui.Institut_id = "' . $inst_id . '" AND ui.inst_perms != "user"
		 ');

	while ($db->next_record()) 
	{
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
//		echo nl2br(htmlentities($data_object));
		output_data($data_object, $o_mode);
		$data_object = "";
	}

	$data_object = xml_close_tag( $xml_groupnames_person["group"]);
	output_data($data_object, $o_mode);
	$data_object = "";
}

?>