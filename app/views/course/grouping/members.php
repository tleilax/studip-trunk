<?php foreach ($courses as $course) : ?>
    <article class="studip toggle <?= ContentBoxHelper::classes($course->id) ?>" id="<?= $course->id ?>">
        <header>
            <h1>
                <a href="<?= ContentBoxHelper::href($new->id, array('contentbox_type' => 'news')) ?>" data-course-id="<?= $course->id ?>"
                    data-get-members-url="<?= $controller->url_for('course/grouping/child_course_members', $course->id) ?>"
                    class="get-course-members">
                    <?= Icon::create('seminar', 'clickable')->asImg(); ?>
                    <?= htmlReady($course->getFullname()); ?>
                </a>
            </h1>
        </header>
        <section>
            <article id="course-members-<?= $course->id ?>">
            </article>
        </section>
    </article>
<?php endforeach ?>
