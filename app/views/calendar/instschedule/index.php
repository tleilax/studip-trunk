<?php
$zoom = Request::get('zoom', 0);

$text  = _("Der Stundenplan zeigt Ihre regelm��igen Veranstaltungen dieses Semesters sowie von Ihnen selbst erstellte Belegungen.");
$text2 = sprintf( _("Um neue Veranstaltungen hinzuzuf�gen, verwenden Sie die %sVeranstaltungssuche%s."),
        '<a href="'. UrlHelper::getLink('sem_portal.php') .'">', '</a>');
if ($zoom) {
    $zoom_text = '<a href="'. UrlHelper::getLink('', array('zoom' => 0)) .'">'. _("Normalansicht") .'</a>';
} else {
    $zoom_text = '<a href="'. UrlHelper::getLink('', array('zoom' => 7)) .'">'. _("Gro�ansicht") .'</a>';
}

$infobox = array();
$infobox['picture'] = 'schedule.jpg';

$infobox['content'] = array(
    array(
        'kategorie' => _("Information:"),
        'eintrag'   => array(
            array("text" => $text, "icon" => "ausruf_small2.gif"),
            array("text" => $text2, "icon" => "ausruf_small2.gif")
        )
    ),
    
    array(
        'kategorie' => _("Aktionen:")
    )
);

$infobox['content'][1]['eintrag'][] = array (
    'text' => '<a href="'. $controller->url_for('calendar/instschedule/index/'. implode(',', $days) .'?printview=true') .'" target="_blank">'._("Druckansicht") .'</a>',
    'icon' => 'link_intern.gif'
);

$semester_chooser  = '<form method="post" action="'. $controller->url_for('calendar/instschedule') .'">';
$semester_chooser .= '<select name="semester_id">';
foreach (array_reverse($semesters) as $semester) :
    $semester_chooser .= '<option value="'. $semester['semester_id'] .'"';
    if ($current_semester['semester_id'] == $semester['semester_id']) :
        $semester_chooser .= ' selected="selected"';
    endif;
    $semester_chooser .= '>'. $semester['name'] .'</option>';
endforeach;
$semester_chooser .= '</select> ';
$semester_chooser .= '<input type="image" src="'. Assets::image_path('GruenerHakenButton.png') .'"></form>';

$infobox['content'][1]['eintrag'][] = array (
    'text' => $semester_chooser,
    'icon' => 'icons/16/black/schedule.png'
);
?>
<div style="text-align: center; font-weight: bold; font-size: 1.2em">
    <?= $GLOBALS['SessSemName']['header_line'] ?>  <?= _("im") ?>
    <?= $current_semester['name'] ?>
</div>
<?= $this->render_partial('calendar/daily_weekly.php', compact('calendar_view')); ?>
