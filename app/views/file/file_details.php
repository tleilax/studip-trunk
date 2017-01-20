<div id="file_details_window">
    <?= $this->render_partial('file/_file_aside.php') ?>
    <div id="preview_container">
        <? if($is_downloadable): ?>
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
            <?= _('Keine Beschreibung vorhanden!') ?>
        <? endif ?>
        </article>
    </div>
</div>
<div data-dialog-button>
    <? if($previous_file_ref_id): ?>
        <?= \Studip\LinkButton::create(_('<< Vorherige Datei'),
                $controller->url_for('file/details/' . $previous_file_ref_id),
            ['data-dialog' => 1]
            ) ?>
    <? endif ?>
    <? if($next_file_ref_id): ?>
        <?= \Studip\LinkButton::create(_('Nächste Datei >>'),
                $controller->url_for('file/details/' . $next_file_ref_id),
            ['data-dialog' => 1]
            ) ?>
    <? endif ?>
    <? if ($is_editable) : ?>
        <?= \Studip\LinkButton::create(_('Bearbeiten'),
            $controller->url_for('file/edit/' . $file_ref->id),
            ['data-dialog' => 1]
        ) ?>
    <? endif ?>
    <? if ($is_downloadable) : ?>
        <?= \Studip\LinkButton::create(_('Herunterladen'),
            $file_ref->getDownloadURL()
        ) ?>
    <? endif ?>
</div>