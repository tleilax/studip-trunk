<html>
<head>
<?=Assets::stylesheet('style.css')?>
</head>
<body>
<center>
<table style="text-align:left; width:700px; min-width:700px; max-width:700px; background-color:white;">
  <tr>
    <td style="height:90px; min-height:90px; max-height:90px;">
      <img src="<?=$GLOBALS['ASSETS_URL']?>images/locale/<?=$lang?>/LC_PICTURES/mail_header_notification.png">
    </td>
  </tr>
  <tr>
    <td>
      <span style="font-size:12px;"><?=_("Sie erhalten hiermit in regelm��igen Abst�nden Informationen �ber Neuigkeiten und �nderungen in Ihren abonnierten Veranstaltungen.")?><br/><br/>
        <?=_("�ber welche Inhalte und in welchem Format Sie informiert werden wollen, k�nnen Sie hier einstellen:")?><br/>
        <a href="<?=URLHelper::getLink('sem_notification.php')?>"><?=URLHelper::getLink('sem_notification.php')?></a><br/><br/>
      </span>
      <table border=0 style="width:700px;">
<? foreach ($news as $sem_titel=>$data) : ?>
        <tr>
          <td colspan="2" class="topic" style="font-size:14px; font-weight:bold;">
            <a style="text-decoration:none; color:white;" href="<?=URLHelper::getLink('seminar_main.php?auswahl='.$data[0]['range_id'])?>"><?=htmlReady($sem_titel)?><?=(($semester = get_semester($n['range_id'])) ? ' ('.$semester.')' : '')?></a>
          </td>
        </tr>
<? foreach ($data as $n) : ?>
<? $cssSw->switchClass(); ?>
        <tr>
          <td class="<?=$cssSw->getClass()?>" style="font-size:12px;">
            <a style="text-decoration:none;" href="<?=$n['url']?>"><?=htmlReady($n['txt'])?></a>
          </td>
          <td class="<?=$cssSw->getClass()?>" style="width:25px; text-align:center;">
            <a href="<?=$n['url']?>"><?=Assets::img($n['icon'],array('alt'=>htmlReady($n['txt']),'title'=>htmlReady($n['txt'])))?></a>
          </td>
        </tr>
<? endforeach ?>
<? endforeach ?>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <hr>
      <span style="font-size:10px;"><?=_("Diese Nachricht wurde automatisch vom Stud.IP-System generiert. Sie k�nnen darauf nicht antworten.")?></span>
    </td>
  </tr>
</table>
</center>
</body>
</html>
