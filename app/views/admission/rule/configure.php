<?php
use Studip\Button, Studip\LinkButton;

if (!$ruleType) {
    foreach ($ruleTypes as $className => $classDetail) {
    ?>
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admissionrules_available">
        <input type="radio" name="ruletype" value="<?= $className?>" onclick="return STUDIP.Admission.configureRule($(this).val(), '<?= $controller->url_for('admission/rule/configure/'.$className); ?>');"/>&nbsp;<b><?= $via_ajax ? studip_utf8encode($classDetail['name']) : $classDetail['name'] ?></b>
        <?php if ($via_ajax) { ?>
        <a href="#" onclick="return STUDIP.Admission.toggleRuleDescription('<?= $className ?>_details')">
            <?= Assets::img('icons/16/blue/question-circle.png', 
                array('title' => _('Detailliertere Informationen zu diesem Regeltyp'))) ?></a>
        <?php } ?>
        <div id="<?= $className ?>_details" class="admissionrules_description" style="<?= $via_ajax ? ' display: none;' : ''?>">
            <?= $via_ajax ? studip_utf8encode($classDetail['description']) : $classDetail['description'] ?>
        </div>
    </div>
    <?php
    }
    ?>
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admissionrules_button">
        <?= LinkButton::createCancel(_('Abbrechen'), 'cancel', array('onclick' => "$('#configurerule').remove(); return false;")) ?>
    </div>
<?php
} else {
?>
<div id="errormessage"></div>
<form action="<?= $controller->url_for('admission/rule/save', get_class($rule), $rule->getId()) ?>" id="ruleform" onsubmit="return STUDIP.Admission.checkAndSaveRule('<?= $rule->getId() ?>', 'errormessage', '<?= $controller->url_for('admission/rule/validate', get_class($rule)) ?>', 'rules', '<?= $controller->url_for('admission/rule/save', get_class($rule), $rule->getId()) ?>')">
    <?= $ruleTemplate ?>
    <div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admissionrules_config">
        <input type="hidden" id="action" name="action" value=""/>
        <?= Button::createAccept(_('Speichern'), 'submit') ?>
        <?= LinkButton::createCancel(_('Abbrechen'), 'cancel', array('onclick' => "$('#configurerule').remove(); return false;")) ?>
    </div>
</form>
<?php
}
?>