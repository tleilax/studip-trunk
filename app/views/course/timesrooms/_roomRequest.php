<form class="studip_form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Raumanfrage f�r die gesamte Veranstaltung') ?>
            </h1>
            
            <nav>
                <span>
                    <?=Assets::img('icons/blue/info-circle', array('title' => _('Hier k�nnen Sie f�r die gesamte Veranstaltung, also f�r alle regelm��igen und unregelm��igen Termine, '
                                    . 'eine Raumanfrage erstellen. Um f�r einen einzelnen Termin eine Raumanfrage zu erstellen, '
                                    . 'klappen Sie diesen auf und w�hlen dort Raumanfrage erstellen')))?>
                </span>
                <span>
                        <a class="link-add" href="<?=$controller->link_for('course/room_requests/new/'.$course->id)?>" data-dialog
                           title="<?=_('Neue Raumanfrage f�r die Veranstaltung erstellen')?>">
                        <?=_('Neue Raumanfrage')?>
                    </a>
                </span>
                
            </nav>
        </header>
        <section>
            <? $roomRequests_state = $course->getRoomRequestStatus(); ?>
            <? $roomRequests = $course->getRoomRequestInfo(); ?>
            <? if ($roomRequests_state && ($roomRequests_state == 'open' || $roomRequests_state == 'pending')) : ?>
                <?= MessageBox::info(_('F�r diese Veranstaltung liegt eine noch offene Raumanfrage vor.'), array(nl2br(htmlReady($roomRequests)))) ?>
                <?= Studip\LinkButton::create(_('Raumanfrage bearbeiten'), URLHelper::getURL('dispatch.php/course/room_requests/edit/' . $course->getId(), array('request_id' => RoomRequest::existsByCourse($id))), array()) ?>
                <?= Studip\LinkButton::create(_('Raumanfrage zur�ckziehen'), URLHelper::getURL('dispatch.php/course/room_requests/edit/' . $course->getId(), array('request_id' => RoomRequest::existsByCourse($id))), array()) ?>
            <? elseif ($roomRequests_state && $roomRequests_state == 'declined') : ?>
                <?= MessageBox::error(_('Die Raumanfrage f�r diese Veranstaltung wurde abgelehnt!'), array(nl2br(htmlReady($roomRequests)))) ?>
            <? else : ?>
                <?= MessageBox::info(_('Keine Raumanfrage vorhanden')) ?>
            <? endif; ?>    
        </section>
    </section>
</form>