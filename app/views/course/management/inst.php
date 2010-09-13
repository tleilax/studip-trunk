<?
/* * * * * * * * * * * * *
 * * * I N F O B O X * * *
 * * * * * * * * * * * * */
$infobox['picture'] = 'infobox/administration.jpg';
$infobox['content'][] = array(
    'kategorie' => _("Information"),
    'eintrag'   => array(
        array(
            'text' => _("Als Mitarbeiter Ihrer Einrichtung k�nnen Sie f�r diese Inhalte in mehreren Kategorien bereitstellen. Inhalte in Ihrer Einrichtung k�nnen von allen Stud.IP-Nutzern abgerufen werden."),
            "icon" => "icons/16/black/info.png"
         )
     )
);

?>

<div style="padding-left:0.5em; background-color: white; width: 100%">
    <h1 class="smashbox_kategorie"><?=_("Weitere Inhaltselemente")?></h1>

  <div class="smashbox_stripe">
      <div style="margin-left: 1.5em;">

          <a class="click_me" href="<?= UrlHelper::getLink('admin_news.php?view=news_inst') ?>">
          <div>
              <span class="click_head"><?=_("Ank�ndigungen")?></span>
              <p><?=_("Erstellen Sie Ank�ndigungen f�r ihre Einrichtung und bearbeiten Sie laufende Ank�ndigungen.")?></p>
              </div>
          </a>

          <? if (get_config('VOTE_ENABLE')) : ?>
          <a class="click_me" href="<?= UrlHelper::getLink('admin_vote.php?view=vote_inst') ?>">
          <div>
              <span class="click_head"><?=_("Umfragen und Tests")?></span>
              <p><?=_("Erstellen Sie in Ihre Einrichtung einfache Umfragen und Tests.")?></p>
              </div>
          </a>

         <a class="click_me" href="<?= UrlHelper::getLink('admin_evaluation.php?view=eval_inst') ?>">
            <div>
                  <span class="click_head"><?=_("Evaluationen")?></span>
                  <p><?=_("Richten Sie f�r Ihre Einrichtung eine �ffentliche Umfragen ein.")?></p>
            </div>
        </a>
        <? endif ?>

        </div>
        <br style="clear: both;">
    </div>
</div>
