<?php
/**
 * Adds english help tours.
 */
class HelpToursEn extends Migration
{
    function description()
    {
        return 'Adds english help tours.';
    }
    
    function up()
    {
        $this->addHelpToursEN();
    }
    
    function down()
    {
        DBManager::get()->exec("DELETE FROM `help_tours` WHERE `tour_id` = '7af1e1fb7f53c910ba9f42f43a71c723' OR `tour_id` = 'c89ce8e097f212e75686f73cc5008711' OR `tour_id` = 'de1fbce508d01cbd257f9904ff8c3b43' OR `tour_id` = '1badcf28ab5b206d9150b2b9683b4cb6' OR `tour_id` = 'fa963d2ca827b28e0082e98aafc88765' OR `tour_id` = 'f0aeb0f6c4da3bd61f48b445d9b30dc1' OR `tour_id` = '3dbe7099f82dcdbba4580acb1105a0d6' OR `tour_id` = '9e9dca9b1214294b9605824bfe90fba1' OR `tour_id` = '89786eac42f52ac316790825b4f5c0b2' OR `tour_id` = 'e41611616675b218845fe9f55bc11cf6' OR `tour_id` = '83dc1d25e924f2748ee3293aaf0ede8e' OR `tour_id` = '588effa83da976a889a68c152bcabc90' OR `tour_id` = 'd9913517f9c81d2c0fa8362592ce5d0e' OR `tour_id` = '05434e40601a9a2a7f5fa8208ae148c1'");
        DBManager::get()->exec("DELETE FROM `help_tour_steps` WHERE `tour_id` = '7af1e1fb7f53c910ba9f42f43a71c723' OR `tour_id` = 'c89ce8e097f212e75686f73cc5008711' OR `tour_id` = 'de1fbce508d01cbd257f9904ff8c3b43' OR `tour_id` = '1badcf28ab5b206d9150b2b9683b4cb6' OR `tour_id` = 'fa963d2ca827b28e0082e98aafc88765' OR `tour_id` = 'f0aeb0f6c4da3bd61f48b445d9b30dc1' OR `tour_id` = '3dbe7099f82dcdbba4580acb1105a0d6' OR `tour_id` = '9e9dca9b1214294b9605824bfe90fba1' OR `tour_id` = '89786eac42f52ac316790825b4f5c0b2' OR `tour_id` = 'e41611616675b218845fe9f55bc11cf6' OR `tour_id` = '83dc1d25e924f2748ee3293aaf0ede8e' OR `tour_id` = '588effa83da976a889a68c152bcabc90' OR `tour_id` = 'd9913517f9c81d2c0fa8362592ce5d0e' OR `tour_id` = '05434e40601a9a2a7f5fa8208ae148c1'");
        DBManager::get()->exec("DELETE FROM `help_tour_settings` WHERE `tour_id` = '7af1e1fb7f53c910ba9f42f43a71c723' OR `tour_id` = 'c89ce8e097f212e75686f73cc5008711' OR `tour_id` = 'de1fbce508d01cbd257f9904ff8c3b43' OR `tour_id` = '1badcf28ab5b206d9150b2b9683b4cb6' OR `tour_id` = 'fa963d2ca827b28e0082e98aafc88765' OR `tour_id` = 'f0aeb0f6c4da3bd61f48b445d9b30dc1' OR `tour_id` = '3dbe7099f82dcdbba4580acb1105a0d6' OR `tour_id` = '9e9dca9b1214294b9605824bfe90fba1' OR `tour_id` = '89786eac42f52ac316790825b4f5c0b2' OR `tour_id` = 'e41611616675b218845fe9f55bc11cf6' OR `tour_id` = '83dc1d25e924f2748ee3293aaf0ede8e' OR `tour_id` = '588effa83da976a889a68c152bcabc90' OR `tour_id` = 'd9913517f9c81d2c0fa8362592ce5d0e' OR `tour_id` = '05434e40601a9a2a7f5fa8208ae148c1'");
    }
    
    function addHelpToursEN() {
        // add tour data
        $query = "INSERT IGNORE INTO `help_tours` (`tour_id`, `name`, `description`, `type`, `roles`, `version`, `language`, `studip_version`, `installation_id`, `mkdate`) VALUES
('7af1e1fb7f53c910ba9f42f43a71c723', 'Search', 'In this feature tour the most important search functions are explained', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427720631),
('c89ce8e097f212e75686f73cc5008711', 'Participant administration', 'The administration options of the participant administration are explained in this tour.', 'tour', 'tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427721030),
('de1fbce508d01cbd257f9904ff8c3b43', 'Profile page', 'The basic functions and areas of the profile page are presented in this tour.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427722058),
('1badcf28ab5b206d9150b2b9683b4cb6', 'My courses (lecturers)', 'The most important functions of the site \"My courses\" are presented in this tour.', 'tour', 'tutor,dozent,admin,root', 1, 'en', '', '', 1427722642),
('fa963d2ca827b28e0082e98aafc88765', 'My courses (students)', 'The most important functions of the site \"My courses\" are presented in this tour.', 'tour', 'autor', 1, 'en', '3.1', '', 1427723231),
('f0aeb0f6c4da3bd61f48b445d9b30dc1', 'Design of the start page', 'The functions and design possibilities of the start page are presented in this feature tour.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427723894),
('3dbe7099f82dcdbba4580acb1105a0d6', 'Administering the forum', 'The administration of the forum is explained in this tour.', 'tour', 'tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427724314),
('9e9dca9b1214294b9605824bfe90fba1', 'Create study group', 'In this tour the creation of study groups is explained', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427724645),
('89786eac42f52ac316790825b4f5c0b2', 'Use forum', 'The content of this tour is from the old tour of the forum (Sidebar > actions > start tour).', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427783673),
('e41611616675b218845fe9f55bc11cf6', 'Upload own picture', 'This tour explains how users can upload their own profile picture.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427784057),
('83dc1d25e924f2748ee3293aaf0ede8e', 'Blubber', 'This tour explains how to use \"Blubber\"', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427784655),
('588effa83da976a889a68c152bcabc90', 'Blubber', 'This tour explains how to use \"Blubber\"', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427784693),
('d9913517f9c81d2c0fa8362592ce5d0e', 'Blubber', 'This tour explains how to use \"Blubber\"', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427784720),
('05434e40601a9a2a7f5fa8208ae148c1', 'My documents', 'The personal document area will be presented in this tour.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1427786336);
";
        DBManager::get()->exec($query);
        
        // add steps
        $query = "INSERT IGNORE INTO `help_tour_steps` (`tour_id`, `step`, `title`, `tip`, `orientation`, `interactive`, `css_selector`, `route`, `author_email`, `mkdate`) VALUES
('7af1e1fb7f53c910ba9f42f43a71c723', 1, 'Search', 'This tour gives you an overview of the most important \"search\" functions \n\n\nIn order to reach the next step please click \"next\" on the bottom right', 'TL', 0, '', 'dispatch.php/search/courses', '', 1405519865),
('7af1e1fb7f53c910ba9f42f43a71c723', 2, 'Enter search term', 'A search term (such as event name, lecturer) can be entered in this input field.', 'B', 0, 'INPUT#search_sem_quick_search_1.ui-autocomplete-input', 'dispatch.php/search/courses', '', 1405520106),
('7af1e1fb7f53c910ba9f42f43a71c723', 3, 'Semester selection', 'With a click on the drop-down menu you can choose to which semester the search term should refer. \n\nThe current semester is set as standard.', 'TL', 0, 'SELECT#search_sem_sem', 'dispatch.php/search/courses', '', 1405520208),
('7af1e1fb7f53c910ba9f42f43a71c723', 4, 'Navigation', 'If you want to search only one particular area, you can select one here.', 'R', 0, '#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(0)', 'dispatch.php/search/courses', '', 1406121826),
('7af1e1fb7f53c910ba9f42f43a71c723', 5, 'Extended search', 'The search can be extended by further options with the extended search.', 'R', 0, 'A.options-checkbox.options-unchecked', 'dispatch.php/search/courses', '', 1405520436),
('7af1e1fb7f53c910ba9f42f43a71c723', 6, 'Quick search', 'The quick search is also available on other sites of Stud.IP at all times. After entering a key word it is confirmed with \"Enter\" or by clicking the magnifying glass on the right next to the field.', 'B', 0, 'INPUT#search_sem_quick_search_2.quicksearchbox.ui-autocomplete-input', 'dispatch.php/search/courses', '', 1405520634),
('7af1e1fb7f53c910ba9f42f43a71c723', 7, 'Further search possibilities', 'In addition to searching for events there is also the possibility to search the archive for persons, facilities, or resources.', 'R', 0, '#nav_search_resources A SPAN', 'dispatch.php/search/courses', '', 1405520751),
('c89ce8e097f212e75686f73cc5008711', 1, 'Participant administration', 'This tour gives an overview of the participant administration of an event.\r\n\r\nIn order to go to the next step please click \"next\" at the bottom right.', 'B', 0, '', 'dispatch.php/course/members', '', 1405688399),
('c89ce8e097f212e75686f73cc5008711', 2, 'Add persons', 'With these functions you can search for individual persons in Stud.IP and directly  select them as lecturer, tutor or author. It is also possible to insert a list of participants in order to allocate several persons as a tutor of the event at the same time.', 'R', 0, '#layout-sidebar SECTION DIV.sidebar-widget :eq(1)', 'dispatch.php/course/members', '', 1405688707),
('c89ce8e097f212e75686f73cc5008711', 3, 'Upgrade/ downgrade', 'In order to upgrade an already enroled person to a tutor, or to downgrade them to a reader select this person in the list and carry out the requested action by using the dropdown menu.', 'T', 0, '#autor CAPTION', 'dispatch.php/course/members', '', 1405690324),
('c89ce8e097f212e75686f73cc5008711', 4, 'Send circular e-mail', 'A circular e-mail can be sent to all participants of the event here.', 'R', 0, '#layout-sidebar SECTION DIV DIV UL LI A :eq(3)', 'dispatch.php/course/members', '', 1406636964),
('c89ce8e097f212e75686f73cc5008711', 5, 'Send circular e-mail to user group', 'There is further the possibility to send a circular e-mail to individual user groups.', 'BR', 0, '#layout_container #layout_content TABLE CAPTION SPAN A IMG :eq(0)', 'dispatch.php/course/members', '', 1406637123),
('c89ce8e097f212e75686f73cc5008711', 6, 'Create groups', 'The participants of the event can be divided into groups here.', 'R', 0, 'A#nav_course_edit_groups', 'dispatch.php/course/members', '', 1405689311),
('c89ce8e097f212e75686f73cc5008711', 7, 'Name group', 'You can search for a suitable group name in the templates and select it using the yellow double arrow. As an alternative you also have the possibility to determine a new group name by directly entering the name in the right field.', 'B', 0, 'SELECT', 'admin_statusgruppe.php', '', 1405689541),
('c89ce8e097f212e75686f73cc5008711', 8, 'Group size', 'With the field \"group size\" you can set the maximum number of participants of a group. If you do not require this, simply leave the field empty.', 'B', 0, 'INPUT#role_size', 'admin_statusgruppe.php', '', 1405689763),
('c89ce8e097f212e75686f73cc5008711', 9, 'Self-entry', 'If you activate the function \"self-entry\", the participants of the event can enter themselves in the groups.', 'B', 0, 'INPUT#self_assign', 'admin_statusgruppe.php', '', 1405689852),
('c89ce8e097f212e75686f73cc5008711', 10, 'Document folder', 'If you activate the function \"document folder\", an additional document folder will  be created per group. Group-specific documents can be uploaded to this folder.', 'B', 0, 'INPUT#group_folder', 'admin_statusgruppe.php', '', 1405689936),
('de1fbce508d01cbd257f9904ff8c3b43', 1, 'Profile tour', 'This tour gives you an overview of the most important functions of the \"profile\".\r\n\r\nIn order to reach the next step please click \"next\" on the bottom right.', 'T', 0, '', 'dispatch.php/profile', '', 1406722657),
('de1fbce508d01cbd257f9904ff8c3b43', 2, 'Personal picture', 'If you uploaded a picture, it will be displayed here. You can change it at all times.', 'RT', 0, '.avatar-normal', 'dispatch.php/profile', '', 1406722657),
('de1fbce508d01cbd257f9904ff8c3b43', 3, 'Stud.IP-Score', 'The Stud.IP-Score increases with the activities in Stud.IP and thus represents the experience with  Stud.IP.', 'BL', 0, '#layout_content TABLE:eq(0) TBODY:eq(0) TR:eq(0) TD:eq(0) A:eq(0)', 'dispatch.php/profile', '', 1406722657),
('de1fbce508d01cbd257f9904ff8c3b43', 4, 'Announcements', 'You can publish personal announcements on this site.', 'B', 0, '#layout_content SECTION HEADER H1 :eq(0)', 'dispatch.php/profile', '', 1406722657),
('de1fbce508d01cbd257f9904ff8c3b43', 5, 'New announcement', 'Click on the plus sign, if you would like to create an announcement.', 'BR', 0, '#layout_content SECTION HEADER NAV A :eq(0)', 'dispatch.php/profile', '', 1406722657),
('de1fbce508d01cbd257f9904ff8c3b43', 6, 'Personal details', 'Your picture and additional user data can be changed on these sites.', 'BL', 0, '#tabs li:eq(2)', 'dispatch.php/profile', '', 1406722657),
('1badcf28ab5b206d9150b2b9683b4cb6', 1, 'Help tour \"My event\"', 'This tour gives you an overview of the most important functions of the page \"My courses\".\r\n\r\nIn order to reach the next step please click \"next\" on the bottom right.', 'TL', 0, '', 'dispatch.php/my_courses', '', 1406125847),
('1badcf28ab5b206d9150b2b9683b4cb6', 2, 'Overview of events', 'The courses of the current and past semester are displayed here. New courses initially appear in red.', 'TL', 0, '#my_seminars TABLE THEAD TR TH :eq(2)', 'dispatch.php/my_courses', '', 1406125908),
('1badcf28ab5b206d9150b2b9683b4cb6', 3, 'Event details', 'With a click on the \"i\" a window appears with the most important facts of the courses.', 'T', 0, '#my_seminars TABLE THEAD TR TH :eq(3)', 'dispatch.php/my_courses', '', 1406125992),
('1badcf28ab5b206d9150b2b9683b4cb6', 4, 'Course contents', 'All contents (such as e.g. a forum) are displayed by corresponding symbols here.\n\nIf there were any news since the last login these will appear in red.', 'LT', 0, '#my_seminars TABLE THEAD TR TH :eq(4)', 'dispatch.php/my_courses', '', 1406126049),
('1badcf28ab5b206d9150b2b9683b4cb6', 5, 'Editing or deletion of an event', 'A click on the cog wheel enables you to edit a course.\n\nIf you have participant status in a course, you can sign out by clicking on the door icon.', 'TR', 0, '#my_seminars TABLE THEAD TR TH :eq(5)', 'dispatch.php/my_courses', '', 1406126134),
('1badcf28ab5b206d9150b2b9683b4cb6', 6, 'Adjustment to the event view', 'In order to adjust the course overview you can order your courses according to certain criteria (such as e.g. fields of study, lecturers, or colours).', 'R', 0, '#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(2)', 'dispatch.php/my_courses', '', 1406126281),
('1badcf28ab5b206d9150b2b9683b4cb6', 7, 'Access to an event of past and future semesters', 'For example, by clicking on the drop-down menu, courses from past semesters can be displayed.', 'R', 0, '#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(3)', 'dispatch.php/my_courses', '', 1406126316),
('1badcf28ab5b206d9150b2b9683b4cb6', 8, 'Further possible actions', 'Here you can mark all news as read, change colour groups as you please, and also adjust the notifications about activities in the individual courses.', 'R', 0, '#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(1)', 'dispatch.php/my_courses', '', 1406126374),
('1badcf28ab5b206d9150b2b9683b4cb6', 9, 'Study groups and facilities', 'There is moreover the possibility to access personal study groups or facilities.', 'R', 0, '#nav_browse_my_institutes A', 'dispatch.php/my_courses', '', 1406126415),
('fa963d2ca827b28e0082e98aafc88765', 1, 'Help tour \"My courses\"', 'This tour gives you an overview of the most important functions of the site \"My courses\".\n\nIn order to reach the next step please click \"next\" on the bottom right.', 'TL', 0, '', 'dispatch.php/my_courses', '', 1405521184),
('fa963d2ca827b28e0082e98aafc88765', 2, 'Overview of courses', 'The courses of the current and past semester are displayed here. New courses initially appear in red.', 'T', 0, '#my_seminars TABLE THEAD TR TH :eq(2)', 'dispatch.php/my_courses', '', 1405521244),
('fa963d2ca827b28e0082e98aafc88765', 3, 'Course details', 'With a click on the \"i\" a window appears with the most important benchmark data of the course.', 'T', 0, '#my_seminars TABLE THEAD TR TH :eq(3)', 'dispatch.php/my_courses', '', 1405931069),
('fa963d2ca827b28e0082e98aafc88765', 4, 'Course contents', 'All contents (such as e.g. a forum) are displayed by corresponding symbols here.\n\nIf there were any news since the last login these will appear in red.', 'LT', 0, '#my_seminars TABLE THEAD TR TH :eq(4)', 'dispatch.php/my_courses', '', 1405931225),
('fa963d2ca827b28e0082e98aafc88765', 5, 'Leaving the course', 'A click on the door icon enables a direct removal from the course', 'TR', 0, '#my_seminars TABLE THEAD TR TH :eq(5)', 'dispatch.php/my_courses', '', 1405931272),
('fa963d2ca827b28e0082e98aafc88765', 6, 'Access to archived courses', 'If courses have been archived, they can be accessed here.', 'RT', 0, 'A#nav_browse_archive', 'dispatch.php/my_courses', '', 1405931431),
('fa963d2ca827b28e0082e98aafc88765', 7, 'Adjustment to the course view', 'In order to adjust the course overview you can arrange your courses according to certain criteria (such as e.g. fields of study, lecturers or colours).', 'R', 0, '#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(2)', 'dispatch.php/my_courses', '', 1405932131),
('fa963d2ca827b28e0082e98aafc88765', 8, 'Access to an course of past and future semesters', 'By clicking on the drop-down menu courses from past semesters can be displayed for example.', 'R', 0, '#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(3)', 'dispatch.php/my_courses', '', 1405932230),
('fa963d2ca827b28e0082e98aafc88765', 9, 'Further possible actions', 'Here you can mark all news as read, change colour groups as you please, or\n\nalso adjust the notifications about activities in the individual events.', 'R', 0, '#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(1)', 'dispatch.php/my_courses', '', 1405932320),
('fa963d2ca827b28e0082e98aafc88765', 10, 'Study groups and institutes', 'There is moreover the possibility to access personal study groups or institutes.', 'R', 0, '#nav_browse_my_institutes A', 'dispatch.php/my_courses', '', 1405932519),
('f0aeb0f6c4da3bd61f48b445d9b30dc1', 1, 'Functions and design possibilities of the start page', 'This tour gives you an overview of the most important functions of the start page.\n\nIn order to reach the next step please click \"next\" on the bottom right', 'TL', 0, '', 'dispatch.php/start', '', 1405934926),
('f0aeb0f6c4da3bd61f48b445d9b30dc1', 2, 'Individual design of the start page', 'The default configuration of the start page is that the elements \"Quicklinks\", \"announcements\", \"my current appointments\" and  \"surveys\" are displayed. The elements are called widgets and  can be deleted, added and moved. Each widget can be individually added, deleted and moved.', 'TL', 0, '', 'dispatch.php/start', '', 1405934970),
('f0aeb0f6c4da3bd61f48b445d9b30dc1', 3, 'Add widget', 'Widgets can be added here. In addition to the standard widgets the personal timetable can, for example, be displayed on the start page. Newly added widgets appear right at the bottom on the start page. In addition, it is possible to jump directly to each widget in the sidebar.', 'R', 0, '#layout-sidebar SECTION DIV DIV UL LI :eq(4)', 'dispatch.php/start', '', 1405935192),
('f0aeb0f6c4da3bd61f48b445d9b30dc1', 4, 'Jump labels', 'In addition, it is possible to jump directly to each widget using jump labels.', 'R', 0, '#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(0)', 'dispatch.php/start', '', 1406623464),
('f0aeb0f6c4da3bd61f48b445d9b30dc1', 5, 'Position widget', 'A widget can be moved to the desired position using drag&drop: For this purpose you click into the headline of a widget, hold down the mouse button, and drag the widget to the desired position.', 'B', 0, '.widget-header', 'dispatch.php/start', '', 1405935687),
('f0aeb0f6c4da3bd61f48b445d9b30dc1', 6, 'Edit widget', 'With several widgets a further symbol is displayed in addition to the X for closing. The widget \"Quicklinks\", for example, can be adjusted individually by clicking on this button, the announcements can be subscribed to and appointments can be added with the actual appointments or timetable.', 'L', 0, '#layout_content DIV UL DIV SPAN A IMG :eq(0)', 'dispatch.php/start', '', 1405935792),
('f0aeb0f6c4da3bd61f48b445d9b30dc1', 7, 'Remove widget', 'Each widget can be removed by clicking on the X in the right upper corner. If required, it can be added again at all times.', 'R', 0, '.widget-header', 'dispatch.php/start', '', 1405935376),
('3dbe7099f82dcdbba4580acb1105a0d6', 1, 'Administering the forum', 'You have the possibility to look at a tour for the administration of the forum.\n\nIn order to begin the tour please click \"next\" at the bottom right.', 'TL', 0, '', 'plugins.php/coreforum', '', 1405418008),
('3dbe7099f82dcdbba4580acb1105a0d6', 2, 'Edit category', 'The name of the category can be changed or, however, the whole category deleted with these icons. The sectors will in this case be shifted into the category \"General\" and are thus retained.\n\nThe category \"General\" cannot be deleted and is therefore included in each forum.', 'BR', 0, '#forum #sortable_areas TABLE CAPTION #tutorCategoryIcons', 'plugins.php/coreforum', '', 1405424216),
('3dbe7099f82dcdbba4580acb1105a0d6', 3, 'Edit area', 'Action icons will appear, if the cursor is positioned on an area\n\nYou can use the icons to change the name and description of an area, or to delete the whole area.\nThe deletion of an area causes all contained topics to be deleted.', 'B', 0, 'IMG.edit-area', 'plugins.php/coreforum', '', 1405424346),
('3dbe7099f82dcdbba4580acb1105a0d6', 4, 'Sort area', 'With this hatched surface areas can be sorted in at any place by clicking and dragging. This can, on one hand, be used in order to sort areas within a category, and on the other hand, areas can be shifted into other categories.', 'BR', 0, 'HTML #plugins #layout_wrapper #layout_page #layout_container #layout_content #forum #sortable_areas TABLE TBODY #tutorArea TD IMG#tutorMoveArea.handle.js :eq(1)', 'plugins.php/coreforum', '', 1405424379),
('3dbe7099f82dcdbba4580acb1105a0d6', 5, 'Add new area', 'New areas can be added to a category here.', 'BR', 0, 'TFOOT TR TD A SPAN', 'plugins.php/coreforum', '', 1405424421),
('3dbe7099f82dcdbba4580acb1105a0d6', 6, 'Create new category', 'A new category in the forum can be created here. Enter the title of the new category for this purpose.', 'TL', 0, '#tutorAddCategory H2', 'plugins.php/coreforum', '', 1405424458),
('9e9dca9b1214294b9605824bfe90fba1', 1, 'Create study group', 'Study groups enable the cooperation with fellow students or colleagues. This tour gives you an overview of how you can create study groups.\n\nIn order to go to the next step please click \"next\" at the bottom right.', 'R', 0, '', 'dispatch.php/my_studygroups', '', 1405684423),
('9e9dca9b1214294b9605824bfe90fba1', 2, 'Create study group', 'A new study group can be created with a click on \"create new study group\".', 'R', 0, 'A#nav_browse_new', 'dispatch.php/my_studygroups', '', 1406017730),
('9e9dca9b1214294b9605824bfe90fba1', 3, 'Name a study group', 'The name of a study group should be meaningful and unique in the whole Stud.IP.', 'R', 0, 'INPUT#groupname', 'dispatch.php/course/studygroup/new', '', 1405684720),
('9e9dca9b1214294b9605824bfe90fba1', 4, 'Add description', 'The description makes it possible to display additional information that makes it easier to find the group.', 'R', 0, 'TEXTAREA#groupdescription', 'dispatch.php/course/studygroup/new', '', 1405684806),
('9e9dca9b1214294b9605824bfe90fba1', 5, 'Allocate content elements', 'Content elements can be activated here, which are to be available within the study group. The question mark provides more detailed information on the meaning of the individual content elements', 'L', 0, '#layout_content FORM TABLE TBODY TR TD :eq(5)', 'dispatch.php/course/studygroup/new', '', 1405685093),
('9e9dca9b1214294b9605824bfe90fba1', 6, 'Stipulate access', 'The access to the study group can be restricted with this drop down menu.\n\nAll students can register freely and participate in the group with the access \"open for everyone\".\n\nWith the access \"upon request\" participants must be added by the group founder.', 'R', 0, 'SELECT#groupaccess', 'dispatch.php/course/studygroup/new', '', 1405685334),
('9e9dca9b1214294b9605824bfe90fba1', 7, 'Accept terms of use', 'The terms of use have to be accepted before you can create a study group.', 'R', 0, 'P LABEL', 'dispatch.php/course/studygroup/new', '', 1405685652),
('9e9dca9b1214294b9605824bfe90fba1', 8, 'Save study group', 'After you saved a study group it will appear under \"My courses\" > \"My study groups\".', 'L', 0, '#layout_content FORM TABLE TBODY TR TD :eq(14)', 'dispatch.php/course/studygroup/new', '', 1405686068),
('89786eac42f52ac316790825b4f5c0b2', 1, 'Forum', 'This tour provides an overview of the elements and interactional possibilities of the forum.\n\nIn order to go to the next step please click \"next\" on the bottom right.', 'BL', 0, '', 'plugins.php/coreforum', '', 1405415772),
('89786eac42f52ac316790825b4f5c0b2', 2, 'You are here:...', 'Here you can see which sector of the forum you are currently looking at.', 'BL', 0, 'DIV#tutorBreadcrumb', 'plugins.php/coreforum', '', 1405415875),
('89786eac42f52ac316790825b4f5c0b2', 3, 'Category', 'The forum is divided into categories, topics and posts. A category summarises forum areas into larger units of meaning.', 'BL', 0, '#layout_content #forum #sortable_areas TABLE CAPTION .category_name :eq(0)', 'plugins.php/coreforum', '', 1405416611),
('89786eac42f52ac316790825b4f5c0b2', 4, 'Area', 'This is an area within a category. Areas contain threads. The order of areas can be altered using drag&drop', 'BL', 0, '#layout_content #forum TABLE THEAD TR TH :eq(0)', 'plugins.php/coreforum', '', 1405416664),
('89786eac42f52ac316790825b4f5c0b2', 5, 'Info-Icon', 'This icon turns red as soon as there is something new in this sector.', 'B', 0, 'IMG#tutorNotificationIcon', 'plugins.php/coreforum', '', 1405416705),
('89786eac42f52ac316790825b4f5c0b2', 6, 'Search', 'All contents of this forum can be browsed here. Multiple word searches are also supported. In addition, the search can be limited to any combination of title, content and author.', 'BL', 0, '#layout-sidebar SECTION #tutorSearchInfobox DIV #tutorSearchInfobox UL LI INPUT :eq(1)', 'plugins.php/coreforum', '', 1405417134),
('89786eac42f52ac316790825b4f5c0b2', 7, 'Subscribe to forum', 'You can subscribe to the whole forum or individual topics . In this case a notification will be generated and you receive a meassage for each new post in this forum.', 'B', 0, '#layout-sidebar SECTION DIV DIV UL LI A :eq(5)', 'plugins.php/coreforum', '', 1405416795),
('e41611616675b218845fe9f55bc11cf6', 1, 'Profile', 'This tour gives you an overview of the most important functions of the \"profile\".\n\nIn order to get to the next step please click \"next\" on the bottom right.', 'B', 0, '', 'dispatch.php/profile', '', 1406722657),
('e41611616675b218845fe9f55bc11cf6', 2, 'Upload a picture', 'A profile picture can be uploaded on this site.', 'BL', 0, '#nav_profile_avatar A SPAN', 'dispatch.php/settings/avatar', '', 1406722657),
('e41611616675b218845fe9f55bc11cf6', 3, 'Select picture', 'A image file can be uploaded for this purpose.', 'L', 0, 'input[name=imgfile]', 'dispatch.php/settings/avatar', '', 1406722657),
('e41611616675b218845fe9f55bc11cf6', 4, 'Requirements', 'The image file must be available in **.jpg**, **.png** or **.gif** format.\n\nThe document size must not exceed 700 KB.', 'L', 0, '#layout_content #edit_avatar TBODY TR TD FORM B :eq(2)', 'dispatch.php/settings/avatar', '', 1406722657),
('83dc1d25e924f2748ee3293aaf0ede8e', 1, 'What is Blubber?', 'This tour provides an overview of the most important functions of \"Blubber\".\n\nIn order to reach the next step please click \"next\" on the bottom right.', 'TL', 0, '', 'plugins.php/blubber/streams/forum', '', 1405507364),
('83dc1d25e924f2748ee3293aaf0ede8e', 2, 'Create contribution', 'A discussion can be started here by writing a text. Paragraphs can be created by pressing shift+enter. The text will be sent by pressing enter.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/forum', '', 1405507478),
('83dc1d25e924f2748ee3293aaf0ede8e', 3, 'Design text', 'The text can be formatted and smileys can be used.\n\nThe customary formatting such as e.g. **bold** or %%italics%%  can be used.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/forum', '', 1405508371),
('83dc1d25e924f2748ee3293aaf0ede8e', 4, 'Mention persons', 'Others can be informed about a post by mentioning them in the post, using the format @user name or @''first name last name''.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/forum', '', 1405672301),
('83dc1d25e924f2748ee3293aaf0ede8e', 5, 'Add document', 'Documents can be inserted into a post by dragging them into an input field using drag&drop.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/forum', '', 1405508401),
('83dc1d25e924f2748ee3293aaf0ede8e', 6, 'Hashtags', 'Posts can be issued with key words (\"hashtags\") by placing a # in front of the chosen word.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/forum', '', 1405508442),
('83dc1d25e924f2748ee3293aaf0ede8e', 7, 'Hashtag cloud', 'By clicking on a hashtag, all posts containing this hashtag will be displayed.', 'RT', 0, 'DIV.sidebar-widget-header', 'plugins.php/blubber/streams/forum', '', 1405508505),
('83dc1d25e924f2748ee3293aaf0ede8e', 8, 'Change contribution', 'If the cursor is positioned on a post, its date will appear. For your own posts an additional icon will appear on the right next to the date. This icon allow you to subsequently edit your post.', 'BR', 0, 'DIV DIV A SPAN.time', 'plugins.php/blubber/streams/forum', '', 1405507901),
('83dc1d25e924f2748ee3293aaf0ede8e', 9, 'Link contribution', 'If the cursor is positioned on the first contribution to the discussion a link icon will appear on the left next to the date. If this is clicked using the right mouse button the link can be copied on this contribution in order to be able to insert it in another place.', 'BR', 0, 'DIV DIV A.permalink', 'plugins.php/blubber/streams/forum', '', 1405508281),
('588effa83da976a889a68c152bcabc90', 1, 'What is Blubber?', 'This tour provides an overview of the most important functions of \"Blubber\".\n\nIn order to reach the next step please click \"next\" on the bottom right.', 'TL', 0, '', 'plugins.php/blubber/streams/profile', '', 1405507364),
('588effa83da976a889a68c152bcabc90', 2, 'Create contribution', 'A discussion can be started here by writing a text. Paragraphs can be created by pressing shift+enter. The text will be sent by pressing enter.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/profile', '', 1405507478),
('588effa83da976a889a68c152bcabc90', 3, 'Design text', 'The text can be formatted and smileys can be used.\n\nThe customary formatting such as e.g. **bold** or %%italics%%  can be used.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/profile', '', 1405508371),
('588effa83da976a889a68c152bcabc90', 4, 'Mention persons', 'Others can be informed about a post by mentioning them in the post, using the format @user name or @''first name last name''.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/profile', '', 1405672301),
('588effa83da976a889a68c152bcabc90', 5, 'Add document', 'Documents can be inserted into a post by dragging them into an input field using drag&drop.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/profile', '', 1405508401),
('588effa83da976a889a68c152bcabc90', 6, 'Hashtags', 'Posts can be issued with key words (\"hashtags\") by placing a # in front of the chosen word.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/profile', '', 1405508442),
('588effa83da976a889a68c152bcabc90', 7, 'Hashtag cloud', 'By clicking on a hashtag, all posts containing this hashtag will be displayed.', 'RT', 0, 'DIV.sidebar-widget-header', 'plugins.php/blubber/streams/profile', '', 1405508505),
('588effa83da976a889a68c152bcabc90', 8, 'Change contribution', 'If the cursor is positioned on a post, its date will appear. For your own posts an additional icon will appear on the right next to the date. This icon allow you to subsequently edit your post.', 'BR', 0, 'DIV DIV A SPAN.time', 'plugins.php/blubber/streams/profile', '', 1405507901),
('588effa83da976a889a68c152bcabc90', 9, 'Link contribution', 'If the cursor is positioned on the first contribution to the discussion a link icon will appear on the left next to the date. If this is clicked using the right mouse button the link can be copied on this contribution in order to be able to insert it in another place.', 'BR', 0, 'DIV DIV A.permalink', 'plugins.php/blubber/streams/profile', '', 1405508281),
('d9913517f9c81d2c0fa8362592ce5d0e', 1, 'What is Blubber?', 'This tour provides an overview of the most important functions of \"Blubber\".\n\nIn order to reach the next step please click \"next\" on the bottom right.', 'TL', 0, '', 'plugins.php/blubber/streams/global', '', 1405507364),
('d9913517f9c81d2c0fa8362592ce5d0e', 2, 'Create contribution', 'A discussion can be started here by writing a text. Paragraphs can be created by pressing shift+enter. The text will be sent by pressing enter.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/global', '', 1405507478),
('d9913517f9c81d2c0fa8362592ce5d0e', 3, 'Design text', 'The text can be formatted and smileys can be used.\n\nThe customary formatting such as e.g. **bold** or %%italics%%  can be used.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/global', '', 1405508371),
('d9913517f9c81d2c0fa8362592ce5d0e', 4, 'Mention persons', 'Others can be informed about a post by mentioning them in the post, using the format @user name or @''first name last name''.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/global', '', 1405672301),
('d9913517f9c81d2c0fa8362592ce5d0e', 5, 'Add document', 'Documents can be inserted into a post by dragging them into an input field using drag&drop.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/global', '', 1405508401),
('d9913517f9c81d2c0fa8362592ce5d0e', 6, 'Hashtags', 'Posts can be issued with key words (\"hashtags\") by placing a # in front of the chosen word.', 'BL', 0, 'TEXTAREA#new_posting.autoresize', 'plugins.php/blubber/streams/global', '', 1405508442),
('d9913517f9c81d2c0fa8362592ce5d0e', 7, 'Hashtag cloud', 'By clicking on a hashtag, all posts containing this hashtag will be displayed.', 'RT', 0, '#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(1)', 'plugins.php/blubber/streams/global', '', 1405508505),
('d9913517f9c81d2c0fa8362592ce5d0e', 8, 'Change contribution', 'If the cursor is positioned on a post, its date will appear. For your own posts an additional icon will appear on the right next to the date. This icon allow you to subsequently edit your post.', 'BR', 0, 'DIV DIV A SPAN.time', 'plugins.php/blubber/streams/global', '', 1405507901),
('d9913517f9c81d2c0fa8362592ce5d0e', 9, 'Link contribution', 'If the cursor is positioned on the first contribution to the discussion a link icon will appear on the left next to the date. If this is clicked using the right mouse button the link can be copied on this contribution in order to be able to insert it in another place.', 'BR', 0, 'DIV DIV A.permalink', 'plugins.php/blubber/streams/global', '', 1405508281),
('05434e40601a9a2a7f5fa8208ae148c1', 1, 'My documents', 'My documents is the personal document area. Documents can be stored on Stud.IP here in order to be able to download them from there onto other computers.\n\nOther students or lecturers do not receive any access to documents, which are uploaded into the personal document area.\n\nIn order to reach the next step please click on the right at the bottom on \"next\".', 'TL', 0, '', 'dispatch.php/document/files', '', 1405592884),
('05434e40601a9a2a7f5fa8208ae148c1', 2, 'Available storage space', 'The storage space of the personal document area is limited. It is displayed how much storage space is still available.', 'BR', 0, 'DIV.caption-actions', 'dispatch.php/document/files', '', 1405594184),
('05434e40601a9a2a7f5fa8208ae148c1', 3, 'New documents and indices', 'New documents can be uploaded from the computer into the personal document area and new indices can be created here.', 'TL', 0, '#layout-sidebar SECTION DIV DIV UL LI A :eq(0)', 'dispatch.php/document/files', '', 1405593409),
('05434e40601a9a2a7f5fa8208ae148c1', 4, 'Document overview', 'All documents and indices are listed in a tabular form. In addition to the name even more information is displayed such as the document type or the document size.', 'TL', 0, '#layout_content FORM TABLE THEAD TR TH :eq(3)', 'dispatch.php/document/files', '', 1405593089),
('05434e40601a9a2a7f5fa8208ae148c1', 5, 'Actions', 'Already uploaded documents and folders can be edited, downloaded, shifted, copied and deleted here.', 'TR', 0, '#layout_content FORM TABLE THEAD TR TH :eq(7)', 'dispatch.php/document/files', '', 1405594079),
('05434e40601a9a2a7f5fa8208ae148c1', 6, 'Export', 'Here you have the possibility to download individual folders or the full document area as a ZIP document. All documents and indices are contained therein.', 'TL', 0, '#layout-sidebar SECTION DIV DIV.sidebar-widget-header :eq(1)', 'dispatch.php/document/files', '', 1405593708);
";
        DBManager::get()->exec($query);
        
        // add settings
        $query = "INSERT IGNORE INTO `help_tour_settings` (`tour_id`, `active`, `access`) VALUES
('7af1e1fb7f53c910ba9f42f43a71c723', 1, 'standard'),
('c89ce8e097f212e75686f73cc5008711', 1, 'standard'),
('de1fbce508d01cbd257f9904ff8c3b43', 1, 'standard'),
('1badcf28ab5b206d9150b2b9683b4cb6', 1, 'standard'),
('fa963d2ca827b28e0082e98aafc88765', 1, 'standard'),
('f0aeb0f6c4da3bd61f48b445d9b30dc1', 1, 'standard'),
('3dbe7099f82dcdbba4580acb1105a0d6', 1, 'standard'),
('9e9dca9b1214294b9605824bfe90fba1', 1, 'standard'),
('89786eac42f52ac316790825b4f5c0b2', 1, 'standard'),
('e41611616675b218845fe9f55bc11cf6', 1, 'standard'),
('83dc1d25e924f2748ee3293aaf0ede8e', 1, 'standard'),
('588effa83da976a889a68c152bcabc90', 1, 'standard'),
('d9913517f9c81d2c0fa8362592ce5d0e', 1, 'standard'),
('05434e40601a9a2a7f5fa8208ae148c1', 1, 'standard');
";
        DBManager::get()->exec($query);
    }
}
