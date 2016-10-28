<form method="post" class="default"
      action="<?= $controller->url_for('/new') ?>"
      data-dialog="size=auto; reload-on-close">
    <input type="hidden" name="parent_folder_id" value="<?= $parent_folder_id ?>">
    <?= $this->render_partial('file/new_edit_folder_form.php',
        [ 
            'name' => $name,
            'description' => $description
        ]) ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Erstellen')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen')) ?>
    </div>    
</form>
