<?php if ($className) { ?>
<select name="compare_operator[]" size="1" class="conditionfield_compare_op">
    <?php foreach ($field->getValidCompareOperators() as $op) { ?>
    <option value="<?= $op ?>"><?= htmlReady($op) ?></option>
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
        <option value="">-- <?= $this->via_ajax ? utf8_encode(_('bitte auswählen')) : _('bitte auswählen') ?> --</option>
        <?php foreach ($conditionFields as $className => $displayName) { ?>
        <option value="<?= $className ?>"><?= htmlReady($displayName) ?></option>
        <?php } ?>
    </select>
    <a href="#" onclick="return STUDIP.Conditions.removeConditionField($(this).parent())" class="conditionfield_delete">
        <?= Assets::img('icons/16/blue/trash.png'); ?></a>
</div>
<?php } ?>