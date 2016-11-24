<aside style="float:left; width: 20%;">
    <?= Icon::create(
        'file',
        'info',
        [
            'style' => 'width: 100%; max-height: 18em; height: 100%;'
        ]) ?>
    <h3 style="text-align: center; font-size: 140%;"><?= htmlReady($file_ref->name) ?></h3>
    <dl>
        <dt><?= _('Größe') ?></dt>
        <dd><?= relSize($file_ref->size, false) ?></dd>
        
        <dt><?= _('Erstellt') ?></dt>
        <dd><?= date('d.m.Y H:i', $file_ref->mkdate) ?></dd>
        
        <dt><?= _('Geändert') ?></dt>
        <dd><?= date('d.m.Y H:i', $file_ref->chdate) ?></dd>
        
        <dt><?= _('Besitzer/-in') ?></dt>
        <dd>
        <? if($file_ref->owner): ?>
        <?= htmlReady($file_ref->owner->getFullName()) ?>
        <? endif ?>
        </dd>
    </dl>
</aside>
