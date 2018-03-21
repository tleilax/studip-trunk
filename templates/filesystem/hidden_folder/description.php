<div><?= htmlReady($type) ?>:</div>
<div>
    <div><?= _('Ein unsichtbarer Ordner, welcher nur von Lehrenden und TutorInnen gesehen werden kann.') ?></div>
    <div><?= _('Der Ordner lässt sich auch für Studierende sichtbar schalten.') ?></div>
</div>
<? if ($folderdata['description']) : ?>
    <div>
        <?= htmlReady($folderdata['description']) ?>
    </div>
<? endif ?>