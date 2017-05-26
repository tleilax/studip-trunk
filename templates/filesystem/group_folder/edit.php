<label>
    <?= _("Wählen sie eine zugehörige Gruppe aus") ?>
    <? $groups = Statusgruppen::findBySeminar_id(Request::get('cid')); ?>
    <select name="group">
    <? if($groups != null) : ?>
        <? foreach($groups as $group) : ?>
        <option value=<?= htmlReady($group->statusgruppe_id) ?>>
            <?= htmlReady($group->name) ?>
        </option>
        <? endforeach ?>
    <? else : ?>
        <option value=<?= null ?>><?= _("Es existiert keine Gruppe in dieser Veranstaltung") ?>
    <? endif ?>
    </select>
</label>