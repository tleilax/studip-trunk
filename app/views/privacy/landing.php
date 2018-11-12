
<ul class="boxed-grid">
<li>
    <a href="<?= $controller->url_for("privacy/index/{$user_id}"); ?>" <?= (Request::isDialog())?'data-dialog="size=big"':'' ?>>
        <h3>
            <?= Icon::create('persons', 'clickable')->asImg(false) ?>
            <?= _('Alle Daten') ?>
        </h3>
        <p>
            <?= _('Übersicht aller Personendaten') ?>
        </p>
    </a>
</li>
<li>
    <a href="<?= $controller->url_for("privacy/index/{$user_id}/core"); ?>" <?= (Request::isDialog())?'data-dialog="size=big"':'' ?>>
        <h3>
            <?= Icon::create('person', 'clickable')->asImg(false) ?>
            <?= _('Kerndaten') ?>
        </h3>
        <p>
            <?= _('Angaben zur Person, Konfigurationen, Logs') ?>
        </p>
    </a>
</li>
<li>
    <a href="<?= $controller->url_for("privacy/index/{$user_id}/membership"); ?>" <?= (Request::isDialog())?'data-dialog="size=big"':'' ?>>
        <h3>
            <?= Icon::create('seminar', 'clickable')->asImg(false) ?>
            <?= _('Veranstaltungen, Einrichtungen') ?>
        </h3>
        <p>
            <?= _('Zuordnung zu Veranstaltungen, Einrichtungen, Fächern, Studiengängen') ?>
        </p>
    </a>
</li>
<li>
    <a href="<?= $controller->url_for("privacy/index/{$user_id}/date"); ?>" <?= (Request::isDialog())?'data-dialog="size=big"':'' ?>>
        <h3>
            <?= Icon::create('date', 'clickable')->asImg(false) ?>
            <?= _('Kalender/Termine') ?>
        </h3>
        <p>
            <?= _('Kalendereinträge und Termine') ?>
        </p>
    </a>
</li>
<li>
    <a href="<?= $controller->url_for("privacy/index/{$user_id}/message"); ?>" <?= (Request::isDialog())?'data-dialog="size=big"':'' ?>>
        <h3>
            <?= Icon::create('mail', 'clickable')->asImg(false) ?>
            <?= _('Nachrichten') ?>
        </h3>
        <p>
            <?= _('Nachrichten, Kommentare, Blubber, News') ?>
        </p>
    </a>
</li>
<li>
    <a href="<?= $controller->url_for("privacy/index/{$user_id}/content"); ?>" <?= (Request::isDialog())?'data-dialog="size=big"':'' ?>>
        <h3>
            <?= Icon::create('forum2', 'clickable')->asImg(false) ?>
            <?= _('Inhalte') ?>
        </h3>
        <p>
            <?= _('Dateien, Forum, Wiki, Literaturlisten') ?>
        </p>
    </a>
</li>
<li>
    <a href="<?= $controller->url_for("privacy/index/{$user_id}/quest"); ?>" <?= (Request::isDialog())?'data-dialog="size=big"':'' ?>>
        <h3>
            <?= Icon::create('vote', 'clickable')->asImg(false) ?>
            <?= _('Fragebögen, Aufgaben') ?>
        </h3>
        <p>
            <?= _('Fragebögen, Umfragen, Aufgaben') ?>
        </p>
    </a>
</li>
<li>
    <a href="<?= $controller->url_for("privacy/index/{$user_id}/plugins"); ?>" <?= (Request::isDialog())?'data-dialog="size=big"':'' ?>>
        <h3>
            <?= Icon::create('plugin', 'clickable')->asImg(false) ?>
            <?= _('Plugin-Inhalte') ?>
        </h3>
        <p>
            <?= _('Inhalte aus Plugins') ?>
        </p>
    </a>
</li>

<!--
    this is pretty ugly but we need to spawn some empty elements so that the
    last row of the flex grid won't be messed up if the boxes don't line up
-->
    <li></li><li></li><li></li>
    <li></li><li></li><li></li>
</ul>
