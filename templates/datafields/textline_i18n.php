<? if ($enabled) : ?>
    <div class="i18n_group">
<? endif; ?>
<? foreach ($languages as $locale => $lang) : ?>
    <? if ($locale === $base_lang) : ?>
        <? $attr = [
                'name' => sprintf('%s[%s][base]', $name, $attributes['datafield_id']),
                'value' => $value->original(),
                'id' => $attributes['input_attributes']['id']
            ]; ?>
    <? else : ?>
        <? $attr = [
                'name' => sprintf('%s[%s][%s]', $name, $attributes['datafield_id'], $locale),
                'value' => $value->translation($locale),
                'id' => null
            ]; 
            unset($attributes['input_attributes']['id']);
        ?>
    <? endif; ?>
        <div class="i18n" data-lang="<?= $lang['name'] ?>" data-icon="url(<?= Assets::image_path('languages/' . $lang['picture']); ?>)">
    <? $attr = array_merge($attr, $attributes['input_attributes']); ?>
    <? if (isset($attr['required']) && empty($attr['value']) && $locale !== $base_lang) : ?>
        <? unset($attr['required']); ?>
    <? endif; ?>
            <input type="text"<?
    foreach ($attr as $key => $val) :
        if (isset($val)) :
            ?> <?= ($val === true ? $key : $key . '="' . htmlReady($val) . '"') ?> <?
        endif;
    endforeach;
            ?>></div>
<? endforeach; ?>
<? if ($enabled) : ?>
    </div>
<? endif; ?>