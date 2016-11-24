<aside style="float:left; width: 20%;">
    <?= Icon::create(
        ($folder->file_refs) ? 'folder-full' : 'folder-empty',
        'info',
        [
            'style' => 'width: 100%; max-height: 16em; height: 100%;'
        ]) ?>
    <h3 style="text-align: center; font-size: 140%;"><?= htmlReady($folder->name) ?></h3>
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
