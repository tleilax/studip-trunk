<h1><?= _("Veranstaltungsanmeldung")?></h1>
<?= $admission_error ?>
<? if ($courseset_message) : ?>
<p>
    <?= $courseset_message ?>
</p>
<? endif ?>
<? if ($admission_form) : ?>
    <form action="<?= $controller->link_for('apply/' . $course_id) ?>" method="post">
        <?= $admission_form ?>
        <div>
        <?= Studip\Button::create(_("OK"), 'apply') ?>
        </div>
    </form>
<? endif ?>