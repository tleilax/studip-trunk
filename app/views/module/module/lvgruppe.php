<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<? $perm = MvvPerm::get($lvgruppe) ?>
<h1>
    <?= $headline ?>
</h1>
<form data-dialog class="default" action="<?= $submit_url ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label><?= _('Name') ?>
            <input <?= $perm->disable('name') ?> id="name" type="text" name="name" value="<?= htmlReady($lvgruppe->name) ?>" size="50">
        </label>
        <label><?= _('Alternativtext') ?>
            <?= MvvI18N::textarea('alttext', $lvgruppe->alttext, ['class' => 'add_toolbar resizable'])->checkPermission($lvgruppe) ?>
    </fieldset>
    <footer data-dialog-button>
        <? if ($lvgruppe->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
            <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Lehrveranstaltungsgruppe anlegen'))) ?>
            <? endif; ?>
        <? elseif ($perm->havePermWrite()) : ?>
        <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>
