<div style="text-align: left;">
    <a href="javascript:" onClick="STUDIP.Raumzeit.toggleCheckboxes('<?= $tpl['cycle_id'] ?: 'irregular' ?>')" style="margin-right: 15px">
        <?= _('Alle ausw�hlen/abw�hlen') ?>
    </a>
    <select name="checkboxAction">
        <option style="font-weight: bold;"><?= _('ausgew�hlte Termine...') ?></option>
        <? if ($tpl['cycle_id']) : ?>
            <option value="cancel"><?= _('ausfallen lassen') ?></option>
            <option value="takeplace"><?= _('stattfinden lassen') ?></option>
        <? else : ?>
            <option value="delete"><?= _('l�schen') ?></option>
        <? endif ?>
        <option value="edit"><?= _('bearbeiten') ?></option>
    </select>
    <?= Studip\Button::create(_('Ausf�hren')) ?>
</div>
