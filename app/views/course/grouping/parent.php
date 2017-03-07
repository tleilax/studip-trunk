<h1>
    <?= _('Zuordnung zu Hauptveranstaltung') ?>
</h1>
<?php if ($parent) : ?>
    <form class="default" action="<?= $controller->url_for('course/grouping/unassign_parent') ?>">
        <section>
            <p>
                <?= sprintf(_('Diese Veranstaltung gehört zur Hauptveranstaltung %s.'),
                    '<a href="' . URLHelper::getURL('dispatch.php/course/grouping/children', array('cid' => $parent->id)) .
                    '" title="' . $parent->getFullname() . '">' . $parent->getFullname() . '</a>') ?>
            </p>
        </section>
        <footer>
            <?= Studip\Button::createCancel(_('Zuordnung aufheben'), 'unassign') ?>
        </footer>
    </form>
<?php else : ?>
    <p>
        <?= _('Diese Veranstaltung ist noch keiner Hauptveranstaltung zugeordnet.') ?>
    </p>
    <form class="default" action="<?= $controller->url_for('course/grouping/assign_parent') ?>">
        <section>
            <?= $search->render() ?>
        </section>
        <footer>
            <?= Studip\Button::createAccept(_('Zuordnen'), 'assign') ?>
        </footer>
    </form>
<?php endif ?>
