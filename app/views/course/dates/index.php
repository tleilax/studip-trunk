<?php
    $lastSemester = null;
    $allSemesters = array();
    foreach ($dates as $key => $date) {
        $currentSemester = Semester::findByTimestamp($date['date']);
        if ($currentSemester && (
            !$lastSemester ||
            $currentSemester->getId() !== $lastSemester->getId()
        )) {
            $allSemesters[] = $currentSemester;
            $lastSemester = $currentSemester;
        }
    }
    $lostDateKeys = array();

    if (!count($dates)) {
        PageLayout::postMessage(
            MessageBox::info(_('Keine Termine vorhanden'))
        );
    }
?>

<? foreach ($allSemesters as $semester): ?>
<table class="dates default sortable-table" data-sortlist="[[0, 0]]" data-table-id="<?= htmlReady($semester->id) ?>">
    <caption><?= htmlReady($semester['name']) ?></caption>
        <colgroup class="hidden-small-down">
        <? if (count($course->statusgruppen) > 0): ?>
            <col>
            <col width="10%">
            <col width="20%">
            <col width="20%">
            <col width="20%">
            <col width="10%">
        <? else: ?>
            <col>
            <col width="10%">
            <col width="30%">
            <col width="30%">
            <col width="10%">
        <? endif; ?>
        <? if ($has_access): ?>
            <col width="48px">
        <? endif; ?>
        </colgroup>
    <thead>
        <tr>
            <th data-sort="htmldata"><?= _('Zeit') ?></th>
            <th data-sort="text" class="hidden-small-down"><?= _('Typ') ?></th>
        <? if (count($course->statusgruppen)): ?>
            <th data-sort="text" class="hidden-small-down"><?= _('Sichtbarkeit') ?></th>
        <? endif; ?>
            <th data-sort="text" class="hidden-small-down"><?= _('Thema') ?></th>
            <th data-sort="text"><?= _('Raum') ?></th>
            <th data-sort="htmldata"><?= _('Dateien')?></th>
        <? if ($has_access): ?>
            <th datasort="false"></th>
        <? endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php
        // print dates
        foreach ($dates as $key => $date) {
            $dateSemester = Semester::findByTimestamp($date['date']);
            if ($dateSemester && $semester->getId() === $dateSemester->getId()) {
                if (is_null($is_next_date) && $date['end_time'] >= time() && !is_a($date, 'CourseExDate')) {
                    $is_next_date = $key;
                }
                $partial = $date instanceof CourseExDate ? '_date_row-exdate' : '_date_row';
                echo $this->render_partial("course/dates/{$partial}.php", [
                    'date'         => $date,
                    'is_next_date' => $is_next_date === $key,
                    'course'       => $course,
                    'has_acces'    => $has_access,
                ]);
            } elseif (!$dateSemester && !in_array($key, $lostDateKeys)) {
                $lostDateKeys[] = $key;
            }
        }
    ?>
    </tbody>
</table>
<? endforeach; ?>

<? if (count($lostDateKeys) > 0): ?>
<table class="dates default sortable-table" data-sortlist="[[0, 0]]" data-table-id="none">
    <caption><?= _('Ohne Semester') ?></caption>
    <thead>
    <tr>
        <th data-sort="htmldata"><?= _('Zeit') ?></th>
        <th data-sort="text" class="hidden-small-down"><?= _('Typ') ?></th>
        <? if (count($course->statusgruppen)): ?>
            <th data-sort="text" class="hidden-small-down"><?= _('Sichtbarkeit') ?></th>
        <? endif; ?>
        <th data-sort="text" class="hidden-small-down"><?= _('Thema') ?></th>
        <th data-sort="text"><?= _('Raum') ?></th>
        <th data-sort="htmldata"><?= _('Dateien') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
        foreach ($lostDateKeys as $key) {
            $date = $dates[$key];
            echo $this->render_partial(
                'course/dates/_date_row.php',
                compact('date', 'dates', 'key')
            );
        }
    ?>
    </tbody>
</table>
<? endif; ?>
