<?
$tab_arr = '';
$tab = '';
$max_columns = 0;
$rows = ($end - $start + 1) * 3600 / $calendar->getUserSettings('step_week');
// calculating the maximum title length
$length = ceil(125 / $calendar->view->getType());

$params = array(
        'precol'       => FALSE,
        'compact'      => FALSE,
        'link_edit'    => $link_edit,
        'title_length' => $length,
        'height'       => 20,
        'padding'      => 4,
        'spacing'      => 1,
        'bg_image'     => 'small');

$max_columns = 0;
for ($i = 0; $i < $calendar->view->getType(); $i++) {
    $tab_arr[$i] = createEventMatrix($calendar->view->wdays[$i], $start * 3600, $end * 3600, $calendar->getUserSettings('step_week'));
    if ($tab_arr[$i]['max_cols']) {
        $max_columns += ($tab_arr[$i]['max_cols'] + 1);
    } else {
        $max_columns++;
    }
}

$rowspan = ceil(3600 / $calendar->getUserSettings('step_week'));
$height = ' height="20"';

if ($rowspan > 1) {
    $colspan_1 = ' colspan="2"';
    $colspan_2 = $max_columns + 4;
} else {
    $colspan_1 = '';
    $colspan_2 = $max_columns + 2;
}

if ($calendar->view->getType() == 7) {
    $width = '1%';
} else {
    $width = '3%';
}

?>
<table border="0" width="100%" cellspacing="1" cellpadding="0" class="steelgroup0">
    <tr>
        <td colspan="<?= $colspan_2 ?>">
            <table width="100%" border="0" cellpadding="2" cellspacing="0" align="center" class="steelgroup0">
                <tr>
                    <td align="center" width="15%">
                        <a href="<?= URLHelper::getLink('', array('cmd' => 'showweek', 'atime' => mktime(12, 0, 0, date('n', $calendar->view->getStart()), date('j', $calendar->view->getStart()) - 7, date('Y', $calendar->view->getStart())))) ?>">
                            <?= Assets::img('icons/16/blue/arr_2left.png', tooltip2(_("eine Woche zur�ck"))) ?>
                        </a>
                    </td>
                    <td width="70%" class="calhead">
                        <? printf(_("%s. Woche vom %s bis %s"), strftime("%V", $calendar->view->getStart()), strftime("%x", $calendar->view->getStart()), strftime("%x", $calendar->view->getEnd())) ?>
                    </td>
                    <td align="center" width="15%">
                        <a href="<?= URLHelper::getLink('', array('cmd' => 'showweek', 'atime' => mktime(12, 0, 0, date('n', $calendar->view->getStart()), date('j', $calendar->view->getStart()) + 7, date('Y', $calendar->view->getStart())))) ?>">
                            <?= Assets::img('icons/16/blue/arr_2right.png', tooltip2(_("eine Woche vor"))) ?>
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td nowrap="nowrap" align="center" width="<?= $width ?>"<?= $colspan_1 ?>>
            <? if ($start > 0) : ?>
            <a href="<?= URLHelper::getLink('', array('cmd' => 'showweek', 'atime' => mktime($start - 1, 0, 0, date('n', $calendar->view->getStart()), date('j', $calendar->view->getStart()), date('Y', $calendar->view->getStart())))) ?>">
                <?= Assets::img('icons/16/blue/arr_1up.png', tooltip2(_("zeig davor"))) ?>
            </a>
            <? endif ?>
        </td>
        <? // weekday and date as title for each column ?>
        <? for ($i = 0; $i < $calendar->view->getType(); $i++) : ?>
        <td class="steelgroup0" style="text-align:center; font-weight:bold;" <?= ($calendar->view->getType() == 5 ? 'width="19%"' : 'width="13%"') ?><?= ($tab_arr[$i]['max_cols'] > 0 ? ' colspan="' . ($tab_arr[$i]['max_cols'] + 1) . '"' : '' ) ?>>
            <a class="calhead" href="<?= URLHelper::getLink('', array('cmd' => 'showday', 'atime' => $calendar->view->wdays[$i]->getTs())) ?>">
                <?= wday($calendar->view->wdays[$i]->getTs(), 'SHORT') . ' ' . date('d', $calendar->view->wdays[$i]->getTs()) ?>
            </a>
            <? if ($holiday = $calendar->view->wdays[$i]->isHoliday()) : ?>
            <div style="font-size:9pt; color:#bbb; height:auto; overflow:visible; font-weight:bold;"><?= $holiday['name'] ?></div>
            <? endif ?>
        </td>
        <? endfor ?>
        <td nowrap="nowrap" align="center" width="<?= $width ?>"<?= $colspan_1 ?>>
            <? if ($start > 0) : ?>
            <a href="<?= URLHelper::getLink('', array('cmd' => 'showweek', 'atime' => mktime($start - 1, 0, 0, date('n', $calendar->view->getStart()), date('j', $calendar->view->getStart()), date('Y', $calendar->view->getStart())))) ?>">
                <?= Assets::img('icons/16/blue/arr_1up.png', tooltip(_("zeig davor"))) ?>
            </a>
            <? endif ?>
        </td>
    </tr>
    <tr>
        <? // Zeile mit Tagesterminen audgeben ?>
        <td class="precol1w"<?= $colspan_1 ?> height="25">
            <?= _("Tag") ?>
        </td>
        <? for ($i = 0; $i < $calendar->view->getType(); $i++) : ?>
        <td class="steel1" style="text-align:right; vertical-align:bottom;"<?= (($tab_arr[$i]['max_cols'] > 0) ? ' colspan="' . ($tab_arr[$i]['max_cols'] + 1) . '"' : '') ?>>
            <?= $this->render_partial('calendar/_day_dayevents', array('em' => $tab_arr[$i])) ?>
        </td>
        <? endfor ?>
        <td class="precol1w"<?= $colspan_1 ?>>
            <?= _("Tag") ?>
        </td>
    </tr>
    <? $j = $start ?>
    <? for ($i = 0; $i < $rows; $i++) : ?>
    <tr>
        <? if ($i % $rowspan == 0) : ?>
            <? if ($rowspan == 1) : ?>
            <td class="precol1w"<?= $height ?>><?= $j ?></td>
            <?  else : ?>
            <td class="precol1w" rowspan="<?= $rowspan ?>"><?= $j ?></td>
            <? endif ?>
        <? endif ?>
        <? if ($rowspan > 1) : ?>
            <? $minutes = (60 / $rowspan) * ($i % $rowspan); ?>
            <? if ($minutes == 0) : ?>
            <td class="precol2w"<?= $height ?>>00</td>
            <? else : ?>
            <td class="precol2w"<?= $height ?>><?= $minutes ?></td>
            <? endif ?>
        <? endif ?>
        <? for ($y = 0; $y < $calendar->view->getType(); $y++) : ?>
            <?= $this->render_partial('calendar/_day_cell', array('day' => $calendar->view->wdays[$y], 'em' => $tab_arr[$y], 'row' => $i, 'i' => $j)); ?>
        <? endfor ?>
        <? if ($rowspan > 1) : ?>
            <? if ($minutes == 0) : ?>
            <td class="precol2w"<?= $height ?>>00</td>
            <? else : ?>
            <td class="precol2w"<?= $height ?>><?= $minutes ?></td>
            <? endif ?>
        <? endif ?>
        <? if (($i + 2) % $rowspan == 0) : ?>
            <? if ($rowspan == 1) : ?>
            <td class="precol1w"<?= $height ?>><?= $j ?></td>
            <?  else : ?>
            <td class="precol1w" rowspan="<?= $rowspan ?>"><?= $j ?></td>
            <? $j = $j + ceil($calendar->getUserSettings('step_week') / 3600); ?>
            <? endif ?>
        <? endif ?>
    </tr>
    <? endfor ?>
    <tr>
        <td<?= $colspan_1 ?> style="text-align:center;">
        <? if ($end < 23) : ?>
            <a href="<?= URLHelper::getLink('', array('cmd' => 'showweek', 'atime' => mktime($end + 1, 0, 0, date('n', $calendar->view->getStart()), date('j', $calendar->view->getStart()), date('Y', $calendar->view->getStart())))) ?>">
                <?= Assets::img('icons/16/blue/arr_1down.png', tooltip2(_("zeig danach"))) ?>
            </a>
        <? endif ?>
        </td>
        <td colspan="<?= $max_columns ?>">&nbsp;</td>
        <td<?= $colspan_1 ?> align="center">
        <? if ($end < 23) : ?>
            <a href="<?= URLHelper::getLink('', array('cmd' => 'showweek', 'atime' => mktime($end + 1, 0, 0, date('n', $calendar->view->getStart()), date('j', $calendar->view->getStart()), date('Y', $calendar->view->getStart())))) ?>">
                <?= Assets::img('icons/16/blue/arr_1down.png', tooltip(_("zeig danach"))) ?>
            </a>
        <? endif ?>
        </td>
    </tr>
</table>

<?
/*
// put the table together
for ($i = 1; $i < $rows + 2; $i++){
    if ($compact) {
        $tab[$i] .= '<tr>';
    }
    for ($j = 0; $j < $week_obj->getType(); $j++){
        $tab[$i] .= $tab_arr[$j]['table'][$i - 1];
    }
    if ($compact) {
        $tab[$i] .= "</tr>\n";
    }
}

if ($compact) {
    $tab = implode('', $tab);
}

$tab = array('table' => $tab, 'max_columns' => $max_columns);



// Zeile mit Tagesterminen ausgeben
$out .= "<tr><td class=\"precol1w\"$colspan_1 height=\"25\">" . _("Tag");
$out .= "</td>{$tab['table'][1]}<td class=\"precol1w\"$colspan_1>";
$out .= _("Tag") . "</td></tr>\n";
$out .= "<tr height=\"2\"><td colspan=\"" . (2 * $colspan_1 + $colspan_2) . "\"></tr>\n";

$j = $start;
for ($i = 2; $i < sizeof($tab['table']); $i++) {
    $out .= "<tr>";

    if ($i % $rowspan == 0) {
        if ($rowspan == 1) {
            $out .= "<td class=\"precol1w\"$height>$j</td>";
        } else {
            $out .= "<td class=\"precol1w\" rowspan=\"$rowspan\">$j</td>";
        }
    }
    if ($rowspan > 1) {
        $minutes = (60 / $rowspan) * ($i % $rowspan);
        if ($minutes == 0) {
            $minutes = '00';
        }
        $out .= "<td class=\"precol2w\"$height>$minutes</td>\n";
    }

    $out .= $tab['table'][$i];

    if ($rowspan > 1) {
        $out .= "<td class=\"precol2w\">$minutes</td>\n";
    }
    if ($i % $rowspan == 0) {
        if ($rowspan == 1) {
            $out .= "<td class=\"precol1w\">$j</td>";
        } else {
            $out .= "<td class=\"precol1w\" rowspan=\"$rowspan\">$j</td>";
        }
        $j = $j + ceil($step / 3600);
    }

    $out .= "</tr>\n";
}

$out .= "<tr><td$colspan_1 align=\"center\">";
if ($end < 23) {
    $out .= "<a href=\"$PHP_SELF?cmd=showweek&atime=";
    $out .= mktime($end + 1, 0, 0, date('n', $week_obj->getStart()),
        date('j', $week_obj->getStart()), date('Y', $week_obj->getStart()));
    $out .= '"><img border="0" src="' . Assets::image_path('icons/16/blue/arr_1down.png') . '" ';
    $out .= tooltip(_("zeig danach")) . "></a>";
} else {
    $out .= '&nbsp';
}
$out .= "</td><td colspan=\"{$tab['max_columns']}\">&nbsp;</td>";
$out .= "<td$colspan_1 align=\"center\">";
if ($end < 23) {
    $out .= "<a href=\"$PHP_SELF?cmd=showweek&atime=";
    $out .= mktime($end + 1, 0, 0, date('n', $week_obj->getStart()),
        date('j', $week_obj->getStart()), date('Y', $week_obj->getStart()));
    $out .= '"><img border="0" src="' . Assets::image_path('icons/16/blue/arr_1down.png') . '" ';
    $out .= tooltip(_("zeig danach")) . "></a>";
} else {
    $out .= '&nbsp;';
}
$out .= "</td></tr>\n</table>\n";

return $out;
*/

?>