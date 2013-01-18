<div class="rule" id="rule_<?= $rule->getId() ?>">
    <?= $rule->toString() ?>
    <input type="hidden" name="rules[]" value="<?= htmlentities(serialize($rule)) ?>"/>
    <a href="#" onclick="return STUDIP.Conditions.removeRule($(this).parent())" class="rule_delete">
        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
</div>