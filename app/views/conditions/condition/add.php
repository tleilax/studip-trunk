<div class="condition" id="<?= $condition->getId() ?>">
    <?= $via_ajax ? utf8_encode($condition->toString()) : $condition->toString() ?>
    <input type="hidden" name="conditions[]" value="<?= $via_ajax? utf8_encode(htmlentities(serialize($condition))) : htmlentities(serialize($condition)) ?>"/>
    <a href="#" onclick="return STUDIP.Conditions.removeConditionField($(this).parent())"
            class="conditionfield_delete">
        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
</div>