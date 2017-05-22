<aside id="file_aside">
    <div class="FileIcon"><?= Icon::create(
            FileManager::getIconNameForMimeType($file_ref->mime_type),
            'info'
        ) ?></div>
    <table class="default nohover">
        <caption><?= htmlReady($file_ref->name) ?></caption>
        <tobdy>
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
                        <h3><?=_('Hinweis zur Nutzung und Weitergabe:')?></h3>
                        <article><?= htmlReady($file_ref->terms_of_use->student_description) ?></article>

                        <h3><?= _('Downloadbedingungen') ?></h3>

                        <? if($file_ref->terms_of_use->download_condition == 0): ?>
                            <p><?= _('Keine Beschr�nkung') ?></p>
                        <? elseif($file_ref->terms_of_use->download_condition == 1): ?>
                            <p><?= _('Nur innerhalb geschlossener Gruppen') ?></p>
                        <? elseif($file_ref->terms_of_use->download_condition == 2): ?>
                            <p><?= _('Nur f�r Besitzer/-in erlaubt') ?></p>
                        <? else: ?>
                            <p><?= _('Nicht definiert') ?></p>
                        <? endif ?>
                    </td>
                </tr>
            <? endif ?>
        </tobdy>
    </table>
</aside>
