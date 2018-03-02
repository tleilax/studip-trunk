<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<h1>
    <? if ($fach->isNew()) : ?>
    <?= _('Neues Fach') ?>
    <? else : ?>
    <?= sprintf(_('Fach: %s'), htmlReady($fach->getDisplayName())) ?>
    <? endif; ?>
</h1>
<? $perm = MvvPerm::get($fach) ?>
<? $i18n_input = $controller->get_template_factory()->open('shared/i18n/input_grouped.php'); ?>
<? $i18n_textarea = $controller->get_template_factory()->open('shared/i18n/textarea_grouped.php'); ?>
<form class="default" action="<?= $controller->url_for('/fach/', $fach->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label>
            <?= _('Name') ?>
            <?= I18N::inputTmpl($i18n_input, 'name', $fach->name, ['perm' => $perm, 'input_attributes' => ['maxlength' => '255'], 'required' => '']); ?>
        </label>
        <label>
            <?= _('Kurzname') ?>
            <?= I18N::inputTmpl($i18n_input, 'name_kurz', $fach->name_kurz, ['perm' => $perm, 'input_attributes' => ['maxlength' => '50']]); ?>
        </label>
        <label>
            <?= _('Beschreibung') ?>
            <?= I18N::textareaTmpl($i18n_textarea, 'beschreibung', $fach->beschreibung, ['perm' => $perm, 'input_attributes' => ['class' => 'add_toolbar ui-resizable wysiwyg']]); ?>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Verantwortliche Einrichtung') ?></legend>
        <? if ($perm->haveFieldPermDepartments(MvvPerm::PERM_WRITE)) : ?>
        <?= $search_institutes->render(); ?>
        <? if (Request::submitted('search_institutes')) : ?>
            <?= Icon::create('refresh', 'clickable', ['name' => 'reset_institutes', 'data-qs_id' => $search_institutes_id])->asInput(); ?>
        <? else : ?>
            <?= Icon::create('search', 'clickable', ['name' => 'search_dokumente', 'data-qs_id' => $search_institutes_id, 'data-qs_name' => $search_institutes->getId(), 'class' => 'mvv-qs-button'])->asInput(); ?>
        <? endif; ?>
        <? endif;?>
        <ul id="institut_target" class="mvv-assigned-items mvv-institute sortable">
            <? if ($perm->haveFieldPermDepartments(MvvPerm::PERM_WRITE)) : ?>
                <li class="mvv-item-list-placeholder"<?= (count($fach->departments) ? ' style="display: none;"' : '') ?>><?= _('Bitte mindestens eine verantwortliche Einrichtung hinzufügen.') ?></li>
            <? elseif (!count($fach->getFachbereiche())) : ?>
                <li class="mvv-item-list-placeholder"><?= _('Es wurde noch keine verantwortliche Einrichtung angegeben.') ?></li>
            <? endif; ?>
            <? foreach ($fach->getFachbereiche() as $fachbereich) : ?>
            <li id="fachbereiche_<?= $fachbereich->getId() ?>" class="sort_items">
                <div class="mvv-item-list-text">
                    <? if ($fachbereich) : ?>
                    <?= htmlReady($fachbereich->getDisplayName()) ?>
                    <? else: ?>
                    <?= _('unbekannte Einrichtung') ?>
                    <? endif; ?>
                </div>
                <? if($perm->haveFieldPerm('institut_id', MvvPerm::PERM_WRITE)): ?>
                <div class="mvv-item-list-buttons">
                    <a href="#" class="mvv-item-remove"><?= Icon::create('trash', 'clickable', array('title' => _('Einrichtung entfernen')))->asImg(); ?></a>
                </div>
                <? endif;?>
                <input type="hidden" name="institut_items[]" value="<?= $fachbereich->getId() ?>">
            </li>
            <? endforeach; ?>
        </ul>
    </fieldset>
    <fieldset>
        <legend><?= _('Zusätzliche Angaben') ?></legend>
        <label><?= _('Schlagworte') ?>
        <? if ($perm->haveFieldPerm('schlagworte')) : ?>
            <textarea cols="60" rows="5" name="schlagworte" id="schlagworte" class="add_toolbar ui-resizable wysiwyg"><?= htmlReady($fach->schlagworte) ?></textarea>
        <? else : ?>
            <textarea readonly cols="60" rows="5" name="schlagworte" id="schlagworte" class="ui-resizable"><?= htmlReady($fach->schlagworte) ?></textarea>
        <? endif; ?>
            <?= _('Hier können zusätzlich Schlagworte angegeben werden, die in der Suche berücksichtigt werden.') ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
    <? if ($fach->isNew()) : ?>
        <? if ($perm->havePermCreate()) : ?>
            <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Fach anlegen'))) ?>
        <? endif; ?>
    <? else : ?>
        <? if ($perm->havePermWrite()) : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
    <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('/index'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>