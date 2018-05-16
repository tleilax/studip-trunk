<?php
SkipLinks::addIndex(_('Suchformular'), 'search_sem_xts', 100);
// add skip link for simple search here
SkipLinks::addLink(
    _('Schnellsuche'),
    URLHelper::getURL('dispatch.php/search/courses', ['cmd' => 'qs', 'level' => 'f']),
    120
);
?>

<?= $search_obj->getFormStart(URLHelper::getLink('?send=yes'), [ 'class' => 'default' ]) ?>
<fieldset>
    <label for="search_sem_title"><?= _('Titel') ?>
    <?= $search_obj->getSearchField('title', [
            'id' => 'search_sem_title'
    ]) ?>
    </label>
    <label for="search_sem_number"><?= _('Nummer') ?>
    <?= $search_obj->getSearchField('number', [
        'id' => 'search_sem_number'
    ]) ?>
    </label>
    <label for="search_sem_sub_title"><?= _('Untertitel') ?>
    <?= $search_obj->getSearchField('sub_title', [
            'id' => 'search_sem_sub_title'
    ]) ?>
    </label>
    <label for="search_sem_type"><?= _('Typ') ?>
    <?= $search_obj->getSearchField('type', ['id' => 'search_sem_type']) ?>
    </label>
    <label for="search_sem_comment"><?=  _('Kommentar') ?>
    <?= $search_obj->getSearchField('comment', [
            'id' => 'search_sem_comment'
    ]) ?>
    </label>
    <label for="search_sem_lecturer"><?= _('Lehrende') ?>
    <?= $search_obj->getSearchField('lecturer', [
            'id' => 'search_sem_lecturer'
    ]) ?>
    </label>
<? if ($show_class): ?>
    <label for="search_sem_scope"><?= _('Bereich') ?>
    <?= $search_obj->getSearchField('scope', [
        'id' => 'search_sem_scope'
    ]) ?>
    </label>
<? endif; ?>
    <label for="search_sem_combination"><?= _('Verknüpfung') ?>
    <?= $search_obj->getSearchField('combination', ['id' => 'search_sem_combination']) ?>
    </label>
</fieldset>

<footer>
    <span class="button-group">
        <?= $search_obj->getSearchButton() ?>
        <?= Studip\LinkButton::create(
            _('Zurücksetzen'),
            URLHelper::getURL('?cmd=xts&level=f&reset_all=1'),
            ['title' => _('Zurücksetzen')]
        ) ?>
    </span>
</footer>

<?= $search_obj->getFormEnd() ?>
