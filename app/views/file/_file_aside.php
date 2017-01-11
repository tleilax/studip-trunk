<aside id="file_aside">
    <div class="FileIcon"><?= Icon::create('file','info',[]) ?></div>
    <h3><?= htmlReady($file_ref->name) ?></h3>
    <table class="default">
        <tr>
            <td><?= _('Gr��e') ?></td>
            <td><?= relSize($file_ref->size, false) ?></td>
        </tr>
        <tr>
            <td><?= _('Erstellt') ?></td>
            <td><?= date('d.m.Y H:i', $file_ref->mkdate) ?></td>
        </tr>
        <tr>
            <td><?= _('Ge�ndert') ?></td>
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
