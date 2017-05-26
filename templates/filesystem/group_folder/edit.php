<?php
$groups = Statusgruppen::findBySeminar_id(Request::get('cid'));
?>
<label>
    <?= _('W�hlen sie eine zugeh�rige Gruppe aus') ?>
    <select name="group">
    <? if (count($groups) === 0): ?>
        <option value="">
            <?= _('Es existiert keine Gruppe in dieser Veranstaltung') ?>
        </option>
    <? endif; ?>
    <? foreach ($groups as $group): ?>
        <option value="<?= htmlReady($group->id) ?>">
            <?= htmlReady($group->name) ?>
        </option>
    <? endforeach; ?>
    </select>
</label>
