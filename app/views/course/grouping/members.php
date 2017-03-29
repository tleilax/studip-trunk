<?php foreach ($courses as $child) : ?>
    <article class="studip toggle <?= ContentBoxHelper::classes($child->id) ?>" id="<?= $child->id ?>">
        <header>
            <h1>
                <a href="<?= ContentBoxHelper::href($child->id, array('contentbox_type' => 'news')) ?>" data-course-id="<?= $child->id ?>"
                    data-get-members-url="<?= $controller->url_for('course/grouping/child_course_members', $child->id) ?>"
                    class="get-course-members">
                    <?= Icon::create('seminar', 'clickable')->asImg(); ?>
                    <?= htmlReady($child->getFullname()) ?>
                </a>
            </h1>
            <span class="actions">
                <?php $actionMenu = ActionMenu::get() ?>
                <?php $actionMenu->addLink(URLHelper::getLink('dispatch.php/messages/write',
                    ['filter' => 'all',
                        'course_id' => $child->id,
                        'default_subject' => '[' . $child->getFullname() . ']']),
                    _('Nachricht schicken'),
                    Icon::create('mail', 'clickable', ['title' => _('Nachricht schicken')]),
                    ['data-dialog' => 'size=auto']) ?>
                <?= $actionMenu->render() ?>
            </span>
        </header>
        <section>
            <article id="course-members-<?= $child->id ?>">
            </article>
        </section>
    </article>
<?php endforeach ?>
<?php if (count($parentOnly) > 0) : ?>
    <article class="studip toggle <?= ContentBoxHelper::classes($course->id) ?>" id="<?= $course->id ?>">
        <header>
            <h1>
                <a href="<?= ContentBoxHelper::href($course->id, array('contentbox_type' => 'news')) ?>" data-course-id="<?= $course->id ?>"
                    data-get-members-url="<?= $controller->url_for('course/grouping/parent_only_members') ?>"
                    class="get-course-members">
                    <?= Icon::create('seminar', 'clickable')->asImg(); ?>
                    <?= _('In keiner Unterveranstaltung') ?>
                </a>
            </h1>
        </header>
        <section>
            <article id="course-members-<?= $course->id ?>">
            </article>
        </section>
    </article>
<?php endif ?>
