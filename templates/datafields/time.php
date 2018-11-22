<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($tooltip): ?>
        <?= tooltipIcon($tooltip, $important ?: false) ?>
    <? endif; ?>
</label>

<div style="white-space: nowrap;">
    <input type="text" name="<?= $name ?>[<?= $model->id ?>][]"
           value="<?= $values[0] ?>" title="<?= _('Stunden') ?>"
           maxlength="2" class="size-s no-hint"
           <? if ($model->is_required) echo 'required'; ?>>
    :
    <input type="text" name="<?= $name ?>[<?= $model->id ?>][]"
           value="<?= $values[1] ?>" title="<?= _('Minuten') ?>"
           maxlength="2" class="size-s no-hint"
           <? if ($model->is_required) echo 'required'; ?>>
</div>
