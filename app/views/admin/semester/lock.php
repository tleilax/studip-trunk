<h1><?= _('Zusätzliche optionale Sperrebene'); ?> </h1>

<label><?= _('Für alle Veranstaltungen') ?>
    <select name="lock_sem_all" style="max-width: 200px">
        <? for ($i = 0; $i < count($all_lock_rules); $i++) : ?>
            <option value="<?= $all_lock_rules[$i]["lock_id"] ?>"
                <?= ($all_lock_rules[$i]["lock_id"] == $values['lock_rule']) ? 'selected' : '' ?>>
                <?= htmlReady($all_lock_rules[$i]["name"]) ?>
            </option>
        <? endfor ?>
    </select>
</label>

<div data-dialog-button>
    <?= \Studip\Button::createAccept(_('Sperren'), 'confirm_lock'); ?>
</div>