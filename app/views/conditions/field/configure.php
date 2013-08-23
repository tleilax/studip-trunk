<?php if ($className) { ?>
<select name="compare_operator[]" size="1" class="conditionfield_compare_op">
    <?php foreach ($field->getValidCompareOperators() as $op => $text) { ?>
    <option value="<?= $op ?>"><?= htmlReady($via_ajax ? studip_utf8encode($text) : $text) ?></option>
    <?php } ?>
</select>
<select name="value[]" size="1" class="conditionfield_value">
    <?php foreach ($field->getValidValues() as $id => $name) { ?>
    <option value="<?= $id ?>"><?= htmlReady($name) ?></option>
    <?php } ?>
</select>
<?php } else { ?>
<div class="conditionfield">
    <select name="field[]" class="conditionfield_class" size="1" onchange="STUDIP.Conditions.getConditionFieldConfiguration(this, '<?= $controller->url_for('conditions/field/configure') ?>')">
        <option value="">-- <?= $via_ajax ? studip_utf8encode(_('bitte auswählen')) : _('bitte auswählen') ?> --</option>
        <?php foreach ($conditionFields as $className => $displayName) { ?>
        <option value="<?= $className ?>"><?= htmlReady($displayName) ?></option>
        <?php } ?>
    </select>
    <a href="#" onclick="return STUDIP.Conditions.removeConditionField($(this).parent())"
		class="conditionfield_delete">
        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
</div>
<?php } ?>