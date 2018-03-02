<? use Studip\Button, Studip\LinkButton; ?>
<h1>
    <? if ($abschluss->isNew()) : ?>
    <?= _('Neuer Abschluss') ?>
    <? else : ?>
    <?= sprintf(_('Abschluss: %s'), htmlReady($abschluss->getDisplayName())) ?>
    <? endif; ?>
</h1>
<? $perm = MvvPerm::get($abschluss) ?>
<? $i18n_input = $controller->get_template_factory()->open('shared/i18n/input_grouped.php'); ?>
<? $i18n_textarea = $controller->get_template_factory()->open('shared/i18n/textarea_grouped.php'); ?>
<form class="default" action="<?= $controller->url_for('/abschluss', $abschluss->getId()) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label>
            <?= _('Name') ?>
            <?= I18N::inputTmpl($i18n_input, 'name', $abschluss->name, ['perm' => $perm, 'input_attributes' => ['maxlength' => '255', 'required' => '']]) ?>
        </label>
        <label>
            <?= _('Kurzname') ?>
            <?= I18N::inputTmpl($i18n_input, 'name_kurz', $abschluss->name_kurz, ['perm' => $perm, 'input_attributes' => ['maxlength' => '50']]) ?>
        </label>
        <label>
            <?= _('Beschreibung') ?>
            <?= I18N::textareaTmpl($i18n_textarea, 'beschreibung', $abschluss->beschreibung, ['perm' => $perm, 'input_attributes' => ['class' => 'add_toolbar ui-resizable wysiwyg']]); ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Abschluss-Kategorie wählen') ?></legend>
        <? if ($perm->haveFieldPerm('category_assignment')) : ?>
        <label><?= _('Abschluss-Kategorie') ?></label>
            <select id="abschluss_kategorie" name="kategorie_id" size="1">
                <option value=""><?= _('-- bitte wählen --') ?></option>
                <? foreach ($abschluss_kategorien as $kategorie) : ?>
                <option <?= ($kategorie->getId() == $abschluss->kategorie_id ? 'selected ' : '') ?>value="<?= $kategorie->getId() ?>"><?= htmlReady($kategorie->name) ?></option>
                <? endforeach; ?>
            </select>
        </label>
        <? else : ?>
            <?= htmlReady(AbschlussKategorie::get($abschluss->kategorie_id)->getDisplayName()) ?>
        <? endif; ?>
    </fieldset>
    <footer data-dialog-button>
    <? if ($abschluss->isNew()) : ?>
        <? if ($perm->havePermCreate()) : ?>
        <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Abschluss anlegen'))) ?>
        <? endif; ?>
    <? else : ?>
        <? if ($perm->havePermWrite()) : ?>
        <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
    <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('/index'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>