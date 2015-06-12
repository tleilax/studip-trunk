<form action="<?= $controller->url_for('course/study_areas/save/'.$course->id)?>" method="post">
    <?= $tree?>

    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
    </div>
</form>