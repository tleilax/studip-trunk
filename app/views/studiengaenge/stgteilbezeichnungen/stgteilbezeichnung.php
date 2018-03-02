<? use Studip\Button, Studip\LinkButton; ?>
<? $perm = MvvPerm::get($stgteilbezeichnung) ?>
<? $i18n_input = $controller->get_template_factory()->open('shared/i18n/input_grouped.php'); ?>
<h3>
    <? if ($stgteilbezeichnung->isNew()) : ?>
    <?= _('Neue Studiengangteil-Bezeichnung') ?>
    <? else : ?>
    <?= sprintf(_('Studiengangteil-Bezeichnung: %s'), htmlReady($stgteilbezeichnung->name)) ?>
    <? endif; ?>
</h3>
<form class="mvv-form default"
      action="<?= $controller->url_for('studiengaenge/stgteilbezeichnungen/store'. ($stgteilbezeichnung->getId() ? '/' . $stgteilbezeichnung->getId() : '')) ?>"
      method="post"<?= Request::isXhr() ? ' data-dialog' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label><?= _('Studiengangteil-Bezeichnung') ?>
            <?= I18N::inputTmpl($i18n_input, 'name', $stgteilbezeichnung->name, ['perm' => $perm, 'input_attributes' => ['maxlength' => '100', 'required' => '']]); ?>
        </label>
        <label><?= _('Kurzname') ?>
            <?= I18N::inputTmpl($i18n_input, 'name_kurz', $stgteilbezeichnung->name_kurz, ['perm' => $perm, 'input_attributes' => ['maxlength' => '20']]); ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <? if ($stgteilbezeichnung->isNew()) : ?>
        <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Studiengangteil-Bezeichnung anlegen'))) ?>
        <? else : ?>
        <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('studiengaenge/stgteilbezeichnungen/index'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>