<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitSearchPluginRkgoe.class.php
// 
// 
// Copyright (c) 2003 Andr� Noack <noack@data-quest.de>
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

require_once ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/lit_search_plugins/StudipLitSearchPluginZ3950Abstract.class.php");

/**
* Plugin for retrieval using Z39.50 
*
* 
*
* @access	public	
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	
**/
class StudipLitSearchPluginRkhan extends StudipLitSearchPluginZ3950Abstract {
	
	
	function StudipLitSearchPluginRkhan(){
		parent::StudipLitSearchPluginZ3950Abstract();
		$this->description = "Gesamtkatalog Hannover";
		$this->z_host = "z3950.gbv.de:20010/rkhan";
		$this->z_options = array('user' => '999', 'password' => 'abc');
		$this->z_syntax = "MARC";
		$this->convert_umlaute = true;
		$this->z_accession_bib = "12";
		$this->z_accession_re = '/[0-9]{8}[0-9X]{1}/';
		$this->z_profile = array('1016' => _("Alle W�rter [ALL]"),
					 '4' => _("Titel (Stichwort) [TIT]"),
					 '21' => _("Schlagw�rter [SLW]"),					 					 
					 '5' => _("Serie, Zeitschrift (Phrase) [GTI]"),
					 '1004' => _("Person, Autor [PER]"),					 					 
					 '7' => _("ISBN [ISB]"),					 					 
					 '8' => _("ISSN [ISN]"),					 					 
					 '1007' => _("Nummern (allgemein) [NUM]"),					 					 
					 '2' => _("K�rperschaftsname (Phrase) [KOS]"),					 					 
					 '1005' => _("K�rperschaft (Stichwort) [KOR]"),					 					 
					 '3' => _("Kongress (Phrase) [KNS]"),					 					 
					 '1006' => _("Kongress (Stichwort) [KON]"),					 					 
					 '1018' => _("Ort,Verlag (Stichwort) [PUB]"),					 					 
					 '20' => _("Basisklassifikation [BKL]"),					 					 
					 '12' => _("PICA Prod.-Nr. [PPN]")					 					 
					); /*  '5' => _("Serie, Zeitschrift (Stichwort) [SER]"),
					       herausgenommen, da #5 standardm��ig auf Phrase gemappt ist und eine Stichwortsuche nicht m�glich ist.
					       Eine Stichwortsuche m�sste �ber das Structure-Attribut (#4=2) kenntlich gemacht werden. Dies wird aber 
					       von Stud.IP nicht unterst�tzt.
					   */
	}
}
?>

