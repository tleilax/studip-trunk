<form method="post" class="default"
      action="<?= $controller->url_for('/move/' . $folder_id) ?>"
      data-dialog="reload-on-close;size=auto">
    <input type="hidden" name="form_sent" value="1">
    <p><?= _('Bitte den Zielordner auswählen') . ':' ?></p>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Verschieben')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen')) ?>
    </div>
</form>
