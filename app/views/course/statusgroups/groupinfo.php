<header>
    <h1><?= sprintf(_('Informationen zu %s'), htmlReady($group->name)) ?></h1>
</header>
<section>
    <p>
        <?= $group->size > 0 ?
            formatReady(sprintf(_('Diese Gruppe ist auf **%u** Mitglieder beschr�nkt.'), $group->size)) :
            formatReady(_('Die Gr��e dieser Gruppe ist **nicht beschr�nkt**.')) ?>
    </p>

    <?php if ($group->selfassign) : ?>
        <?php if ($group->selfassign == 1) : ?>
            <p>
                <?= _('Die Teilnehmenden dieser Veranstaltung k�nnen sich ' .
                    'selbst in beliebig viele der Gruppen eintragen, bei denen ' .
                    'kein Exklusiveintrag aktiviert ist.') ?>
            </p>
        <?php elseif ($group->selfassign == 2) : ?>
            <p>
                <?= _('Die Teilnehmenden dieser Veranstaltung k�nnen sich ' .
                'in genau einer der Gruppen eintragen, bei denen der ' .
                'Exklusiveintrag aktiviert ist.') ?>
            </p>
        <?php endif ?>
        <?php if ($group->selfassign_start && $group->selfassign_end) : ?>
            <p>
                <?= formatReady(sprintf(_('Der Eintrag ist m�glich **von %s bis %s**.'),
                    date('d.m.Y H:i', $group->selfassign_start),
                    date('d.m.Y H:i', $group->selfassign_end))) ?>
            </p>
        <?php elseif ($group->selfassign_start && !$group->selfassign_end) : ?>
            <p>
                <?= formatReady(sprintf(_('Der Eintrag ist m�glich **ab %s**.'),
                    date('d.m.Y H:i', $group->selfassign_start))) ?>
            </p>
        <?php elseif (!$group->selfassign_start && $group->selfassign_end) : ?>
            <p>
                <?= formatReady(sprintf(_('Der Eintrag ist m�glich **bis %s**.'),
                    date('d.m.Y H:i', $group->selfassign_end))) ?>
            </p>
        <?php endif ?>
    <?php endif ?>

    <?php if ($folder = $group->getFolder()) : ?>
        <p>
            <?= formatReady(sprintf(_('Zu dieser Gruppe geh�rt ein [Dateiordner]%s .'),
                URLHelper::getURL('folder.php#anker', array(
                    'cid' => $course_id,
                    'data[cmd]' => 'tree',
                    'open' => $folder->id
                )))) ?>
        </p>
    <?php endif ?>

    <?php if ($group->dates->count() > 0) : ?>
        <p>
            <?= _('Zugeordnete Termine:') ?>
            <ul>
                <?php foreach ($group->dates as $d) : ?>
                    <li>
                        <?= htmlReady($d->toString()) ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </p>
    <?php endif ?>

    <?php if ($topics = $group->findTopics()) : ?>
        <p>
            <?= _('Zugeordnete Themen:') ?>
            <ul>
            <?php foreach ($topics as $t) : ?>
                <li><?= htmlReady($t->title) ?></li>
            <?php endforeach ?>
            </ul>
        </p>
    <?php endif ?>

    <?php if ($lecturers = $group->findLecturers()) : ?>
        <p>
            <?= sprintf(_('Zugeordnete %s:'), get_title_for_status('dozent', 2)) ?>
            <ul>
                <?php foreach ($lecturers as $l) : ?>
                    <li><?= htmlReady($l->getFullname()) ?></li>
                <?php endforeach ?>
            </ul>
        </p>
    <?php endif ?>
</section>

<footer data-dialog-button>
    <?= Studip\LinkButton::createCancel(_('Schlie�en'),
        $controller->url_for('course/statusgroups')) ?>
</footer>
