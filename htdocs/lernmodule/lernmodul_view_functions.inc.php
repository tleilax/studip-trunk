<?

function show_user_modules($firstname, $surname, $ilias_user_id)
{
	global $cssSw;
	$module_count = 0;
	$mod_array = get_user_modules($firstname, $surname, $ilias_user_id);
	if ($mod_array != false)
	{	
		while ($module_count < sizeof($mod_array))
		{
			$edit_link = link_edit_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$delete_link = link_delete_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$cssSw->switchClass();
			?><tr><td class="<? echo $cssSw->getClass(); ?>"><? echo "<b>" . $mod_array[$module_count]["title"] . "</b> - " . $mod_array[$module_count]["description"]; ?>
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $edit_link;?>"><img src='pictures/icon-posting.gif' border=0  alt="<? echo _("Bearbeiten") ?>" title="<? echo _("Bearbeiten") ?>" ></a>&nbsp; 
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $delete_link;?>"><img src='pictures/trash.gif' border=0 alt="<? echo _("L�schen") ?>" title="<? echo _("L�schen") ?>"></a>
			</td></tr><?
			$module_count ++;
		}
	}
}

function show_seminar_modules($seminar_id)
{
	global $cssSw;
	$module_count = 0;
	$mod_array = get_seminar_modules($seminar_id);
	if ($mod_array != false)
	{	
		echo "<b>" . _("Folgende Lernmodule sind der Veranstaltung zugeordnet:") . "</b><br><br>";
		?>
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr align="center" valign="top">
				<th width="80%" align="left"><b><? echo _("Name");?></b></th>
				<th width="20%"><b><? echo _("Entfernen");?></b></th>
			</tr>		
		<?
		while ($module_count < sizeof($mod_array))
		{
			$cssSw->switchClass();
			$module_info = get_module_info($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$link_del = $PHP_SELF . "?op_seminar_id=" . $seminar_id . "&do_op=clear&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"];
			echo "<tr><td class=\"" . $cssSw->getClass() . "\"><b>" . $module_info["title"] . "</b> - " . $module_info["description"] . "</td><td align=\"center\" class=\"" . $cssSw->getClass() . "\">" .
			"<a href=\"" . $link_del . "\"><img src='pictures/trash.gif' border=0 alt=\"" .  _("Verkn�pfung aufheben") . "\" title=\"" . _("Verkn�pfung aufheben") . "\"></a></td></tr>";
			$module_count ++;
		}
		echo "</table><br>";
	}
	else
		echo "<b>" . _("Mit dieser Veranstaltung sind bisher keine ILIAS-Lernmodule verkn�pft.") . "</b><br><br>";
}
	
function show_all_modules($seminar_id)
{
	global $cssSw;
	$module_count = 0;
	$mod_array = get_all_modules();
	if ($mod_array != false)
	{	
		?> 
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr align="center" valign="top">
				<th width="80%" align="left"><b><? echo _("Name"); ?></b></th>
				<th width="20%"><b><? echo _("Hinzuf�gen");?></b></th>
			</tr>		
		<?
		while ($module_count < sizeof($mod_array))
		{
			$cssSw->switchClass();
			$link_con = $PHP_SELF . "?op_seminar_id=" . $seminar_id . "&do_op=connect&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"];
			?><tr><td class="<? echo $cssSw->getClass(); ?>"><? echo "<b>" . $mod_array[$module_count]["title"] . "</b> - " . $mod_array[$module_count]["description"]; ?>
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $link_con;?>"><img src='pictures/icon-posting.gif' border=0  alt="<? echo _("Hinzuf�gen") ?>" title="<? echo _("Hinzuf�gen") ?>" ></a>&nbsp; 
			</td></tr><?
			$module_count ++;
		}
	}
}
	
function show_all_modules_admin()
{
	global $cssSw;
	$module_count = 0;
	$mod_array = get_all_modules();
	if ($mod_array != false)
	{	
		while ($module_count < sizeof($mod_array))
		{
			$edit_link = link_edit_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$delete_link = link_delete_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$cssSw->switchClass();
			?><tr><td class="<? echo $cssSw->getClass(); ?>"><? echo "<b>" . $mod_array[$module_count]["title"] . "</b> - " . $mod_array[$module_count]["description"]; ?>
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $edit_link; ?>"><img src='pictures/icon-posting.gif' border=0  alt="<? echo _("Bearbeiten") ?>" title="<? echo _("Bearbeiten") ?>" ></a>&nbsp; 
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $delete_link; ?>"><img src='pictures/trash.gif' border=0 alt="<? echo _("L�schen") ?>" title="<? echo _("L�schen") ?>"></a>
			</td></tr><?
			$module_count ++;
		}
	}
}

function show_seminar_modules_links($seminar_id)
{
	link_seminar_modules($seminar_id);
}
?>