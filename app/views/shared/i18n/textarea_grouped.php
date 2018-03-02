<? if ($enabled) : ?>
    <div class="i18n_group">
<? endif; ?>
<?
$perm = $attributes['perm'];
unset($attributes['perm']);
?>
<? foreach ($languages as $locale => $lang) : ?>
    <? if ($locale === $base_lang) : ?>
        <?
        $attr = [
                'name' => $name,
                'id' => $attributes['input_attributes']['id']
            ];
        $text = $value->original();
        ?>
    <? else : ?>
        <?
        $attr = [
                'name' => $name . '_i18n[' . $locale . ']',
                'id' => null
            ];
        $text = $value->translation($locale);
        ?>
    <? endif; ?>
        <div class="i18n" data-lang="<?= $lang['name'] ?>" data-icon="url(<?= Assets::image_path('languages/' . $lang['picture']); ?>)">
    <? $attr = array_merge($attr, (array) $attributes['input_attributes']); ?>
    <? if (isset($attr['required']) && empty($attr['value']) && $locale !== $base_lang) : ?>
        <? unset($attr['required']); ?>
    <? endif; ?>
            <textarea <?= $perm->haveFieldPerm($attr['name'], MvvPerm::PERM_WRITE) ? '' : 'readonly'; ?><?
    foreach ($attr as $key => $val) :
        if (isset($val)) :
            ?> <?= ($val === true ? $key : $key . '="' . htmlReady($val) . '"') ?> <?
        endif;
    endforeach;
    ?>><?= ($wysiwyg ? wysiwygReady($text) : htmlReady($text)); ?></textarea></div>
<? endforeach; ?>
<? if ($enabled) : ?>
    </div>
<? endif; ?>
