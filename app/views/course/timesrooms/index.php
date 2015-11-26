<? if (!Request::isXhr()): ?>
    <?= $this->render_partial('course/timesrooms/editSemester.php') ?>
<? else : ?>
    <?= $this->render_partial('course/timesrooms/_select_semester_range.php') ?>
<? endif ?>

<? if ($show['regular']) : ?>
    <!--Regelmäßige Termine-->
    <?= $this->render_partial('course/timesrooms/_regularEvents.php', array()) ?>
<? endif; ?>

<? if ($show['irregular']) : ?>
    <!--Unregelmäßige Termine-->
    <?= $this->render_partial('course/timesrooms/_irregularEvents', array()) ?>
<? endif; ?>

<? if ($show['roomRequest']) : ?>
    <!--Raumanfrage-->
    <?= $this->render_partial('course/timesrooms/_roomRequest.php', array()) ?>
<? endif; ?>
