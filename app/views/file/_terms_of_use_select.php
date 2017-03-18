<? if ($content_terms_of_use_entries): ?>
<fieldset>
    <legend><?= _('Nutzungsbedingungen auswählen') ?></legend>

    <div class="file_select_possibilities content_terms_of_use_icons" id="file_license_chooser_1">
        <? foreach ($content_terms_of_use_entries as $content_terms_of_use_entry) : ?>
            <input type="radio" name="content_terms_of_use_id"
                value="<?= htmlReady($content_terms_of_use_entry->id) ?>"
                id="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>"
                <?= ($content_terms_of_use_entry->id == $selected_terms_of_use_id) ? 'checked="checked"' : '' ?> >
            <label class="content_terms_of_use_entry" for="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>">
                <? if ($content_terms_of_use_entry['icon']) : ?>
                    <? if (filter_var($content_terms_of_use_entry['icon'], FILTER_VALIDATE_URL)) : ?>
                        <img src="<?= htmlReady($content_terms_of_use_entry['icon']) ?>" width="32px" height="32px">
                    <? else : ?>
                        <?= Icon::create($content_terms_of_use_entry['icon'], 'clickable')->asImg('32px') ?>
                    <? endif ?>
                <? endif ?>
            </label>
        <? endforeach ?>
    </div>
    <div class="terms_of_use_description_container">
    <? foreach($content_terms_of_use_entries as $content_terms_of_use_entry): ?>
    <section class="terms_of_use_description <?= $content_terms_of_use_entry->id != $selected_terms_of_use_id ? 'invisible' : '' ?>" id="terms_of_use_description-<?= htmlReady($content_terms_of_use_entry->id) ?>">
        <h3><?= htmlReady($content_terms_of_use_entry->name) ?></h3>
        <p><?= htmlReady($content_terms_of_use_entry->description) ?></p>
    </section>
    <? endforeach ?>
    </div>
</fieldset>
<? endif ?>
