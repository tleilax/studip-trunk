<h1>
    <?= _('Unterveranstaltungen') ?>
</h1>
<?php if (count($children) > 0) : ?>
    <ul>
        <?php foreach ($children as $child) : ?>
            <li>
                <a href="<?= $controller->url_for('course/management', array('cid' => $child->id)) ?>">
                    <?= $child->getFullname() ?>
                </a>
                <a href="<?= $controller->url_for('course/grouping/unassign_child', $child->id) ?>" data-confirm="<?=
                    _('Wollen Sie die Zuordnung dieser Unterveranstaltung wirklich entfernen?')?>">
                    <?= Icon::create('trash', 'clickable')->asImg() ?>
                </a>
            </li>
        <?php endforeach ?>
    </ul>
<?php else : ?>
    <p>
        <?= _('Diese Veranstaltung hat keine Unterveranstaltungen.') ?>
    </p>
    <form class="default" action="<?= $controller->url_for('course/grouping/assign_child') ?>">
        <section>
            <?= $search->render() ?>
        </section>
        <footer>
            <?= Studip\Button::createAccept(_('Unterveranstaltung zuordnen'), 'assign') ?>
        </footer>
    </form>
<?php endif ?>
