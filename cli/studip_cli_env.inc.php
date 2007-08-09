<?php
/**
* studip_cli_env.inc.php
* 
* sets up a faked Stud.IP environment with usable $auth, $user and $perm objects
* for a faked 'root' user, sets custom error handler wich writes to STDERR
*
* @author		Andr� Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id: kill_studip_user.php 6996 2006-11-21 13:24:52Z mlunzena $
* @access		public
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// studip_cli_env.inc.php
// 
// Copyright (C) 2006 Andr� Noack <noack@data-quest.de>,
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

function CliErrorHandler($errno, $errstr, $errfile, $errline) {
	if ($errno & ~E_NOTICE & ~E_STRICT && error_reporting()){
		fwrite(STDERR,"$errstr \n$errfile line $errline\n");
		exit(1);
	}
}

function parse_msg_to_clean_text($long_msg,$separator="�") {
	$msg = explode ($separator,$long_msg);
	$ret = array();
	for ($i=0; $i < count($msg); $i=$i+2) {
		if ($msg[$i+1]) $ret[] = trim(decodeHTML(preg_replace ("'<[\/\!]*?[^<>]*?>'si", "", $msg[$i+1])));
	}
	return join("\n", $ret);
}

$STUDIP_BASE_PATH = realpath( dirname(__FILE__) . '/..');
$include_path = get_include_path();
$include_path .= PATH_SEPARATOR . $STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'public';
set_include_path($include_path);
require_once $STUDIP_BASE_PATH . "/lib/phplib/my_prepend4.php";

set_error_handler('CliErrorHandler');

$PLUGINS_CACHING = FALSE;	//maybe only the www user is allowed to create tmp dirs?

//cli scripts run always as faked (Stud.IP) root
$auth = new Seminar_Auth();
$auth->auth = array('uid' => 'cli',
					'uname' => 'cli',
					'perm' => 'root');

$user = new Seminar_User();
$user->fake_user = true;
$user->register_globals = false;
$user->start('cli');

$perm = new Seminar_Perm();
?>
