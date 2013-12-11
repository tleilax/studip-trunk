<?php
use Studip\Button, Studip\LinkButton;
?>
<div id="errormessage"></div>
<form action="<?= $controller->url_for('admission/rule/save', get_class($rule), $rule->getId()) ?>" id="ruleform" onsubmit="return STUDIP.Admission.checkAndSaveRule('<?= $rule->getId() ?>', 'errormessage', '<?= $controller->url_for('admission/rule/validate', get_class($rule)) ?>', 'rules', '<?= $controller->url_for('admission/rule/save', get_class($rule), $rule->getId()) ?>')">
    <?= $ruleTemplate ?>
    <div class="submit_wrapper">
        <input type="hidden" id="action" name="action" value=""/>
        <?= CSRFProtection::tokenTag(); ?>
        <?= Button::createAccept(_('Speichern'), 'submit', array('id' => 'submitrule')) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), 'cancel', array(
                'id' => 'cancelrule',
                'onclick' => "return STUDIP.Admission.closeDialog(this)")
            ) ?>
    </div>
</form>