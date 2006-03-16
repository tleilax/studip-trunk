-- 
-- Daten f�r Tabelle `auth_user_md5`
-- 

INSERT IGNORE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `auth_plugin`) VALUES ('76ed43ef286fb55cf9e41beadb484a9f', 'root@studip', 'ae2b1fca515949e5d54fb22b8ed95575', 'root', 'Root', 'Studip', 'root@localhost', NULL);
INSERT IGNORE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `auth_plugin`) VALUES ('205f3efb7997a0fc9755da2b535038da', 'test_dozent', 'ae2b1fca515949e5d54fb22b8ed95575', 'dozent', 'Testaccount', 'Dozent', 'dozent@studip.de', NULL);
INSERT IGNORE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `auth_plugin`) VALUES ('6235c46eb9e962866ebdceece739ace5', 'test_admin', 'ae2b1fca515949e5d54fb22b8ed95575', 'admin', 'Testaccount', 'Admin', 'admin@studip.de', NULL);
INSERT IGNORE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `auth_plugin`) VALUES ('7e81ec247c151c02ffd479511e24cc03', 'test_tutor', 'ae2b1fca515949e5d54fb22b8ed95575', 'tutor', 'Testaccount', 'Tutor', 'tutor@studip.de', NULL);
INSERT IGNORE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `auth_plugin`) VALUES ('e7a0a84b161f3e8c09b4a0a2e8a58147', 'test_autor', 'ae2b1fca515949e5d54fb22b8ed95575', 'autor', 'Testaccount', 'Autor', 'autor@studip.de', NULL);

-- 
-- Daten f�r Tabelle `dokumente`
-- 

INSERT IGNORE INTO `dokumente` (`dokument_id`, `range_id`, `user_id`, `seminar_id`, `name`, `description`, `filename`, `mkdate`, `chdate`, `filesize`, `autor_host`, `downloads`, `url`, `protected`) VALUES ('c51a12e44c667b370fe2c497fbfc3c21', '823b5c771f17d4103b1828251c29a7cb', '76ed43ef286fb55cf9e41beadb484a9f', '834499e2b8a2cd71637890e5de31cba3', 'Stud.IP-Produktbrosch�re im PDF-Format', '', 'studip_broschuere.pdf', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 295294, '217.94.188.5', 2, 'http://www.studip.de/download/studip_broschuere.pdf', 0);

-- 
-- Daten f�r Tabelle `folder`
-- 

INSERT IGNORE INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('dad53cd0f0d9f36817c3c9c7c124bda3', 'ec2e364b28357106c0f8c282733dbe56', '', 'Allgemeiner Dateiordner', 'Ablage f�r allgemeine Ordner und Dokumente der Einrichtung', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
INSERT IGNORE INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('b58081c411c76814bc8f78425fb2ab81', '7a4f19a0a2c321ab2b8f7b798881af7c', '', 'Allgemeiner Dateiordner', 'Ablage f�r allgemeine Ordner und Dokumente der Einrichtung', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
INSERT IGNORE INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('694cdcef09c2b8e70a7313b028e36fb6', '110ce78ffefaf1e5f167cd7019b728bf', '', 'Allgemeiner Dateiordner', 'Ablage f�r allgemeine Ordner und Dokumente der Einrichtung', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
INSERT IGNORE INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('823b5c771f17d4103b1828251c29a7cb', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Allgemeiner Dateiordner', 'Ablage f�r allgemeine Ordner und Dokumente der Veranstaltung', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 
-- Daten f�r Tabelle `Institute`
-- 

INSERT IGNORE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`) VALUES ('1535795b0d6ddecac6813f5f6ac47ef2', 'Test Fakult�t', '1535795b0d6ddecac6813f5f6ac47ef2', 'Geismar Landstr. 17b', '37083 G�ttingen', 'http://www.studip.de', '0551 / 381 985 0', 'testfakultaet@studip.de', '0551 / 381 985 3', 1, 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Studip', 0);
INSERT IGNORE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`) VALUES ('2560f7c7674942a7dce8eeb238e15d93', 'Test Einrichtung', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 1, 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Studip', 0);
INSERT IGNORE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`) VALUES ('536249daa596905f433e1f73578019db', 'Test Lehrstuhl', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 3, 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Studip', 0);
INSERT IGNORE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`) VALUES ('f02e2b17bc0e99fc885da6ac4c2532dc', 'Test Abteilung', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 4, 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Studip', 0);
INSERT IGNORE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`) VALUES ('ec2e364b28357106c0f8c282733dbe56', 'externe Bildungseinrichtungen', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Studip', 0);
INSERT IGNORE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`) VALUES ('7a4f19a0a2c321ab2b8f7b798881af7c', 'externe Einrichtung A', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Studip', 0);
INSERT IGNORE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`) VALUES ('110ce78ffefaf1e5f167cd7019b728bf', 'externe Einrichtung B', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Studip', 0);

-- 
-- Daten f�r Tabelle `lit_catalog`
-- 

INSERT IGNORE INTO `lit_catalog` (`catalog_id`, `user_id`, `mkdate`, `chdate`, `lit_plugin`, `accession_number`, `dc_title`, `dc_creator`, `dc_subject`, `dc_description`, `dc_publisher`, `dc_contributor`, `dc_date`, `dc_type`, `dc_format`, `dc_identifier`, `dc_source`, `dc_language`, `dc_relation`, `dc_coverage`, `dc_rights`) VALUES ('54181f281faa777941acc252aebaf26d', 'studip', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Gvk', '387042768', 'Quickguide Strahlenschutz : [Aufgaben, Organisation, Schutzma�nahmen].', 'Wolf, Heike', '', '', 'Kissing : WEKA Media', '', '2004-01-01', '', '74 S : Ill.', '', '', 'ger', '[Der Strahlenschutzbeauftragte in Medizin und Technik / Heike Wolf] Praxisl�sungen', '', '');
INSERT IGNORE INTO `lit_catalog` (`catalog_id`, `user_id`, `mkdate`, `chdate`, `lit_plugin`, `accession_number`, `dc_title`, `dc_creator`, `dc_subject`, `dc_description`, `dc_publisher`, `dc_contributor`, `dc_date`, `dc_type`, `dc_format`, `dc_identifier`, `dc_source`, `dc_language`, `dc_relation`, `dc_coverage`, `dc_rights`) VALUES ('d6623a3c2b8285fb472aa759150148ad', 'studip', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Gvk', '387042253', 'R�ntgenverordnung : (R�V) ; Verordnung �ber den Schutz vor Sch�den durch R�ntgenstrahlen.', 'Wolf, Heike', '', '', 'Kissing : WEKA Media', '', '2004-01-01', '', '50 S.', '', '', 'ger', '[Der Strahlenschutzbeauftragte in Medizin und Technik / Heike Wolf] Praxisl�sungen', '', '');
INSERT IGNORE INTO `lit_catalog` (`catalog_id`, `user_id`, `mkdate`, `chdate`, `lit_plugin`, `accession_number`, `dc_title`, `dc_creator`, `dc_subject`, `dc_description`, `dc_publisher`, `dc_contributor`, `dc_date`, `dc_type`, `dc_format`, `dc_identifier`, `dc_source`, `dc_language`, `dc_relation`, `dc_coverage`, `dc_rights`) VALUES ('15074ad4f2bd2c57cbc9dfb343c1355b', 'studip', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Gvk', '384065813', 'Der Kater mit Hut', 'Geisel, Theodor Seuss', '', '', 'M�nchen [u.a.] : Piper', '', '2004-01-01', '', '75 S : zahlr. Ill ; 19 cm.', 'ISBN: 349224078X (kart.)', '', 'ger', 'Serie Piper ;, 4078', '', '');
INSERT IGNORE INTO `lit_catalog` (`catalog_id`, `user_id`, `mkdate`, `chdate`, `lit_plugin`, `accession_number`, `dc_title`, `dc_creator`, `dc_subject`, `dc_description`, `dc_publisher`, `dc_contributor`, `dc_date`, `dc_type`, `dc_format`, `dc_identifier`, `dc_source`, `dc_language`, `dc_relation`, `dc_coverage`, `dc_rights`) VALUES ('ce704bbc9453994daa05d76d2d04aba0', 'studip', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Gvk', '379252104', 'Die volkswirtschaftliche Perspektive', 'Heise, Michael', '', '', 'In: Zeitschrift f�r das gesamte Kreditwesen, Vol. 57, No. 4 (2004), p. 211-217, Frankfurt, M. : Knapp', 'Kater, Ulrich;', '2004-01-01', '', 'graph. Darst.', '', '', 'ger', '', '', '');
INSERT IGNORE INTO `lit_catalog` (`catalog_id`, `user_id`, `mkdate`, `chdate`, `lit_plugin`, `accession_number`, `dc_title`, `dc_creator`, `dc_subject`, `dc_description`, `dc_publisher`, `dc_contributor`, `dc_date`, `dc_type`, `dc_format`, `dc_identifier`, `dc_source`, `dc_language`, `dc_relation`, `dc_coverage`, `dc_rights`) VALUES ('b5d115a7f7cad02b4535fb3090bf18da', 'studip', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Gvk', '386883831', 'E-Learning: Qualit�t und Nutzerakzeptanz sichern : Beitr�ge zur Planung, Umsetzung und Evaluation multimedialer und netzgest�tzter Anwendungen', 'Zinke, Gert', '', '', 'Bielefeld : Bertelsmann', 'H�rtel, Michael; Bundesinstitut f�r Berufsbildung, ;', '2004-01-01', '', '159 S : graph. Darst ; 225 mm x 155 mm.', 'ISBN: 3763910204', '', 'ger', 'Berichte zur beruflichen Bildung ;, 265', '', '');

-- 
-- Daten f�r Tabelle `lit_list`
-- 

INSERT IGNORE INTO `lit_list` (`list_id`, `range_id`, `name`, `format`, `user_id`, `mkdate`, `chdate`, `priority`, `visibility`) VALUES ('3332f270b96fb23cdd2463cef8220b29', '834499e2b8a2cd71637890e5de31cba3', 'Basisliteratur der Veranstaltung', '**{dc_creator}** |({dc_contributor})||\r\n{dc_title}||\r\n{dc_identifier}||\r\n%%{published}%%||\r\n{note}||\r\n[{lit_plugin}]{external_link}|\r\n', '76ed43ef286fb55cf9e41beadb484a9f', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 1, 1);

-- 
-- Daten f�r Tabelle `lit_list_content`
-- 

INSERT IGNORE INTO `lit_list_content` (`list_element_id`, `list_id`, `catalog_id`, `user_id`, `mkdate`, `chdate`, `note`, `priority`) VALUES ('1e6d6e6f179986f8c2be5b1c2ed37631', '3332f270b96fb23cdd2463cef8220b29', '15074ad4f2bd2c57cbc9dfb343c1355b', '76ed43ef286fb55cf9e41beadb484a9f', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '', 1);
INSERT IGNORE INTO `lit_list_content` (`list_element_id`, `list_id`, `catalog_id`, `user_id`, `mkdate`, `chdate`, `note`, `priority`) VALUES ('4bd3001d8260001914e9ab8716a4fe70', '3332f270b96fb23cdd2463cef8220b29', 'ce704bbc9453994daa05d76d2d04aba0', '76ed43ef286fb55cf9e41beadb484a9f', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '', 2);
INSERT IGNORE INTO `lit_list_content` (`list_element_id`, `list_id`, `catalog_id`, `user_id`, `mkdate`, `chdate`, `note`, `priority`) VALUES ('ce226125c3cf579cf28e5c96a8dea7a9', '3332f270b96fb23cdd2463cef8220b29', '54181f281faa777941acc252aebaf26d', '76ed43ef286fb55cf9e41beadb484a9f', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '', 3);
INSERT IGNORE INTO `lit_list_content` (`list_element_id`, `list_id`, `catalog_id`, `user_id`, `mkdate`, `chdate`, `note`, `priority`) VALUES ('1d4ff2d55489dd9284f6a83dfc69149e', '3332f270b96fb23cdd2463cef8220b29', 'd6623a3c2b8285fb472aa759150148ad', '76ed43ef286fb55cf9e41beadb484a9f', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '', 4);
INSERT IGNORE INTO `lit_list_content` (`list_element_id`, `list_id`, `catalog_id`, `user_id`, `mkdate`, `chdate`, `note`, `priority`) VALUES ('293e90c3c6511d2c8e1d4ba7b51daa98', '3332f270b96fb23cdd2463cef8220b29', 'b5d115a7f7cad02b4535fb3090bf18da', '76ed43ef286fb55cf9e41beadb484a9f', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '', 5);

-- 
-- Daten f�r Tabelle `news`
-- 

INSERT IGNORE INTO `news` (`news_id`, `topic`, `body`, `author`, `date`, `user_id`, `expire`, `allow_comments`, `chdate`, `chdate_uid`, `mkdate`) VALUES ('29f2932ce32be989022c6f43b866e744', 'Herzlich Willkommen!', 'Das Stud.IP-Team heisst sie herzlich willkommen. \r\nBitte schauen Sie sich ruhig um!\r\n\r\nWenn Sie das System selbst installiert haben und diese News sehen, haben Sie die Demonstrationsdaten in die Datenbank eingef�gt. Wenn Sie produktiv mit dem System arbeiten wollen, sollten Sie diese Daten sp�ter wieder l�schen, da die Passw�rter der Accounts (vor allem des root-Accounts) �ffentlich bekannt sind.', 'Root Studip', UNIX_TIMESTAMP(), '76ed43ef286fb55cf9e41beadb484a9f', 14562502, 1, UNIX_TIMESTAMP(), '', UNIX_TIMESTAMP());

-- 
-- Daten f�r Tabelle `news_range`
-- 

INSERT IGNORE INTO `news_range` (`news_id`, `range_id`) VALUES ('29f2932ce32be989022c6f43b866e744', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `news_range` (`news_id`, `range_id`) VALUES ('29f2932ce32be989022c6f43b866e744', 'studip');

-- 
-- Daten f�r Tabelle `px_topics`
-- 

INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('5260172c3d6f9d56d21b06bf4c278b52', '0', '5260172c3d6f9d56d21b06bf4c278b52', 'Allgemeine Diskussionen', 'Hier ist Raum f�r allgemeine Diskussionen', 1084723039, 1084723039, '', '134.76.62.67', 'ec2e364b28357106c0f8c282733dbe56', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('b30ec732ee1c69a275b2d6adaae49cdc', '0', 'b30ec732ee1c69a275b2d6adaae49cdc', 'Allgemeine Diskussionen', 'Hier ist Raum f�r allgemeine Diskussionen', 1084723053, 1084723053, '', '134.76.62.67', '7a4f19a0a2c321ab2b8f7b798881af7c', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('9f394dffd08043f13cc65ffff65bfa05', '0', '9f394dffd08043f13cc65ffff65bfa05', 'Allgemeine Diskussionen', 'Hier ist Raum f�r allgemeine Diskussionen', 1084723061, 1084723061, '', '134.76.62.67', '110ce78ffefaf1e5f167cd7019b728bf', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('b82dbe1088c76b8b3dee91582359081e', '0', 'b82dbe1088c76b8b3dee91582359081e', 'Vorlesung am 12.07.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527931, 1142527931, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('b51842880f11da0c6d13e3211909e6ba', '0', 'b51842880f11da0c6d13e3211909e6ba', 'Sitzung am 10.07.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527930, 1142527930, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('a97c9f55cd83f52c3fc06729bb7c7ff8', '0', 'a97c9f55cd83f52c3fc06729bb7c7ff8', 'Vorlesung am 05.07.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527929, 1142527929, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('0f028d4739fccf3cc3cde43a39701aae', '0', '0f028d4739fccf3cc3cde43a39701aae', 'Sitzung am 03.07.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527928, 1142527928, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('d0bf755d257733b1303b5e06ac75f626', '0', 'd0bf755d257733b1303b5e06ac75f626', 'Vorlesung am 28.06.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527927, 1142527927, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('b5c7f562d51a7d4c7b28017629cd9495', '0', 'b5c7f562d51a7d4c7b28017629cd9495', 'Sitzung am 26.06.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527926, 1142527926, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('9b80db61208e062378c5ce8153972ba3', '0', '9b80db61208e062378c5ce8153972ba3', 'Vorlesung am 21.06.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527925, 1142527925, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('121f549504675721701bc634b2bb50fc', '0', '121f549504675721701bc634b2bb50fc', 'Sitzung am 19.06.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527924, 1142527924, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('4ab99993c5faf4abdea42ba7a89c917b', '0', '4ab99993c5faf4abdea42ba7a89c917b', 'Vorlesung am 14.06.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527923, 1142527923, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('5cf801402d62fc501e31d4d852821352', '0', '5cf801402d62fc501e31d4d852821352', 'Sitzung am 12.06.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527922, 1142527922, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('300d0696f6884b8551e9dcdca17da679', '0', '300d0696f6884b8551e9dcdca17da679', 'Vorlesung am 07.06.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527921, 1142527921, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('e18ffd416f1a94ee8394f10c0ee0315e', '0', 'e18ffd416f1a94ee8394f10c0ee0315e', 'Vorlesung am 31.05.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527920, 1142527920, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('5aaab41aafcdf736e1fc1fbf6185e81c', '0', '5aaab41aafcdf736e1fc1fbf6185e81c', 'Sitzung am 29.05.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527919, 1142527919, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('291d467f21ec65233fce3ca1837d8856', '0', '291d467f21ec65233fce3ca1837d8856', 'Vorlesung am 24.05.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527918, 1142527918, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('4fff4add4288d2fb3233cc6f40e02552', '0', '4fff4add4288d2fb3233cc6f40e02552', 'Sitzung am 22.05.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527917, 1142527917, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('792d18d3425c0c8df3573df3e3b7f326', '0', '792d18d3425c0c8df3573df3e3b7f326', 'Vorlesung am 17.05.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527916, 1142527916, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('1a56866ca4ed0abd0019b1d336eebc77', '0', '1a56866ca4ed0abd0019b1d336eebc77', 'Sitzung am 15.05.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527915, 1142527915, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('aed162c905f2cdc44ae8c446299a7679', '0', 'aed162c905f2cdc44ae8c446299a7679', 'Vorlesung am 10.05.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527914, 1142527914, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('6658d14dc84decbf0918f2a282ce574c', '0', '6658d14dc84decbf0918f2a282ce574c', 'Sitzung am 08.05.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527913, 1142527913, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('671fa8b02931fdb6e467ad55b3600171', '0', '671fa8b02931fdb6e467ad55b3600171', 'Vorlesung am 03.05.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527912, 1142527912, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('085324f24e447478077055b69d918ab0', '0', '085324f24e447478077055b69d918ab0', 'Vorlesung am 26.04.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527911, 1142527911, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('cf2e2cb863b166416ca182abeae0f6b0', '0', 'cf2e2cb863b166416ca182abeae0f6b0', 'Sitzung am 24.04.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527910, 1142527910, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('265d16c046159c9731f96101f91b9776', '0', '265d16c046159c9731f96101f91b9776', 'Vorlesung am 19.04.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527909, 1142527909, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('635213bd243476a66df5d4ed23d609f9', '0', '635213bd243476a66df5d4ed23d609f9', 'Vorlesung am 12.04.2006', 'Hier kann zu diesem Termin diskutiert werden', 1142527908, 1142527908, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('13d38de8a6a039d23bc542e27c15ab28', '0', '13d38de8a6a039d23bc542e27c15ab28', 'Allgemeine Diskussionen', 'Hier ist Raum f�r allgemeine Diskussionen', 1142527873, 1142527873, 'Root Studip', '192.168.0.15', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');

-- 
-- Daten f�r Tabelle `range_tree`
-- 

INSERT IGNORE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('3f93863e3d37ba0df286a6e7e26974ef', 'root', 0, 0, 'Einrichtungen der Universit�t', '', '');
INSERT IGNORE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('1323254564871354786157481484621', '3f93863e3d37ba0df286a6e7e26974ef', 1, 0, '', 'inst', '1535795b0d6ddecac6813f5f6ac47ef2');
INSERT IGNORE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('ce6c87bbf759b4cfd6f92d0c5560da5c', '1323254564871354786157481484621', 0, 0, 'Test Einrichtung', 'inst', '2560f7c7674942a7dce8eeb238e15d93');
INSERT IGNORE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('2f4f90ac9d8d832cc8c8a95910fde4eb', '1323254564871354786157481484621', 0, 1, 'Test Lehrstuhl', 'inst', '536249daa596905f433e1f73578019db');
INSERT IGNORE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('5d032f70c255f3e57cf8aa85a429ad4e', '1323254564871354786157481484621', 0, 2, 'Test Abteilung', 'inst', 'f02e2b17bc0e99fc885da6ac4c2532dc');
INSERT IGNORE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('a3d977a66f0010fa8e15c27dd71aff63', 'root', 0, 1, 'externe Bildungseinrichtungen', 'fak', 'ec2e364b28357106c0f8c282733dbe56');
INSERT IGNORE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('e0ff0ead6a8c5191078ed787cd7c0c1f', 'a3d977a66f0010fa8e15c27dd71aff63', 0, 0, 'externe Einrichtung A', 'inst', '7a4f19a0a2c321ab2b8f7b798881af7c');
INSERT IGNORE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('105b70b72dc1908ce2925e057c4a8daa', 'a3d977a66f0010fa8e15c27dd71aff63', 0, 1, 'externe Einrichtung B', 'inst', '110ce78ffefaf1e5f167cd7019b728bf');

-- 
-- Daten f�r Tabelle `sem_tree`
-- 

INSERT IGNORE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`) VALUES ('5b73e28644a3e259a6e0bc1e1499773c', 'root', 1, '', '', '1535795b0d6ddecac6813f5f6ac47ef2');
INSERT IGNORE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`) VALUES ('439618ae57d8c10dcaabcf7e21bcc1d9', '5b73e28644a3e259a6e0bc1e1499773c', 0, '', 'Test Studienbereich A', NULL);
INSERT IGNORE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`) VALUES ('5c41d2b4a5a8338e069dda987a624b74', '5b73e28644a3e259a6e0bc1e1499773c', 1, '', 'Test Studienbereich B', NULL);
INSERT IGNORE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`) VALUES ('3d39528c1d560441fd4a8cb0b7717285', '439618ae57d8c10dcaabcf7e21bcc1d9', 0, '', 'Test Studienbereich A-1', NULL);
INSERT IGNORE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`) VALUES ('dd7fff9151e85e7130cdb684edf0c370', '439618ae57d8c10dcaabcf7e21bcc1d9', 1, '', 'Test Studienbereich A-2', NULL);
INSERT IGNORE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`) VALUES ('01c8b1d188be40c5ac64b54a01aae294', '5b73e28644a3e259a6e0bc1e1499773c', 2, '', 'Test Studienbereich C', NULL);

-- 
-- Daten f�r Tabelle `seminar_inst`
-- 

INSERT IGNORE INTO `seminar_inst` (`seminar_id`, `institut_id`) VALUES ('834499e2b8a2cd71637890e5de31cba3', '2560f7c7674942a7dce8eeb238e15d93');

-- 
-- Daten f�r Tabelle `seminar_sem_tree`
-- 

INSERT IGNORE INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES ('834499e2b8a2cd71637890e5de31cba3', '3d39528c1d560441fd4a8cb0b7717285');
INSERT IGNORE INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES ('834499e2b8a2cd71637890e5de31cba3', '5c41d2b4a5a8338e069dda987a624b74');
INSERT IGNORE INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES ('834499e2b8a2cd71637890e5de31cba3', 'dd7fff9151e85e7130cdb684edf0c370');

-- 
-- Daten f�r Tabelle `seminar_user`
-- 

INSERT IGNORE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`, `comment`) VALUES ('834499e2b8a2cd71637890e5de31cba3', '205f3efb7997a0fc9755da2b535038da', 'dozent', 2, '',UNIX_TIMESTAMP(), '');
INSERT IGNORE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`, `comment`) VALUES ('834499e2b8a2cd71637890e5de31cba3', '7e81ec247c151c02ffd479511e24cc03', 'tutor', 2, '',UNIX_TIMESTAMP(), '');
INSERT IGNORE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`, `comment`) VALUES ('834499e2b8a2cd71637890e5de31cba3', 'e7a0a84b161f3e8c09b4a0a2e8a58147', 'autor', 2, '',UNIX_TIMESTAMP(), '');

-- 
-- Daten f�r Tabelle `seminare`
-- 

INSERT IGNORE INTO `seminare` (`Seminar_id`, `VeranstaltungsNummer`, `Institut_id`, `Name`, `Untertitel`, `status`, `Beschreibung`, `Ort`, `Sonstiges`, `Passwort`, `Lesezugriff`, `Schreibzugriff`, `start_time`, `duration_time`, `art`, `teilnehmer`, `vorrausetzungen`, `lernorga`, `leistungsnachweis`, `metadata_dates`, `mkdate`, `chdate`, `ects`, `admission_endtime`, `admission_turnout`, `admission_binding`, `admission_type`, `admission_selection_take_place`, `admission_group`, `admission_prelim`, `admission_prelim_txt`, `admission_starttime`, `admission_endtime_sem`, `visible`, `showscore`, `modules`) VALUES ('834499e2b8a2cd71637890e5de31cba3', '1234', '2560f7c7674942a7dce8eeb238e15d93', 'Test Lehrveranstaltung', 'eine normale Lehrveranstaltung', 1, '', '', '', '', 1, 1, 1143842400, 0, '', 'f�r alle Studierenden', 'abgeschlossenes Grundstudium', 'Referate in Gruppenarbeit', 'Klausur', 'a:5:{s:3:"art";s:1:"0";s:12:"start_termin";i:-1;s:11:"start_woche";s:1:"0";s:6:"turnus";s:1:"0";s:11:"turnus_data";a:2:{i:0;a:9:{s:3:"idx";s:5:"11000";s:3:"day";s:1:"1";s:12:"start_stunde";i:10;s:12:"start_minute";i:0;s:10:"end_stunde";i:12;s:10:"end_minute";i:0;s:4:"room";s:9:"H�rsaal 1";s:11:"resource_id";b:0;s:4:"desc";s:7:"Sitzung";}i:1;a:9:{s:3:"idx";s:5:"31100";s:3:"day";s:1:"3";s:12:"start_stunde";i:11;s:12:"start_minute";i:0;s:10:"end_stunde";i:12;s:10:"end_minute";i:0;s:4:"room";s:9:"H�rsaal 2";s:11:"resource_id";b:0;s:4:"desc";s:9:"Vorlesung";}}}', 1084723360, 1142527813, '4', -1, 0, 0, 0, 0, '', 0, '', -1, -1, 1, 0, 431);
-- 
-- Daten f�r Tabelle `statusgruppe_user`
-- 

INSERT IGNORE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`) VALUES ('efb56e092f33cb78a8766676042dc1c5', '7e81ec247c151c02ffd479511e24cc03', 1);
INSERT IGNORE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`) VALUES ('5d40b1fc0434e6589d7341a3ee742baf', '205f3efb7997a0fc9755da2b535038da', 1);

-- 
-- Daten f�r Tabelle `statusgruppen`
-- 

INSERT IGNORE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`) VALUES ('86498c641ccf4f4d4e02f4961ccc3829', 'Lehrbeauftragte', '2560f7c7674942a7dce8eeb238e15d93', 3, 0, 0,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());
INSERT IGNORE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`) VALUES ('600403561c21a50ae8b4d41655bd2191', 'HochschullehrerIn', '2560f7c7674942a7dce8eeb238e15d93', 4, 0, 0,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());
INSERT IGNORE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`) VALUES ('efb56e092f33cb78a8766676042dc1c5', 'wiss. MitarbeiterIn', '2560f7c7674942a7dce8eeb238e15d93', 2, 0, 0,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());
INSERT IGNORE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`) VALUES ('5d40b1fc0434e6589d7341a3ee742baf', 'DirektorIn', '2560f7c7674942a7dce8eeb238e15d93', 1, 0, 0,UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 
-- Daten f�r Tabelle `studiengaenge`
-- 

INSERT IGNORE INTO `studiengaenge` (`studiengang_id`, `name`, `beschreibung`, `mkdate`, `chdate`) VALUES ('63b13b29db6adcf0e2814a6388d4583c', 'Test Studiengang 1', '', UNIX_TIMESTAMP(),UNIX_TIMESTAMP());
INSERT IGNORE INTO `studiengaenge` (`studiengang_id`, `name`, `beschreibung`, `mkdate`, `chdate`) VALUES ('4a55e9df07a18e76ebb84e27ae212b30', 'Test Studiengang 2', '',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

-- 
-- Daten f�r Tabelle `termine`
-- 


INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('0a8a1920cb2c939757d05bc39749bbf0', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1144832400, 1144836000, 1142527908, 1142527908, 7, '635213bd243476a66df5d4ed23d609f9', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('8e41494f680f267bdcada36f971a14b0', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1152694800, 1152698400, 1142527908, 1142527908, 7, 'b82dbe1088c76b8b3dee91582359081e', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('d1170c49f7ec56be106015204a33acd2', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1152518400, 1152525600, 1142527908, 1142527908, 1, 'b51842880f11da0c6d13e3211909e6ba', 'H�rsaal 1');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('941e01aac1eb29d4c82cd4c1dfd5745e', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1152090000, 1152093600, 1142527908, 1142527908, 7, 'a97c9f55cd83f52c3fc06729bb7c7ff8', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('77f030503ebe4bb6c702f43cc0190737', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1151913600, 1151920800, 1142527908, 1142527908, 1, '0f028d4739fccf3cc3cde43a39701aae', 'H�rsaal 1');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('53d72133db428d947e7c75cb0280ec16', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1151485200, 1151488800, 1142527908, 1142527908, 7, 'd0bf755d257733b1303b5e06ac75f626', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('f8b95870511e7b29c50f088032a8efc5', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1151308800, 1151316000, 1142527908, 1142527908, 1, 'b5c7f562d51a7d4c7b28017629cd9495', 'H�rsaal 1');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('cf40370b7bfa034725c5c9b0314172fd', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1150880400, 1150884000, 1142527908, 1142527908, 7, '9b80db61208e062378c5ce8153972ba3', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('3f76744fcb5b426986846a406cf45474', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1150704000, 1150711200, 1142527908, 1142527908, 1, '121f549504675721701bc634b2bb50fc', 'H�rsaal 1');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('285d267aaee089a8f8b9581b8ba9b496', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1150275600, 1150279200, 1142527908, 1142527908, 7, '4ab99993c5faf4abdea42ba7a89c917b', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('8718d8aba2646a81e17937c97790deb4', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1150099200, 1150106400, 1142527908, 1142527908, 1, '5cf801402d62fc501e31d4d852821352', 'H�rsaal 1');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('f46618fb55c06822d38560e8623fc4fb', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1149670800, 1149674400, 1142527908, 1142527908, 7, '300d0696f6884b8551e9dcdca17da679', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('e4e32ed355a52998aaccdec967fe9bd5', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1149066000, 1149069600, 1142527908, 1142527908, 7, 'e18ffd416f1a94ee8394f10c0ee0315e', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('ee1210754cb1f2e244f8150cd67b21c0', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1148889600, 1148896800, 1142527908, 1142527908, 1, '5aaab41aafcdf736e1fc1fbf6185e81c', 'H�rsaal 1');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('c985313bd1dcf8d5a52c92850ec17489', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1148461200, 1148464800, 1142527908, 1142527908, 7, '291d467f21ec65233fce3ca1837d8856', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('60872a4bbfb1b28c379039663d3b62ec', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1148284800, 1148292000, 1142527908, 1142527908, 1, '4fff4add4288d2fb3233cc6f40e02552', 'H�rsaal 1');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('8c81aacd7024a68736c8fb22e1c10a2d', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1147856400, 1147860000, 1142527908, 1142527908, 7, '792d18d3425c0c8df3573df3e3b7f326', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('9a1c4d5cbacd0569710197e076726eeb', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1145437200, 1145440800, 1142527908, 1142527908, 7, '265d16c046159c9731f96101f91b9776', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('c111341f2051314747853172d1871fd6', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1145865600, 1145872800, 1142527908, 1142527908, 1, 'cf2e2cb863b166416ca182abeae0f6b0', 'H�rsaal 1');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('c614e4c6aa2b2da98585ef605dc2ae9a', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1146042000, 1146045600, 1142527908, 1142527908, 7, '085324f24e447478077055b69d918ab0', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('3e187bd8f92b002f549ac1bb139546cc', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1146646800, 1146650400, 1142527908, 1142527908, 7, '671fa8b02931fdb6e467ad55b3600171', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('f1f0ae844015bf93acab243f16f1f252', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1147075200, 1147082400, 1142527908, 1142527908, 1, '6658d14dc84decbf0918f2a282ce574c', 'H�rsaal 1');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('72add02e4774d843c75302bd65b79cfc', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1147251600, 1147255200, 1142527908, 1142527908, 7, 'aed162c905f2cdc44ae8c446299a7679', 'H�rsaal 2');
INSERT IGNORE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`) VALUES ('29b31c1b96a09d3e2a9fcba95a36e323', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1147680000, 1147687200, 1142527908, 1142527908, 1, '1a56866ca4ed0abd0019b1d336eebc77', 'H�rsaal 1');


-- 
-- Daten f�r Tabelle `user_info`
-- 

INSERT IGNORE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `guestbook`, `email_forward`, `smiley_favorite`, `smiley_favorite_publish`) VALUES ('76ed43ef286fb55cf9e41beadb484a9f', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, 0, '', 0);
INSERT IGNORE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `guestbook`, `email_forward`, `smiley_favorite`, `smiley_favorite_publish`) VALUES ('e7a0a84b161f3e8c09b4a0a2e8a58147', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, 0, '', 0);
INSERT IGNORE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `guestbook`, `email_forward`, `smiley_favorite`, `smiley_favorite_publish`) VALUES ('205f3efb7997a0fc9755da2b535038da', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, 0, '', 0);
INSERT IGNORE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `guestbook`, `email_forward`, `smiley_favorite`, `smiley_favorite_publish`) VALUES ('6235c46eb9e962866ebdceece739ace5', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, 0, '', 0);
INSERT IGNORE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `guestbook`, `email_forward`, `smiley_favorite`, `smiley_favorite_publish`) VALUES ('7e81ec247c151c02ffd479511e24cc03', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, 0, '', 0);

-- 
-- Daten f�r Tabelle `user_inst`
-- 

INSERT IGNORE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES ('205f3efb7997a0fc9755da2b535038da', '2560f7c7674942a7dce8eeb238e15d93', 'dozent', '', '', '', '', 0, 0, 1);
INSERT IGNORE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES ('6235c46eb9e962866ebdceece739ace5', '2560f7c7674942a7dce8eeb238e15d93', 'admin', '', '', '', '', 0, 0, 1);
INSERT IGNORE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES ('7e81ec247c151c02ffd479511e24cc03', '2560f7c7674942a7dce8eeb238e15d93', 'tutor', '', '', '', '', 0, 0, 1);
INSERT IGNORE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES ('e7a0a84b161f3e8c09b4a0a2e8a58147', '2560f7c7674942a7dce8eeb238e15d93', 'user', '', '', '', '', 0, 0, 1);

-- 
-- Daten f�r Tabelle `vote`
-- 

INSERT IGNORE INTO `vote` (`vote_id`, `author_id`, `range_id`, `type`, `title`, `question`, `state`, `startdate`, `stopdate`, `timespan`, `mkdate`, `chdate`, `resultvisibility`, `multiplechoice`, `anonymous`, `changeable`, `co_visibility`, `namesvisibility`) VALUES ('b5329b23b7f865c62028e226715e1914', '76ed43ef286fb55cf9e41beadb484a9f', 'studip', 'vote', 'Nutzen Sie bereits Stud.IP?', 'Haben Sie Stud.IP bereits im Einsatz oder planen Sie, es einzusetzen?', 'active', 1142525040, NULL, NULL, 1142525062, 1142527620, 'delivery', 1, 0, 1, NULL, 0);
-- 
-- Daten f�r Tabelle `voteanswers`
-- 

INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('5fb01b6623c848c3bf33cce70675b91a', 'b5329b23b7f865c62028e226715e1914', 'Ich bin nicht interessiert', 12, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('03bce9c940fc76f5eb90ab7b151cf34d', 'b5329b23b7f865c62028e226715e1914', 'Ich schaue mich nur mal um', 11, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('dddf684fbcac58f7ffd0804b7095c71b', 'b5329b23b7f865c62028e226715e1914', 'Ich plane, es demn�chst einzusetzen', 10, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('6f51e5d957aa6e7a3e8494e0e56c43aa', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 1.2', 8, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('f31fab58d15388245396dc59de346e90', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 1.3', 9, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('8502e4b4600a12b2d5d43aefe2930be4', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 1.1.5', 7, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('8112e4b4600a12b2d5d43aecf2930be4', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 1.1.0', 6, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('8342e4b4600a12b2d5d43aecf2930be4', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 1.0', 5, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('dc1b49bf35e9cfbfcece807b21cec0ef', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 0.9.5', 4, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('ddfd889094a6cea75703728ee7b48806', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 0.9.0', 3, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('58281eda805a0fe5741c74a2c612cb05', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 0.8.15', 2, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('c8ade4c7f3bbe027f6c19016dd3e001c', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 0.8.0', 1, 0, 0);
INSERT IGNORE INTO `voteanswers` (`answer_id`, `vote_id`, `answer`, `position`, `counter`, `correct`) VALUES ('112f7c8f52b0a2a6eff9cddf93b419c7', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 0.7.5', 0, 0, 0);
