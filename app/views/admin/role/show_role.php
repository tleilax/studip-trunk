<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;
?>

<h3><?= _('Rollenzuweisungen anzeigen') ?></h3>

<form action="<?= $controller->url_for('admin/role/show_role') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <select name="role" style="width: 300px">
        <? foreach ($roles as $getrole): ?>
            <option value="<?= $getrole->getRoleid() ?>" <? if ($getrole->getRoleid() == $roleid) echo 'selected'; ?>>
                <?= htmlReady($getrole->getRolename()) ?>
                <? if ($getrole->getSystemtype()): ?>
                    [<?= _('Systemrolle') ?>]
                <? endif; ?>
            </option>
        <? endforeach; ?>
    </select>
    <?= Button::create(_('Auswählen'), 'selectrole', array('title' => _('Rolle auswählen')))?>
</form>

<? if (isset($role)): ?>
<form action="<?= $controller->url_for('admin/role/remove_user/' . $role->getRoleId() . '/bulk') ?>" method="post">
    <table class="default" id="role-users">
        <colgroup>
            <col width="2%">
            <col width="3%">
            <col width="33%">
            <col width="5%">
            <col>
            <col width="24px">
        </colgroup>
        <caption>
        <? if ($role->getSystemtype()): ?>
            <?= sprintf(_('Liste der Benutzer mit der Rolle "%s"'),
                        htmlReady($role->getRolename())) ?>
        <? else: ?>
            <div class="caption-container">
                <div class="caption-content">
                    <?= sprintf(_('Liste der Benutzer mit der Rolle "%s"'),
                                htmlReady($role->getRolename())) ?>
                </div>
                <div class="caption-actions">
                    <?= $mps->render() ?>
                </div>
            </div>
        <? endif; ?>
        </caption>
        <thead>
            <tr>
                <th>
                    <input type="checkbox"
                           data-proxyfor="#role-users tbody :checkbox"
                           data-activates="#role-users tfoot button">
                </th>
                <th>&nbsp;</th>
                <th><?= _('Name') ?></th>
                <th><?= _('Status') ?></th>
                <th><?= _('Einrichtungszuordnung') ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
    <? if (count($users) === 0): ?>
            <tr>
                <td colspan="6" style="text-align: center;">
                    <?= _('Es wurden keine Benutzer gefunden.') ?>
                </td>
            </tr>
    <? else: ?>
        <? foreach (array_values($users) as $index => $user): ?>
            <tr>
                <td>
                    <input type="checkbox" name="ids[]" value="<?= $user['user_id'] ?>">
                </td>
                <td style="text-align: right;">
                    <?= $index + 1 ?>.
                </td>
                <td>
                    <a href="<?= $controller->url_for('admin/role/assign_role', $user['user_id']) ?>">
                        <?= htmlReady(sprintf('%s %s (%s)', $user['Vorname'], $user['Nachname'], $user['username'])) ?>
                    </a>
                </td>
                <td><?= $user['perms'] ?></td>
                <td>
                <? $institutes = join(', ', $user['institutes']); ?>
                    <?= htmlReady(substr($institutes, 0, 60)) ?>
                    <? if (strlen($institutes) > 60): ?>
                    ...<?= tooltipIcon(join("\n", $user['institutes']))?>
                    <? endif ?>
                </td>
                <td class="actions">
                    <a href="<?= $controller->url_for('admin/role/remove_user/' . $roleid . '/' . $user['user_id']) ?>" data-confirm="<?= _('Soll dieser Person wirklich die Rolle entzogen werden?') ?>">
                        <?= Assets::img('icons/blue/trash.svg', tooltip2(_('Rolle entziehen'))) ?>
                    </a>
                </td>
            </tr>
        <? endforeach; ?>
    <? endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">
                    <?= _('Alle markierten Einträge') ?>
                    <?= Studip\Button::create(_('Löschen'), 'delete', array(
                            'data-confirm' => _('Sollen die markierten Einträge wirklich gelöscht werden?'),
                    )) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>

    <br>

    <table class="default">
        <caption>
            <div class="caption-container">
                <div class="caption-content">
                    <?= sprintf(_('Liste der Plugins mit der Rolle "%s"'),
                                htmlReady($role->getRolename())) ?>
                </div>
                <div class="caption-actions">
                    <a href="<?= $controller->url_for('admin/role/add_plugin/' . $roleid) ?>" data-dialog="size=auto">
                        <?= Assets::img('icons/blue/add/plugin.svg') ?>
                        <?= _('Plugins hinzufügen') ?>
                    </a>
                </div>
            </div>
        </caption>
        <colgroup>
            <col width="3%">
            <col width="40%">
            <col>
        </colgroup>
        <thead>
            <tr>
                <th style="width: 3%;"></th>
                <th style="width: 40%;"><?= _('Name') ?></th>
                <th><?= _('Typ') ?></th>
            </tr>
        </thead>
        <tbody>
    <? if (count($plugins) === 0): ?>
            <tr>
                <td colspan="3">
                    <?= _('Es wurden keine Plugins gefunden.') ?>
                </td>
            </tr>
    <? else: ?>
        <? foreach (array_values($plugins) as $index => $plugin): ?>
            <tr>
                <td style="text-align: right;">
                    <?= $index + 1 ?>.
                </td>
                <td>
                    <a href="<?= $controller->url_for('admin/role/assign_plugin_role', $plugin['id']) ?>">
                        <?= htmlReady($plugin['name']) ?>
                    </a>
                </td>
                <td><?= join(', ', $plugin['type']) ?></td>
            </tr>
        <? endforeach; ?>
    <? endif; ?>
        </tbody>
    </table>
<? endif; ?>
