<aside id="file_aside">
    <div class="FileIcon"><?= Icon::create(
            FileManager::getIconNameForMimeType($file_ref->mime_type),
            'info'
        ) ?></div>
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
                    <a href="<?= URLHelper::getLink(
                        'dispatch.php/profile',
                        ['username' => $file_ref->owner->username]
                        ) ?>">
                        <?= htmlReady($file_ref->owner->getFullName()) ?>
                    </a>
                <? else: ?>
                    <?= 'user_id ' . htmlReady($file_ref->user_id) ?>
                <? endif ?>
            </td>
        </tr>
        <? if($file_ref->terms_of_use): ?>
            <tr>
                <td colspan="2">
                    <h3>
                        <?= Icon::create(
                            $file_ref->terms_of_use->icon,
                            'info')->asImg(
                            '16px',
                            ['class' => 'text-bottom']
                        ) ?>
                        <?= htmlReady($file_ref->terms_of_use->name) ?>
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
</aside>
