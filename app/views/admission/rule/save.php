<div class="rule" id="rule_<?= $rule->getId() ?>">
    <a href="#" onclick="return STUDIP.Admission.toggleDetails('rule_arrow_<?= $rule->getId() ?>', 'rule_details_<?= $rule->getId() ?>')">
        <?= Assets::img('icons/16/blue/arr_1right.png', 
            array('id' => 'rule_arrow_'.$rule->getId(), 
            'align' => 'top', 'rel' => Assets::image_path('icons/16/blue/arr_1down.png'))) ?>
        <?= $via_ajax ? utf8_encode($rule->getName()) : $rule->getName() ?>
    </a>
    <a href="#" onclick="return STUDIP.Admission.configureRule('<?= get_class($rule) ?>', '<?= $controller->url_for('admission/rule/configure', get_class($rule), $rule->getId()) ?>')">
        <?= Assets::img('icons/16/blue/edit.png'); ?></a>
    <a href="#" onclick="return STUDIP.Dialogs.showConfirmDialog('<?= 
                _('Soll die Anmelderegel wirklich gelöscht werden?') ?>', 
                'javascript:STUDIP.Admission.removeRule(\'rule_<?= $rule->getId() ?>\', \'rules\')')">
        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
    <div id="rule_details_<?= $rule->getId() ?>" style="display: none; margin-left: 20px;">
        <?= $via_ajax ? utf8_encode($rule->toString()) : $rule->toString() ?>
    </div>
    <input type="hidden" name="rules[]" value="<?= htmlentities(serialize($rule)) ?>"/>
</div>