<?

require_once 'app/controllers/authenticated_controller.php';

class Admin_Cronjobs_LogsController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/admin/config/cronjobs');
        PageLayout::setTitle(_('Cronjob-Verwaltung') . ' - ' . _('Log-Einträge'));

        if (empty($_SESSION['cronlog-filter'])) {
            $_SESSION['cronlog-filter'] = array(
                'where'  => '1',
                'values' => array(),
            );
        }

        if (Request::isXhr()) {
            $this->set_layout(null);
            $this->set_content_type('text/html;Charset=windows-1252');
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }
    }

    public function index_action($page = 1)
    {
        $filter = $_SESSION['cronlog-filter'];
        
        $this->max_per_page   = Config::get()->ENTRIES_PER_PAGE;
        $this->total          = CronjobLog::countBySql();
        $this->total_filtered = CronjobLog::countBySql($filter['where']);
        $this->page           = max(1, min($page, ceil($this->total_filtered / $this->max_per_page)));

        $order = " ORDER BY executed DESC";
        $limit = sprintf(" LIMIT %u, %u", ($this->page - 1) * $this->max_per_page, $this->max_per_page);
        $this->logs = CronjobLog::findBySQL($filter['where'] . $order . $limit);

        // Infobox image was produced from an image by Robbert van der Steeg
        // http://www.flickr.com/photos/robbie73/5924985913/
        $this->setInfoboxImage(Assets::image_path('infobox/time.jpg'));

        // Navigation
        $cronjobs = sprintf('<a href="%s">%s</a>',
                            $this->url_for('admin/cronjobs/schedules'),
                            _('Cronjobs verwalten'));
        $this->addToInfobox(_('Navigation'), $cronjobs);

        $tasks = sprintf('<a href="%s">%s</a>',
                         $this->url_for('admin/cronjobs/tasks'),
                         _('Aufgaben verwalten'));
        $this->addToInfobox(_('Navigation'), $tasks);

        $logs = sprintf('<a href="%s"><strong>%s</strong></a>',
                        $this->url_for('admin/cronjobs/logs'),
                        _('Logs anzeigen'));
        $this->addToInfobox(_('Navigation'), $logs, 'icons/16/red/arr_1right');

        // Filters
        $template = $this->get_template_factory()->open('admin/cronjobs/logs/infobox-filter');
        $template->controller = $this;
        $template->schedules  = CronjobSchedule::findBySql('1');
        $template->tasks      = CronjobTask::findBySql('1');
        $template->filter     = $filter['values'];
        $filters = $template->render();
        $this->addToInfobox(_('Darstellung einschränken'), $filters);

        $this->addToInfobox(_('Darstellung einschränken'),
                            sprintf(_('Passend: %u / %u Logeinträge'), $this->total_filtered, $this->total));

        // Actions
        // TODO: Clean logs
        // $register = sprintf('<a href="%s">%s</a>',
        //                     $this->url_for('task/register'),
        //                     _('Neue Aufgabe registrieren'));
        // $this->addToInfobox(_('Aktionen'), $register, 'icons/16/black/plus');
    }

    public function filter_action()
    {
        $filter     = array_filter(Request::optionArray('filter'));
        $conditions = array();

        if (!empty($filter['status'])) {
            $conditions[] = ($filter['status'] === 'passed')
                          ? "exception = 'N;'"
                          : "exception != 'N;'";
        }

        if (!empty($filter['schedule_id'])) {
            $conditions[] = "schedule_id = " . DBManager::get()->quote($filter['schedule_id']);
        }

        if (!empty($filter['task_id'])) {
            $temp = CronjobSchedule::findByTask_id($filter['task_id']);
            $temp = SimpleORMapCollection::createFromArray($temp);
            $schedule_ids = $temp->pluck('schedule_id') ?: null;
            $conditions[] = "schedule_id IN (" . DBManager::get()->quote($schedule_ids). ")";
        }

        $_SESSION['cronlog-filter'] = array(
            'where'  => implode(" AND " , $conditions) ?: '1',
            'values' => $filter,
        );
        $this->redirect('admin/cronjobs/logs');
    }

    public function schedule_action($schedule_id)
    {
        $_SESSION['cronlog-filter'] = compact('schedule_id');
        $this->redirect('admin/cronjobs/logs');
    }

    public function task_action($task_id)
    {
        $_SESSION['cronlog-filter'] = compact('task_id');
        $this->redirect('admin/cronjobs/logs');
    }

    public function display_action($id, $page = 1)
    {
        $this->log = CronjobLog::find($id);

        $title = sprintf(_('Logeintrag für Cronjob "%s" anzeigen'),
                         $this->log->schedule->title);
        if (Request::isXhr()) {
            header('X-Title: ' . $title);
        } else {
            PageLayout::setTitle($title);
        }
    }

    public function delete_action($id, $page = 1)
    {
        CronjobLog::find($id)->delete();

        $message = sprintf(_('Der Logeintrag wurde gelöscht.'), $deleted);
        PageLayout::postMessage(MessageBox::success($message));

        $this->redirect('admin/cronjobs/logs/index/' . $page);
    }

    public function bulk_action($page = 1)
    {
        $action = Request::option('action');
        $ids    = Request::optionArray('ids');
        $logs   = CronjobLog::findMany($ids);

        if ($action === 'delete') {
            foreach ($logs as $log) {
                $log->delete();
            }

            $n = count($logs);
            $message = sprintf(ngettext('%u Logeintrag wurde gelöscht.', '%u Logeinträge wurden gelöscht.', $n), $n);
            PageLayout::postMessage(MessageBox::success($message));
        }

        $this->redirect('admin/cronjobs/logs/index/' . $page);
    }

}