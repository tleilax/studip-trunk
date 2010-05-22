<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/**
* RSSFeed.class.php
*
* RSSFeed main-class
*
*
* @author               Jan Kulmann <jankul@tzi.de>
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// RSSFeed.class.php
// Copyright (C) 2005 Jan Kulmann <jankul@tzi.de>
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
define('MAGPIE_CACHE_DIR', $TMP_PATH.'/magpie_cache');
define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');

require_once('vendor/magpierss/rss_fetch.inc');
require_once('lib/visual.inc.php');

class RSSFeed {

    var $ausgabe = array();

    var $domain = "";
    var $max_items = 15;

    var $class_id = "";

    /** Constructor
    */
    function RSSFeed($rssfeed_url, $max_items = 15) {
        if ($rssfeed_url=="") die(_("Bitte eine g�ltige URL angeben!"));
        $rssfeed_url = TransformInternalLinks($rssfeed_url);
        $parsed_url = parse_url($rssfeed_url);
        $this->domain = $parsed_url["host"];
        $this->internal_feed = (($parsed_url['host'] == $_SERVER['HTTP_HOST'] || $parsed_url['host'].':'.$parsed_url['port'] == $_SERVER['HTTP_HOST']) && strpos($parsed_url['path'], $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']) === 0);
        $this->max_items = $max_items;
        $this->class_id = md5($rssfeed_url);

        $this->ausgabe = @fetch_rss($rssfeed_url);

    }


    /** Erzeugt eine HTML-Tabelle mit den einzelnen News-Eintraegen.
    */
    function get_feed_anzeige() {
        global $PHP_SELF, $more;
        $i = 1;
        if ($more == $this->class_id) echo "<a name=\"news_anchor\"></a>\n";
        echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
        foreach ($this->ausgabe->items as $v) {
            if (strlen(trim($v["title"]))>0) {
                $desc = strip_tags(studip_utf8decode($v["description"] ? $v["description"] : $v['summary']));
                if (strlen($desc) > 150) $desc = substr($desc, 0, 150) . "...";
                if ($i > $this->max_items && $more != $this->class_id) {
                    echo "<tr><td align=\"left\" valign=\"TOP\" colspan=\"2\"><a href=\"$PHP_SELF?more=".$this->class_id."#news_anchor\"><font size=\"-1\"><I>mehr...</I></font></a></td></tr>\n";
                    break;
                }
                echo "<tr>
                <td width=\"1\" align=\"left\">
                <IMG SRC=\"". $GLOBALS['ASSETS_URL'] . "images/".(!$this->internal_feed ? 'link_extern.gif' : 'link_intern.gif" hspace="2')."\">
                </td>
                <td align=\"left\" valign=\"TOP\">
                <a href=\"".TransformInternalLinks($v["link"])."\" ".(!$this->internal_feed  ? "TARGET=\"_blank\"" : "") . " TITLE=\"".htmlReady($desc)."\">
                <font size=\"-1\">".htmlReady(studip_utf8decode($v["title"]))."</font>
                </a></td></tr>\n";
                if ($v['enclosure_url']) {
                    echo "<tr><td width=\"1\" align=\"left\" valign=\"TOP\">&nbsp;</td>
                    <td align=\"left\" valign=\"TOP\"><a href=\"{$v['enclosure_url']}\" TARGET=\"_blank\"><img src=\"". $GLOBALS['ASSETS_URL'] . "images/podcast_icon.gif\" border=\"0\" align=\"absmiddle\"></a>
                    <font size=\"-2\">".htmlReady('('.$v['enclosure_type'] . ' - ' . floor($v['enclosure_length']/1024) . ' kb)')."</font>
                    </td></tr>\n";
                }
                if ($desc ) {
                    echo "<tr><td width=\"1\" align=\"left\" valign=\"TOP\">&nbsp;</td><td align=\"left\" valign=\"TOP\"><font size=\"-2\">".htmlReady($desc)."</font></td></tr>\n";
                }
                $i++;
            }
        }
        echo "</table>\n";
    }

    /** Startfunktion fuer den Feed.
    */
    function rssfeed_start() {
        if (!($this->ausgabe)) {
                    echo _("Timeout beim laden von ").$this->domain."...";
            } else {
                    $this->get_feed_anzeige();
                    echo "<font size=\"-1\"><BR>Copyright &copy; ".$this->domain
                    . ($this->ausgabe->channel['link'] ? '<br>'.formatReady($this->ausgabe->channel['link'],1,1) : '')
                    . "</font>";
            }
    }

} // End of class
?>
