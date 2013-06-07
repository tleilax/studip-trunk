<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
    <div class="admissionrule_label">
        <label for="message"><?= _('Nachricht bei fehlgeschlagener Anmeldung') ?>:</label>
    </div>
    <div class="admission_value">
        <textarea name="message" rows="4" cols="50"><?= $rule->getMessage() ?></textarea>
    </div>
</div>