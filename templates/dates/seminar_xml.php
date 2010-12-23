<?
$turnus_list = array(
    0 => _("w�chentlich"),
    1 => _("zweiw�chentlich"),
    2 => _("dreiw�chentlich")
);

$output = array();

if (is_array($dates['regular']['turnus_data'])) foreach ($dates['regular']['turnus_data'] as $cycle) :
?>
<raumzeit>
    <datum><?= $turnus_list[$cycle['cycle']] ?></datum>
    <wochentag><?= getWeekDay($cycle['day']) ?></wochentag>
    <zeit><?= $cycle['start_hour'] ?>:<?= $cycle['start_minute'] ?>-<?= $cycle['end_hour'] ?>:<?= $cycle['end_minute'] ?></zeit>
    <raum>
        <gebucht><?= htmlspecialchars(implode(', ', getPlainRooms($cycle['assigned_rooms']))) ?></gebucht>
        <freitext><?= htmlspecialchars(implode(', ', array_keys($cycle['freetext_rooms']))) ?></freitext>
    </raum>
</raumzeit>
<? endforeach ?>
<? $presence_types = getPresenceTypes(); ?>
<? if (is_array($dates['irregular'])) foreach ($dates['irregular'] as $date) : ?>
<raumzeit>
    <datum><?= date('d.m.Y', $date['start_time']) ?></datum>
    <wochentag><?= getWeekDay(date('w', $date['start_time'])) ?></wochentag>
    <zeit><?= date('H:i', $date['start_time']) ?>-<?= date('H:i', $date['end_time']) ?></zeit>
    <raum>
        <gebucht><?= htmlspecialchars(implode(', ', getPlainRooms(array($date['resource_id'] => 1)))) ?></gebucht>
        <freitext><?= htmlspecialchars($date['raum']) ?></freitext>
    </raum>
</raumzeit>
<? endforeach ?>
