<?
# Lifter002: TODO - showRequest() left undone (cause it's horrible)
# Lifter003: TEST
# Lifter005: TODO
# Lifter007: TODO
# Lifter010: TODO
/**
 * ShowToolsRequests.class.php
 *
 * room-management tool for room-admins
 *
 *
 * @author           Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @access           public
 * @modulegroup      resources
 * @module           ToolsRequestResolve.class.php
 * @package          resources
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ShowToolsRequests.class.php
// die Suchmaschine fuer Ressourcen
// Copyright (C) 2004 Cornelis Kater <ckater@gwdg.de>, data-quest GmbH <info@data-quest.de>
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

use Studip\Button, Studip\LinkButton;

/**
 * ShowToolsRequests, room-management tool for room-admin
 *
 * @access   public
 * @author   Cornelis Kater <kater@data-quest.de>
 * @package  resources
 **/
class ShowToolsRequests
{
    var $requests;          //the requests i'am responsibel for
    var $semester_id;
    var $show_requests_no_time = false;
    var $sem_type;
    var $faculty;
    var $tagged;

    public function __construct($semester_id, $resolve_requests_no_time = null, $sem_type = null, $faculty = null, $tagged = null)
    {
        $this->semester_id = $semester_id ?: SemesterData::GetSemesterIdByDate(time());
        if (!is_null($resolve_requests_no_time)) {
            $this->show_requests_no_time = !$resolve_requests_no_time;
        }
        if (!is_null($sem_type)) {
            $this->sem_type = $sem_type;
        }
        if (!is_null($faculty)) {
            $this->faculty = $faculty;
        }
        if (!is_null($tagged)) {
            $this->tagged = $tagged;
        }
    }

    public function getMyOpenSemRequests()
    {
        $this->restoreOpenRequests();
        return (int)$this->requests_stats_open['my_sem'];
    }

    public function getMyOpenNoTimeRequests()
    {
        $this->restoreOpenRequests();
        return (int)$this->requests_stats_open['no_time'];
    }

    public function getMyOpenResRequests()
    {
        $this->restoreOpenRequests();
        return (int)$this->requests_stats_open['my_res'];
    }

    public function getMyOpenRequests()
    {
        $this->restoreOpenRequests();
        return (int)$this->requests_stats_open['sum'];
    }

    public function restoreOpenRequests()
    {
        if (is_null($this->requests)) {
            $this->requests = (array)getMyRoomRequests($GLOBALS['user']->id, $this->semester_id, true, null, $this->sem_type, $this->faculty, $this->tagged);//MOD_BREMEN
            foreach ($this->requests as $val) {
                $this->requests_stats_open['sum'] += !$val["closed"] && ($val["have_times"] || $this->show_requests_no_time);
                $this->requests_stats_open['my_res'] += !$val["closed"] && $val["my_res"] && ($val["have_times"] || $this->show_requests_no_time);
                $this->requests_stats_open['my_sem'] += !$val["closed"] && $val["my_sem"] && ($val["have_times"] || $this->show_requests_no_time);
                $this->requests_stats_open['no_time'] += !$val["closed"] && !$val["have_times"];
            }
        }
    }

    public function getMyRequestedRooms()
    {
        $no_time      = (int)$this->show_requests_no_time;
        $res_requests = array_filter($this->requests, function ($val) use ($no_time) {
            return !$val['closed'] && $val['my_res'] && ($val['have_times'] || $no_time);
        });

        if (count($res_requests) > 0) {
            $query     = "SELECT ro.resource_id, ro.name, COUNT(ro.resource_id) as anzahl
                      FROM resources_requests rr
                      INNER JOIN resources_objects ro USING (resource_id)
                      WHERE rr.request_id IN (?)
                      GROUP BY ro.resource_id
                      ORDER BY ro.name";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([array_keys($res_requests),]);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    public function selectSemInstituteNames($inst_id)
    {
        $query     = "SELECT a.Name AS inst_name, b.Name AS fak_name
                  FROM Institute AS a
                  LEFT JOIN Institute b ON (a.fakultaets_id = b.Institut_id)
                  WHERE a.Institut_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$inst_id]);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function selectDates($seminar_id, $termin_id = '')
    {
        if (!$termin_id) {
            if (Config::get()->RESOURCES_HIDE_PAST_SINGLE_DATES) {
                $query      = "SELECT *, resource_id
                          FROM termine
                          LEFT JOIN resources_assign AS ra ON (ra.assign_user_id = termine.termin_id)
                          WHERE date >= UNIX_TIMESTAMP(NOW() - INTERVAL 1 HOUR)
                            AND range_id = ?
                          ORDER BY date, content";
                $parameters = [$seminar_id];
            } else {
                $query      = "SELECT *, resource_id
                          FROM termine
                          LEFT JOIN resources_assign AS ra ON (ra.assign_user_id = termine.termin_id)
                          WHERE range_id = ?
                          ORDER BY date, content";
                $parameters = [$seminar_id];
            }
        } else {
            $query      = "SELECT *, resource_id
                      FROM termine
                      LEFT JOIN resources_assign AS ra ON (ra.assign_user_id = termine.termin_id)
                      WHERE range_id = ? AND termin_id = ?
                      ORDER BY date, content";
            $parameters = [$seminar_id, $termin_id];
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function showToolStart()
    {
        $template                    = $GLOBALS['template_factory']->open('resources/planning/start');
        $template->semester_id       = $this->semester_id;
        $template->open_requests     = $this->getMyOpenRequests();
        $template->open_sem_requests = $this->getMyOpenSemRequests();
        $template->open_res_requests = $this->getMyOpenResRequests();
        $template->no_time           = $this->getMyOpenNoTimeRequests();
        $template->display_no_time   = $this->show_requests_no_time;
        $template->display_sem_type  = $this->sem_type;//MOD_BREMEN
        $template->display_faculty   = $this->faculty;//MOD_BREMEN
        $template->display_tagged    = $this->tagged;//MOD_BREMEN
        $template->rooms             = $this->getMyRequestedRooms();
        echo $template->render();
    }

    public function showRequestList()
    {
        $template                 = $GLOBALS['template_factory']->open('resources/planning/request_list.php');
        $template->license_to_kil = (Config::get()->RESOURCES_ALLOW_DELETE_REQUESTS && getGlobalPerms($GLOBALS['user']->id) == 'admin');

        echo $template->render();
    }

    /**
     *
     * @param $request_id
     */
    public function showRequest($request_id)
    {
        global $perm;

        $reqObj   = new RoomRequest($request_id);
        $semObj   = new Seminar($reqObj->getSeminarId());
        $sem_link = $perm->have_studip_perm('tutor', $semObj->getId()) ? "seminar_main.php?auswahl=" . $semObj->getId() : "dispatch.php/course/details/?sem_id=" . $semObj->getId() . "&send_from_search=1&send_from_search_page=" . URLHelper::getLink("resources.php?working_on_request=$request_id");
        ?>
        <form method="POST" action="<? echo URLHelper::getLink('?working_on_request=' . $request_id); ?>">
            <?= CSRFProtection::tokenTag() ?>
            <input type="hidden" name="view" value="edit_request">
            <table class="default nohover">
                <colgroup>
                    <col width="4%">
                    <col width="35%">
                    <col width="61%">
                </colgroup>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="2" valign="top">
                        <a href="<?= URLHelper::getLink($sem_link) ?>">
                            <b><?= $semObj->seminar_number ? htmlReady($semObj->seminar_number) . ':' : '' ?><?= htmlReady($semObj->getName()) ?></b>
                        </a>
                        <br>
                        <?
                        $names = $this->selectSemInstituteNames($semObj->getInstitutId());

                        print "&nbsp;&nbsp;&nbsp;&nbsp;" . _("Art der Anfrage") . ": " . $reqObj->getTypeExplained() . "<br>";
                        print "&nbsp;&nbsp;&nbsp;&nbsp;" . _("Erstellt von") . ": <a href=\"" . URLHelper::getLink('dispatch.php/profile?username=' . get_username($reqObj->getUserId())) . "\">" . htmlReady(get_fullname($reqObj->getUserId())) . "</a><br>";
                        print "&nbsp;&nbsp;&nbsp;&nbsp;" . _("Erstellt am") . ": " . strftime('%x %H:%M', $reqObj->mkdate) . '<br>';
                        print "&nbsp;&nbsp;&nbsp;&nbsp;" . _("Letzte �nderung") . ": " . strftime('%x %H:%M', $reqObj->chdate) . '<br>';
                        print "&nbsp;&nbsp;&nbsp;&nbsp;" . _("Letzte �nderung von") . ": <a href=\"" . URLHelper::getLink('dispatch.php/profile?username=' . get_username($reqObj->last_modified_by ?: $reqObj->user_id)) . "\">" . htmlReady(get_fullname($reqObj->last_modified_by ?: $reqObj->user_id)) . "</a><br>";
                        print "&nbsp;&nbsp;&nbsp;&nbsp;" . _("Lehrende") . ': ';
                        $dozent = false;
                        foreach ($semObj->getMembers('dozent') as $doz) {
                            if ($dozent) {
                                echo ", ";
                            }
                            echo '<a href ="' . URLHelper::getLink('dispatch.php/profile?username=' . $doz['username']) . '">' . HtmlReady($doz['fullname']) . "</a>";
                            $dozent = true;
                        }
                        print "<br>";
                        print "&nbsp;&nbsp;&nbsp;&nbsp;" . _("verantwortliche Einrichtung") . ": " . htmlReady($names['inst_name']) . "<br>";
                        print "&nbsp;&nbsp;&nbsp;&nbsp;" . _("verantwortliche Fakult�t") . ": " . htmlReady($names['fak_name']) . "<br>";
                        print "&nbsp;&nbsp;&nbsp;&nbsp;" . _("aktuelle Teilnehmerzahl") . ": " . $semObj->getNumberOfParticipants('total') . '<br>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td valign="top">
                        <b><?= _("angeforderte Belegungszeiten") ?>:</b><br><br>
                        <?
                        $dates = $semObj->getGroupedDates($reqObj->getTerminId(), $reqObj->getMetadateId());
                        if ($dates['first_event']) {
                            $i = 1;
                            if (is_array($dates['info']) && sizeof($dates['info']) > 0) {
                                foreach ($dates['info'] as $info) {
                                    $name = $info['name'];
                                    if ($info['weekend']) $name = '<span style="color:red">' . $info['name'] . '</span>';
                                    printf("<span style=\"color: blue; font-style: italic; font-weight: bold \">%s</span>. %s<br>", $i, $name);
                                    $i++;
                                }
                            }

                            if ($reqObj->getType() != 'date') {
                                echo _("regelm��ige Buchung ab") . ": " . strftime("%x", $dates['first_event']);
                            }
                        } else {
                            print _("nicht angegeben");
                        }
                        ?>
                    </td>
                    <td style="border-left:1px dotted black; background-color: #f3f5f8" width="51%" rowspan="4"
                        valign="top">
                        <table cellpadding="2" cellspacing="0" border="0" width="90%">
                            <tr>
                                <td width="70%">
                                    <b><?= _("angeforderter Raum") ?>:</b>
                                </td>
                                <?
                                unset($resObj);
                                $cols = 0;
                                if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"])) foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"] as $key => $val) {
                                    $cols++;
                                    print "<td width=\"1%\" align=\"left\"><span style=\"color: blue; font-style: italic; font-weight: bold \">" . $cols . ".</span></td>";
                                }
                                ?>
                                <td width="29%" align="right">
                                </td>
                            </tr>
                            <tr>
                                <td width="70%">
                                    <?
                                    if ($request_resource_id = $reqObj->getResourceId()) {
                                        $resObj = ResourceObject::Factory($request_resource_id);
                                        print $resObj->getFormattedLink($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["first_event"]);
                                        print tooltipicon(_('Der ausgew�hlte Raum bietet folgende der w�nschbaren Eigenschaften:') . "\n" . $resObj->getPlainProperties(true), $resObj->getOwnerId() == 'global');
                                        if ($resObj->getOwnerId() == 'global') {
                                            print ' [global]';
                                        }
                                    } else
                                        print _("Es wurde kein Raum angefordert.");

                                    ?>
                                </td>
                                <?
                                $i = 0;
                                if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"]) && sizeof($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"]) > 0) foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"] as $key => $val) {
                                    print "<td width=\"1%\" nowrap>";
                                    if ($request_resource_id) {
                                        if ($request_resource_id == $val["resource_id"]) {
                                            print Icon::create('accept', 'accept', ['title' => _("Dieser Raum ist augenblicklich gebucht"),
                                                                                    true])->asImg();
                                            echo '<input type="radio" name="selected_resource_id[' . $i . ']" value="' . $request_resource_id . '" checked="checked">';
                                        } else {
                                            $overlap_status = $this->showGroupOverlapStatus($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["detected_overlaps"][$request_resource_id], $val["events_count"], $val["overlap_events_count"][$request_resource_id], $val["termin_ids"]);
                                            print $overlap_status["html"];
                                            printf("<input type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s>", $i, $request_resource_id, ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["selected_resources"][$i] == $request_resource_id) ? "checked" : "", ($overlap_status["status"] == 2 || !ResourcesUserRoomsList::CheckUserResource($request_resource_id)) ? "disabled" : "");
                                        }
                                    } else
                                        print "&nbsp;";
                                    print "</td>";
                                    $i++;
                                }

                                ?>
                                <td width="29%" align="right">
                                    <?
                                    if (is_object($resObj)) {
                                        $seats           = $resObj->getSeats();
                                        $requested_seats = $reqObj->getSeats();
                                        if ((is_numeric($seats)) && (is_numeric($requested_seats))) {
                                            $percent_diff = (100 / $requested_seats) * $seats;
                                            if ($percent_diff > 0) $percent_diff = "+" . $percent_diff;
                                            if ($percent_diff < 0) $percent_diff = "-" . $percent_diff;
                                            print round($percent_diff) . "%";
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?
                            if (get_config('RESOURCES_ENABLE_GROUPING')) {
                                $room_group = RoomGroups::GetInstance();
                                $group_id   = $_SESSION['resources_data']['actual_room_group'];
                                ?>
                                <tr>
                                    <td style="border-top:1px solid;" width="100%" colspan="<?= $cols + 2 ?>">
                                        <b><?= _("Raumgruppe ber�cksichtigen") ?>:</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="<?= $cols ?>">
                                        <select name="request_tool_choose_group">
                                            <option <?= (is_null($group_id) ? 'selected' : '') ?>
                                                    value="-"><?= _("Keine Raumgruppe anzeigen") ?></option>
                                            <?
                                            foreach ($room_group->getAvailableGroups() as $gid) {
                                                echo '<option value="' . $gid . '" ' . (!is_null($group_id) && $group_id == $gid ? 'selected' : '') . '>' . htmlReady(my_substr($room_group->getGroupName($gid), 0, 45)) . ' (' . $room_group->getGroupCount($gid) . ')</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td colspan="2">
                                        <?= Button::create(_('Ausw�hlen'), 'request_tool_group') ?><br>
                                    </td>
                                </tr>
                                <?
                                if ($room_group->getGroupCount($group_id)) {
                                    foreach ($room_group->getGroupContent($group_id) as $key) {
                                        ?>
                                        <tr>
                                            <td width="70%">
                                                <?
                                                $resObj = ResourceObject::Factory($key);
                                                print $resObj->getFormattedLink($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["first_event"]);
                                                print tooltipicon(_('Der ausgew�hlte Raum bietet folgende der w�nschbaren Eigenschaften:') . "\n" . $resObj->getPlainProperties(true), $resObj->getOwnerId() == 'global');
                                                if ($resObj->getOwnerId() == 'global') {
                                                    print ' [global]';
                                                }
                                                ?>
                                            </td>
                                            <?
                                            $i = 0;
                                            if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"])) {
                                                foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"] as $key2 => $val2) {
                                                    print "<td width=\"1%\" nowrap>";
                                                    if ($key == $val2["resource_id"]) {
                                                        print Icon::create('accept', 'accept', ['title' => _("Dieser Raum ist augenblicklich gebucht"),
                                                                                                true])->asImg();
                                                        echo '<input type="radio" name="selected_resource_id[' . $i . ']" value="' . $key . '" checked="checked">';
                                                    } else {
                                                        $overlap_status = $this->showGroupOverlapStatus($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["detected_overlaps"][$key], $val2["events_count"], $val2["overlap_events_count"][$resObj->getId()], $val2["termin_ids"]);
                                                        print $overlap_status["html"];
                                                        printf("<input type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s>", $i, $key, ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["selected_resources"][$i] == $key) ? "checked" : "", ($overlap_status["status"] == 2 || !ResourcesUserRoomsList::CheckUserResource($key)) ? "disabled" : "");
                                                    }
                                                    print "</td>";
                                                    $i++;
                                                }
                                            }
                                            ?>
                                            <td width="29%" align="right">
                                                <?
                                                if (is_object($resObj)) {
                                                    $seats           = $resObj->getSeats();
                                                    $requested_seats = $reqObj->getSeats();
                                                    if ((is_numeric($seats)) && (is_numeric($requested_seats))) {
                                                        $percent_diff = (100 / $requested_seats) * $seats;
                                                        if ($percent_diff > 0) $percent_diff = "+" . $percent_diff;
                                                        if ($percent_diff < 0) $percent_diff = "-" . $percent_diff;
                                                        print round($percent_diff) . "%";
                                                    }
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?
                                    }
                                }
                            }
                            ?>
                            <tr>
                                <td style="border-top:1px solid;" width="100%" colspan="<?= $cols + 2 ?>">
                                    <b><?= _("weitere passende R�ume") ?>:</b>
                                </td>
                            </tr>
                            <?
                            if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["considered_resources"])) foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["considered_resources"] as $key => $val) {
                                if ($val["type"] == "matching") $matching_rooms[$key] = true;
                                if ($val["type"] == "clipped") $clipped_rooms[$key] = true;
                                if ($val["type"] == "grouped") $grouped_rooms[$key] = true;
                            }

                            if (sizeof($matching_rooms)) {
                                // filter list to [search_limit_low]...[search_limit_high]
                                $search_limit_low  = $_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["search_limit_low"];
                                $search_limit_high = $_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["search_limit_high"];
                                $matching_rooms    = array_slice($matching_rooms, $search_limit_low, $search_limit_high - $search_limit_low);
                                foreach ($matching_rooms as $key => $val) {
                                    ?>
                                    <tr>
                                        <td width="70%">
                                            <?
                                            $resObj = ResourceObject::Factory($key);
                                            print $resObj->getFormattedLink($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["first_event"]);
                                            print tooltipicon(_('Der ausgew�hlte Raum bietet folgende der w�nschbaren Eigenschaften:') . "\n" . $resObj->getPlainProperties(true), $resObj->getOwnerId() == 'global');
                                            if ($resObj->getOwnerId() == 'global') {
                                                print ' [global]';
                                            }
                                            ?>
                                        </td>
                                        <?
                                        $i = 0;
                                        if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"])) {
                                            foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"] as $key2 => $val2) {
                                                print "<td width=\"1%\" nowrap>";
                                                if ($key == $val2["resource_id"]) {
                                                    print Icon::create('accept', 'accept', ['title' => _("Dieser Raum ist augenblicklich gebucht"),
                                                                                            true])->asImg();
                                                    echo '<input type="radio" name="selected_resource_id[' . $i . ']" value="' . $key . '" checked="checked">';
                                                } else {
                                                    $overlap_status = $this->showGroupOverlapStatus($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["detected_overlaps"][$key], $val2["events_count"], $val2["overlap_events_count"][$resObj->getId()], $val2["termin_ids"]);
                                                    print $overlap_status["html"];
                                                    printf("<input type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s>", $i, $key, ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["selected_resources"][$i] == $key) ? "checked" : "", ($overlap_status["status"] == 2 || !ResourcesUserRoomsList::CheckUserResource($key)) ? "disabled" : "");
                                                }
                                                print "</td>";
                                                $i++;
                                            }
                                        }
                                        ?>
                                        <td width="29%" align="right">
                                            <?
                                            if (is_object($resObj)) {
                                                $seats           = $resObj->getSeats();
                                                $requested_seats = $reqObj->getSeats();
                                                if ((is_numeric($seats)) && (is_numeric($requested_seats))) {
                                                    $percent_diff = (100 / $requested_seats) * $seats;
                                                    if ($percent_diff > 0) $percent_diff = "+" . $percent_diff;
                                                    if ($percent_diff < 0) $percent_diff = "-" . $percent_diff;
                                                    print round($percent_diff) . "%";
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?
                                }
                                ?>
                                <tr>
                                    <td colspan="<?= $cols + 2 ?>" align="center">
                                        <?= _("zeige R�ume") ?>
                                        <a href="<?= URLHelper::getLink('?dec_limit_low=1') ?>">-</a>
                                        <input type="text" name="search_rooms_limit_low" size="1"
                                               value="<?= ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["search_limit_low"] + 1) ?>">
                                        <a href="<?= URLHelper::getLink('?inc_limit_low=1') ?>">+</a>

                                        <?= _('bis') ?>
                                        <a href="<?= URLHelper::getLink('?dec_limit_high=1') ?>">-</a>
                                        <input type="text" name="search_rooms_limit_high" size="1"
                                               value="<?= $_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["search_limit_high"] ?>">
                                        <a href="<?= URLHelper::getLink('?inc_limit_high=1') ?>">+</a>

                                        <?= Icon::create('arr_2up', 'sort', ['title' => 'ausgew�hlten Bereich anzeigen'])->asInput(['name' => 'matching_rooms_limit_submit',]) ?>
                                    </td>
                                </tr>
                                <?
                            } else
                                print "<tr><td width=\"100%\" colspan=\"" . ($cols + 1) . "\">" . _("keine gefunden") . "</td></tr>";

                            //Clipped Rooms
                            if (sizeof($clipped_rooms)) {
                                ?>
                                <tr>
                                    <td style="border-top:1px solid;" width="100%" colspan="<?= $cols + 2 ?>">
                                        <b><?= _("R�ume aus der Merkliste") ?>:</b>
                                    </td>
                                </tr>
                                <?
                                foreach ($clipped_rooms as $key => $val) {
                                    ?>
                                    <tr>
                                        <td width="70%">
                                            <?
                                            $resObj = ResourceObject::Factory($key);
                                            print $resObj->getFormattedLink($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["first_event"]);
                                            print tooltipicon(_('Der ausgew�hlte Raum bietet folgende der w�nschbaren Eigenschaften:') . "\n" . $resObj->getPlainProperties(true), $resObj->getOwnerId() == 'global');
                                            if ($resObj->getOwnerId() == 'global') {
                                                print ' [global]';
                                            }
                                            ?>
                                        </td>
                                        <?
                                        $i = 0;
                                        if (is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"])) {
                                            foreach ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"] as $key2 => $val2) {
                                                print "<td width=\"1%\" nowrap>";
                                                if ($key == $val2["resource_id"]) {
                                                    print Icon::create('accept', 'clickable', ['title' => _('Dieser Raum ist augenblicklich gebucht'),
                                                                                               true])->asImg();
                                                } else {
                                                    $overlap_status = $this->showGroupOverlapStatus($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["detected_overlaps"][$key], $val2["events_count"], $val2["overlap_events_count"][$resObj->getId()], $val2["termin_ids"]);
                                                    print $overlap_status["html"];
                                                    printf("<input type=\"radio\" name=\"selected_resource_id[%s]\" value=\"%s\" %s %s>", $i, $key, ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["selected_resources"][$i] == $key) ? "checked" : "", ($overlap_status["status"] == 2 || !ResourcesUserRoomsList::CheckUserResource($key)) ? "disabled" : "");
                                                }
                                                print "</td>";
                                                $i++;
                                            }
                                        }
                                        ?>
                                        <td width="29%" align="right">
                                            <?
                                            if (is_object($resObj)) {
                                                $seats           = $resObj->getSeats();
                                                $requested_seats = $reqObj->getSeats();
                                                if ((is_numeric($seats)) && (is_numeric($requested_seats))) {
                                                    $percent_diff = (100 / $requested_seats) * $seats;
                                                    if ($percent_diff > 0) $percent_diff = "+" . $percent_diff;
                                                    if ($percent_diff < 0) $percent_diff = "-" . $percent_diff;
                                                    print round($percent_diff) . "%";
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?
                                }
                            }
                            ?>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td valign="top">
                        <b><?= _("gew�nschte Raumeigenschaften") ?>:</b><br><br>
                        <?
                        $properties = $reqObj->getProperties();
                        if (sizeof($properties)) {
                            ?>
                            <table width="99%" cellspacing="0" cellpadding="2" border="0">
                                <?

                                foreach ($properties as $key => $val) {
                                    ?>
                                    <tr>
                                        <td width="70%">
                                            <ul>
                                                <li><?= htmlReady($val["name"]) ?></li>
                                            </ul>
                                        </td>
                                        <td width="30%">
                                            <?
                                            switch ($val["type"]) {
                                                case "bool":
                                                    break;
                                                case "num":
                                                case "text":
                                                    print htmlReady($val["state"]);
                                                    break;
                                                case "select":
                                                    $options = explode(";", $val["options"]);
                                                    foreach ($options as $a) {
                                                        if ($val["state"] == $a) print htmlReady($a);
                                                    }
                                                    break;
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?
                                }
                                ?>
                            </table>
                            <?
                        } else
                            print _("Es wurden keine Raumeigenschaften gew�nscht.");
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td valign="top">
                        <b><?= _("Kommentar des Anfragenden") ?>:</b><br><br>
                        <?
                        if ($comment = $reqObj->getComment()) print $comment; else
                            print _("Es wurde kein Kommentar eingegeben");
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td valign="top">
                        <? $user_status_mkdate = $reqObj->getUserStatus($GLOBALS['user']->id); ?>
                        <b><?= ("Benachrichtigungen") ?>:</b><br>
                        <input type="radio" onChange="jQuery(this).closest('form').submit()" name="reply_recipients"
                               id="reply_recipients_requester" value="requester" checked>
                        <label for="reply_recipients_requester">
                            <?= _("Ersteller") ?>
                        </label>
                        <input type="radio" onChange="jQuery(this).closest('form').submit()" name="reply_recipients"
                               id="reply_recipients_lecturer"
                               value="lecturer" <?= ($reqObj->reply_recipients == 'lecturer' ? 'checked' : '') ?>>
                        <label for="reply_recipients_lecturer">
                            <?= _("Ersteller und alle Lehrenden") ?>
                        </label>
                        <br>
                        <b><?= ("Anfrage markieren") ?>:</b><br>
                        <input type="radio" onChange="jQuery(this).closest('form').submit()" name="request_user_status"
                               id="request_user_status_0" value="0" checked>
                        <label for="request_user_status_0">
                            <?= _("unbearbeitet") ?>
                        </label>
                        <input type="radio" onChange="jQuery(this).closest('form').submit()" name="request_user_status"
                               id="request_user_status_1" value="1" <?= ($user_status_mkdate ? 'checked' : '') ?>>
                        <label for="request_user_status_1">
                            <?= _("bearbeitet") ?>
                        </label>
                        <br><br>
                        <b><?= _("Kommentar zur Belegung (intern)") ?>:</b><br><br>
                        <textarea name="comment_internal" style="width: 90%" rows="2"></textarea>
                    </td>
                </tr>
                <tfoot>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="2" valign="top" align="center">
                        <div class="button-group">
                            <?
                            // can we dec?
                            if ($_SESSION['resources_data']["requests_working_pos"] > 0) {
                                $d = -1;
                                if ($_SESSION['resources_data']["skip_closed_requests"]) while ((!$_SESSION['resources_data']["requests_open"][$_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"] + $d]["request_id"]]) && ($_SESSION['resources_data']["requests_working_pos"] + $d > 0)) $d--;
                                if ((sizeof($_SESSION['resources_data']["requests_open"]) > 1) && (($_SESSION['resources_data']["requests_open"][$_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"] + $d]["request_id"]]) || (!$_SESSION['resources_data']["skip_closed_requests"]))) $inc_possible = true;
                            }


                            if ($inc_possible) {
                                echo Button::create('<< ' . _('Zur�ck'), 'dec_request');
                            }


                            echo Button::createCancel(_('Abbrechen'), 'cancel_edit_request');
                            echo Button::create(_('L�schen'), 'delete_request');

                            if ((($reqObj->getResourceId()) || (sizeof($matching_rooms)) || (sizeof($clipped_rooms)) || (sizeof($grouped_rooms))) && ((is_array($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["groups"])) || ($_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"]]["assign_objects"]))) {
                                echo Button::createAccept(_('Speichern'), 'save_state');
                                echo Button::createCancel(_('Ablehnen'), 'suppose_decline_request');
                            }

                            // can we inc?
                            if ($_SESSION['resources_data']["requests_working_pos"] < sizeof($_SESSION['resources_data']["requests_working_on"]) - 1) {
                                $i = 1;
                                if ($_SESSION['resources_data']["skip_closed_requests"]) while ((!$_SESSION['resources_data']["requests_open"][$_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"] + $i]["request_id"]]) && ($_SESSION['resources_data']["requests_working_pos"] + $i < sizeof($_SESSION['resources_data']["requests_working_on"]) - 1)) $i++;
                                if ((sizeof($_SESSION['resources_data']["requests_open"]) > 1) && (($_SESSION['resources_data']["requests_open"][$_SESSION['resources_data']["requests_working_on"][$_SESSION['resources_data']["requests_working_pos"] + $i]["request_id"]]) || (!$_SESSION['resources_data']["skip_closed_requests"]))) $dec_possible = true;
                            }

                            if ($dec_possible) {
                                echo Button::create(_('Weiter') . ' >>', 'inc_request');
                            }
                            ?>
                        </div>

                        <?
                        if (sizeof($_SESSION['resources_data']["requests_open"]) > 1) printf("<br>" . _("<b>%s</b> von <b>%s</b> Anfragen in der Bearbeitung wurden noch nicht aufgel�st."), sizeof($_SESSION['resources_data']["requests_open"]), sizeof($_SESSION['resources_data']["requests_working_on"]));
                        printf("<br>" . _("Aktueller Request: ") . "<b>%s</b>", $_SESSION['resources_data']["requests_working_pos"] + 1);
                        ?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
        <?
    }

    /**
     *
     * @param $overlaps
     * @param $events_count
     * @param $overlap_events_count
     * @param $group_dates
     */
    public function showGroupOverlapStatus($overlaps, $events_count, $overlap_events_count, $group_dates)
    {
        if ($overlap_events_count) {
            $lock_desc = '';
            foreach ($overlaps as $val) {
                if ($val['lock']) {
                    $lock_desc .= sprintf(_('%s, %s Uhr bis %s, %s Uhr') . "\n", date('d.m.Y', $val['begin']), date('H:i', $val['begin']), date('d.m.Y', $val['end']), date('H:i', $val['end']));
                }
            }
            if ($lock_desc) {
                $lock_desc = _("Sperrzeit(en):\n") . $lock_desc;
            }
            $desc = '';
            if ($overlap_events_count >= round($events_count * Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE / 100)) {
                if ($overlap_events_count == 1) if ($lock_desc) $desc .= sprintf(_("Es besteht eine Belegungssperre zur gew�nschten Belegungszeit.") . "\n" . $lock_desc); else
                    $desc .= sprintf(_("Es existieren �berschneidungen zur gew�nschten Belegungszeit.") . "\n"); else
                    $desc .= sprintf(_("Es existieren �berschneidungen oder Belegungssperren zu mehr als %s%% aller gew�nschten Belegungszeiten.") . "\n" . $lock_desc, Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE);
                $html   = Icon::create('radiobutton-checked', 'attention', ['title' => $desc])->asImg();
                $status = 2;
            } else {
                $desc .= sprintf(_("Einige der gew�nschten Belegungszeiten �berschneiden sich mit eingetragenen Belegungen bzw. Sperrzeiten:\n"));
                foreach ($group_dates as $key => $val) {
                    if ($overlaps[$key]) foreach ($overlaps[$key] as $key2 => $val2) if ($val2["lock"]) $desc .= sprintf(_("%s, %s Uhr bis %s, %s Uhr (Sperrzeit)") . "\n", date("d.m.Y", $val2["begin"]), date("H:i", $val2["begin"]), date("d.m.Y", $val2["end"]), date("H:i", $val2["end"])); else
                        $desc .= sprintf(_("%s von %s bis %s Uhr") . "\n", date("d.m.Y", $val2["begin"]), date("H:i", $val2["begin"]), date("H:i", $val2["end"]));
                }
                $html   = Icon::create('radiobutton-checked', 'sort', ['title' => $desc])->asImg();
                $status = 1;
            }
        } else {
            $html   = Icon::create('radiobutton-checked', 'accept', ['title' => _('Es existieren keine �berschneidungen')])->asImg();
            $status = 0;
        }
        return ["html" => $html, "status" => $status];
    }


    public function showOverlapStatus($overlaps, $events_count, $overlap_events_count)
    {
        if (is_array($overlaps)) {
            $lock_desc = '';
            foreach ($overlaps as $val) {
                if ($val["lock"]) {
                    $lock_desc .= sprintf(_('%s, %s Uhr bis %s, %s Uhr') . "\n", date('d.m.Y', $val['begin']), date('H:i', $val['begin']), date('d.m.Y', $val['end']), date('H:i', $val['end']));
                }
            }
            if ($lock_desc) {
                $lock_desc = _("Sperrzeit(en):\n") . $lock_desc;
            }
            $desc = '';
            if ($overlap_events_count >= round($events_count * Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE / 100)) {
                if ($overlap_events_count == 1) if ($overlaps[0]["lock"]) $desc .= sprintf(_("Es besteht eine Belegungssperre zur gew�nschten Belegungszeit.") . "\n" . $lock_desc); else
                    $desc .= sprintf(_("Es existieren �berschneidungen zur gew�nschten Belegungszeit.") . "\n"); else
                    $desc .= sprintf(_("Es existieren �berschneidungen oder Belegungssperren zu mehr als %s%% aller gew�nschten Belegungszeiten.") . "\n" . $lock_desc, Config::get()->RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE);
                $html   = Icon::create('decline', 'attention', ['title' => $desc])->asImg();
                $status = 2;
            } else {
                $desc .= sprintf(_("Einige der gew�nschten Belegungszeiten �berschneiden sich mit eingetragenen Belegungen bzw. Sperrzeiten:\n"));
                foreach ($overlaps as $val) {
                    if ($val["lock"]) $desc .= sprintf(_("%s, %s Uhr bis %s, %s Uhr (Sperrzeit)") . "\n", date("d.m.Y", $val["begin"]), date("H:i", $val["begin"]), date("d.m.Y", $val["end"]), date("H:i", $val["end"])); else
                        $desc .= sprintf(_("%s von %s bis %s Uhr") . "\n", date("d.m.Y", $val["begin"]), date("H:i", $val["begin"]), date("H:i", $val["end"]));
                }
                $html   = Icon::create('exclaim-circle', 'inactive', ['title' => $desc])->asImg();
                $status = 1;
            }
        } else {
            $html   = Icon::create('accept', 'accept', ['title' => _('Es existieren keine �berschneidungen')])->asImg();
            $status = 0;
        }
        return ["html" => $html, "status" => $status];
    }
}
