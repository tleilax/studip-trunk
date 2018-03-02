<? use Studip\Button, Studip\LinkButton; ?>
<? $perm = MvvPerm::get($abschnitt) ?>
<? $i18n_input = $controller->get_template_factory()->open('shared/i18n/input_grouped.php'); ?>
<? $i18n_textarea = $controller->get_template_factory()->open('shared/i18n/textarea_grouped.php'); ?>
<h3>
    <? if ($abschnitt->isNew()) : ?>
    <?= sprintf(_('Einen neuen Studiengangteil-Abschnitt für die Version "%s" anlegen.'),
            htmlReady($version->getDisplayName())) ?>
    <? else : ?>
    <?= sprintf(_('Studiengangteil-Abschnitt "%s" der Version "%s" bearbeiten.'),
            htmlReady($this->abschnitt->name),
            htmlReady($this->version->getDisplayName())) ?>
    <? endif; ?>
</h3>
<form class="default" action="<?= $controller->url_for('/abschnitt', $abschnitt->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label><?= _('Name') ?>
            <?= I18N::inputTmpl($i18n_input, 'name', $abschnitt->name, ['perm' => $perm, 'input_attributes' => ['maxlength' => '254']]); ?>
        </label>
        <label>
            <?= _('Kommentar') ?>
            <?= I18N::textareaTmpl($i18n_textarea, 'kommentar', $abschnitt->kommentar, ['perm' => $perm, 'input_attributes' => ['class' => 'add_toolbar resizable ui-resizable']]); ?>
        <label><?= _('Kreditpunkte') ?>
            <input <?= $perm->disable('kp') ?> type="text" name="kp" id="kp" value="<?= htmlReady($abschnitt->kp) ?>" size="3" maxlength="2">
        </label>
        <label><?= _('Zwischenüberschrift') ?>
            <?= I18N::inputTmpl($i18n_input, 'ueberschrift', $abschnitt->ueberschrift, ['perm' => $perm, 'input_attributes' => ['maxlength' => '254']]); ?>
        </label>
    </fieldset>
    <input type="hidden" name="version_id" value="<?= $this->version->id ?>">
    <footer data-dialog-button >
        <? if ($abschnitt->isNew()) : ?>
        <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Abschnitt anlegen'))) ?>
        <? else : ?>
        <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>