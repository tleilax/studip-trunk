<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<? $perm = MvvPerm::get($lvgruppe) ?>
<? $i18n_textarea = $controller->get_template_factory()->open('shared/i18n/textarea_grouped.php'); ?>
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
            <?= I18N::textareaTmpl($i18n_textarea, 'alttext', $lvgruppe->alttext, ['perm' => $perm, 'input_attributes' => ['class' => 'add_toolbar resizable']]); ?>
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
