<?= $admission_error ?>
<? if ($courseset_message) : ?>
<p>
    <?= $courseset_message ?>
</p>
<? endif ?>
<? foreach(PageLayout::getMessages() as $pm) : ?>
    <?= $pm ?>
<? endforeach; ?>
<? if ($admission_form) : ?>
    <form name="apply_admission" action="<?= $controller->link_for('/apply/' . $course_id) ?>" method="post">
        <?= $admission_form ?>
        <div data-dialog-button>
        <?= Studip\Button::createAccept(_("OK"), 'apply', array('data-dialog' => 'size=big')) ?>
        <?= Studip\Button::createCancel(_("Abbrechen"), 'cancel') ?>
        </div>
        <?= CSRFProtection::tokenTag() ?>
    </form>
<? endif ?>
<? if ($priocourses) : ?>
    <form name="claim_admission" action="<?= $controller->link_for('/claim/' . $course_id) ?>" method="post" class="default">
    <? if (is_array($priocourses)): ?>
        <?= $this->render_partial('course/enrolment/_priocourses.php') ?>
        <div data-dialog-button>
            <?= Studip\Button::createAccept(_("Speichern"), 'claim', array('data-dialog' => 'size=big')) ?>
        </div>
    <? else : ?>
        <? if (!$already_claimed) :?>
                <?= \Studip\Button::createAccept(_("Zur Platzverteilung anmelden"), 'claim', array('data-dialog' => 'size=big')); ?>
        <? else : ?>
                <?= \Studip\Button::createCancel(_("Von der Platzverteilung abmelden"), 'claim', array('data-dialog' => 'size=big')); ?>
        <? endif ?>
        <input type="hidden" name="courseset_claimed" value="<?= ($already_claimed ? '0' : '1') ?>" >
        <div>
        (<?= sprintf(_("max. Teilnehmendenanzahl: %s / Anzahl der Anmeldungen: %s"), $priocourses->admission_turnout, $num_claiming) ?>)
        </div>
    <? endif ?>
    <div data-dialog-button>
    <?= Studip\Button::createCancel(_("Schließen"), 'cancel') ?>
    </div>
    <?= CSRFProtection::tokenTag() ?>
    </form>
<? endif ?>
<? if (!$priocourses && !$admission_form) :?>
    <? if (!$enrol_user): ?>
        <div data-dialog-button>
            <?=Studip\LinkButton::createAccept(_('OK'), URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $course_id])) ?>
        </div>
    <? elseif ($confimed): ?>
        <div data-dialog-button>
            <?=Studip\LinkButton::createAccept(_('Zur Veranstaltung'), URLHelper::getLink('seminar_main.php', ['auswahl' => $course_id])) ?>
        </div>
    <? endif; ?>
<? endif ?>
<script>STUDIP.enrollment();</script>
