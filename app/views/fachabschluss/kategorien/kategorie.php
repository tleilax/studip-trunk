<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<script>
    MVV.PARENT_ID = '<?= $abschluss_kategorie->getId() ?>';
    MVV.OBJECT_TYPE = 'AbschlussKategorie';
</script>
<h1>
    <? if ($abschluss_kategorie->isNew()) : ?>
    <?= _('Neue Abschluss-Kategorie') ?>
    <? else : ?>
    <?= sprintf(_('Abschluss-Kategorie: %s'), htmlReady($abschluss_kategorie->getDisplayName())) ?>
    <? endif; ?>
</h1>
<? $perm = MvvPerm::get($abschluss_kategorie) ?>
<? $i18n_input = $controller->get_template_factory()->open('shared/i18n/input_grouped.php'); ?>
<? $i18n_textarea = $controller->get_template_factory()->open('shared/i18n/textarea_grouped.php'); ?>
<form class="default" action="<?= $controller->url_for('fachabschluss/kategorien/kategorie/' . $abschluss_kategorie->getId()) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label>
            <?= _('Name') ?>
            <?= I18N::inputTmpl($i18n_input, 'name', $abschluss_kategorie->name, ['perm' => $perm, 'input_attributes' => ['maxlength' => '255', 'required' => '']]); ?>
        <label>
            <?= _('Kurzname') ?>
            <?= I18N::inputTmpl($i18n_input, 'name_kurz', $abschluss_kategorie->name_kurz, ['perm' => $perm, 'input_attributes' => ['maxlength' => '50']]); ?>
        </label>
        <label>
            <?= _('Beschreibung') ?>
            <?= I18N::textareaTmpl($i18n_textarea, 'beschreibung', $abschluss_kategorie->beschreibung, ['perm' => $perm, 'input_attributes' => ['class' => 'add_toolbar ui-resizable wysiwyg']]); ?>
        </lab
    </fieldset>
    <?= $this->render_partial('shared/form_dokumente', array('perm_dokumente' => $perm->haveFieldPerm('document_assignments', MvvPerm::PERM_CREATE))) ?>
    <footer data-dialog-buttons>
    <? if ($abschluss_kategorie->isNew()) : ?>
        <? if ($perm->havePermCreate()) : ?>
        <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Abschluss-Kategorie anlegen'))) ?>
        <? endif; ?>
    <? else : ?>
        <? if ($perm->havePermWrite()) : ?>
        <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
    <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('fachabschluss/kategorien'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>