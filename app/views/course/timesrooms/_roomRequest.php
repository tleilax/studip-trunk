<form class="studip_form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Raumanfrage für die gesamte Veranstaltung') ?>
            </h1>
            
            <nav>
                <span>
                    <?=Assets::img('icons/blue/info-circle', array('title' => _('Hier können Sie für die gesamte Veranstaltung, also für alle regelmäßigen und unregelmäßigen Termine, '
                                    . 'eine Raumanfrage erstellen. Um für einen einzelnen Termin eine Raumanfrage zu erstellen, '
                                    . 'klappen Sie diesen auf und wählen dort Raumanfrage erstellen')))?>
                </span>
                <span>
                        <a class="link-add" href="<?=$controller->link_for('course/room_requests/new/'.$course->id)?>" data-dialog
                           title="<?=_('Neue Raumanfrage für die Veranstaltung erstellen')?>">
                        <?=_('Neue Raumanfrage')?>
                    </a>
                </span>
                
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
                <?= MessageBox::info(_('Keine Raumanfrage vorhanden')) ?>
            <? endif; ?>    
        </section>
    </section>
</form>