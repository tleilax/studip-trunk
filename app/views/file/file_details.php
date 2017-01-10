<?= Icon::create(
    'file',
    'info',
    [])->asImg('32px') ?>
<h1><?= htmlReady($file_ref->name) ?></h1>
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
        <? else: ?>
        <?= 'user_id ' . htmlReady($file_ref->user_id) ?>
        <? endif ?>
        </td>
    </tr>
    <? if($file_ref->terms_of_use): ?>
    <tr>
        <td><?= _('Nutzungsbedingungen') ?></td>
        <td>
            <h3>
                <?= Icon::create($file_ref->terms_of_use->icon, 'info') ?>
                <span><?= htmlReady($file_ref->terms_of_use->name) ?></span>
            </h3>
            <article><?= htmlReady($file_ref->terms_of_use->description) ?></article>
            
            <h3><?= _('Downloadbedingungen') ?></h3>
            
            <? if($file_ref->terms_of_use->download_condition == 0): ?>
            <p><?= _('Keine Beschränkung') ?></p>
            <? elseif($file_ref->terms_of_use->download_condition == 1): ?>
            <p><?= _('Nur innerhalb geschlossener Gruppen') ?></p>
            <? elseif($file_ref->terms_of_use->download_condition == 2): ?>
            <p><?= _('Nur für Besitzer/-in erlaubt') ?></p>
            <? else: ?>
            <p><?= _('Nicht definiert') ?></p>
            <? endif ?>
        </td>
    </tr>
    <? endif ?>
</table>
<article><?= htmlReady($file_ref->description); ?></article>
