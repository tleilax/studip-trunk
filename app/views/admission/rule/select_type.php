<form name="select_rule_type" class="studip_form" action="<?= $controller->url_for('admission/rule/configure') ?>" method="post">
<?php
use Studip\Button;

foreach ($ruleTypes as $className => $classDetail) {
?>
    <div id="<?= $className ?>">
        <input type="radio" name="ruletype" value="<?= $className ?>"/>&nbsp;<b><?= $via_ajax ? studip_utf8encode($classDetail['name']) : $classDetail['name'] ?></b>
        <?php if ($via_ajax) { ?>
        <a href="#" onclick="return STUDIP.Admission.toggleRuleDescription('<?= $className ?>_details')">
            <?= Assets::img('icons/16/blue/question-circle.png', 
                array('title' => _('Detailliertere Informationen zu diesem Regeltyp'))) ?></a>
        <?php } ?>
        <div id="<?= $className ?>_details" class="admissionrules_description" style="<?= $via_ajax ? ' display: none;' : ''?>">
            <?= $via_ajax ? studip_utf8encode($classDetail['description']) : $classDetail['description'] ?>
        </div>
    </div>
    <br/>
<?php
}
?>
    <div class="submit_wrapper">
        <?= CSRFProtection::tokenTag() ?>
        <?= Button::create(_('Weiter >>'), 'configure', array(
            'onclick' => "return STUDIP.Admission.configureRule(this, '".
                $controller->url_for('admission/rule/configure')."')")) ?>
        <?= Button::createCancel(_('Abbrechen'), 'cancel', array('onclick' => "return STUDIP.Admission.closeDialog(this)")) ?>
    </div>
</form>