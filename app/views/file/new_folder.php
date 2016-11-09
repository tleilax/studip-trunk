<form method="post" class="default"
      action="<?= $controller->url_for('/new') ?>"
      <? if(Request::isDialog()): ?>
      data-dialog="reload-on-close;size=auto"
      <? endif ?>
      >
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="parent_folder_id" value="<?= $parent_folder_id ?>">
    <?= $this->render_partial('file/new_edit_folder_form.php',
        [ 
            'name' => $name,
            'description' => $description,
            'folder_types' => $folder_types,
            'new_folder_form' => true
        ]) ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Erstellen')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('/goto/' . $parent_folder_id)) ?>
    </div>    
</form>
