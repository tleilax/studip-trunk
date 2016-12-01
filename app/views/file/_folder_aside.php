<aside id="folder_aside">
    <?= Icon::create(
        ($folder->file_refs) ? 'folder-full' : 'folder-empty',
        'info',
        []) ?>
    <h3><?= htmlReady($folder->name) ?></h3>
    <dl>
        <dt><?= _('Erstellt') ?></dt>
        <dd><?= date('d.m.Y H:i', $folder->mkdate) ?></dd>
        
        <dt><?= _('Geändert') ?></dt>
        <dd><?= date('d.m.Y H:i', $folder->chdate) ?></dd>
        
        <dt><?= _('Besitzer/-in') ?></dt>
        <dd>
        <? if($folder->owner): ?>
        <?= htmlReady($folder->owner->getFullName()) ?>
        <? endif ?>
        </dd>
    </dl>
</aside>
