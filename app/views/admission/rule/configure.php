<?php
use Studip\Button, Studip\LinkButton;

if (!$ruleType) {
    foreach ($ruleTypes as $className => $classDetail) {
    ?>
    <div style="padding: 5px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <input type="radio" name="ruletype" value="<?= $className?>" onclick="return STUDIP.Admission.configureRule($(this).val(), '<?= $controller->url_for('admission/rule/configure/'.$className); ?>');"/>&nbsp;<b><?= $via_ajax ? utf8_encode($classDetail['name']) : $classDetail['name'] ?></b>
        <?php if ($via_ajax) { ?>
        <a href="#" onclick="return STUDIP.Admission.toggleRuleDescription('<?= $className ?>_details')">
            <?= Assets::img('icons/16/blue/question-circle.png', 
                array('title' => _('Detailliertere Informationen zu diesem Regeltyp'))) ?></a>
        <?php } ?>
        <div id="<?= $className ?>_details" style="font-style: italic; padding-left: 25px;<?= $via_ajax ? ' display: none;' : ''?>">
            <?= $via_ajax ? utf8_encode($classDetail['description']) : $classDetail['description'] ?>
        </div>
    </div>
    <?php
    }
    ?>
    <div style="padding: 5px; text-align: center;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
        <?= Button::create(_('Abbrechen'), 'cancel', array('onclick' => "$('#configurerule').remove()")) ?>
    </div>
<?php
} else {
?>
<form action="<?= $controller->url_for('admission/rule/add') ?>" id="ruleform" onsubmit="return STUDIP.Admission.addRule('rules', '<?= $controller->url_for('admission/rule/add') ?>')">
<?php
    echo $ruleType::getTemplate();
?>
    <div style="width: 95%; padding: 5px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>" align="center">
        <input type="hidden" name="ruletype" value="<?= $ruleType ?>"/>
        <?= Button::create(_('Speichern'), 'submit') ?>
        <?= Button::create(_('Abbrechen'), 'cancel', array('onclick' => "$('#configurerule').remove()")) ?>
    </div>
</form>
<?php
}
?>