<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admissionrule_data">
    <div class="admissionrule_label_fullsize">
        <label for="message"><?= _('Nachricht bei fehlgeschlagener Anmeldung') ?>:</label>
    </div>
    <div class="admissionrule_value">
        <textarea name="message" rows="4" cols="50"><?= $rule->getMessage() ?></textarea>
    </div>
</div>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admissionrule_data">
    <div class="admissionrule_label">
        <label for="start_time"><?= _('Gültigkeitszeitraum der Regel') ?>:</label>
    </div>
    <div class="admissionrule_value">
        <?= _('von') ?>
        <input type="text" size="8" maxlength="10" name="start_date" 
            id="start_date" value="<?= $rule->getStartTime() ? 
            date('d.m.Y', $rule->getStartTime()) : '' ?>"/>
        <?= _('bis') ?>
        <input type="text" size="8" maxlength="10" name="end_date" 
            id="end_date" value="<?= $rule->getEndTime() ? 
            date('d.m.Y', $rule->getEndTime()) : '' ?>"/>
        <script>
            $('#start_date').datepicker();
            $('#end_date').datepicker();
        </script>
    </div>
</div>