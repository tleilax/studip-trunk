<div id="file_edit_window">
    <?= $this->render_partial('file/_folder_aside.php',
        [
            'folder' => $folder
        ])  ?>
    <div id="file_management_forms">
        <form method="post" class="default"
            action="<?= $controller->url_for('/edit_folder/' . $folder_id) ?>"
            <? if(Request::isDialog()): ?>
            data-dialog="reload-on-close"
            <? endif ?>
            >
            <?= CSRFProtection::tokenTag() ?>
            <?= $this->render_partial('file/new_edit_folder_form.php',
                [
                    'name' => $name,
                    'description' => $description
                ]) ?>
            <div data-dialog-button>
                <?= Studip\Button::createAccept(_('Speichern'), 'edit') ?>
                <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('/goto/' . $parent_folder_id)) ?>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
//On focus the whole folder name shall be selected.
//The input field with id edit_folder_name is in the view new_edit_folder_form!
var folder_name_edit_input = document.getElementById('edit_folder_name');
if(folder_name_edit_input) {
    folder_name_edit_input.select();
}
</script>
