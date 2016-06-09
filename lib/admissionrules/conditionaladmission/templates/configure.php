<?php
use Studip\Button, Studip\LinkButton;
?>
<h3><?= $rule->getName() ?></h3>
<?= $tpl ?>
<br/>
<label for="conditionlist" class="caption">
    <?= _('Anmeldebedingungen') ?>:
</label>
<br/>
<a href="<?= URLHelper::getURL('dispatch.php/userfilter/filter/configure/condadmission_conditions') ?>" onclick="return STUDIP.UserFilter.configureCondition('condition', '<?= URLHelper::getURL('dispatch.php/userfilter/filter/configure/condadmission_conditions') ?>')">
    <?= Assets::img('icons/16/blue/add.png', array(
        'alt' => _('Bedingung hinzufügen'),
        'title' => _('Bedingung hinzufügen'))) ?> <?= _('Bedingung hinzufügen') ?></a>
<br/>
<div id="condadmission_conditions">
    <span class="nofilter" style="<?=(!$rule->getUngroupedConditions() && !$rule->getConditiongroups()) ? '' : 'display: none'?>">
        <i><?= _('Sie haben noch keine Bedingungen festgelegt.'); ?></i>
    </span>
    <div class="userfilter" style="<?=(!$rule->getUngroupedConditions() || !$rule->getConditiongroups()) ? '' : 'display: none'?>">
        <? if ($rule->conditiongroupsAllowed()) { ?>
        <div class="grouped_conditions" id="new_conditiongroup" style="margin-bottom: 5px; display: none">
            <div class="condition_list">
                <?=_('Kontingent:')?> <input type="text" name="quota" size="5"> <?=_('Prozent')?>
            </div>
            <?= Button::create(_('Kontingent aufheben'), 'ungroup_conditions', array('class' => 'ungroup_conditions', 'onclick' => 'return STUDIP.UserFilter.ungroupConditions(this)')) ?>
        </div>
        <? } else { 
            $rule->removeConditiongroups(); ?>
            <div id="no_conditiongroups"></div>
        <? } ?>
        <div class="ungrouped_conditions">
            <div class="condition_list">
            <?php foreach ($rule->getUngroupedConditions() as $condition) {
                $condition->show_user_count = true; ?>
                <div class="condition" id="condition_<?= $condition->getId() ?>">
                    <? if ($rule->conditiongroupsAllowed()) { ?>
                        <input type="checkbox" name="conditions_checkbox[]" value="<?= htmlReady(serialize($condition)) ?>"/>
                    <? } ?>
                    <?= $condition->toString() ?>
                    <a href="#" onclick="return STUDIP.UserFilter.removeConditionField($(this).parent())"
                        class="conditionfield_delete">
                    <?= Assets::img('icons/16/blue/trash.png'); ?></a>
                    <input type="hidden" name="conditions[]" value="<?= htmlReady(serialize($condition)) ?>"/>
                    <input type="hidden" name="conditiongroup_<?=$condition->getId()?>" value=""/>
                </div>
            <?php } ?>
            </div>
        </div>
        <? if ($rule->conditiongroupsAllowed()) { ?>
            <?= Button::create(_('Kontingent erstellen'), 'group_conditions', array('class' => 'group_conditions', 'onclick' => 'return STUDIP.UserFilter.groupConditions()', 'style' => (count($rule->getUngroupedConditions()) > 1 ? '' : 'display: none'))) ?>
            <?php foreach ($rule->getConditiongroups() as $conditiongroup_id => $conditiongroup) { ?>
            <div class="grouped_conditions" id="conditiongroup_<?=$conditiongroup_id?>" style="margin-bottom: 5px">
                <div class="condition_list">
                    <?=_('Kontingent:')?> <input type="text" name="quota_<?=$conditiongroup_id?>" value="<?=$rule->getQuota($conditiongroup_id)?>" size="5"> <?=_('Prozent')?>
                    <?php foreach ($conditiongroup as $condition) {
                        $condition->show_user_count = true; ?>
                        <div class="condition" id="condition_<?= $condition->getId() ?>">
                            <input type="checkbox" name="conditions_checkbox[]" value="<?= htmlReady(serialize($condition)) ?>"/ style="display: none">
                            <?= $condition->toString() ?>
                            <a href="#" onclick="return STUDIP.UserFilter.removeConditionField($(this).parent())"
                                        class="conditionfield_delete">
                            <?= Assets::img('icons/16/blue/trash.png'); ?></a>
                            <input type="hidden" name="conditions[]" value="<?= htmlReady(serialize($condition)) ?>"/>
                            <input type="hidden" name="conditiongroup_<?=$condition->getId()?>" value="<?= $conditiongroup_id ?>"/>
                        </div>
                    <?php } ?>
                </div>
                <?= Button::create(_('Kontingent aufheben'), 'ungroup_conditions', array('class' => 'ungroup_conditions', 'onclick' => 'return STUDIP.UserFilter.ungroupConditions(this)')) ?>
            </div>
            <?php } ?>
        <? } ?>
    </div>
</div>
<br/>