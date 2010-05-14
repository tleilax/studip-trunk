<?php
require_once 'lib/classes/MessageBox.class.php';
require_once 'lib/visual.inc.php';

include 'lib/include/html_head.inc.php';

$current_page = _('Kein Objekt gew�hlt')
?>

<?= $this->render_partial('header', compact('current_page')) ?>

<div style="background-color: white; width: 70%; padding: 1em; margin: auto;">
    <?= MessageBox::exception(htmlentities($exception->getMessage()), array(
            _('Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher ein Objekt (Veranstaltung oder Einrichtung) gew�hlt haben.'),
            sprintf(_('Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich l�nger als %s Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zur�ck zur Anmeldung zu gelangen.'), $GLOBALS['AUTH_LIFETIME']))) ?>

    <? if ($last_edited = Request::get('content') . Request::get('description') . Request::get('body')) : ?>
        <p>
            <?= _('Folgender von ihnen eingegebene Text konnte nicht gespeichert werden:') ?>
        </p>
        <div class="steel1" style="padding: 5px; border: 1px solid;">
            <?= htmlentities($last_edited) ?>
        </div>
    <? endif ?>
    <p>
      <?= _("Zur�ck zur") ?> <a href="<?= URLHelper::getLink('index.php') ?>"><?= _("Startseite") ?></a>
    </p>
</div>

<? include 'lib/include/html_end.inc.php'; ?>
