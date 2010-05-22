<?php
# Lifter002: TODO
# Lifter007: TEST
# Lifter003: TODO
/**
 * eval_summary.php - Hauptseite fuer Eval-Auswertungen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan Kulmann <jankul@zmml.uni-bremen.de>
 * @copyright   2007-2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


require '../lib/bootstrap.php';

require_once 'lib/visual.inc.php';
require_once "vendor/phplot/phplot.php";
require_once 'lib/msg.inc.php';
require_once 'lib/visual.inc.php';
require_once 'config.inc.php';
require_once 'lib/functions.php';
require_once 'lib/datei.inc.php';
require_once 'lib/evaluation/evaluation.config.php';
require_once EVAL_FILE_EVAL;
require_once EVAL_FILE_OBJECTDB;
require_once 'lib/export/export_tmp_gc.inc.php';

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// �berpr�fen, ob die Evaluation existiert oder der Benutzer gen�gend Rechte hat
$eval = new Evaluation($eval_id);
$eval->check();
if (EvaluationObjectDB::getEvalUserRangesWithNoPermission($eval) == YES || count($eval->errorArray) > 0) {
    throw new Exception(_("Diese Evaluation ist nicht vorhanden oder Sie haben nicht ausreichend Rechte!"));
}

$HELP_KEYWORD="Basis.Evaluationen";
Navigation::activateItem('/tools/evaluation');
$CURRENT_PAGE.= _("Evaluations-Auswertung");

// Gehoert die benutzende Person zum Seminar-Stab (Dozenten, Tutoren) oder ist es ein ROOT?
$staff_member = $perm->have_studip_perm("tutor", $SessSemName[1]);

// Template vorhanden?
$has_template = 0;
$db_template = new DB_Seminar();
$question_type = "";

$tmp_path_export = $GLOBALS['TMP_PATH']. '/export/';
export_tmp_gc();

/*
    1 = normale HTML-Ansicht in Stud.IP
    2 = Druckansicht, ohne HTML-Elemente
*/
if (!isset($ausgabeformat)) {
    $ausgabeformat = 1;
}

if ($ausgabeformat==1) {
    include ('lib/include/html_head.inc.php'); // Output of html head
    include ('lib/include/header.php');    //hier wird der "Kopf" nachgeladen
}

if (isset($cmd)) {
    if ($cmd=="change_group_type" && isset($evalgroup_id) && isset($group_type)) {
        $db = new DB_Seminar();
        $db->query(sprintf("SELECT * FROM eval_group_template WHERE evalgroup_id='%s'",$evalgroup_id));
        if ($db->next_record()) { // Datensatz schon vorhanden --> UPDATE
            if ($group_type=="normal") {
                $db->query(sprintf("DELETE FROM eval_group_template WHERE group_type='table' AND evalgroup_id='%s'",$evalgroup_id));
                $db->next_record();
            } else
                $db->query(sprintf("UPDATE eval_group_template SET group_type='%s' WHERE evalgroup_id='%s' AND user_id='%s'",$group_type,$evalgroup_id,$auth->auth["uid"]));
        } else { // Datensatz nicht vorhanden --> INSERT
            // Ist der User auch wirklich der Eigentuemer der Eval?
            $db->query(sprintf("SELECT * FROM eval WHERE eval_id='%s'",$eval_id));
            if ($db->next_record() && ($db->f("author_id")==$auth->auth["uid"] || $staff_member))
                $db->query(sprintf("INSERT INTO eval_group_template (evalgroup_id, user_id, group_type) VALUES ('%s','%s','%s')",$evalgroup_id,$auth->auth["uid"],$group_type));
        }
    }
}


function do_template($column)
{
    global $has_template, $db_template;

    if ($has_template==0 || ($has_template==1 && $db_template->f($column)))
        return true;
    else
        return false;
}


/**
 * returning the type of the graph
 *
 * @return string
 */
function do_graph_template()
{
    global $db_template, $has_template, $question_type;

    if ($has_template==1) {
        if ($question_type=="likertskala") {
            return $db_template->f("likertscale_gfx_type");
        }
        if ($question_type=="multiplechoice") {
            return $db_template->f("mchoice_scale_gfx_type");
        }
        if ($question_type=="polskala") {
            return $db_template->f("polscale_gfx_type");
        }
    } else {
        return "bars";
    }
}

/**
 * drawing the graph for a evaluation question
 *
 * @param array() $data
 * @param string $evalquestion_id
 */
function do_graph($data, $evalquestion_id)
{
    global $tmp_path_export, $auth, $PATH_EXPORT;

    $type = do_graph_template();

    //Define the object
    if ($type == "pie") {
        // Beim pie muss die Zeichenflaeche etwas groesser gewaehlt werden...
        $graph = new PHPlot(500,300);
    } else {
        $graph = new PHPlot(300,250);
    }

    if ($type == "pie") {
        // Beim pie muss das Array umgeformt werden. Bug in PHPlot?
        $tmp = array();
        $tmp2 = array();
        $legend = array();
        array_push($tmp,"Test");
        foreach($data as $k=>$d) {
            array_push($tmp, $d[1]);
            array_push($legend, $d[0]);
        }
        array_push($tmp2, $tmp);
        $data = $tmp2;
        $graph->SetLegend($legend);
    }

    //png sieht besser aus, mriehe
    if (!isset($GLOBALS['EVAL_AUSWERTUNG_GRAPH_FORMAT'])) {
        $GLOBALS['EVAL_AUSWERTUNG_GRAPH_FORMAT'] = 'png';
    }

    //Data Colors
    $graph->SetDataColors(
        array("blue", "green", "yellow", "red", "PeachPuff", "orange", "pink", "lavender",
            "navy", "peru", "salmon", "maroon", "magenta", "orchid", "ivory"),
        array("black") //Border Colors
    );

    $graph->SetPlotAreaWorld(NULL, 0); // y-achse bei 0 starten
    $graph->SetPrecisionY(0); //anzahl kommastellen y-achse

    $graph->SetPlotBgColor(array(222,222,222));
    $graph->SetDataType("text-data");
    $graph->SetFileFormat($GLOBALS['EVAL_AUSWERTUNG_GRAPH_FORMAT']);
    $graph->SetOutputFile($tmp_path_export."/evalsum".$evalquestion_id.$auth->auth["uid"].".".$GLOBALS['EVAL_AUSWERTUNG_GRAPH_FORMAT']);
    $graph->SetIsInline(true);
    $graph->SetDataValues($data);
    $graph->SetPlotType($type);
    $graph->SetXLabelAngle(0);
    //$graph->SetShading(0); // kein 3D

    $graph->SetLineWidth(1);
    $graph->SetDrawXDataLabels(true);
    //Draw it
    $graph->DrawGraph();
}

function freetype_answers($parent_id, $anz_nutzer)
{
    global $ausgabeformat;

    $db_answers = new DB_Seminar();
    $db_answers->query(sprintf("SELECT ea.* FROM evalanswer ea, evalanswer_user eau WHERE ea.parent_id='%s' AND ea.text!='' AND eau.evalanswer_id=ea.evalanswer_id ORDER BY ea.position",$parent_id));
    echo "  <tr>\n";
    echo "    <td COLSPAN=\"2\">\n";
    echo "      <table BORDER=\"0\" WIDTH=\"100%\">\n";
    echo "        <tr><td COLSPAN=\"2\" class=\"blank\"><FONT SIZE=\"-1\"><B>"._("Antworten")."</B></FONT></td></tr>\n";
    $counter = 1;
    while ($db_answers->next_record()) {
        echo "      <tr>\n";
        echo "        <td WIDTH=\"1%\" VALIGN=\"TOP\"><FONT SIZE=\"-1\"><B>".$counter.".</B></FONT></td><td><FONT SIZE=\"-1\">".formatReady($db_answers->f("text"))."</FONT></td>\n";
        echo "      </tr>\n";
        $counter++;
    }
    echo "      </table>\n";
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "  <tr><td COLSPAN=\"2\"><FONT SIZE=\"-1\">"._("Anzahl der Teilnehmer").": ".$anz_nutzer."</FONT></td></tr>\n";
}

function user_answers_residual($parent_id)
{
    $db_user_answers = new DB_Seminar();
    $db_user_answers->query(sprintf("SELECT eau.* FROM evalanswer_user eau, evalanswer ea WHERE ea.parent_id='%s' AND ea.residual=1 AND eau.evalanswer_id=ea.evalanswer_id",$parent_id));
    $db_user_answers->next_record();
    return $db_user_answers->num_rows();
}

function user_answers($evalanswer_id)
{
    $db_user_answers = new DB_Seminar();
    $db_user_answers->query(sprintf("SELECT * FROM evalanswer_user WHERE evalanswer_id='%s'",$evalanswer_id));
    $db_user_answers->next_record();
    return $db_user_answers->num_rows();
}

function answers($parent_id, $anz_nutzer, $question_type)
{
    global $graph_switch, $auth, $ausgabeformat, $has_template;

    // Rueckgabearray, damit die Daten noch aufzutrennen sind...
    $ret_array = array("id"=>$parent_id,                         // Question-ID
               "txt"=>"",                                // HTML-Ausgabe
               "antwort_texte"=>array(),                 // Antwort-Texte
               "frage"=>"",                              // Frage-Text
               "has_residual"=>0,                // Enthaltungen?
               "antwort_durchschnitt"=>"",               // Antwort-Durchschnitt
               "summe_antworten"=>"",                    // Summe der Antworten
               "anzahl_teilnehmer"=>$anz_nutzer,         // Anzahl der Teilnehmer dieser Frage
               "auswertung"=>array()                     // 1. Anzahl der Antworten zu einer Antwort
                                                 // 2. Prozente einer Antwort
                                                 // 3. Prozente einer Antwort ohne Enthaltungen
              );

    $summary = array ();

    $css=new cssClassSwitcher;

    $db_answers_sum = new DB_Seminar();
    $db_answers_sum->query(sprintf("SELECT COUNT(*) anz FROM evalanswer AS ea LEFT JOIN evalanswer_user AS eau USING (evalanswer_id) WHERE ea.parent_id='%s' AND eau.evalanswer_id=ea.evalanswer_id",$parent_id));
    $db_answers_sum->next_record();

    $db_answers = new DB_Seminar();
    $db_answers->query(sprintf("SELECT * FROM evalanswer WHERE parent_id='%s' ORDER BY position",$parent_id));
    $antwort_nummer = 0;
    $edit = "";
    $txt = "";
    $gesamte_antworten = 0;
    $antwort_durchschnitt = 0;
    $has_residual = user_answers_residual($parent_id);
    $i = 1;
    $edit .= "<tr class=\"steel1\"><td WIDTH=\"1%\">&nbsp;</td><td WIDTH=\"70%\"><FONT SIZE=\"-1\"><B>"._("Antworten")."</B></FONT></td><td WIDTH=\"29%\"><FONT SIZE=\"-1\"><B>"._("Auswertung")."</B></FONT></td></tr>\n";
    while ($db_answers->next_record()) {
        $css->switchClass();
        $antwort_nummer++;
        $answer_counter = user_answers($db_answers->f("evalanswer_id"));
        if ($db_answers->f("residual")==0) {
            $gesamte_antworten += $answer_counter;
            $antwort_durchschnitt += $answer_counter * $antwort_nummer;
        }
        $prozente_wo_residual = 0;
        if ($has_residual && ($db_answers_sum->f("anz")-$has_residual)>0) $prozente_wo_residual = ROUND($answer_counter*100/($db_answers_sum->f("anz")-$has_residual));
        $prozente = 0;
        if ($db_answers_sum->f("anz")>0) $prozente = ROUND($answer_counter*100/$db_answers_sum->f("anz"));
        $edit .= "<tr class=\"".($i==1?"steel1kante":$css->getClass())."\"><td WIDTH=\"1%\"><FONT SIZE=\"-1\"><B>".$antwort_nummer.".&nbsp;</B></FONT></td><td WIDTH=\"70%\"><FONT SIZE=\"-1\">".($db_answers->f("text")!="" ? formatReady($db_answers->f("text")) : $db_answers->f("value"))."</FONT></td>";
        if ($has_residual) $edit .= "<td WIDTH=\"29%\"><FONT SIZE=\"-1\">".$answer_counter." (".$prozente."%) ".($db_answers->f("residual")==0 ? "(".$prozente_wo_residual."%)<B>*</B>" : "" )."</FONT></td></tr>\n";
        else $edit .= "<td WIDTH=\"29%\"><FONT SIZE=\"-1\">".$answer_counter." (".$prozente."%)</FONT></td></tr>\n";
        array_push($summary, array($antwort_nummer."(".$prozente."%)",$answer_counter));

        array_push($ret_array["antwort_texte"], ($db_answers->f("text")!="" ? formatReady($db_answers->f("text")) : $db_answers->f("value")));
        array_push($ret_array["auswertung"], array($answer_counter, $prozente, ($db_answers->f("residual")==0 ? $prozente_wo_residual : null)));
        if ($has_residual) $ret_array["has_residual"] = 1;

        $i = 0;
    }
    do_graph($summary, $parent_id);

    if ($gesamte_antworten > 0 && $antwort_durchschnitt > 0) $antwort_durchschnitt = ROUND($antwort_durchschnitt / $gesamte_antworten, 3);

    $ret_array["antwort_durchschnitt"] = $antwort_durchschnitt;
    $ret_array["summe_antworten"] = $gesamte_antworten;

    $txt .= "  <tr>\n";
    $txt .= "    <td WIDTH=\"70%\" VALIGN=\"TOP\">\n";
    $txt .= "      <table WIDTH=\"98%\" BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"0\">";
    $txt .= $edit."\n";
    $txt .= "        <tr class=\"blank\"><td COLSPAN=\"3\"><FONT SIZE=\"-1\">&nbsp;</FONT></td></tr>";
    $txt .= "        <tr class=\"blank\"><td COLSPAN=\"3\"><FONT SIZE=\"-1\"><B>&#x2211;</B>=".$gesamte_antworten." "._("Antworten")."</FONT></td></tr>";

    $txt .= "        <tr class=\"blank\">";
    if ($question_type=="multiplechoice") {
        $txt .= "        <td COLSPAN=\"3\">";
    } else {
        $txt .= "<td COLSPAN=\"2\"><FONT SIZE=\"-1\"><B>&#x2205;</B>-"._("Antwort").": ".$antwort_durchschnitt.($has_residual==0 ? "" : "<B>*</B>")."</FONT></td><td>";
    }
    $txt .= "          <FONT SIZE=\"-1\">"._("Anzahl der Teilnehmer").": ".$anz_nutzer."</FONT></td></tr>";

    if ($has_residual) $txt .= "        <tr class=\"blank\"><td COLSPAN=\"3\"><FONT SIZE=\"-1\"><B>*</B>"._("Werte ohne Enthaltungen").".</FONT></td></tr>";
    $txt .= "      </table>";
    $txt .= "    </td>\n";
    $txt .= "    <td WIDTH=\"30%\" VALIGN=\"TOP\" ALIGN=\"RIGHT\">\n";
    if (do_template("show_graphics")) {
        $txt .= '<IMG SRC="' . GetDownloadLink('evalsum'.$parent_id.$auth->auth['uid'].'.'.$GLOBALS['EVAL_AUSWERTUNG_GRAPH_FORMAT'], 'evalsum'.$parent_id.$auth->auth['uid'].'.'.$GLOBALS['EVAL_AUSWERTUNG_GRAPH_FORMAT'], 2) .'">'."\n";
    } else $txt .= "&nbsp;\n";
    $txt .= "    </td>\n";
    $txt .= "  </tr>\n";

    $ret_array['txt'] = $txt;

    return $ret_array;

}

function groups($parent_id)
{
    global $ausgabeformat, $global_counter, $local_counter, $question_type, $eval_id, $PHP_SELF, $evalgroup_id;

    $db_groups = new DB_Seminar();
    $db_groups->query(sprintf("SELECT * FROM evalgroup WHERE parent_id='%s' ORDER BY position",$parent_id));

    while ($db_groups->next_record()) {
        // Heraussuchen, ob es sich um ein Freitext-Template handelt...
        $db = new DB_Seminar();
        $db->query(sprintf("SELECT * FROM evalquestion WHERE evalquestion_id='%s'",$db_groups->f("template_id")));
        $freetype = FALSE;
        if ($db->next_record()) {
            if (strstr($db->f("text"),"Freitext"))
                $freetype = TRUE;
        }

        if ($db_groups->f("child_type")=="EvaluationGroup") {
            $global_counter += 1;
            $local_counter   = 0;

            echo "  <tr><td class=\"".($ausgabeformat==1 ? "topic" : "blank")."\" ALIGN=\"LEFT\" COLSPAN=\"2\">\n";
            if (do_template("show_group_headline"))
                echo "    <B>".$global_counter.". ".formatReady($db_groups->f("title"))."</B>&nbsp;\n";
            else echo "&nbsp;";
        } else {
            $local_counter += 1;

            $group_type = "normal";

            $db_group_type = new DB_Seminar();
            $db_group_type->query(sprintf("SELECT * FROM eval_group_template WHERE evalgroup_id='%s'",$db_groups->f("evalgroup_id")));
            if ($db_group_type->next_record()) $group_type = $db_group_type->f("group_type");

            echo "  <tr><td class=\"".($ausgabeformat==1 ? "steelgraulight" : "blank")."\" COLSPAN=\"2\">\n";
            if (do_template("show_questionblock_headline")) {
                echo "<table WIDTH=\"100%\" BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\"><tr><td ALIGN=\"left\"><B>".$global_counter.".".$local_counter.". ".formatReady($db_groups->f("title"))."</B></td>";
                echo "<td ALIGN=\"RIGHT\">".($ausgabeformat==1 && !($freetype) ? "<A HREF=\"$PHP_SELF?eval_id=$eval_id&evalgroup_id=".$db_groups->f("evalgroup_id")."&group_type=".($group_type=="normal" ? "table" : "normal")."&cmd=change_group_type#anker\"><IMG SRC=\"".$GLOBALS['ASSETS_URL']."images/rewind3.gif\" TITLE=\""._("Zum Darstellungstyp")." ".($group_type=="normal"?_("Tabelle"):_("Normal"))." "._("wechseln").".\" BORDER=\"0\"></A>" : "&nbsp;"). "</td>";
                echo "</tr></table>\n";
            }
            if ($evalgroup_id == $db_groups->f("evalgroup_id")) {
                echo "  <A name=\"anker\"></A>\n";
            }
        }

        echo "  </td></tr>";

        if ($db_groups->f("child_type")=="EvaluationQuestion") {
            echo "  <tr><td class=\"blank\" COLSPAN=\"2\">\n";
            $db_questions = new DB_Seminar();
            $db_questions->query(sprintf("SELECT * FROM evalquestion WHERE parent_id='%s' ORDER BY position",$db_groups->f("evalgroup_id")));
            echo "<table BORDER=\"". ($group_type=="normal" || $ausgabeformat==1 ? "0" : "1") ."\" WIDTH=\"100%\" CELLSPACING=\"0\">\n";

            $local_question_counter = 0;
            $answer_arr = array();
            while ($db_questions->next_record()) {

                $question_type = $db_questions->f("type");
                $db_questions_user = new DB_Seminar();
                $db_questions_user->query(sprintf("SELECT COUNT(DISTINCT eau.user_id) anz FROM evalanswer ea, evalanswer_user eau WHERE ea.parent_id='%s' AND eau.evalanswer_id=ea.evalanswer_id",$db_questions->f("evalquestion_id")));
                $db_questions_user->next_record();

                $local_question_counter += 1;

                if (do_template("show_questions") && $group_type=="normal") {
                    echo "    <tr><td class=\"blank\" COLSPAN=\"2\">\n";
                    echo "      <B>".$global_counter.".".$local_counter.".".$local_question_counter.". ".formatReady($db_questions->f("text"))."</B></FONT>\n";
                    echo "    </td></tr>\n";
                }

                if (!($freetype)) {
                    // Keine Freitext-Eingabe
                    $ret = answers($db_questions->f("evalquestion_id"), $db_questions_user->f("anz"), $db_questions->f("type"));
                    $ret["frage"] = $db_questions->f("text");
                    array_push($answer_arr, $ret);
                    if ($group_type=="normal") echo $ret["txt"];
                } else {
                    // Freitext
                    freetype_answers($db_questions->f("evalquestion_id"), $db_questions_user->f("anz"));
                }

            }

            if (!($freetype) && $group_type=="table") {
                $antworten_angezeigt = FALSE;
                $i = 0;
                $has_residual = 0;
                $css=new cssClassSwitcher;
                foreach ($answer_arr as $k1=>$questions) { // Oberste Ebene, hier sind die Questions abgelegt

                    $css->switchClass();

                    if (!($antworten_angezeigt)) {
                        $i = 1;
                                            echo "  <tr class=\"steel1\"><td><FONT SIZE=\"-1\">&nbsp;</FONT></td>";
                                            foreach ($questions["antwort_texte"] as $k2=>$v2) { // 1. Unterebene, hier sind die Antworttexte abgelegt
                                                echo "<td><FONT SIZE=\"-1\">".$v2."</FONT></td>";
                                            }
                        echo "<td ALIGN=\"center\"><FONT SIZE=\"-1\"><B>&#x2211;</B></FONT></td><td ALIGN=\"center\"><FONT SIZE=\"-1\"><B>&#x2205;</B></FONT></td><td ALIGN=\"center\"><FONT SIZE=\"-1\">"._("Teilnehmer")."</FONT></td>";
                                            echo "</tr>";
                                            $antworten_angezeigt = TRUE;
                                        }

                    echo "<tr class=\"". ($i==1?"steel1kante":$css->getClass())."\">";
                    echo "  <td><FONT SIZE=\"-1\">".$questions["frage"]."</FONT></td>";
                    foreach ($questions["auswertung"] as $k3=>$v3) {
                        echo "<td WIDTH=\"10%\" VALIGN=\"TOP\" ".($i!=1?"CLASS=\"".$css->getClass()."\"":"")."><FONT SIZE=\"-1\">";
                        echo $v3[0]." (".$v3[1]."%)"; // 2. Unterebene, hier sind die Zahlen abgelegt
                        if ($v3[2]) echo " (".$v3[2]."%)<B>*</B>";
                        echo "</FONT></td>";
                    }

                    $i=0;
                    if ($questions["has_residual"]) $has_residual = 1;

                    echo "<td ALIGN=\"center\" WIDTH=\"3%\" VALIGN=\"TOP\"><FONT SIZE=\"-1\">".$questions["summe_antworten"]."</FONT></td><td ALIGN=\"center\" WIDTH=\"3%\" VALIGN=\"TOP\"><FONT SIZE=\"-1\">".$questions["antwort_durchschnitt"].($questions["has_residual"]?"<B>*</B>":"")."</FONT></td><td ALIGN=\"center\" WIDTH=\"6%\" VALIGN=\"TOP\"><FONT SIZE=\"-1\">".$questions["anzahl_teilnehmer"]."</FONT></td>";

                    echo "</tr>";
                }
                if ($has_residual) echo "<tr><td><FONT SIZE=\"-1\"><B>*</B>"._("Werte ohne Enthaltungen").".</FONT></td></tr>";
            }

            echo "</table>\n";
            echo "</td></tr>\n";
        }
        groups($db_groups->f("evalgroup_id"));
    }
}


$db = new DB_Seminar();

if ($staff_member)
    $db->query(sprintf("SELECT * FROM eval WHERE eval_id='%s'",$eval_id));
else
    $db->query(sprintf("SELECT * FROM eval WHERE eval_id='%s' AND author_id='%s'",$eval_id,$auth->auth["uid"]));

if ($db->next_record()) {
  $db_template->query(sprintf("SELECT t.* FROM eval_templates t, eval_templates_eval te WHERE te.eval_id='%s' AND t.template_id=te.template_id",$eval_id));
  if ($db_template->next_record()) $has_template = 1;

  $db_owner = new DB_Seminar();
  $db_owner->query(sprintf("SELECT ".$_fullname_sql['no_title']." AS fullname FROM auth_user_md5 WHERE user_id='%s'", $db->f("author_id")));
  $db_owner->next_record();

  $global_counter = 0;
  $local_counter  = 0;

  $db_number_of_votes = new DB_Seminar();
  $db_number_of_votes->query(sprintf("SELECT COUNT(DISTINCT user_id) anz FROM eval_user WHERE eval_id='%s'", $eval_id));
  $db_number_of_votes->next_record();

  // Evaluation existiert auch...
  echo "<table BORDER=\"0\" WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"0\">\n";
  echo "<tr><td class=\"topic\" align=\"left\"><FONT COLOR=\"".($ausgabeformat==1 ? "white" : "black")."\">".($ausgabeformat==1 ? "<IMG SRC=\"".$GLOBALS['ASSETS_URL']."images/eval-icon.gif\" BORDER=\"0\">&nbsp;" : "" )."<B>"._("Evaluations-Auswertung")."</B></FONT></td>\n";
  echo "<td class=\"".($ausgabeformat==1 ? "topic" : "blank" )."\" ALIGN=\"RIGHT\">".($ausgabeformat==1 ? "<A HREF=\"eval_summary_export.php?eval_id=".$eval_id."\" TARGET=\"_blank\"><FONT COLOR=\"WHITE\">"._("PDF-Export")."</FONT></A><B>&nbsp;|&nbsp;</B><A HREF=\"".$PHP_SELF."?eval_id=".$eval_id."&ausgabeformat=2\" TARGET=\"_blank\"><FONT COLOR=\"WHITE\">"._("Druckansicht")."</FONT></A>&nbsp;&nbsp;<A HREF=\"eval_config.php?eval_id=".$eval_id."\"><IMG SRC=\"".$GLOBALS['ASSETS_URL']."images/pfeillink.gif\" BORDER=\"0\" ALT=\""._("Auswertung konfigurieren")."\" TITLE=\""._("Auswertung konfigurieren")."\"></A>" : "" ) ."&nbsp;</td>\n";
  echo "</tr>\n";
  echo "<tr><td class=\"blank\" COLSPAN=\"2\" align=\"left\">&nbsp;</td></tr>\n";
  echo "<tr><td class=\"blank\" COLSPAN=\"2\" align=\"left\"><FONT SIZE=\"+1\"><B>&nbsp;&nbsp;".formatReady($db->f("title"))."</B></FONT></td>\n";
  echo "</tr>\n";

  echo "<tr><td class=\"blank\" COLSPAN=\"2\" align=\"left\">&nbsp;</FONT></td></tr>\n";

  // Gesamtstatistik
  if (do_template("show_total_stats")) {
    echo "  <tr>\n";
    echo "    <td COLSPAN=\"2\" class=\"blank\"><FONT SIZE=\"-1\">\n";
    echo "      &nbsp;&nbsp;".$db_number_of_votes->f("anz")." "._("Teilnehmer insgesamt").".&nbsp;";
    echo "      "._("Die Teilnahme war")." ". ($db->f("anonymous")==0 ? _("nicht") : "") . " "._("anonym").".";
    echo "      "._("Eigent&uuml;mer").": ".$db_owner->f("fullname").". ".("Erzeugt am").": ".date('d.m.Y H:i:s');
    echo "    </FONT></td>\n";
    echo "  </tr>\n";
  }

  echo "  <tr><td COLSPAN=\"2\">\n";
  echo "    <table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"8\">\n";

  groups($db->f("eval_id"));

  echo "    </table>\n";
  echo "  </td></tr>\n";
  echo "</table>\n";
}

include ('lib/include/html_end.inc.php');
page_close();