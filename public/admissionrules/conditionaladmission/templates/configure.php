<h3><?= $rule->getName() ?></h3>
<?= $tpl ?>
<div class="table_row_<?= TextHelper::cycle('even', 'odd'); ?> admissionrule_data">
    <div class="admissionrule_label_fullsize">
        <?= _('Anmeldebedingungen') ?>:
    </div>
    <div class="admissionrule_value" id="conditions">
        <?php if (!$rule->getConditions()) { ?>
        <span id="noconditions">
            <i><?= _('Sie haben noch keine Bedingungen festgelegt.'); ?></i>
        </span>
        <?php } else { ?>
        <div id="conditionlist">
            <?php foreach ($rule->getConditions() as $condition) { ?>
                <div class="condition" id="condition_<?= $condition->getId() ?>">
                    <?= $condition->toString() ?>
                    <a href="#" onclick="return STUDIP.Conditions.removeConditionField($(this).parent())"
                        class="conditionfield_delete">
                        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
                    <input type="hidden" name="conditions[]" value="<?= htmlentities(serialize($condition), ENT_COMPAT | ENT_HTML401, 'iso-8859-1') ?>"/>
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