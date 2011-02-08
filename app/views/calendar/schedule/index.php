<?
# Lifter010: TODO
$zoom = Request::get('zoom', 0);

$text  = _("Der Stundenplan zeigt Ihre regelm��igen Veranstaltungen dieses Semesters sowie von Ihnen selbst erstellte Belegungen.");
$text2 = sprintf( _("Um neue Veranstaltungen hinzuzuf�gen, verwenden Sie die %sVeranstaltungssuche%s."),
        '<a href="'. UrlHelper::getLink('sem_portal.php') .'">', '</a>');

if (!$show_hidden) {
    $hidden_text = '<a href="'. $controller->url_for('calendar/schedule/?show_hidden=true') .'">'. _("Ausgeblendete Veranstaltungen anzeigen") .'</a>';
} else {
    $hidden_text = '<a href="'. $controller->url_for('calendar/schedule') .'">'. _("Ausgeblendete Veranstaltungen verbergen") .'</a>';
}

$infobox = array();
$infobox['picture'] = 'infobox/schedules.jpg';

$infobox['content'] = array(
    array(
        'kategorie' => _("Information:"),
        'eintrag'   => array(
            array("text" => $text, "icon" => "icons/16/black/info.png"),
            array("text" => $text2, "icon" => "icons/16/black/search.png")
        )
    ),

    array(
        'kategorie' => _("Aktionen:")
    ),

    array(
        'kategorie' => _("Darstellungsgr��e:")
    )

);

if (!$inst_mode) {
    $infobox['content'][1]['eintrag'][] = array (
        'text' => '<a href="'. $controller->url_for('calendar/schedule/entry') .'">'._("Neuer Eintrag") .'</a>',
        'icon' => 'icons/16/black/add/date.png'
    );
}

$infobox['content'][1]['eintrag'][] = array (
    'text' => '<a href="'. $controller->url_for('calendar/schedule/index/'. implode(',', $days)
           .  '?printview=true' . (Request::get('show_hidden') ? '&show_hidden=true' : ''))
           .  '" target="_blank">'._("Druckansicht") .'</a>',
    'icon' => "icons/16/black/print.png"
);

$infobox['content'][1]['eintrag'][] = array (
    'text' => '<a href="'. $controller->url_for('calendar/schedule/index?show_settings=true') .'">'. _("Einstellungen �ndern") .'</a>',
    'icon' => "icons/16/black/admin.png"
);

$infobox['content'][1]['eintrag'][] = array (
    'text' => $hidden_text,
    'icon' => 'icons/16/black/visibility-visible.png'
);

// Infobox-exntries for viewport size
$infobox['content'][2]['eintrag'] = array (
    array (
        'icon' => 'icons/16/'. ($zoom == 0 ? 'red' : 'black') . '/schedule.png',
        'text' => '<a href="'. UrlHelper::getLink('', array('zoom' => 0)) .'">'. _("klein") .'</a>'
    ),
    array (
        'icon' => 'icons/16/'. ($zoom == 2 ? 'red' : 'black') . '/schedule.png',
        'text' => '<a href="'. UrlHelper::getLink('', array('zoom' => 2)) .'">'. _("mittel") .'</a>'
    ),
    array (
        'icon' => 'icons/16/'. ($zoom == 4 ? 'red' : 'black') . '/schedule.png',
        'text' => '<a href="'. UrlHelper::getLink('', array('zoom' => 4)) .'">'. _("gro�") .'</a>'
    )
);
?>
<div style="text-align: center; font-weight: bold; font-size: 1.2em">
    <?= _("Mein Stundenplan im") ?>
    <?= $current_semester['name'] ?>
</div>
<? if (Request::get('show_settings')) : ?>
    <?= $this->render_partial('calendar/schedule/settings', array('settings' => $GLOBALS['my_schedule_settings']));?>
<? endif ?>
<?= $this->render_partial('calendar/daily_weekly.php', compact('calendar_view')); ?>
