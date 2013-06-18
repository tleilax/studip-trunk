<div class="admissionrule" id="rule_<?= $rule->getId() ?>">
    <div class="admissionrule_display" id="rule_data_<?= $rule->getId() ?>">
        <?= $via_ajax ? utf8_encode($rule->toString()) : $rule->toString() ?>
        <input type="hidden" name="rules[]" value="<?= htmlentities(serialize($rule), ENT_COMPAT | ENT_HTML401, 'iso-8859-1') ?>"/>
    </div>
    <div class="admissionrule_actions" id="rule_actions_<?= $rule->getId() ?>">
        <a href="#" onclick="return STUDIP.Admission.configureRule('<?= get_class($rule) ?>', '<?= $controller->url_for('admission/rule/configure', get_class($rule), $rule->getId()) ?>')">
            <?= Assets::img('icons/16/blue/edit.png'); ?></a>
        <a href="#" onclick="return STUDIP.Dialogs.showConfirmDialog('<?= 
                    _('Soll die Anmelderegel wirklich gel�scht werden?') ?>', 
                    'javascript:STUDIP.Admission.removeRule(\'rule_<?= $rule->getId() ?>\', \'rules\')')">
            <?= Assets::img('icons/16/blue/trash.png'); ?></a>
    </div>
</div>
