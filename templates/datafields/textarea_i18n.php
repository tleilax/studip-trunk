<? if ($enabled) : ?>
    <div class="i18n_group">
<? endif; ?>
<? foreach ($languages as $locale => $lang) : ?>
    <? if ($locale === $base_lang) : ?>
        <? $attr = [
                'name' => sprintf('%s[%s][base]', $name, $attributes['datafield_id']),
                'id' => $attributes['input_attributes']['id']
            ];
            $text = $value->original();
        ?>
    <? else : ?>
        <? $attr = [
                'name' => sprintf('%s[%s][%s]', $name, $attributes['datafield_id'], $locale),
                'id' => null
            ];
            unset($attributes['input_attributes']['id']);
            $text = $value->translation($locale);
        ?>
    <? endif; ?>
        <div class="i18n" data-lang="<?= $lang['name'] ?>" data-icon="url(<?= Assets::image_path('languages/' . $lang['picture']); ?>)">
    <? $attr = array_merge($attr, $attributes['input_attributes']); ?>
    <? if (isset($attr['required']) && empty($attr['value']) && $locale !== $base_lang) : ?>
        <? unset($attr['required']); ?>
    <? endif; ?>
            <textarea<?
    foreach ($attr as $key => $val) :
        if (isset($val)) :
            ?> <?= ($val === true ? $key : $key . '="' . htmlReady($val) . '"') ?> <?
        endif;
    endforeach;
    ?>><?= ($attr['wysiwyg'] ? wysiwygReady($text) : htmlReady($text)); ?></textarea></div>
<? endforeach; ?>
<? if ($enabled) : ?>
    </div>
<? endif; ?>