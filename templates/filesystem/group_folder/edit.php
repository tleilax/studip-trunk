<label>
    <?= _("W�hlen sie eine zugeh�rige Gruppe aus") ?>
    <? $groups = Statusgruppen::findBySeminar_id(Request::get('cid')); ?>
    <select name="group">
    <? foreach($groups as $group){ ?>
        <option value=<?= $group->statusgruppe_id ?>><?= $group->name ?> </option>
    <? } ?>
    </select>
</label>