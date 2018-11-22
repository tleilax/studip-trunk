<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($tooltip): ?>
        <?= tooltipIcon($tooltip, $important ?: false) ?>
    <? endif; ?>

    <input type="url" name="<?= $name ?>[<?= $model->id ?>]"
           value="<?= htmlReady($value) ?>" id="<?= $name ?>_<?= $model->id ?>"
           size="30" placeholder="http://"
           <? if ($model->is_required) echo 'required'; ?>>
</label>
