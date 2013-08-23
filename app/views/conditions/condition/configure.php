<?php
use Studip\Button, Studip\LinkButton;
?>
<div id="conditionfields">
    <?= $this->render_partial('conditions/field/configure.php'); ?>
</div>
<br/>
<a href="#" onclick="return STUDIP.Conditions.addConditionField('conditionfields', '<?= $controller->url_for('conditions/field/configure') ?>')">
    <?= Assets::img('icons/16/blue/add.png', array('alt' => _('Auswahlfeld hinzufügen'))) ?>
    <?php
        $text = _('Auswahlfeld hinzufügen');
        if ($via_ajax) {
            $text = studip_utf8encode($text);
        }
        echo $text;
    ?>
</a>
<br/><br/>
<div align="center">
    <?= Button::createAccept(_('Speichern'), 'submit', array('onclick' => "STUDIP.Conditions.addCondition('".$containerId."', '".$controller->url_for('conditions/condition/add', 'condamission_conditions')."');")) ?>
    <?= Button::createCancel(_('Abbrechen'), 'cancel', array('onclick' => '$("#condition").remove()')) ?>
</div>