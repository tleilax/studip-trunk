<form method="post" action="<?= $controller->url_for("admin/statusgroups/deleteGroup/{$group->id}") ?>">
    <?= CSRFProtection::tokenTag() ?>
    <?= sprintf(_('Gruppe %s wirklich löschen?'), htmlReady($group->name)) ?>
    <br>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Löschen'), 'confirm', array('data-dialog-button' => '')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/statusgroups/index'), array('data-dialog-button' => '', 'data-dialog' => 'close')) ?>
    </div>
</form>
