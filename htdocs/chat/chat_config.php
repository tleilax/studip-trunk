<?
/**
* Config file for package: Chat
* 
*
* @author		Andr� Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @modulegroup		chat_modules
* @module		chat_config
* @package		Chat
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// chat_nicklist.php
// Shows the nicklist
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

/**
* Shared Memory Key, must be unique
* @const CHAT_SHM_KEY
*/
define("CHAT_SHM_KEY",98374);    //muss eindeutig sein!!!
/**
* Shared Memory Size, in Kbytes
* @const CHAT_SHM_SIZE
*/
define("CHAT_SHM_SIZE",512);     //in Kbyte

define("CHAT_FILE_NAME", "chat_data");
define("CHAT_FILE_PATH", "/tmp");

/**
* Used for shm access, do not alter
* @const CHAT_USER_KEY
*/
define("CHAT_USER_KEY",1);       //am besten nicht �ndern
/**
* Used for shm access, do not alter
* @const CHAT_DETAIL_KEY
*/
define("CHAT_DETAIL_KEY",2);     //dito
/**
* max Number of entries in one chat room
* @const CHAT_MAX_MSG
*/

define("CHAT_MAX_MSG",50);
/**
* Time in seconds before chat user gets kicked
* @const CHAT_IDLE_TIMEOUT
*/
define("CHAT_IDLE_TIMEOUT",600);       //in Sekunden
/**
* Time in seconds before chat admin gets kicked
* @const CHAT_ADMIN_IDLE_TIMEOUT
*/
define("CHAT_ADMIN_IDLE_TIMEOUT",7200);       //in Sekunden
/**
* Time in microseconds for client to sleep
*
* A higher number means lower CPU usage on the server, but slower response times for the clients
* @const CHAT_SLEEP_TIME
*/
define("CHAT_SLEEP_TIME",500000);       //in usleep(micro s)
/**
* Time seconds to 'ping' the clients
*
* used to prevent browser timeouts 
* @const CHAT_TO_PREV_TIME
*/
define("CHAT_TO_PREV_TIME",3.5);       //in Sekunden
/**
* Global array, contains pre-defined colors (use HTML compliant names)
* @var array $chatColors
*/
$chatColors = array("black","blue","green","orange","indigo","darkred","red","darkblue","maroon","pink");
/**
* Global array, contains chat commands with according help text
* @var array $chatCmd
*/
$chatCmd = array("quit" => _(" [msg] - Sie verlassen den Chat mit der Botschaft [msg]"),
			"color" => _(" [colorcode] - Ihre Schriftfarbe wird auf [colorcode] gesetzt"),
			"me" => _(" [msg] - Ihr Name wird zusammen mit [msg] vom Chatbot ausgegeben"),
			"private" => _(" [username][msg] - Die Botschaft [msg] wird geheim an [username] �bermittelt"),
			"help" => _(" - Zeigt diesen Hilfetext"),
			"kick" => _(" [username] - Wirft [username] aus dem Chat wenn sie Chatadmin sind, mit /kick all werfen sie alle anderen Nutzer aus dem Chat"),
			"sms" => _(" [username][msg] - Verschickt eine Studip SMS [msg] an [username]"),
			"password" => _(" [password] - Setzt das Pa�wort f�r den Chat, wenn [password] leer ist wird ein eventuell vorhandenes Pa�wort gel�scht"));

?>
