<?php
/*
seminar_open.php - Initialisierung einer Stud.IP-Session
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>

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

//Funktion zum generieren der default Werte des Messagings
function check_messaging_default() {
	global $my_messaging_settings;
	
	if (!$my_messaging_settings["changed"]) {
		$my_messaging_settings=array(
			"show_only_buddys"=>FALSE, 
			"delete_messages_after_logout"=>FALSE,
			"start_messenger_at_startup"=>FALSE,
			"active_time"=>5,
			"default_setted"=>time()
			);
		}			
	}
	
	
//Funktion zum generieren der default Werte des Stundenplans	
function check_schedule_default() {
	global $my_schedule_settings;
		
	if (!$my_schedule_settings["changed"]) {
		$my_schedule_settings=array(
			"glb_start_time"=>8, 
			"glb_end_time"=>19,
			"glb_days"=>array(
				"mo"=>"TRUE",
				"di"=>"TRUE",
				"mi"=>"TRUE",
				"do"=>"TRUE",
				"fr"=>"TRUE",
				"sa"=>"",
				"so"=>""
				),
			"changed"=>"FALSE",
			"default_setted"=>time()
			);
		}		
	}

 if ($auth->is_authenticated() && $user->id != "nobody") {
	if ($SessionStart > $CurrentLogin) {      // gerade eingeloggt
		//Registrieren aller Uservariablen
		$LastLogin=$CurrentLogin;
		$CurrentLogin=$SessionStart;
		$user->register("loginfilelast");
		$user->register("loginfilenow");
		$user->register("CurrentLogin");
		$user->register("LastLogin");
		$user->register("forum");
		$user->register("my_messaging_settings");
		$user->register("my_schedule_settings");
		$user->register("my_personal_sems");
		$user->register("my_buddies");
		//Default-Funktionen ausfuehren
		check_messaging_default();
		check_schedule_default();
	}
 }

if ($SessionStart==0) { 
	$SessionStart=time(); 
	$SessionSeminar="";
	$SessSemName="";
	$sess->register("SessionStart");
	$sess->register("SessionSeminar");
	$sess->register("SessSemName");
}		
	
// Funktion, um den Namen der eigenen Seite zu bekommen:

	$url=parse_url($PHP_SELF);
	$i_page_array = explode("/" , $url[path]);
	end ($i_page_array);
	$i_page = current($i_page_array);
	unset($url); unset($i_page_array);

// steht jetzt lesbar in $i_page
	
// Funktion, um die �bergebenen Parameter der Seite zu bekommen:

	$i_query = explode('&',getenv("QUERY_STRING"));

// steht jetzt lesbar im Array $i_query
	
?>