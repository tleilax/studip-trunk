<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// show_smiley.php
// 
// Copyright (c) 2002 Andr� Noack <andre.noack@gmx.net>
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
// $Id$
require_once "config.inc.php";
$folder=dir($ABSOLUTE_PATH_STUDIP."/".$SMILE_PATH);
$SMILE_SHORT_R=array_flip($SMILE_SHORT);
$i_smile = array();
while ($entry=$folder->read()){
	$dot = strrpos($entry,".");
	$l = strlen($entry) - $dot;
	$name = substr($entry,0,$dot);
	$ext = strtolower(substr($entry,$dot+1,$l));
	if ($dot AND !is_dir($entry) AND $ext=="gif"){
		$i_smile[] = array($entry,$name);
	}
}
$folder->close();
?>
<html>
<head>
<title>Alle Smileys (<?=count($i_smile)?>)</title>
<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<div align="center"><b>Aktuelle Smiley Anzahl: <?=count($i_smile)?></b></div>
<table align="center"><tr><td valign="top" align="center"><table><tr>
<th>Bild</th><th>Schreibweise</th><th>K�rzel</th>
</tr>
<?
ob_start();
for($i=0;$i < count($i_smile);++$i){
		echo "\n<tr><td class=\"blank\" align=\"center\"><img src=\"$SMILE_PATH/".$i_smile[$i][0]."\"></td>";
		echo "\n<td class=\"blank\" align=\"center\">:".$i_smile[$i][1].":</td>";
		($SMILE_SHORT_R[$i_smile[$i][1]]) ? print "\n<td class=\"blank\" align=\"center\">".$SMILE_SHORT_R[$i_smile[$i][1]]."</td>" : print "\n<td class=\"blank\" align=\"center\">&nbsp</td>";
		echo "\n</tr>";
		$max = ceil(count($i_smile)/3);
		if (!(($i+1) % $max )) {
			?>
			</table></td><td valign="top">
			<table align="center"><tr>
			<th>Bild</th><th>Schreibweise</th><th>K�rzel</th>
			</tr>
			<?
			ob_end_flush();
			ob_start();
		}
}
?>
</table></td></tr></table></body></html>

