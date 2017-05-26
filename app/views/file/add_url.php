<form action="<?= $controller->link_for('/add_url/' . $top_folder->id) ?>" method="post" data-dialog class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Datei aus dem Internet verlinken') ?></legend>
        <label>
            <?= _('Webadresse') ?>
            <input type="text" name="url" placeholder="https://..." required
                   value="<?= htmlReady(Request::get('url')) ?>">
        </label>
        <label>
            <?= _('Gew�nschter Dateiname') ?>
            <input type="text" name="name" placeholder="<?= _('Beispielname.pdf') ?>"
                   value="<?= htmlReady(Request::get('name')) ?>">
        </label>

        <label>
            <?= _('Zugriffsart') ?>
        </label>
        <label>
            <input type="radio" name="access_type" value="redirect"
                   <? if (Request::option('access_type') !== 'proxy') echo 'checked'; ?>>
            <?= _('Direktlink')?>
        </label>
        <label>
            <input type="radio" name="access_type" value="proxy"
                    <? if (Request::option('access_type') === 'proxy') echo 'checked'; ?>>
            <?= _('Link �ber Proxy')?>
        </label>
    </fieldset>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(
            _('Zur�ck'),
            $controller->url_for('/add_files_window/' . Request::option('to_folder_id'), $options),
            ['data-dialog' => '']
        ) ?>
    </div>
</form>
