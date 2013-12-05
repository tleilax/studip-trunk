<div>
<label for="admission_user_limit"><?=_("Ich m�chte folgende Anzahl an Veranstaltungen belegen:") ?></label>
<select name="admission_user_limit">
<? foreach(range(1, $max_limit) as $max) : ?>
    <option <?= $user_max_limit == $max ? 'selected' : '' ?>>
    <?= $max ?>
    </option>
<? endforeach ?>
</select>
</div>
<table class="default collapsable zebra-hover">
<caption>
<?= _("Priorisierung von Veranstaltungen") ?>
</caption>
<thead>
    <tr>
    <th><?= _("Name")?></th>
    <th><?= _("Pl�tze")?></th>
    <th>&#x03A3; / &#x2300; <?= _("Priorit�t")?></th>
    <th><?= _("Eigene Priorit�t")?></th>
    <th><?= _("Entfernen")?> </th>
    </tr>
</thead>
<tbody>
<? foreach ($priocourses as $course) :?>
    <tr>
    <td><?= htmlReady($course->name) ?></td>
    <td><?= htmlReady($course->admission_turnout) ?></td>
    <td><?= (int)$prio_stats[$course->id]['c'] . ' / ' . round($prio_stats[$course->id]['a'],1) ?></td>
    <td>
    <? foreach(range(1, $max_limit*3) as $p) : ?>
        <input type="radio" <?= ($user_prio[$course->id] == $p ? 'checked' : '')?> name="admission_prio[<?= htmlready($course->id) ?>]" value="<?= $p?>">
    <? endforeach;?>
    </td>
    <td>
    <input type="checkbox" name="admission_prio_delete[<?= htmlready($course->id) ?>]" value="1">
    </td>
    </tr>
<? endforeach?>
</tbody>
</table>