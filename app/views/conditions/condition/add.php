<div class="condition" id="<?= $condition->getId() ?>">
    <?= nl2br($condition->toString()) ?>
    <input type="hidden" name="conditions[]" value="<?= htmlentities(serialize($condition)) ?>"/>
    <a href="#" onclick="return STUDIP.Conditions.removeConditionField($(this).parent())" class="conditionfield_delete">
        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
</div>