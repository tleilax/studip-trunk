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
        <input type="number" name="starthour" size="1" max="12"
            value="<?= $rule->getStartTime() ? date('H', $rule->getStartTime()) : date('H') ?>"/>
        :
        <input type="number" name="startminute" size="1"
            value="<?= $rule->getStartTime() ? date('i', $rule->getStartTime()) : date('i') ?>"/>
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
        <input type="number" name="endhour" size="1" max="12"
            value="<?= $rule->getEndTime() ? date('H', $rule->getEndTime()) : date('H') ?>"/>
        :
        <input type="number" name="endminute" size="1"
            value="<?= $rule->getEndTime() ? date('i', $rule->getEndTime()) : date('i') ?>"/>
    </div>
</div>
<script>
    $('#startdate').datepicker();
    $('#enddate').datepicker();
</script>