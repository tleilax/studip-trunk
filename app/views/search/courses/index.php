<table cellpadding="5" border="0" width="100%" id="main_content"><tr><td colspan="2">
            <?
            //
            if ($_SESSION['sem_portal']["bereich"] == "mod") {
                print "<br>"._("Hier finden Sie alle verf�gbaren Studienmodule.");
            } elseif ($anzahl_seminare_class > 0) {
                print $SEM_CLASS[$_SESSION['sem_portal']["bereich"]]["description"]."<br>" ;
            } elseif ($_SESSION['sem_portal']["bereich"] != "all") {
                print "<br>"._("In dieser Kategorie sind keine Veranstaltungen angelegt.<br>Bitte w&auml;hlen Sie einen andere Kategorie!");
            }

            echo "</td></tr><tr><td class=\"blank\" align=\"left\">";
            if ($_SESSION['sem_portal']["bereich"] != "mod"){
                if ($_SESSION['sem_browse_data']['cmd'] == "xts"){
                    echo \Studip\LinkButton::create(_('Schnellsuche'), URLHelper::getLink('?cmd=qs&level=f'), array('title' => _("Zur Schnellsuche zur�ckgehen")));
                } else {
                    echo \Studip\LinkButton::create(_('Erweiterte Suche'), URLHelper::getLink('?cmd=xts&level=f'), array('title' => _("Erweitertes Suchformular aufrufen")));
                }
            }
            echo "</td>\n";
            echo "<td class=\"blank\" align=\"right\">";
            echo \Studip\LinkButton::create(_('Zur�cksetzen'), URLHelper::getURL('?reset_all=1'), array('title' => _("zur�cksetzen")));
            echo "</td></tr>\n";


            ?>

</table>

<? $sem_browse_obj->do_output() ?>

<?
$sidebar = Sidebar::get();
$sidebar->setImage(Assets::image_path("sidebar/seminar-sidebar.png"));


if ($sem_browse_obj->show_result && count($_SESSION['sem_browse_data']['search_result'])){
    $group_by_links = "";
    $grouping = new LinksWidget();
    $grouping->setTitle(_("Suchergebnis gruppieren:"));
    foreach ($sem_browse_obj->group_by_fields as $i => $field){
        $grouping->addLink(
            $field['name'],
            URLHelper::getLink('?', array('group_by' => $i, 'keep_result_set' => 1)),
            $_SESSION['sem_browse_data']['group_by'] == $i ? "icons/16/red/arr_1right" : ""
        );
    }
    $sidebar->addWidget($grouping);
} elseif ($_SESSION['sem_portal']['bereich'] != 'mod') {
    $toplist_names = array("dummy",_("Teilnehmeranzahl"), _("die meisten Materialien"), _("aktivste Veranstaltungen"),_("neueste Veranstaltungen"));
    $toplist = new LinksWidget();
    $toplist->setTitle(_("Topliste: ").$toplist_names[$_SESSION['sem_portal']["toplist"] ?: 4]);
    foreach ((array) $toplist_entries as $key => $entry) {
        $toplist->addLink(
            ($key + 1).". ".$entry['name'],
            URLHelper::getURL("details.php", array('sem_id' => $entry['seminar_id'],
                'cid' => null,
                'send_from_search' => 1,
                'send_from_search_page' => URLHelper::getUrl(basename($_SERVER['PHP_SELF']), array('cid' => null)))
            ),
            null
        );
    }

    $sidebar->addWidget($toplist);

    $toplist_switcher = new LinksWidget();
    $toplist_switcher->setTitle(_("Weitere Toplisten"));
    foreach (array(4,1,2,3) as $i) {
        $toplist_switcher->addLink(
            $toplist_names[$i],
            URLHelper::getURL("?", array('choose_toplist' => $i)),
            $_SESSION['sem_portal']["toplist"] == $i ? "icons/16/red/arr_1right" : null
        );
    }
    $sidebar->addWidget($toplist_switcher);
}
