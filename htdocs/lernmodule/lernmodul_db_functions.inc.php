<?
function get_module_info($co_inst, $co_id)
{
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT DISTINCT meta.id, meta.inst, meta.title, meta.description ".
			" FROM lerneinheit LEFT JOIN meta USING (id, inst)".
			" WHERE meta.status='final' ".
			" AND public = 'y' ".
			" AND meta.typ = 'le' ".
			" AND lerneinheit.id = '$co_id' ".
			" AND lerneinheit.inst = '$co_inst' ");
	if ($ilias_db->next_record())
	{
		$module_info["title"] = $ilias_db -> f("title");
		$module_info["description"] = $ilias_db -> f("description");
		return $module_info;
	}
	else
		return false;
}

function get_user_modules($firstname, $surname, $ilias_user_id)
{
	$mod_array = false;
	$module_count = 0;
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT DISTINCT meta.id, meta.inst, meta.title, meta.description ".
			" FROM lerneinheit LEFT JOIN meta USING(id, inst) LEFT JOIN meta_author USING(id, inst, typ) LEFT JOIN meta_contrib USING(id, inst, typ)".
			" WHERE meta.status='final' ".
			" AND public = 'y' ".
			" AND meta.typ = 'le' ".
			" AND (((meta_author.author_firstname = '$firstname') AND (meta_author.author_surname = '$surname')  AND (meta_author.author_local_id = '$ilias_user_id')) ".
			" OR ((meta_contrib.contrib_firstname = '$firstname')  AND (meta_contrib.contrib_surname = '$surname') AND (meta_contrib.contrib_local_id = '$ilias_user_id')))"
			);
	while ($ilias_db->next_record())
	{
		$mod_array[$module_count]["inst"] = $ilias_db -> f("inst");
		$mod_array[$module_count]["id"] = $ilias_db -> f("id");
		$mod_array[$module_count]["title"] = $ilias_db -> f("title");
		$mod_array[$module_count]["description"] = $ilias_db -> f("description");
		$module_count ++;
	}
	return $mod_array;
}

function get_seminar_modules($seminar_id)
{
	$mod_array = false;
	$module_count = 0;
	$db = New DB_Seminar;
	$db -> query("SELECT co_inst, co_id FROM seminar_lernmodul WHERE seminar_id = '$seminar_id'");
	while ($db->next_record())
	{
		$mod_array[$module_count]["inst"] = $db -> f("co_inst");
		$mod_array[$module_count]["id"] = $db -> f("co_id");
		$module_count ++;
	}
	return $mod_array;
}

function get_all_modules()
{
	$mod_array = false;
	$module_count = 0;
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT lerneinheit.id, lerneinheit.inst, meta.title, meta.description".
			" FROM lerneinheit, meta ".
			" WHERE meta.status='final' ".
			" AND public = 'y' ".
			" AND meta.typ = 'le' " .
			" AND lerneinheit.id = meta.id ".
			" AND lerneinheit.inst = meta.inst ");
	while ($ilias_db->next_record())
	{
		$mod_array[$module_count]["inst"] = $ilias_db -> f("inst");
		$mod_array[$module_count]["id"] = $ilias_db -> f("id");
		$mod_array[$module_count]["title"] = $ilias_db -> f("title");
		$mod_array[$module_count]["description"] = $ilias_db -> f("description");
		$module_count ++;
	}
	return $mod_array;
}
	
?>