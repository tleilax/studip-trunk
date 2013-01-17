<?php
/**
 * cleanup_log.php
 *
 * @author Tobias Thelen <tobias.thelen@uni-osnabrueck.de>
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @access public
 * @since  2.4
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// cleanup_log.php
// 
// Copyright (C) 2013 Jan-Hendrik Willms <tleilax+studip@gmail.com>
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

require_once 'lib/classes/CronJob.class.php';

class CleanupLogJob extends CronJob
{
    /**
     * 
     */
    public static function getName()
    {
        return _('Logs aufr�umen');
    }
    
    /**
     * 
     */
    public static function getDescription()
    {
        return _('Entfernt abgelaufene Log-Eintr�ge');
    }

    /**
     * 
     */
    public function setUp()
    {
        require_once 'app/models/event_log.php';
    }

    /**
     * 
     */
    public function execute($last_result, $paramters = array())
    {
        $event_log = new EventLog();
        $event_log->cleanup_log_events();
    }
}

