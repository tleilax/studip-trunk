<form action="<?= $controller->link_for('/add_url/' . $top_folder->id) ?>" method="post" data-dialog class="default">

    <fieldset>
        <legend><?= _("Datei aus dem Internet verlinken") ?></legend>
        <label>
            <?= _("Gewünschter Dateiname") ?>
            <input type="text" name="name" placeholder="Beispielname.pdf">
        </label>
        <label>
            <?= _("Webadresse") ?>
            <input type="text" name="url" placeholder="https://..." required>
        </label>
    </fieldset>
    <div data-dialog-button>
        <?= Studip\LinkButton::create(_("Zurück"), $controller->url_for('/add_files_window/' . Request::get("to_folder_id"), $options), array('data-dialog' => 1)) ?>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>
</form>