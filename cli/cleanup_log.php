#!/usr/bin/php -q
<?php
/**
* cleanup_log.php
* 
* 
* 
*
* @author		Tobias Thelen <tobias.thelen@uni-osnabrueck.de>
* @version		$Id$
* @access		public
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// cleanup_log.php
// 
// Copyright (C) 2008 Tobias Thelen <tobias.thelen@uni-osnabrueck.de>
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
require_once dirname(__FILE__) . '/studip_cli_env.inc.php';
require_once 'lib/language.inc.php';
require_once 'lib/functions.php';
require_once 'lib/log_events.inc.php';

cleanup_log_events();


