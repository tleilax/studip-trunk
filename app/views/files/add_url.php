<form action="<?= $controller->link_for('/add_url/' . $top_folder->id) ?>" method="post" data-dialog class="default">
<?=CSRFProtection::tokenTag()?>
    <fieldset>
        <legend><?= _("Datei aus dem Internet verlinken") ?></legend>
        <label>
            <?= _("Webadresse") ?>
            <input type="text" name="url" placeholder="https://..." required value="<?=htmlReady(Request::get('url'))?>">
        </label>
        <label>
            <?= _("Gewünschter Dateiname") ?>
            <input type="text" name="name" placeholder="Beispielname.pdf" value="<?=htmlReady(Request::get('name'))?>">
        </label>
        <label>
            <?= _("Zugriffsart") ?>
        </label>
            <label>
                <input type="radio" name="access_type" value="redirect" checked>
                <?= _("Weiterleitung")?>
            </label>
            <label>
                <input type="radio" name="access_type" value="proxy" <?=Request::get('access_type') == 'proxy' ? 'checked' : ''?>>
                <?= _("Durchleitung")?>
            </label>
    </fieldset>
    <div data-dialog-button>
        <?= Studip\LinkButton::create(_("Zurück"), $controller->url_for('/add_files_window/' . Request::option("to_folder_id"), $options), array('data-dialog' => 1)) ?>
        <?= \Studip\Button::create(_("Speichern"), 'store') ?>
    </div>
</form>