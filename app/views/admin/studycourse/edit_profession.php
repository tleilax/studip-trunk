<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<?= (isset($flash['error']))?MessageBox::error($flash['error'], $flash['error_detail']):'' ?>
<form action="<?= $controller->url_for('admin/studycourse/edit_profession/'.$edit['studiengang_id']) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <tr class="table_row_odd">
            <td><?= _("Name des Studienfaches:") ?> </td>
            <td><input type="text" name="professionname" size="60" maxlength="254" value="<?= htmlReady($edit['name']) ?>"></td>
        </tr>
        <tr class="table_row_even">
            <td><?= _("Beschreibung:") ?> </td>
            <td><textarea cols="57" rows="5" name="description"><?= htmlReady($edit['beschreibung']) ?></textarea></td>
        </tr>
        <tr class="table_footer">
            <td></td>
            <td>
                 <?= Button::createAccept(_('�bernehmen'),'uebernehmen', array('title' => _('�nderungen �bernehmen')))?>
                 <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/studycourse/profession'), array('title' => _('Zur�ck zur �bersicht')))?>
            </td>
        </tr>
    </table>
</form>