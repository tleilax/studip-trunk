<? use Studip\Button, Studip\LinkButton; ?>
<? $perm = new MvvPerm($zuordnung) ?>
<? $i18n_input = $controller->get_template_factory()->open('shared/i18n/input_grouped.php'); ?>
<? $i18n_textarea = $controller->get_template_factory()->open('shared/i18n/textarea_grouped.php'); ?>
<h3>
    <?= _('Modulzuordnung bearbeiten') ?>
</h3>
<form class="default" action="<?= $controller->url_for('/modul_zuordnung', $zuordnung->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label><?= _('Bezeichnung der Modulzuordnung') ?>
            <?= I18N::inputTmpl($i18n_input, 'bezeichnung', $zuordnung->bezeichnung, ['perm' => $perm, 'input_attributes' => ['maxlength' => '250']]); ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Weitere Angaben') ?></legend>
        <label for="zuordnung_flexnow_modul"><?= _('ID der Zuordnung aus Fremdsystem') ?>
            <input <?= $perm->disable('flexnow_modul') ?> type="text" name="flexnow_modul" id="zuordnung_flexnow_modul" maxlength="250" value="<?= htmlReady($zuordnung->flexnow_modul) ?>">
        </label>
        <label for="zuordnung_modulcode"><?= _('Spezifischer Modulcode') ?>
            <input <?= $perm->disable('modulcode') ?> type="text" name="modulcode" id="zuordnung_modulcode" maxlength="250" value="<?= htmlReady($zuordnung->modulcode) ?>">
        </label>
    </fieldset>
    <footer data-dialog-button >
        <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('/'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>