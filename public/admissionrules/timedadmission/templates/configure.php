<h3><?= $rule->getName() ?></h3>
<label for="message" class="caption">
    <?= _('Nachricht bei fehlgeschlagener Anmeldung') ?>:
</label>
<textarea name="message" rows="4" cols="50"><?= $rule->getMessage() ?></textarea>
<br/>
<label for="startdate" class="caption">
    <?= _('Start des Anmeldezeitraums') ?>:
</label>
<div class="form_group">
    <input type="date" name="startdate" id="startdate" size="8"
        value="<?= $rule->getStartTime() ? date('d.m.Y', $rule->getStartTime()) : date('d.m.Y') ?>"/>
    &nbsp;&nbsp;
    <input type="time" name="starttime" id="starttime" size="4"
        value="<?= $rule->getStartTime() ? date('H:i', $rule->getStartTime()) : date('H:i') ?>"/>
</div>
<br/>
<label for="enddate" class="caption">
    <?= _('Ende des Anmeldezeitraums') ?>:
</label>
<div class="form_group">
    <input type="date" name="enddate" id="enddate" size="8"
        value="<?= $rule->getEndTime() ? date('d.m.Y', $rule->getEndTime()) : date('d.m.Y') ?>"/>
    &nbsp;&nbsp;
    <input type="time" name="endtime" id="endtime" size="4"
        value="<?= $rule->getEndTime() ? date('H:i', $rule->getEndTime()) : date('H:i') ?>"/>
</div>
<br/>
<label for="start" class="caption">
    <?= _('Zeitpunkt der automatischen Platzverteilung') ?>:
</label>
<div class="form_group">
    <input type="date" name="distributiondate" id="distributiondate" size="8"
        value="<?= $rule->getDistributionTime() ? date('d.m.Y', $rule->getDistributionTime()) : '' ?>"/>
    &nbsp;&nbsp;
    <input type="time" name="distributiontime" id="distributiontime" size="4"
        value="<?= $rule->getDistributionTime() ? date('H:i', $rule->getDistributionTime()) : '' ?>"/>
</div>
<script>
    $('#startdate').datepicker();
    $('#starttime').timepicker();
    $('#enddate').datepicker();
    $('#endtime').timepicker();
    $('#distributiondate').datepicker();
    $('#distributiontime').timepicker();
</script>