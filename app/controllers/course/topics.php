<?php

require_once 'app/controllers/authenticated_controller.php';

class Course_TopicsController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    public function index_action()
    {
        if (Request::isPost() && Request::option("issue_id") && $GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
            $topic = new CourseTopic(Request::option("issue_id"));
            if ($topic['seminar_id'] !== $_SESSION['SessionSeminar']) {
                throw new AccessDeniedException("Kein Zugriff");
            }
            if (Request::submitted("delete_topic")) {
                $topic->delete();
                PageLayout::postMessage(MessageBox::success(_("Thema gel�scht.")));
            } else {
                $topic['title'] = Request::get("title");
                $topic['description'] = Request::get("description");
                $topic->store();

                //change dates for this topic
                $former_date_ids = $topic->dates->pluck("termin_id");
                $new_date_ids = array_keys(Request::getArray("date"));
                foreach (array_diff($former_date_ids, $new_date_ids) as $delete_termin_id) {
                    $topic->dates->unsetByPk($delete_termin_id);
                }
                foreach (array_diff($new_date_ids, $former_date_ids) as $add_termin_id) {
                    $date = CourseDate::find($add_termin_id);
                    if ($date) {
                        $topic->dates[] = $date;
                    }
                }
                $topic->store();

                if (Request::get("folder") && !$topic->folder) {
                    $topic->createFolder();
                }
                if (Request::get("forumthread") && class_exists("ForumIssue")) {
                    ForumIssue::setThreadForIssue(
                        $_SESSION['SessionSeminar'],
                        $topic->getId(),
                        $topic['title'],
                        $topic['description']
                    );
                }

                PageLayout::postMessage(MessageBox::success(_("Thema gespeichert.")));
            }
        }

        Navigation::activateItem('/course/schedule/topics');
        $this->topics = CourseTopic::findBySeminar_id($_SESSION['SessionSeminar']);
    }

    public function edit_action($topic_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $this->topic = new CourseTopic($topic_id);
        $this->dates = CourseDate::findBySeminar_id($_SESSION['SessionSeminar']);

        if (Request::isXhr()) {
            $this->set_layout(null);
            $this->set_content_type('text/html;Charset=windows-1252');
            $this->response->add_header('X-Title', _("Bearbeiten").": ".$this->topic['title']);
        }
    }


}
