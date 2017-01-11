<div id="file_details_window">
    <aside id="file_aside">
        <div class="FileIcon"><?= Icon::create('file','info') ?></div>
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
    <div id="preview_container">
        <? if($show_preview): ?>
        <? if($file_ref->isImage()): ?>
        <img src="<?= htmlReady($file_ref->getDownloadURL()) ?>"
            <?= $file_ref->file ? 'type="' . $file_ref->file->mime_type . '"' : '' ?> class="file_preview"></img>
        <? elseif($file_ref->isAudio()): ?>
        <audio controls="controls" src="<?= htmlReady($file_ref->getDownloadURL()) ?>"
            <?= $file_ref->file ? 'type="' . $file_ref->file->mime_type . '"' : '' ?> class="file_preview"></audio>
        <? elseif($file_ref->isVideo()): ?>
        <video controls="controls" src="<?= htmlReady($file_ref->getDownloadURL()) ?>"
            <?= $file_ref->file ? 'type="' . $file_ref->file->mime_type . '"' : '' ?> class="file_preview"></video>
        <? endif ?>
        <? endif ?>
        <h3><?= _('Beschreibung') ?></h3>
        <article>
        <? if($file_ref->description): ?>
        <?= htmlReady($file_ref->description); ?>
        <? else: ?>
            <? if($file_ref->folder): ?>
            <?= _('Keine Beschreibung vorhanden!') ?>
                <? if($file_ref->folder->getTypedFolder()->isFileEditable($file_ref->id, User::findCurrent()->id)): ?>
                <a href="<?= $controller->url_for('file/edit/' . $file_ref->id) ?>" data-dialog="1">
                    <?= Icon::create('edit', 'clickable')->asImg('16px', ['class' => 'text-bottom']) ?>
                    <?= _('Beschreibung hinzufügen') ?>
                </a>
                <? endif ?>
            <? endif ?>
        <? endif ?>
        </article>
    </div>
</div>
