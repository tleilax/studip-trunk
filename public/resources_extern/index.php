<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// 
// Copyright (c) 2005 Andr� Noack <noack@data-quest.de> 
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
//$Id$
require_once "resources_extern_config.inc.php";
require_once "resources_extern_functions.inc.php";
require_once "lib/classes/SemesterData.class.php";
$sem = new SemesterData();
$current_sem = $sem->getCurrentSemesterData();
$_view = ($_REQUEST['view'] ? $_REQUEST['view'] : 'start');
$_semester_id = ($_REQUEST['semester_id'] ? $_REQUEST['semester_id'] : $current_sem['semester_id']);
$_timespan = ($_REQUEST['timespan'] ? $_REQUEST['timespan'] : 'course_time');
(in_array($_view, array('start','sem_plan')) || die("Ung�ltiger view"));
$view_script = basename($_view) . '_view.inc.php';
include 'header.inc.php';
include $view_script;
include 'footer.inc.php';
?>
