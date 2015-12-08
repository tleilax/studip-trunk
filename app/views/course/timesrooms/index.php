<? if (Request::isXhr()): ?>
    <?= $this->render_partial('course/timesrooms/_select_semester_range.php') ?>
<? endif ?>

<? if ($show['regular']) : ?>
    <!--Regelmäßige Termine-->
    <?= $this->render_partial('course/timesrooms/_regularEvents.php') ?>
<? endif; ?>

<? if ($show['irregular']) : ?>
    <!--Unregelmäßige Termine-->
    <?= $this->render_partial('course/timesrooms/_irregularEvents') ?>
<? endif; ?>

<? if ($show['roomRequest']) : ?>
    <!--Raumanfrage-->
    <?= $this->render_partial('course/timesrooms/_roomRequest.php') ?>
<? endif; ?>
