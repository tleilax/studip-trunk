<? if ($content_terms_of_use_entries): ?>
    <fieldset class="select_terms_of_use">
        <? foreach ($content_terms_of_use_entries as $content_terms_of_use_entry) : ?>
            <input type="radio" name="content_terms_of_use_id"
                   value="<?= htmlReady($content_terms_of_use_entry->id) ?>"
                   id="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>"
                <?= ($content_terms_of_use_entry->id == $selected_terms_of_use_id) ? 'checked="checked"' : '' ?> >
            <label for="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>">
                <div class="icon">
                    <? if ($content_terms_of_use_entry['icon']) : ?>
                        <? if (filter_var($content_terms_of_use_entry['icon'], FILTER_VALIDATE_URL)) : ?>
                            <img src="<?= htmlReady($content_terms_of_use_entry['icon']) ?>" width="32px" height="32px">
                        <? else : ?>
                            <?= Icon::create($content_terms_of_use_entry['icon'], 'clickable')->asImg('32px') ?>
                        <? endif ?>
                    <? endif ?>
                </div>
                <div class="text">
                    <?= htmlReady($content_terms_of_use_entry->name) ?>
                </div>
                <?= Icon::create("arr_1down", "clickable")->asImg(24, array('class' => "arrow")) ?>
            </label>
            <div class="terms_of_use_description">
                <div class="description">
                <?= formatReady($content_terms_of_use_entry->description ?: _("Keine Beschreibung")) ?>
                </div>
            </div>
        <? endforeach ?>
    </fieldset>

<? endif ?>
