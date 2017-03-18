<div id="file_edit_window">
    <?= $this->render_partial('file/_file_aside.php',
        [
            'file_ref' => $file_ref
        ])  ?>
    <div id="file_management_forms">
        <form
            method="post"
            data-dialog
            class="default"
            action="<?= $controller->link_for('/edit/' . $file_ref->id) ?>">

            <?= CSRFProtection::tokenTag() ?>
            <fieldset>
                <legend><?= _("Datei bearbeiten") ?></legend>
                <label>
                    <?= _('Name') ?>
                    <input id="edit_file_name" type="text" name="name" value="<?= htmlReady($file_ref->name) ?>">
                </label>
                <label>
                    <?= _('Beschreibung') ?>
                    <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($file_ref->description); ?></textarea>
                </label>

                <?= $this->render_partial(
                    'file/_terms_of_use_select.php',
                    ['content_terms_of_use_entries' => $content_terms_of_use_entries]
                    ) ?>

            </fieldset>

            <div data-dialog-button>
                <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
//On focus on the edit file name input field, the whole file name but the
//extension shall be selected. This can't be done with jQuery directly!

var file_name_edit_input = document.getElementById('edit_file_name');
if(file_name_edit_input) {
    file_name_edit_input.addEventListener('focus', function() {
        //select the whole file name, but the file extension
        var file_name_edit_input = document.getElementById('edit_file_name');
        var text = file_name_edit_input.value;

        //get start position of extension:
        var extension_start_pos = text.lastIndexOf('.');

        console.log('text = ' + text + ', last index of . = ' + extension_start_pos);

        file_name_edit_input.selectionStart = 0;
        file_name_edit_input.selectionEnd = extension_start_pos;
    });
}
</script>
