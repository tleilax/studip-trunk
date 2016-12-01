<aside id="file_aside">
    <?= Icon::create(
        'file',
        'info',
        []) ?>
    <h3 style="text-align: center; font-size: 140%;"><?= htmlReady($file_ref->name) ?></h3>
    <dl>
        <dt><?= _('Gr��e') ?></dt>
        <dd><?= relSize($file_ref->size, false) ?></dd>
        
        <dt><?= _('Erstellt') ?></dt>
        <dd><?= date('d.m.Y H:i', $file_ref->mkdate) ?></dd>
        
        <dt><?= _('Ge�ndert') ?></dt>
        <dd><?= date('d.m.Y H:i', $file_ref->chdate) ?></dd>
        
        <dt><?= _('Besitzer/-in') ?></dt>
        <dd>
        <? if($file_ref->owner): ?>
        <?= htmlReady($file_ref->owner->getFullName()) ?>
        <? endif ?>
        </dd>
    </dl>
</aside>
