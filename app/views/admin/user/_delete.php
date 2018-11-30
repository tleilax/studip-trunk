<?
# Lifter010: TODO
use Studip\Button;
?>
<form action="<?= $controller->url_for('admin/user/delete') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
<? if ($users) : ?>
    <? $details = [] ?>
    <? foreach ($users as $user) : ?>
        <? $details[] = htmlReady(sprintf('%s (%s)', $user->getFullName(), $user->username)) ?>
        <input type="hidden" name="user_ids[]" value="<?= $user['user_id'] ?>">
    <? endforeach ?>
<? endif ?>
    <?= MessageBox::warning(_('Wollen Sie die folgenden Nutzer wirklich löschen?'), $details) ?>



    <fieldset>
        <legend><?= _('persohnenbezogene Daten') ?></legend>

        <label>
            <input id="personaldocuments" name="personaldocuments" value="1" checked type="checkbox">
            <?= _('Dokumente löschen?') ?>
            <?= tooltipHtmlIcon(htmlReady(_('persöhnlicher Dateibereich'), true, true)) ?>
        </label>

        <label>
            <input id="personalcontent" name="personalcontent" value="1" checked type="checkbox">
            <?= _('andere Inhalte löschen?') ?>
            <?= tooltipHtmlIcon(htmlReady(_('Inhalte der Profilseite, persöhnliche Blubber, Nachrichten'), true, true)) ?>
        </label>

        <label>
            <input id="personalnames" name="personalnames" value="1" checked type="checkbox">
            <?= _('Namen löschen?') ?>
            <?= tooltipHtmlIcon(htmlReady(_('Vor-/ Nachname, Username, E-Mail'), true, true)) ?>
        </label>

    </fieldset>

    <fieldset>
        <legend><?= _('veranstaltungsbezogene Daten') ?></legend>

        <label>
            <input id="documents" name="documents" value="1" checked type="checkbox">
            <?= _('Dokumente löschen?') ?>
            <?= tooltipHtmlIcon(htmlReady(_('Dateien in Veranstaltungen und Einrichtungen'), true, true)) ?>
        </label>

        <label>
            <input id="coursecontent" name="coursecontent" value="1" checked type="checkbox">
            <?= _('andere Inhalte löschen?') ?>
            <?= tooltipHtmlIcon(htmlReady(_('veranstaltungsbezogene Inhalte, bis auf Wiki und Forum Einträge'), true, true)) ?>
        </label>

        <label>
            <input id="memberships" name="memberships" value="1" checked type="checkbox">
            <?= _('Veranstaltungs-/Einrichtungszuordnungen löschen?') ?>
            <?= tooltipHtmlIcon(htmlReady(_('Zuordnungen zu Veranstaltungen, Einrichtungen, Studiengruppen'), true, true)) ?>
        </label>

    </fieldset>


    <label>
        <input id="mail" name="mail" value="1" checked type="checkbox">
        <?= _('E-Mail-Benachrichtigung verschicken?') ?>
    </label>
    <footer data-dialog-button>
        <?= Button::createAccept(_('JA!'), 'delete', ['title' => _('Benutzer löschen')]) ?>
        <?= Button::createCancel(_('NEIN!'), 'back', ['title' => _('Abbrechen   ')]) ?>
    </footer>
</form>
