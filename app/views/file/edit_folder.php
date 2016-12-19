<div id="file_edit_window">
    <?= $this->render_partial('file/_folder_aside.php') ?>
    <div id="file_management_forms">
        <form method="post" class="default"
            action="<?= $controller->url_for('/edit_folder/' . $folder->id) ?>"
            data-dialog="reload-on-close">
            <?= CSRFProtection::tokenTag() ?>
            <?= $this->render_partial('file/new_edit_folder_form.php') ?>
            <div data-dialog-button>
                <?= Studip\Button::createAccept(_('Speichern'), 'edit') ?>
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
