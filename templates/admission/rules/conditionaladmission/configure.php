<h3><?= $rule->getName() ?></h3>
<?php echo $this->render_partial('admission/rules/configure.php'); ?>
<div style="width: 95%; padding: 5px;" class="table_row_<?= TextHelper::cycle('even', 'odd'); ?>">
    <div align="right" style="display: inline-block; vertical-align: top; font-weight: bold;">
        <label for="start"><?= _('Anmeldebedingungen') ?>:</label>
    </div>
    <div style="display: inline-block; vertical-align: top;" id="conditions">
        <?php if (!$rule->getConditions()) { ?>
        <span id="noconditions">
            <i><?= _('Sie haben noch keine Bedingungen festgelegt.'); ?></i>
        </span>
        <?php } else { ?>
            <?php foreach ($rule->getConditions() as $condition) { ?>
                <div class="condition" id="condition_<?= $condition->getId() ?>">
                    <?= $condition->toString() ?>
                    <a href="#" onclick="return STUDIP.Conditions.removeConditionField($(this).parent())" class="conditionfield_delete">
                        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
                </div>
            <?php } ?>
        <?php } ?>
        <br/><br/>
        <a href="<?= URLHelper::getURL('dispatch.php/conditions/condition/configure') ?>" onclick="return STUDIP.Conditions.configureCondition('<?= URLHelper::getURL('dispatch.php/conditions/condition/configure') ?>')">
            <?= Assets::img('icons/16/blue/plus.png', array(
                'alt' => _('Bedingung hinzufügen'),
                'title' => _('Bedingung hinzufügen'))) ?><?= _('Bedingung hinzufügen') ?></a>
    </div>
</div>