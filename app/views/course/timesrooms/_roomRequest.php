<form class="studip_form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Raumanfrage für die gesamte Veranstaltung') ?>
            </h1>

            <nav>
                <?= tooltipIcon(_('Hier können Sie für die gesamte Veranstaltung, also für alle regelmäßigen und unregelmäßigen Termine, '
                                  . 'eine Raumanfrage erstellen. Um für einen einzelnen Termin eine Raumanfrage zu erstellen, '
                                  . 'klappen Sie diesen auf und wählen dort Raumanfrage erstellen')) ?>
                <a class="link-add" href="<?= $controller->link_for('course/room_requests/new/' . $course->id) ?>"
                   data-dialog
                   title="<?= _('Neue Raumanfrage für die Veranstaltung erstellen') ?>">
                    <?= _('Neue Raumanfrage') ?>
                </a>
            </nav>
        </header>
        <section>
            <? $roomRequests_state = $course->getRoomRequestStatus(); ?>
            <? $roomRequests = $course->getRoomRequestInfo(); ?>
            <? if ($roomRequests_state && ($roomRequests_state == 'open' || $roomRequests_state == 'pending')) : ?>
                <?= MessageBox::info(_('Für diese Veranstaltung liegt eine noch offene Raumanfrage vor.'), array(nl2br(htmlReady($roomRequests)))) ?>
                <?= Studip\LinkButton::create(_('Raumanfrage bearbeiten'), URLHelper::getURL('dispatch.php/course/room_requests/edit/' . $course->getId(), array('request_id' => RoomRequest::existsByCourse($id))), array()) ?>
                <?= Studip\LinkButton::create(_('Raumanfrage zurückziehen'), URLHelper::getURL('dispatch.php/course/room_requests/edit/' . $course->getId(), array('request_id' => RoomRequest::existsByCourse($id))), array()) ?>
            <? elseif ($roomRequests_state && $roomRequests_state == 'declined') : ?>
                <?= MessageBox::error(_('Die Raumanfrage für diese Veranstaltung wurde abgelehnt!'), array(nl2br(htmlReady($roomRequests)))) ?>
            <? else : ?>
                <p class="text-center">
                    <strong><?= _('Keine Raumanfrage vorhanden') ?></strong>
                </p>
            <? endif; ?>
        </section>
        <footer>
            <a class="link-add" href="<?= $controller->link_for('course/room_requests/new/' . $course->id) ?>"
               data-dialog
               title="<?= _('Neue Raumanfrage für die Veranstaltung erstellen') ?>">
                <?= _('Neue Raumanfrage') ?>
            </a>
        </footer>
    </section>
</form>