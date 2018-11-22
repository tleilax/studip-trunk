<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($tooltip): ?>
        <?= tooltipIcon($tooltip, $important ?: false) ?>
    <? endif; ?>

    <textarea name="<?= $name ?>[<?= $model->id ?>]"
              id="<?= $name ?>_<?= $model->id ?>"
              rows="6"
              <? if ($model->is_required) echo 'required'; ?>
    ><?= htmlReady($value) ?></textarea>
</label>
