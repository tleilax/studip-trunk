<form method="post" class="default"
      action="<?= $controller->url_for('/edit/' . $folder_id) ?>"
      <? if(Request::isDialog()): ?>
      data-dialog="reload-on-close;size=auto"
      <? endif ?>
      >
    <?= $this->render_partial('file/new_edit_folder_form.php',
        [
            'name' => $name,
            'description' => $description
        ]) ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('/goto/' . $parent_folder_id) ?>
    </div>
</form>
