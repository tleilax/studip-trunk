<label>
    <span class="datafield_title <?= $model->is_required ? 'required' : '' ?>">
        <?= htmlReady($model->name) ?>
    </span>

    <? if ($tooltip): ?>
        <?= tooltipIcon($tooltip, $important ?: false) ?>
    <? endif; ?>

    <?= I18N::textarea($name, $value, ['required' => (bool) $model->is_required, 'locale_names' => $locale_names]) ?>
</label>
