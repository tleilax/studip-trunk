<?php foreach ($info as $entry) : ?>
<p>
    <?= formatReady($entry) ?>
</p>
<?php endforeach ?>
<footer data-dialog-button>
    <?= Studip\LinkButton::createCancel(_('Schließen'),
        $controller->url_for('course/statusgroups'),
        array('data-dialog' => 'close')) ?>
</footer>
