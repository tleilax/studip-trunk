<? use Studip\Button, Studip\LinkButton; ?>

<?
$default_entries = DataFieldEntry::getDataFieldEntries([$user->user_id, $inst_id]);
?>

<form action="<?= $controller->url_for('settings/statusgruppen/store/role', $role_id) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <input type="hidden" name="name[<?= $inst_id ?>]" value="<?= htmlReady($institute['Name']) ?>">

    <input type="hidden" name="role_id" value="<?= $role_id ?>">
    <input type="hidden" name="group_id[]" value="<?= $role_id ?>">

    <table class="default nohover">
        <thead>
            <tr>
                <th colspan="4">
                    <?= _('Daten für diese Funktion') ?>
                </th>
                <th>
                    <?= _('Standarddaten') ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($datafields as $id => $entry): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td align="left">
                        <?= $entry->getName() ?>
                    </td>
                    <? if ($entry->isEditable() && ($entry->getValue() != 'default_value') && !$locked): ?>
                        <td>
                            <?= $entry->getHTML('datafields') ?>
                        </td>
                        <td style="text-align: right">
                            <a href="<?= $controller->url_for('settings/statusgruppen/default', $inst_id, $role_id, $id, true) ?>">
                                <?= Icon::create('checkbox-unchecked', 'clickable', ['title' => _('Diese Daten von den Standarddaten übernehmen')])->asImg(16, ["class" => 'text-top']) ?>
                            </a>
                        </td>
                    <? elseif ($entry->getValue() == 'default_value'): ?>
                        <td>
                            <?= $default_entries[$id]->getDisplayValue() ?>
                        </td>
                        <td style="text-align:right">
                            <? if ($entry->isEditable() && !$locked): ?>
                                <a href="<?= $controller->url_for('settings/statusgruppen/default', $inst_id, $role_id, $id, false) ?>">                            <?= Icon::create('checkbox-checked', 'clickable', ['title' => _('Diese Daten NICHT von den Standarddaten übernehmen')])->asImg(16, ["class" => 'text-top']) ?>
                                </a>
                            <? endif; ?>
                        </td>
                    <? else: ?>
                        <td colspan="2">
                            <?= $entry->getDisplayValue() ?>
                        </td>
                    <? endif; ?>
                    </td>
                    <td width="30%" class="left bordered"><?= $default_entries[$id]->getDisplayValue() ?></td>
                </tr>
            <? endforeach; ?>
            <? if (!$locked): ?>
                <tr>
                    <td colspan="4" style="text-align:right">
                        <?= _('Standarddaten übernehmen:') ?>
                        <a href="<?= $controller->url_for('settings/statusgruppen/defaults', $role_id, false) ?>">
                            <?= _('keine') ?>
                        </a>
                        /
                        <a href="<?= $controller->url_for('settings/statusgruppen/defaults', $role_id, true) ?>">
                            <?= _('alle') ?>
                        </a>
                    </td>
                </tr>
            <? endif; ?>
        </tbody>
        <? if (!$locked) : ?>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: center">
                        <?= Button::createAccept(_('Änderungen speichern'), 'store') ?>
                    </td>
                </tr>
            </tfoot>
        <? endif; ?>
    </table>
</form>
       