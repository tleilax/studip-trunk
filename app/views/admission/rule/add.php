<div class="rule" id="rule_<?= $rule->getId() ?>">
    <a href="#" onclick="return STUDIP.Admission.toggleRuleDetails('rule_arrow_<?= $rule->getId() ?>', 'rule_details_<?= $rule->getId() ?>')">
        <?= Assets::img('icons/16/blue/arr_1right.png', 
            array('id' => 'rule_arrow_'.$rule->getId(), 
            'align' => 'top', 'rel' => Assets::image_path('icons/16/blue/arr_1down.png'))) ?>
        <?= $rule->getName() ?>
    </a>
    <div id="rule_details_<?= $rule->getId() ?>" style="display:none">
        <?= nl2br($rule->toString()) ?>
    </div>
    <input type="hidden" name="rules[]" value="<?= htmlentities(serialize($rule)) ?>"/>
    <a href="#" onclick="return STUDIP.Conditions.removeRule($(this).parent())" class="rule_delete">
        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
</div>