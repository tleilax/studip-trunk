<?
# Lifter010: TODO - Quicksearches still lack a label

use Studip\Button, Studip\LinkButton;

?>
<form action="<?= $controller->url_for('admin/user/migrate') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= _('Benutzermigration') ?>
        </caption>
        <colgroup>
            <col width="250px">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <td>
                    <?= _('Alter Benutzer:') ?>
                </td>
                <td>
                <? if ($user !== null): ?>
                    <?= QuickSearch::get('old_id', new StandardSearch('user_id'))
                                   ->defaultValue($user->id, $user->getFullname() . ' (' . $user->username . ')')
                                   ->render() ?>
                <? else: ?>
                    <?= QuickSearch::get('old_id', new StandardSearch('user_id'))->render() ?>
                <? endif; ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?= _('Neuer zusammengeführter Benutzer:') ?>
                </td>
                <td>
                    <?= QuickSearch::get('new_id', new StandardSearch('user_id'))->render() ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="convert_ident">
                        <?= _('Identitätsrelevante Daten migrieren:') ?>
                    </label>
                </td>
                <td>
                    <input type="checkbox" name="convert_ident" id="convert_ident" checked>
                    <i>
                        <?= _('(Es werden zusätzlich folgende Daten migriert: '
                             .'Veranstaltungen, Studiengänge, persönliche '
                             .'Profildaten inkl. Nutzerbild, Institute, '
                             .'generische Datenfelder und Buddies.)') ?>
                    </i>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="delete_old">
                        <?= _('Den alten Benutzer löschen:') ?>
                    </label>
                </td>
                <td>
                    <input type="checkbox" name="delete_old" id="delete_old" value="1">
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <?= Button::create(_('Umwandeln'),
                                       'umwandeln',
                                       array('title' => _('Den ersten Benutzer in den zweiten Benutzer migrieren'))) ?>
                    <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/user/index')) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
