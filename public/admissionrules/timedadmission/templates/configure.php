<h3><?= $rule->getName() ?></h3>
<?= $tpl ?>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
    <div class="admissionrule_label">
        <label for="startdate"><?= _('Start des Anmeldezeitraums') ?>:</label>
    </div>
    <div class="admissionrule_value">
        <input type="date" name="startdate" id="startdate" size="8"
            value="<?= $rule->getStartTime() ? date('d.m.Y', $rule->getStartTime()) : date('d.m.Y') ?>"/>
        &nbsp;&nbsp;
        <input type="time" name="starttime" id="starttime" size="4"
            value="<?= $rule->getStartTime() ? date('H:i', $rule->getStartTime()) : date('H:i') ?>"/>
    </div>
</div>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
    <div class="admissionrule_label">
        <label for="enddate"><?= _('Ende des Anmeldezeitraums') ?>:</label>
    </div>
    <div class="admissionrule_value">
        <input type="date" name="enddate" id="enddate" size="8"
            value="<?= $rule->getEndTime() ? date('d.m.Y', $rule->getEndTime()) : date('d.m.Y') ?>"/>
        &nbsp;&nbsp;
        <input type="time" name="endtime" id="endtime" size="4"
            value="<?= $rule->getEndTime() ? date('H:i', $rule->getEndTime()) : date('H:i') ?>"/>
    </div>
</div>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
    <div class="admissionrule_label">
        <label for="start"><?= _('Zeitpunkt der automatischen Platzverteilung') ?>:</label>
    </div>
    <div class="admissionrule_value">
        <input type="date" name="distributiondate" id="distributiondate" size="8"
            value="<?= $rule->getDistributionTime() ? date('d.m.Y', $rule->getDistributionTime()) : '' ?>"/>
        &nbsp;&nbsp;
        <input type="time" name="distributiontime" id="distributiontime" size="4"
            value="<?= $rule->getDistributionTime() ? date('H:i', $rule->getDistributionTime()) : '' ?>"/>
    </div>
</div>
<script>
    $('#startdate').datepicker();
    $('#starttime').timepicker();
    $('#enddate').datepicker();
    $('#endtime').timepicker();
    $('#distributiondate').datepicker();
    $('#distributiontime').timepicker();
</script>