<form method="post" action="<?= $controller->url_for('course/timesrooms/saveStack')?>" class="studip-form" data-dialog="size=big">
    <input type="hidden" name="method" value="edit" />
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Änderungen speichern'), 'save')?>
        <?= Studip\LinkButton::create(_('Zurück zur Übersicht'), $controller->url_for('course/timesrooms/index'), array('data-dialog' => 'size=big')) ?>
    </footer>
</form>
