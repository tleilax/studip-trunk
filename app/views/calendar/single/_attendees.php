<? use Studip\Button, Studip\LinkButton; ?>
<? $show_members = $event->attendees->findOneBy('range_id',
        $calendar->getRangeId(), '!=') ?>
<? // Entkommentieren, wenn Mitglieder eines Termins sichtbar sein
   // sollen, auch wenn man nicht selbst Mitglied ist und ... ?>
<? // $show_members_visiter = $event->attendees->findOneBy('range_id', $GLOBALS['user']->id) ?>
<? // folgende Zeile auskommentieren (siehe _attendees.php). ?>
<? $show_members_visiter = true; ?>
<? if ($show_members && $show_members_visiter) : ?>
    <? $group_status = array(
        CalendarEvent::PARTSTAT_TENTATIVE => _('abwartend'),
        CalendarEvent::PARTSTAT_ACCEPTED => _('angenommen'),
        CalendarEvent::PARTSTAT_DECLINED => _('abgelehnt'),
        CalendarEvent::PARTSTAT_DELEGATED => _('angenommen (keine Teilnahme)'),
        CalendarEvent::PARTSTAT_NEEDS_ACTION => _('')) ?>
    <div>
        <b><?= _('Teilnehmer:') ?></b>
        <?= implode(', ', $event->attendees->map(
            function ($att) use ($event, $group_status) {
                $profil_link = ObjectdisplayHelper::link($att->user);
                if ($event->havePermission(Event::PERMISSION_OWN, $att->user->getId())) {
                    $profil_link .= ' (' . _('Organisator') . ')';
                } else {
                    if ($group_status[$att->group_status]) {
                        $profil_link .= ' (' . $group_status[$att->group_status] . ')';
                    }
                }
                return $profil_link;
            })); ?>
    </div>
<? endif; ?>