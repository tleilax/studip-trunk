<div id="file_details_window">
    <?= $this->render_partial('file/_file_aside.php') ?>

    <div id="preview_container">
    <? if ($is_downloadable): ?>
        <? if ($file_ref->isImage()): ?>
            <img src="<?= htmlReady($file_ref->download_url) ?>" class="file_preview"
                 <? if ($file_ref->file) printf('type="%s"', $file_ref->mime_type); ?>>
        <? elseif ($file_ref->isAudio()): ?>
            <audio controls="controls" src="<?= htmlReady($file_ref->download_url) ?>" class="file_preview"
                    <? if ($file_ref->file) printf('type="%s"', $file_ref->mime_type); ?>></audio>
        <? elseif ($file_ref->isVideo()): ?>
            <video controls="controls" src="<?= htmlReady($file_ref->download_url) ?>" class="file_preview"
                   <? if ($file_ref->file) printf('type="%s"', $file_ref->mime_type); ?>></video>
        <? endif; ?>
    <? endif; ?>

        <h3><?= _('Beschreibung') ?></h3>
        <article>
            <?= htmlReady($file_ref->description ?: _('Keine Beschreibung vorhanden.'), true, true) ?>
        </article>
    </div>
</div>

<div data-dialog-button>
<? if ($previous_file_ref_id): ?>
    <?= Studip\LinkButton::create(
        _('<< Vorherige Datei'),
        $controller->url_for('file/details/' . $previous_file_ref_id, ['from_plugin' => $from_plugin]),
        ['data-dialog' => '']
    ) ?>
<? endif; ?>
<? if ($next_file_ref_id): ?>
    <?= Studip\LinkButton::create(
        _('Nächste Datei >>'),
        $controller->url_for('file/details/' . $next_file_ref_id, ['from_plugin' => $from_plugin]),
        ['data-dialog' => '']
    ) ?>
<? endif; ?>
<? if ($is_editable) : ?>
    <?= Studip\LinkButton::create(
        _('Bearbeiten'),
        $controller->url_for('file/edit/' . $file_ref->id, ['from_plugin' => $from_plugin]),
        ['data-dialog' => '']
    ) ?>
<? endif; ?>
<? if ($is_downloadable) : ?>
    <?= Studip\LinkButton::createDownload(
        _('Herunterladen'),
        $file_ref->getDownloadURL('force_download')
    ) ?>
<? endif; ?>
</div>
