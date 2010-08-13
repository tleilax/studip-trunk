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
    <h2 class="smashbox_kategorie"><?=_("Grundeinstellungen");?></h2>

  <div class="smashbox_stripe">
    <div style="margin-left: 1.5em;">
     <a class="click_me" href="<?= UrlHelper::getLink('admin_institut.php?section=details') ?>">
      <div>
          <span class="click_head"><?=_("Grunddaten");?></span>
          <p><?=_("Pr�fen und Bearbeiten Sie in diesem Verwaltungsbereich die Grundeinstellungen dieser Veranstaltung.");?></p>
      </div>
      </a>

    <a class="click_me" href="<?= UrlHelper::getLink('admin_modules.php?section=modules') ?>">
    <div>
      <span class="click_head"><?=_("Inhaltselemente");?></span>
      <p><?=_("Sie k�nnen mit dieser Funktionen bestimmte Inhalte wie etwa Forum, Dateibereich oder Wiki ein- oder ausschalten und weitere Inhalte aktivieren.");?></p>
      </div>
     </a>
    </div>

    <br style="clear: both;">

    <h2 class="smashbox_kategorie"><?=_("Weitere Inhaltselemente");?></h2>

  <div class="smashbox_stripe">
      <div style="margin-left: 1.5em;">

          <a class="click_me" href="<?= UrlHelper::getLink('admin_news.php?section=news') ?>">
          <div>
              <span class="click_head"><?=_("Ank�ndigungen");?></span>
              <p><?=_("Erstellen Sie Ank�ndigungen f�r ihre Einrichtung und bearbeiten Sie laufende Ank�ndigungen.");?></p>
              </div>
          </a>

          <? if (get_config('VOTE_ENABLE')) : ?>
          <a class="click_me" href="<?= UrlHelper::getLink('admin_vote.php?section=votings') ?>">
          <div>
              <span class="click_head"><?=_("Umfragen und Tests");?></span>
              <p><?=_("Erstellen Sie in Ihre Einrichtung einfache Umfragen und Tests.");?></p>
              </div>
          </a>

         <a class="click_me" href="<?= UrlHelper::getLink('admin_evaluation.php?section=evaluation') ?>">
            <div>
                  <span class="click_head"><?=_("Evaluationen");?></span>
                  <p><?=_("Richten Sie f�r Ihre Einrichtung eine �ffentliche Umfragen ein.");?></p>
            </div>
        </a>
        <? endif ?>

        <? if (get_config('EXTERN_ENABLE') && $GLOBALS['perm']->have_perm('admin')) : ?>
         <a class="click_me" href="<?= UrlHelper::getLink('admin_extern.php?list=TRUE&view=extern_inst&section=extern') ?>">
            <div>
                  <span class="click_head"><?=_("Externe Seiten");?></span>
                  <p><?=_("Verwalten Sie Ihre Einrichtungsswebseiten au�erhalb von Stud.IP aktuell und f�gen Sie Inhalte aus Stud.IP ein.");?></p>
            </div>
        </a>
        <? endif ?>

        </div>
        <br style="clear: both;">
    </div>
</div>
