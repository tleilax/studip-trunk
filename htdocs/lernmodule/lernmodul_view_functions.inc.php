<?

function show_user_modules($benutzername)
{
	global $cssSw;
	$module_count = 0;
	$mod_array = get_user_modules($benutzername);
	if ($mod_array != false)
	{	
		echo "<b>" . _("Auf folgende Lernmodule haben Sie Zugriff:") . "</b><br><br>";
		?> 
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr align="center" valign="top">
				<th width="80%" align="left"><b><? echo _("Name"); ?></b></th>
				<th width="15%"><b><? echo _("Bearbeiten"); ?></b></th>
				<th width="5%"><b>X</b></th>
			</tr>		
		<?
		while ($module_count < sizeof($mod_array))
		{
			$edit_link = link_edit_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$delete_link = link_delete_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$cssSw->switchClass();
			?><tr><td class="<? echo $cssSw->getClass(); ?>"><? echo "<b>" . $mod_array[$module_count]["title"] . "</b> - " . $mod_array[$module_count]["description"]; ?>
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $edit_link;?>" target="_blank"><img src='pictures/icon-posting.gif' border=0  alt="<? echo _("Bearbeiten") ?>" title="<? echo _("Bearbeiten") ?>"></a>&nbsp; 
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $delete_link;?>" target="_blank"><img src='pictures/trash.gif' border=0 alt="<? echo _("L�schen") ?>" title="<? echo _("L�schen") ?>"></a>
			</td></tr><?
			$module_count ++;
		}
		?></table><?
	}
	else
		echo "<b>" . _("Sie haben keinen Zugriff auf bestehende ILIAS-Lernmodule.") . "</b><br><br>";
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
			$link_del = $PHP_SELF . "?view=edit&seminar_id=" . $seminar_id . "&do_op=clear&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"];
			echo "<tr><td class=\"" . $cssSw->getClass() . "\"><b>" . $module_info["title"] . "</b> - " . $module_info["description"] . "</td><td align=\"center\" class=\"" . $cssSw->getClass() . "\">" .
			"<a href=\"" . $link_del . "\"><img src='pictures/trash.gif' border=0 alt=\"" .  _("Verkn�pfung aufheben") . "\" title=\"" . _("Verkn�pfung aufheben") . "\"></a></td></tr>";
			$module_count ++;
		}
		echo "</table><br>";
	}
	else
		echo "<b>" . _("Mit dieser Veranstaltung sind keine ILIAS-Lernmodule verkn�pft.") . "</b><br><br>";
}
	
function show_all_modules($seminar_id)
{
	global $cssSw;
	$module_count = 0;
	$hide_mod = get_seminar_modules($seminar_id);
	$mod_array = get_all_modules($hide_mod);
	if ($mod_array != false)
	{	
		echo "<b>" . _("Folgende Lernmodule k�nnen eingebunden werden:") . "</b><br><br>";
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
			$link_con = $PHP_SELF . "?view=edit&seminar_id=" . $seminar_id . "&do_op=connect&op_co_inst=" . $mod_array[$module_count]["inst"] . "&op_co_id=". $mod_array[$module_count]["id"];
			?><tr><td class="<? echo $cssSw->getClass(); ?>"><? echo "<b>" . $mod_array[$module_count]["title"] . "</b> - " . $mod_array[$module_count]["description"]; ?>
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $link_con;?>"><img src='pictures/icon-posting.gif' border=0  alt="<? echo _("Hinzuf�gen") ?>" title="<? echo _("Hinzuf�gen") ?>" ></a>&nbsp; 
			</td></tr><?
			$module_count ++;
		}
		echo "</table>";
	}
	elseif ($hide_mod != "")
		echo "<b>" . _("Alle verf&uuml;gbaren Lernmodule sind der Veranstaltung zugeordnet.") . "</b><br><br>";
	else
		echo "<b>" . _("Es sind keine Lernmodule vorhanden.") . "</b><br><br>";
}
	
function show_all_modules_admin()
{
	global $cssSw;
	$module_count = 0;
	$mod_array = get_all_modules();
	if ($mod_array != false)
	{	
		echo "<b>" . _("Auf folgende Lernmodule haben Sie Zugriff:") . "</b><br><br>";
		?> 
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr align="center" valign="top">
				<th width="80%" align="left"><b><? echo _("Name"); ?></b></th>
				<th width="15%"><b><? echo _("Bearbeiten"); ?></b></th>
				<th width="5%"><b>X</b></th>
			</tr>		
		<?
		while ($module_count < sizeof($mod_array))
		{
			$edit_link = link_edit_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$delete_link = link_delete_module($mod_array[$module_count]["inst"], $mod_array[$module_count]["id"]);
			$cssSw->switchClass();
			?><tr><td class="<? echo $cssSw->getClass(); ?>"><? echo "<b>" . $mod_array[$module_count]["title"] . "</b> - " . $mod_array[$module_count]["description"]; ?>
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $edit_link; ?>" target="_blank"><img src='pictures/icon-posting.gif' border=0  alt="<? echo _("Bearbeiten") ?>" title="<? echo _("Bearbeiten") ?>"></a>&nbsp; 
			</td><td class="<? echo $cssSw->getClass(); ?>" align="center">
			<a href="<? echo $delete_link; ?>" target="_blank"><img src='pictures/trash.gif' border=0 alt="<? echo _("L�schen") ?>" title="<? echo _("L�schen") ?>"></a>
			</td></tr><?
			$module_count ++;
		}
		?></table><?
	}
	else
		echo "<b>" . _("Es sind keine Lernmodule vorhanden.") . "</b><br><br>";
}

function show_seminar_modules_links($seminar_id)
{
	global $cssSw, $PHP_SELF, $print_open;
	$out_str = link_seminar_modules($seminar_id);
 	if ($out_str == false)
 	{
 		return false;
 	}
 	else
 	{
		if (sizeof($out_str)<2)
			echo "<br><b>" . _("Diese Veranstaltung ist mit einem Lernmodul verbunden:") . "</b><br /><br /><br />";
		else
			echo "<br><b>" . _("Diese Veranstaltung ist mit den folgenden Lernmodulen verbunden:") . "</b><br /><br /><br />";

			for ($i=0; $i<sizeof($out_str); $i++) 
			{
				$printlink = $out_str[$i]["link"];
				$printimage = $out_str[$i]["image"];
				$printcontent = $out_str[$i]["content"] . "<br>";
				$printdesc = $out_str[$i]["desc"];
				?>
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr>
						<?
						if ($print_open[$out_str[$i]["key"]] == true)
							printhead ("99%", FALSE, $PHP_SELF . "?do_close=" . $out_str[$i]["key"] . "&view=show&seminar_id=$seminar_id", "open", true, $printimage, $printlink, $printdesc);
						else
							printhead ("99%", FALSE, $PHP_SELF . "?do_open=" . $out_str[$i]["key"] . "&view=show&seminar_id=$seminar_id", "close", true, $printimage, $printlink, $printdesc);
						?>
					</tr>
				</table>
				<? if ($print_open[$out_str[$i]["key"]] == true) 
				{ ?>
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr>
						<?
						printcontent("99%", FALSE, $printcontent, "");
						?>
					</tr>
				</table>
				<? }
			}
	}
}
?>