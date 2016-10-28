<form method="post" class="default"
      action="<?= $controller->url_for('/edit/' . $folder_id) ?>"
      data-dialog="size=auto; reload-on-close">
    <?= $this->render_partial('file/new_edit_folder_form.php',
        [ 
            'name' => $name,
            'description' => $description
        ]) ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen')) ?>
    </div>
</form>
