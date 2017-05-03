<label>
    <?= _("Wählen sie eine zugehörige Gruppe aus") ?>
    <? $groups = Statusgruppen::findBySeminar_id(Request::get('cid')); ?>
    <select name="group">
    <? foreach($groups as $group){ ?>
        <option value=<?= $group->statusgruppe_id ?>><?= $group->name ?> </option>
    <? } ?>
    </select>
</label>