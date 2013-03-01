<?php
use Studip\Button, Studip\LinkButton;
?>
<div id="conditionfields">
    <?= $this->render_partial('conditions/field/configure.php'); ?>
</div>
<br/>
<a href="#" onclick="return STUDIP.Conditions.addConditionField('conditionfields', '<?= $controller->url_for('conditions/field/configure') ?>')">
    <?= Assets::img('icons/16/blue/plus.png', array('alt' => _('Auswahlfeld hinzufügen'))) ?>
    <?php
        $text = _('Auswahlfeld hinzufügen');
        if ($via_ajax) {
            $text = utf8_encode($text);
        }
        echo $text;
    ?>
</a>
<br/><br/>
<?php
    $text = _('Soll die Bedingung nur zu einer bestimmten Zeit gelten, so kann hier ein Gültigkeitszeitraum angegeben werden:');
    if ($via_ajax) {
        $text = utf8_encode($text);
    }
    echo $text;
?>
<br/>
<?php
    $text = _('Gültigkeit von');
    if ($via_ajax) {
        $text = utf8_encode($text);
    }
    echo $text;
?>
<br/>
<input type="date" name="startdate" id="startdate" size="8"
    value="<?= ($condition && $condition->getStartTime()) ? date('d.m.Y', $condition->getStartTime()) : '' ?>"/>
&nbsp;&nbsp;
<input type="number" name="starttime" id="starttime" size="4"
    value="<?= ($condition && $condition->getStartTime()) ? date('H:i', $condition->getStartTime()) : '' ?>"/>
<br/>
<?= _('bis') ?>
<br/>
<input type="date" name="enddate" id="enddate" size="8"
    value="<?= ($condition && $condition->getStartTime()) ? date('d.m.Y', $condition->getEndTime()) : '' ?>"/>
&nbsp;&nbsp;
<input type="number" name="endtime" id="endtime" size="4"
    value="<?= ($condition && $condition->getStartTime()) ? date('H:i', $condition->getEndTime()) : '' ?>"/>
<br/><br/>
<div align="center">
    <script>
        $('#startdate').datepicker();
        $('#starttime').timepicker();
        $('#enddate').datepicker();
        $('#endtime').timepicker();
    </script>
    <?= Button::createAccept(_('Speichern'), 'submit', array('onclick' => "STUDIP.Conditions.addCondition('conditionfields', 'conditions', '".$controller->url_for('conditions/condition/add')."');")) ?>
    <?= Button::createCancel(_('Abbrechen'), 'cancel', array('onclick' => '$("#condition").remove()')) ?>
</div>