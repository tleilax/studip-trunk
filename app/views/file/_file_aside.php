<aside id="file_aside">
    <?= Icon::create(
        'file',
        'info',
        []) ?>
    <h3><?= htmlReady($file_ref->name) ?></h3>
    <table class="default">
        <tr>
            <td><?= _('Größe') ?></td>
            <td><?= relSize($file_ref->size, false) ?></td>
        </tr>
        <tr>
            <td><?= _('Erstellt') ?></td>
            <td><?= date('d.m.Y H:i', $file_ref->mkdate) ?></td>
        </tr>
        <tr>
            <td><?= _('Geändert') ?></td>
            <td><?= date('d.m.Y H:i', $file_ref->chdate) ?></td>
        </tr>
        <tr>
            <td><?= _('Besitzer/-in') ?></td>
            <td>
            <? if($file_ref->owner): ?>
            <?= htmlReady($file_ref->owner->getFullName()) ?>
            <? endif ?>
            </td>
        </tr>
    </table>
</aside>
