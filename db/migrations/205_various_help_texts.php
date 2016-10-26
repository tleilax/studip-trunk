<?php
/**
 * @author  Moritz Strohm <strohm@data-quest.de>
 * @license GPL2 or any later version
 */
class VariousHelpTexts extends Migration
{
    public function description()
    {
        return 'Adds help texts for the free information page and the plugins Elmo, Ephorus and Perle.';
    }

    public function up()
    {
        $db = DBManager::get();
        
        $db->exec("
            INSERT INTO help_content (global_content_id, content_id, language, content, route, studip_version)
            VALUES
            ('041b600b16d39607d884386c31e63779', '041b600b16d39607d884386c31e63779', 'de', 'Lehrende k�nnen zu frei definierbaren Themen (\"Themenliste bearbeiten\") zeitlich befristete Aufgaben (innerhalb des Themas) f�r die Studierenden hinterlegen. Wer die Aufgabe bereits bearbeitet hat, ist f�r Lehrende der Veranstaltung sofort erkennbar. F�r Studierende ist das anschlie�ende Feedback der/des Lehrenden ein wichtiger Impuls in ihrer weiteren Kompetenzentwicklung.', 'plugins.php/elmoplugin', '4.0'),
            
            ('451bef53dbe42cd201d6d657324f8715', '451bef53dbe42cd201d6d657324f8715', 'de', 'Mit Ephorus Arbeiten (Dateien) der Studierenden auf Plagiate hin �berpr�fen. Die Arbeiten werden mit Quellen im Internet und fr�heren Arbeiten verglichen. Es werden auch Fachzeitschriften, Referenzarbeiten und andere Dokumente, die im Internet zu finden sind, in die Plagiatspr�fung einbezogen. Die Einsch�tzung, ob es sich tats�chlich um eine Plagiat handelt oder nicht, obliegt den Lehrenden.', 'plugins.php/ephorus2plugin', '4.0'),
            
            ('991e7a821ea25760a1585f22865cdb67', '991e7a821ea25760a1585f22865cdb67', 'de', 'Der pers�nliche Lerndialog ist in allen Lernsituationen einsetzbar, in denen ein zentraler direkter pers�nlicher Dialog mit einzelnen Studierenden gew�nscht ist. M�gliche Lehr- und Lernsituationen: Begleitung von Praktika und Projekten, Vorbereitung Referate, Betreuung von l�ngeren schriftlichen Arbeiten.', 'plugins.php/perlediplugin', '4.0'),
            
            ('142482b4b06a376b2eb4c91d38559a15', '142482b4b06a376b2eb4c91d38559a15', 'de', 'Freie Gestaltung von Reiternamen und Inhalten durch Lehrende. Es gibt Raum f�r eigene Informationen, der Name des Reiters ist frei definierbar. Es k�nnen beliebig viele Eintr�ge (\"neue Eintr�ge\") hinzugef�gt werden.', 'dispatch.php/course/scm', '4.0')
            ;"
        );
        
        $db = null;
    }

    public function down()
    {
        $db = DBManager::get();
        
        $db->exec(
            "DELETE FROM help_content
            WHERE content_id IN (
                '041b600b16d39607d884386c31e63779',
                '451bef53dbe42cd201d6d657324f8715',
                '991e7a821ea25760a1585f22865cdb67',
                '142482b4b06a376b2eb4c91d38559a15'
            );"
        );
        
        $db = null;
    }
}
