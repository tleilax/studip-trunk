 <a href="<?= URLHelper::getLink("calendar.php?cmd=showweek&atime=" . $start) ?>">
    <img style="vertical-align:bottom" src="<?= Assets::image_path('icons/16/blue/schedule.png') ?>"
      <?= tooltip(sprintf(_("Zum %s in den pers�nlichen Terminkalender springen"), date("d.m", $start))) ?>>
 </a>