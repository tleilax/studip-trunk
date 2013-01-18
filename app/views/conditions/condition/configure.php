<?php
use Studip\Button, Studip\LinkButton;
?>
<div id="conditionfields">
    <?= $this->render_partial('conditions/field/configure.php'); ?>
</div>
<br/>
<a href="#" onclick="return STUDIP.Conditions.addConditionField('conditionfields', '<?= $controller->url_for('conditions/field/configure') ?>')">
    <?= Assets::img('icons/16/red/plus.png', array('alt' => _('Auswahlfeld hinzufügen'))) ?>
    <?= utf8_encode(_('Auswahlfeld hinzufügen')) ?></a>
<br/><br/>
<?= utf8_encode(_('Soll die Bedingung nur zu einer bestimmten Zeit gelten, so kann hier ein Gültigkeitszeitraum angegeben werden:')) ?>
<br/>
<?= utf8_encode(_('Gültigkeit von')) ?>
<br/>
<input type="date" name="startdate" id="startdate" size="8"
    value="<?= ($condition && $condition->getStartTime()) ? date('d.m.Y', $condition->getStartTime()) : '' ?>"/>
&nbsp;&nbsp;
<input type="number" name="starthour" id="starthour" size="1" max="12"
    value="<?= ($condition && $condition->getStartTime()) ? date('H', $condition->getStartTime()) : '' ?>"/>
:
<input type="number" name="startminute" id="startminute" size="1"
    value="<?= ($condition && $condition->getStartTime()) ? date('i', $condition->getStartTime()) : '' ?>"/>
<br/>
<?= _('bis') ?>
<br/>
<input type="date" name="enddate" id="enddate" size="8"
    value="<?= ($condition && $condition->getStartTime()) ? date('d.m.Y', $condition->getEndTime()) : '' ?>"/>
&nbsp;&nbsp;
<input type="number" name="endhour" id="endhour" size="1" max="12"
    value="<?= ($condition && $condition->getStartTime()) ? date('H', $condition->getEndTime()) : '' ?>"/>
:
<input type="number" name="endminute" id="endminute" size="1"
    value="<?= ($condition && $condition->getStartTime()) ? date('i', $condition->getEndTime()) : '' ?>"/>
<br/><br/>
<div align="center">
    <script>
        $('#startdate').datepicker();
        $('#enddate').datepicker();
    </script>
    <?= Button::create(_('Übernehmen'), 'submit', array('onclick' => "STUDIP.Conditions.addCondition('conditionfields', 'conditions', '".$controller->url_for('conditions/condition/add')."');")) ?>
    <?= Button::create(_('Abbrechen'), 'cancel', array('onclick' => '$("#condition").remove()')) ?>
</div>