<form method="post" class="default"
      <? if(Request::isDialog()): ?>
      data-dialog="size=auto<?= (Request::get('js')) ? '' : ';reload-on-close' ?>"
      <? endif ?>
      action="<?= $controller->url_for('/new') ?>"
      id="new_folder_form">
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
