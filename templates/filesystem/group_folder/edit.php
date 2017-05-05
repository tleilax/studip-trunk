<label>
    <?= _("Wählen sie eine zugehörige Gruppe aus") ?>
    <? $groups = Statusgruppen::findBySeminar_id(Request::get('cid')); ?>
    <select name="group">
    <? if($groups != null) { ?>
        <? foreach($groups as $group){ ?>
        <option value=<?= $group->statusgruppe_id ?>><?= $group->name ?> </option>
        <? } ?>
    <? } else { ?>
        <option value=<?= null?>><?= _("Es existiert keine Gruppe in dieser Veranstaltung") ?>
    <? } ?>
    </select>
</label>