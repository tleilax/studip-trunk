<?php
/*
rss.php - Ausgabe der pers�nlcihen News als rss-Feed
Copyright (C) 2005	Philipp H�gelmeyer <phuegelm@uni-osnabrueck.de>

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
ob_end_clean();
ob_start();
require_once("$ABSOLUTE_PATH_STUDIP/show_news.php");
if (get_config('NEWS_RSS_EXPORT_ENABLE')){
	if ($user_id = StudipNews::GetUserIDFromRssID($_REQUEST['id'])){
		show_rss_news($user_id);
	}
}
ob_end_flush();
?>
