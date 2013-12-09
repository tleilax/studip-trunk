<h1><?= htmlReady($course_name) ?></h1>
<?= $admission_error ?>
<? if ($courseset_message) : ?>
<p>
    <?= $courseset_message ?>
</p>
<? endif ?>
<? if ($admission_form) : ?>
    <form name="apply_admission" action="<?= $controller->link_for('/apply/' . $course_id) ?>" method="post">
        <?= $admission_form ?>
        <div>
        <?= Studip\Button::create(_("OK"), 'apply') ?>
        </div>
        <?= CSRFProtection::tokenTag() ?>
    </form>
<? endif ?>
<? if ($priocourses) : ?>
    <form name="claim_admission" action="<?= $controller->link_for('/claim/' . $course_id) ?>" method="post">
    <? if (is_array($priocourses)): ?>
        <?= $this->render_partial('course/enrolment/_priocourses.php') ?>
    <? else : ?>
        <input type="checkbox" name="courseset_claimed" id="courseset_claimed" value="1" <?= ($already_claimed ? 'checked' : '') ?>>
        <label for="courseset_claimed" style="font-weight:bold"><?=_("Zur Platzverteilung anmelden")?></label>
        &nbsp;(<?= sprintf(_("max. Teilnehmeranzahl: %s / Anzahl der Anmeldungen: %s"), $priocourses->admission_turnout, $num_claiming) ?>)
    <? endif ?>
    <div>
    <?= Studip\Button::create(_("OK"), 'claim') ?>
    </div>
    <?= CSRFProtection::tokenTag() ?>
    </form>
<? endif ?>