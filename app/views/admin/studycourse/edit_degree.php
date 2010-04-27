<?= (isset($flash['error']))?MessageBox::error($flash['error'], $flash['error_detail']):'' ?>
<form action="<?= $controller->url_for('admin/studycourse/edit_degree/'.$edit['abschluss_id']) ?>" method="post">
    <table class="default">
        <tr class="steelgraulight">
            <td><?= _("Name des Studienabschlusses:") ?> </td>
            <td><input type="text" name="degreename" size="60" maxlength="254" value="<?= htmlReady($edit['name']) ?>"></td>
        </tr>
        <tr class="steel1">
            <td><?= _("Beschreibung:") ?> </td>
            <td><textarea cols="57" rows="5" name="description"><?= htmlReady($edit['beschreibung']) ?></textarea></td>
        </tr>
        <tr class="steel2">
            <td></td>
            <td>
                <?= makeButton('uebernehmen2', 'input', _('�nderungen �bernehmen'), 'uebernehmen') ?>
                <a href="<?=$controller->url_for('admin/studycourse/degree')?>"><?= makebutton('abbrechen', 'img', _('Zur�ck zur �bersicht')) ?></a>
            </td>
        </tr>
    </table>
</form>