<?php
foreach ($this->courses as $course) {
    $title = $via_ajax ? utf8_encode($course['Name']) : $course['Name'];
    if ($course['VeranstaltungsNummer']) {
        $title = $course['VeranstaltungsNummer'].' | '.$title;
    } 
?>
<input type="checkbox" name="courses[]" value="<?= $course['seminar_id'] ?>"/> <?= $title ?><br/>
<?php } ?>