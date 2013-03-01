<h3><?= $rule->getName() ?></h3>
<?php echo $this->render_partial('admission/rules/configure.php'); ?>
<div style="width: 95%; padding: 5px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
    <div style="display: inline-block; vertical-align: top; font-weight: bold; width: 45%;">
        <label for="start"><?= _('Start des Anmeldezeitraums') ?>:</label>
    </div>
    <div style="display: inline-block; vertical-align: top;">
        <input type="date" name="startdate" id="startdate" size="8"
            value="<?= $rule->getStartTime() ? date('d.m.Y', $rule->getStartTime()) : date('d.m.Y') ?>"/>
        &nbsp;&nbsp;
        <input type="time" name="starttime" id="starttime" size="4"
            value="<?= $rule->getStartTime() ? date('H:i', $rule->getStartTime()) : date('H:i') ?>"/>
    </div>
</div>
<div style="width: 95%; padding: 5px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
    <div style="display: inline-block; vertical-align: top; font-weight: bold; width: 45%;">
        <label for="start"><?= _('Ende des Anmeldezeitraums') ?>:</label>
    </div>
    <div style="display: inline-block; vertical-align: top;">
        <input type="date" name="enddate" id="enddate" size="8"
            value="<?= $rule->getEndTime() ? date('d.m.Y', $rule->getEndTime()) : date('d.m.Y') ?>"/>
        &nbsp;&nbsp;
        <input type="time" name="endtime" id="endtime" size="4"
            value="<?= $rule->getEndTime() ? date('H:i', $rule->getEndTime()) : date('H:i') ?>"/>
    </div>
</div>
<script>
    $('#startdate').datepicker();
    $('#starttime').timepicker();
    $('#enddate').datepicker();
    $('#endtime').timepicker();
</script>