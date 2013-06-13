<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admissionrule_data">
    <div class="admissionrule_label_fullsize">
        <label for="message"><?= _('Nachricht bei fehlgeschlagener Anmeldung') ?>:</label>
    </div>
    <div class="admissionrule_value">
        <textarea name="message" rows="4" cols="50"><?= $rule->getMessage() ?></textarea>
    </div>
</div>