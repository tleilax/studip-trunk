<?
/**
* Visual presentation of the Export-module.
*
* This file outputs the export-infobox, forms, messages and errors to the screen.
* The HTML-Design and table-Structure for export-pages are part of this file.
* It is used for any part of the export-module. If $o_mode is "direct" or "passthrough"
* it writes only the xml-stream or the output-file to the screen.
*
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup		export_modules
* @module		export_view
* @package		Export
*/
if (($o_mode != "direct") AND ($o_mode != "passthrough"))
{
// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	if ($page == 1)
		echo "\n" . $cssSw->GetHoverJSFunction() . "\n";
	include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");
	if ($page == 1)
		$cssSw->enableHover();

 ?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="topic" colspan="3">&nbsp; <b><? echo $export_pagename; ?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="3">&nbsp;
		</td>
	</tr>
	<tr valign="top">
     		<td width="1%" class="blank">
     		&nbsp;
     		</td>
     		<td width="90%" class="blank">

			<table>
<?

				if (isset($export_error))
					my_error($export_error);
				if (isset($export_msg))
					my_msg($export_msg);
				if (isset($export_info))
					my_info($export_info);
?>
			</table>
			<?
			echo $export_pagecontent;

			if (isset($xml_printlink))
			{
			?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					printhead ("99%", FALSE, "", "open", true, $xml_printimage, $xml_printlink, $xml_printdesc);
					?>
				</tr>
			</table>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					printcontent("99%", FALSE, $xml_printcontent, "");
					?>
				</tr>
			</table>
			<?
			}
			if (isset($xslt_printlink))
			{
			?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					printhead ("99%", FALSE, "", "open", true, $xslt_printimage, $xslt_printlink, $xslt_printdesc);
					?>
				</tr>
			</table>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					printcontent("99%", FALSE, $xslt_printcontent, "");
					?>
				</tr>
			</table>
			<?
			}
			if (isset($result_printlink))
			{
			?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					printhead ("99%", FALSE, "", "open", true, $result_printimage, $result_printlink, $result_printdesc);
					?>
				</tr>
			</table>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<?
					printcontent("99%", FALSE, $result_printcontent, "");
					?>
				</tr>
			</table>
			<br>
			<?
			}
			if (isset($export_weiter_button))
			{
			?>
			<br>
			<br>
			<?
				echo $export_weiter_button;
			}
			?>
		</td>
		<td width="270" NOWRAP class="blank" align="center" valign="top">
			<?
			print_infobox ($infobox, "export.jpg");
			?>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="3">&nbsp;
		</td>
	</tr>
	</table>
	<p>&nbsp;</p>
	</body>
	</html>
<?
}
elseif ($export_error_num > 0)
{
	echo $export_error;
}
?>