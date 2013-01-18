<div style="width: 95%; padding: 5px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
    <div style="display: inline-block; vertical-align: top; font-weight: bold;">
        <label for="maxnumber"><?= _('Nachricht bei fehlgeschlagener Anmeldung') ?>:</label>
    </div>
    <div style="display: inline-block; vertical-align: top;">
        <textarea name="message" rows="4" cols="50"/><?= $rule->rule->getMessage() ?></textarea>
    </div>
</div>