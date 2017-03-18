<? if ($content_terms_of_use_entries): ?>
<?= _('Nutzungsbedingungen auswählen') ?>

<div class="file_select_possibilities" id="file_license_chooser_1">

    <? foreach ($content_terms_of_use_entries as $content_terms_of_use_entry) : ?>
        <input type="radio" name="content_terms_of_use_id"
            value="<?= htmlReady($content_terms_of_use_entry->id) ?>"
            id="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>"
            <?= ($content_terms_of_use_entry->id == $file_ref->content_terms_of_use_id) ? 'checked="checked"' : '' ?> >
        <label for="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>">
            <? if ($content_terms_of_use_entry['icon']) : ?>
                <? if (filter_var($content_terms_of_use_entry['icon'], FILTER_VALIDATE_URL)) : ?>
                    <img src="<?= htmlReady($content_terms_of_use_entry['icon']) ?>">
                <? else : ?>
                    <?= Icon::create($content_terms_of_use_entry['icon'], "clickable")->asImg(50) ?>
                <? endif ?>
            <? endif ?>
            <?= htmlReady($content_terms_of_use_entry['name']) ?>
        </label>

    <? endforeach ?>
</div>
<? endif ?>