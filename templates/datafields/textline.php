<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($tooltip): ?>
        <?= tooltipIcon($tooltip, $important ?: false) ?>
    <? endif; ?>

    <input type="text" name="<?= $name ?>[<?= $model->id ?>]"
           value="<?= htmlReady($value) ?>" id="<?= $name ?>_<?= $model->id ?>"
           <? if ($model->is_required) echo 'required'; ?>>
</label>
