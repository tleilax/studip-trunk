<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<?= (isset($flash['error']))?MessageBox::error($flash['error'], $flash['error_detail']):'' ?>
<form action="<?= $controller->url_for('admin/studycourse/edit_degree/'.$edit['abschluss_id']) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <tr class="table_row_odd">
            <td><?= _("Name des Studienabschlusses:") ?> </td>
            <td><input type="text" name="degreename" size="60" maxlength="254" value="<?= htmlReady($edit['name']) ?>"></td>
        </tr>
        <tr class="table_row_even">
            <td><?= _("Beschreibung:") ?> </td>
            <td><textarea cols="57" rows="5" name="description"><?= htmlReady($edit['beschreibung']) ?></textarea></td>
        </tr>
        <tr class="table_footer">
            <td></td>
            <td>
                <?= Button::createAccept(_('�bernehmen'),'uebernehmen', array('title' => _('�nderungen �bernehmen')))?>
                <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/studycourse/degree'), array('title' => _('Zur�ck zur �bersicht')))?>
            </td>
        </tr>
    </table>
</form>