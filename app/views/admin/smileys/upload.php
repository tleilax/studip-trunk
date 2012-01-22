<? use Studip\Button, Studip\LinkButton; ?>

<form action="<?= $controller->url_for('admin/smileys/upload', $view) ?>"
      method="post" enctype="multipart/form-data">
    <?= CSRFProtection::tokenTag() ?>

    <table align="center" cellpadding="2" cellspacing="0">
        <thead>
            <tr>
                <th colspan="2"><b><?= _('Neues Smiley hochladen') ?></b></th>
            </tr>
        </thead>
        <tbody>
            <tr class="steelgraulight">
                <td>
                    <label for="replace"><?= _('existierende Datei �berschreiben') ?></label>
                </td>
                <td>
                    <input type="checkbox" id="replace" name="replace" value="1">
                </td>
            </tr>
            <tr class="steel1">
                <td>
                    <label for="file"><?= _('Bilddatei ausw�hlen') ?></label>
                </td>
                <td>
                    <input type="file" id="file" name="smiley_file" required>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" align="center">
                    <?= Button::createAccept('hochladen', 'upload') ?>
                    <?= LinkButton::createCancel('abbrechen', $controller->url_for('admin/smileys?view=' . $view))?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
