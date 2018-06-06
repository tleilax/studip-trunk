<?
# Lifter010: TODO
?>
<html>
<head>
  <?= Assets::stylesheet('studip-base.css') ?>
</head>
<body>
  <div style="background-color: white; margin: auto; max-width: 700px; padding: 4px;">
    <?= Assets::img("locale/$lang/LC_PICTURES/mail_header.png") ?>
    <p>
      <?= formatReady($message, true, true) ?>
    </p>
    <? if (isset($attachments) && count($attachments)) : ?>
    <hr>
    <span class="minor">
      <?=_("Dateianhänge:")?>
        <ul>
        <? foreach($attachments as $attachment) : ?>
       	  <li>
            <a href="<?= $attachment->getDownloadURL() ?>"><?= htmlReady($attachment->name . ' (' . relsize($attachment->file->size, false) . ')') ?></a>
          </li>
        <? endforeach;?>
     	</ul>
     </span>
  	<? endif;?>
    <hr>
    <span class="minor">
      <?= sprintf(_("Diese E-Mail ist eine Kopie einer systeminternen Nachricht, die in Stud.IP an %s versendet wurde."), htmlReady($rec_fullname)) ?><br>
      <?= sprintf(_("Sie erreichen Stud.IP unter %s"), "<a href=\"" . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "\">" . $GLOBALS['ABSOLUTE_URI_STUDIP'] . "</a>") ?>
    </span>
  </div>
</body>
</html>
