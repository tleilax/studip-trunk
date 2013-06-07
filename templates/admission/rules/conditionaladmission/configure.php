<h3><?= $rule->getName() ?></h3>
<?php echo $this->render_partial('admission/rules/configure.php'); ?>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admission_data">
    <div class="conditionaladmission_label">
        <label for="start"><?= _('Anmeldebedingungen') ?>:</label>
    </div>
    <div class="conditionaladmission_value" id="conditions">
        <?php if (!$rule->getConditions()) { ?>
        <span id="noconditions">
            <i><?= _('Sie haben noch keine Bedingungen festgelegt.'); ?></i>
        </span>
        <?php } else { ?>
        <div id="conditionlist">
            <?php foreach ($rule->getConditions() as $condition) { ?>
                <div class="condition" id="condition_<?= $condition->getId() ?>">
                    <?= $condition->toString() ?>
                    <a href="#" onclick="return STUDIP.Dialogs.showConfirmDialog('<?= 
                        _('Soll die Bedingung wirklich gelöscht werden?') ?>', 
                        'javascript:STUDIP.Admission.removeConditionField($(this.parent()))')"
                        class="conditionfield_delete">
                        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
                </div>
            <?php } ?>
        </div>
        <?php } ?>
        <br/><br/>
        <a href="<?= URLHelper::getURL('dispatch.php/conditions/condition/configure') ?>" onclick="return STUDIP.Conditions.configureCondition('<?= URLHelper::getURL('dispatch.php/conditions/condition/configure') ?>')">
            <?= Assets::img('icons/16/blue/add.png', array(
                'alt' => _('Bedingung hinzufügen'),
                'title' => _('Bedingung hinzufügen'))) ?><?= _('Bedingung hinzufügen') ?></a>
    </div>
</div>