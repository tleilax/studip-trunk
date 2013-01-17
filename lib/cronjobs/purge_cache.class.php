<?php
/**
 * purge_cache.php
 * 
 * @author André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @access public
 * @since  2.4
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// purge_cache.php
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

class PurgeCacheJob extends CronJob
{
    /**
     * 
     */
    public static function getName()
    {
        return _('Dateicache leeren');
    }

    /**
     * 
     */
    public static function getDescription()
    {
        return _('Leert den dateibasierten Cache');
    }

    /**
     * 
     */
    public static function getParameters()
    {
        return array(
            'verbose' => array(
                'type'        => 'boolean',
                'default'     => false,
                'status'      => 'optional',
                'description' => _('Sollen Ausgaben erzeugt werden oder nicht (sind später im Log des Cronjobs sichtbar)'),
            ),
        );
    }

    /**
     * 
     */
    public function setUp()
    {
        require_once 'lib/classes/StudipFileCache.class.php';
    }

    /**
     * 
     */
    public function execute($last_result, $parameters = array())
    {
        $cache = new StudipFileCache();
        $cache->purge(empty($parameters['verbose']));
    }
}
