<form class="datafield_param" action="<?= $controller->url_for('admin/datafields/edit/'.$datafield_id) ?>" method="post" <?= $hidden ? 'style="display:none;"' : '' ?>>
   <textarea name="typeparam" cols="15" rows="5" wrap="off"><?= $typeparam ?></textarea>
   <input type="hidden" name="datafield_id" value="<?= $datafield_id ?>" /><br>
   <input type="image" name="save" src="<?= Assets::image_path('icons/16/blue/accept.png') ?>" title="<?= _('�nderungen speichern') ?>" />
   <input type="image" name="preview" src="<?= Assets::image_path('icons/16/blue/question-circle.png') ?>" title="<?= _('Vorschau') ?>" />
    <a class="cancel" href="<?= $controller->url_for('admin/datafields') ?>">
        <?= Assets::img('icons/16/blue/decline.png', array('title' => _('Bearbeitung abbrechen'))) ?>
    </a>
</form>
