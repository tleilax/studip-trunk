<input type="hidden" name="room_request_form" value="1">
<? if (isset($new_room_request_type)) : ?>
    <input type="hidden" name="new_room_request_type" value="<?= $new_room_request_type ?>">
<? endif ?>
<table class="default">
<tr>
    <td colspan="2">
        <?
        echo _("Sie haben die M�glichkeit, gew�nschte Raumeigenschaften sowie einen konkreten Raum anzugeben. Diese Raumw�nsche werden von der zentralen Raumverwaltung bearbeitet.");
        echo "<br>"._("<b>Achtung:</b> Um sp�ter einen passenden Raum f�r Ihre Veranstaltung zu bekommen, geben Sie bitte <u>immer</u> die gew�nschten Eigenschaften mit an!");
        ?>
    </td>
</tr>
<tr>
    <td width="50%">
        <b><?=("Art des Wunsches:")?></b><br><br>
        <?
        echo htmlready($request->getTypeExplained(),1,1);
        ?>
    </td>
    <td width="50%">
        <b><?=("Bearbeitungsstatus:")?></b><br><br>
        <?
        if ($request->isNew()) {
            echo _("Diese Anfrage ist noch nicht gespeichert");
        } else {
            echo $request->getStatusExplained();
        }
        ?>
    </td>
</tr>
<?
if ($request_resource_id = $request->getResourceId()) :
    $resObject = ResourceObject::Factory($request_resource_id);
?>
<tr>
    <td colspan="2">
        <b><?=("gew�nschter Raum:")?></b><br><br>
        <b><?= htmlReady($resObject->getName()) ?></b>,
        <?= _("verantwortlich:") ?>
        <a href="<?= $resObject->getOwnerLink() ?>"><?= htmlReady($resObject->getOwnerName()) ?></a>
        <input type="image" src="<?= Assets::image_path('icons/16/blue/refresh.png') ?>" <?= tooltip(_("den ausgew�hlten Raum l�schen")) ?> name="reset_resource_id">
        <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/icons/16/grey/info-circle.png"
            <?= tooltip(_("Der ausgew�hlte Raum bietet folgende der w�nschbaren Eigenschaften:")
            ." \n".$resObject->getPlainProperties(TRUE), TRUE, TRUE) ?>>
    </td>
</tr>
<? endif ?>
<tr>
    <td colspan="2">
        <table class="default">
            <tr>
                <td width="50%" style="background-image: url('<?= $GLOBALS['ASSETS_URL'] ?>images/line2.gif'); background-repeat: repeat-y; background-position: right;">
                    <?
                    print "<b>"._("Raumeigenschaften angeben:")."</b><br><br>";
                    if ($request->getCategoryId()) {
                        if (count($room_categories)) {
                            print _("Gew�hlter Raumtyp:");
                            print "&nbsp;<select name=\"select_room_type\">";
                            foreach ($room_categories as $rc) {
                                printf("<option value=\"%s\" %s>%s </option>",
                                $rc["category_id"],
                                ($request->category_id == $rc["category_id"] ) ? "selected" : "",
                                htmlReady($rc["name"])
                                );
                            }
                            print "</select>";
                            print "&nbsp;<input type=\"IMAGE\" value=\""._("Raumtyp ausw�hlen")."\" name=\"send_room_type\" src=\"" . Assets::image_path('icons/16/blue/accept.png') . "\" ".tooltip(_("Raumtyp ausw�hlen")).">";
                            print "&nbsp;&nbsp;<input type=\"IMAGE\" src=\"" . Assets::image_path('icons/16/blue/refresh.png') . "\" ".tooltip(_("alle Angaben zur�cksetzen"))." name=\"reset_room_type\"><br><br>";
                        }

                        print _("Folgende Eigenschaften sind w�nschbar:")."<br><br>";
                        print "<table class=\"default\">";
                        foreach ($request->getAvailableProperties() as $prop) {
                            ?>
                            <tr>
                                <td width="30%" >
                                    <?= htmlReady($prop["name"]) ?>
                                </td>
                                <td width="70%">
                                <?
                                switch ($prop["type"]) {
                                    case "bool":
                                        printf ("<input type=\"CHECKBOX\" name=\"request_property_val[%s]\" %s>&nbsp;%s", $prop["property_id"], ($request->getPropertyState($prop["property_id"])) ? "checked": "", htmlReady($prop["options"]));
                                    break;
                                    case "num":
                                        if ($prop["system"] == 2) {
                                            printf ("<input type=\"TEXT\" name=\"request_property_val[%s]\" value=\"%s\" size=\"5\">", $prop["property_id"], htmlReady($request->getPropertyState($prop["property_id"])));
                                            if ($admission_turnout) {
                                                printf ("<br><input type=\"CHECKBOX\" name=\"seats_are_admission_turnout\" %s>&nbsp;",  ($request->getPropertyState($prop["property_id"]) == $admission_turnout && $admission_turnout > 0) ? "checked" :"");
                                                print _("max. Teilnehmeranzahl �bernehmen")."";
                                            }
                                        } else {
                                            printf("<input type=\"TEXT\" name=\"request_property_val[%s]\" value=\"%s\" size=\"30\">", $prop["property_id"], htmlReady($request->getPropertyState($prop["property_id"])));
                                        }
                                    break;
                                    case "text";
                                        printf ("<textarea name=\"request_property_val[%s]\" cols=\"30\" rows=\"2\" >%s</textarea>", $prop["property_id"], htmlReady($request->getPropertyState($prop["property_id"])));
                                    break;
                                    case "select";
                                        $options = explode (";",$prop["options"]);
                                        printf ("<select name=\"request_property_val[%s]\">", $prop["property_id"]);
                                        print   "<option value=\"\">--</option>";
                                        foreach ($options as $a) {
                                            printf ("<option %s value=\"%s\">%s</option>", ($request->getPropertyState($prop["property_id"]) == $a) ? "selected":"", $a, htmlReady($a));
                                        }
                                        print "</select>";
                                    break;
                                }
                                ?>
                                </td>
                            </tr>
                            <?
                        }
                        print "</table>";

                    } elseif (count($room_categories)) {
                        print _("Bitte geben Sie zun�chst einen Raumtyp an, der f�r Sie am besten geeignet ist:")."<br><br>";
                        print "<select name=\"select_room_type\">";
                        print ("<option value=\"\">["._("bitte ausw�hlen")."]</option>");
                        foreach ($room_categories as $rc) {
                            printf ("<option value=\"%s\">%s </option>", $rc["category_id"], htmlReady($rc["name"]));
                        }
                        print "</select>";
                        print "&nbsp;<input type=\"IMAGE\" value=\""._("Raumtyp ausw�hlen")."\" name=\"send_room_type\" src=\"".Assets::image_path('icons/16/blue/accept.png')."\" ".tooltip(_("Raumtyp ausw�hlen")).">";
                    }
                    ?>

                </td>

                <td width="50%" valign="top">
                    <?
                    print "<b>"._("Raum suchen:")."</b><br>";
                    if (is_array($search_result)) {
                        if (count($search_result)) {
                            printf ("<br><b>%s</b> ".(!$search_by_properties ? _("R�ume gefunden:") : _("passende R�ume gefunden."))."<br><br>", sizeof($search_result));
                            print "<select name=\"select_room\">";
                            foreach ($search_result as $key => $val) {
                                printf ("<option value=\"%s\">%s </option>", $key, htmlReady(my_substr($val, 0, 30)));
                            }
                            print "</select>";
                            print "&nbsp;<input type=\"IMAGE\" src=\"" . Assets::image_path('icons/16/blue/accept.png') . "\" ".tooltip(_("Den Raum als Wunschraum ausw�hlen"))." name=\"send_room\">";
                            print "&nbsp;&nbsp;<input type=\"IMAGE\" src=\"" . Assets::image_path('icons/16/blue/refresh.png') . "\" ".tooltip(_("neue Suche starten"))." name=\"reset_room_search\">";
                            if ($search_by_properties) {
                                print "<br><br>"._("(Diese R�ume erf�llen die Wunschkriterien, die Sie links angegeben haben.)");
                            }
                        } else {
                            print "<br>"._("<b>Keinen</b> Raum gefunden.")."<br>";
                        }
                    }
                    if (!count($search_result)) {
                        ?>
                        <br>
                        <?=_("Geben Sie zur Suche den Raumnamen ganz oder teilweise ein:"); ?>
                        <input type="text" size="30" maxlength="255" name="search_exp_room">
                        <input type="image" src="<?= Assets::image_path('icons/16/blue/search.png') ?>" <? echo tooltip(_("Suche starten")) ?> name="search_room"><br>
                        <?
                    }
                    ?>
                </td>
            </tr>
            <?
            if ($request->category_id) {
            ?>
            <tr>
                <td align="right">
                    <?=("passende R�ume suchen")?>
                    <input type="image" src="<?= Assets::image_path('icons/16/yellow/arr_2right.png') ?>" <? echo tooltip(_("passende R�ume suchen")) ?> name="search_properties">
                </td>
                <td>
                    &nbsp;
                </td>
            </tr>
            <?
            }
            ?>
        </table>
    </td>
</tr>
<tr>
    <td colspan="2">
        <b><?=("Nachricht an den Raumadministrator:")?></b><br><br>
            <?=_("Sie k�nnen hier eine Nachricht an den Raumadministrator verfassen, um weitere W�nsche oder Bemerkungen zur gew�nschten Raumbelegung anzugeben.")?> <br><br>
            <textarea name="comment" cols="58" rows="4" style="width:90%"><?=htmlReady($request->getComment()); ?></textarea>
    </td>
</tr>
<tr>
    <td align="center" colspan="2">
            <?=$submit?>
    </td>
</tr>
</table>