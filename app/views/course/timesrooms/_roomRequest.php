<form class="studip_form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Raumanfrage f�r die gesamte Veranstaltung') ?>
            </h1>

            <nav>
                <?= tooltipIcon(_('Hier k�nnen Sie f�r die gesamte Veranstaltung, also f�r alle regelm��igen und unregelm��igen Termine, '
                                  . 'eine Raumanfrage erstellen.')) ?>
                <a class="link-add" href="<?= URLHelper::getURL('dispatch.php/course/room_requests/new/' . $course->id, array('new_room_request_type' => 'course', 'origin' => 'course_timesrooms')) ?>"
                   data-dialog="size=big"
                   title="<?= _('Neue Raumanfrage f�r die Veranstaltung erstellen') ?>">
                    <?= _('Neue Raumanfrage') ?>
                </a>
            </nav>
        </header>
        <section>
            <? $roomRequests = RoomRequest::findBySeminar_id($course->getId()); ?>
            <? if (!empty($roomRequests)) : ?>
                <? $open = 0; ?>
                <? $declined = 0; ?>
                <? foreach ($roomRequests as $request) :?>
                    <? if ($request->closed == 0 || $request->closed == 1 ) : ?>
                        <? $open++; ?>
                    <? elseif ($request->closed == 3) : ?>
                        <? $declined++; ?>
                    <? endif; ?>
                <? endforeach;?>    
                
                <? if ($open > 0) : ?>
                    <?= MessageBox::info(sprintf(ngettext('F�r diese Veranstaltung liegt eine offene Raumanfrage vor.',
                            'F�r diese Veranstaltung liegen %s offene Raumanfragen vor', $open), $open))?>
                <? endif; ?>
                
                <? if ($declined > 0 ) : ?>
                    <?= MessageBox::error(sprintf(ngettext('Es wurde eine Raumanfrage f�r diese Veranstaltung abgelehnt!',
                            'Es wurden %s Raumanfragen f�r diese Veranstaltung abgelehnt', $declined), $declined)) ?>
                <? endif; ?>
                <?= Studip\LinkButton::create(_('Raumanfragen anzeigen'),
                    URLHelper::getURL('dispatch.php/course/room_requests/index/' . $course->getId())) ?>
                
            <? else : ?>
                <p class="text-center">
                    <strong><?= _('Keine Raumanfrage vorhanden') ?></strong>
                </p>
            <? endif; ?>
        </section>
    </section>
</form>