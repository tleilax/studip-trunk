<?
# Lifter010: TODO
?>
 <a href="<?= URLHelper::getLink("calendar.php?caluser=self&cmd=showweek&atime=" . $start) ?>">
    <img src="<?= Assets::image_path('icons/16/blue/schedule.png') ?>"
      <?= tooltip(sprintf(_("Zum %s in den pers�nlichen Terminkalender springen"), date("d.m", $start))) ?>>
 </a>