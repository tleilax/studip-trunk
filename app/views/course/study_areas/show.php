<form action="<?= $controller->url_for('course/study_areas/save/' . $course->id) ?>" method="post">
    <?= $tree ?>

    <? if (Request::isXhr()) : ?>
        <div data-dialog-button>
            <?= Studip\Button::createAccept(_('Speichern')) ?>
        </div>
    <? endif ?>
</form>