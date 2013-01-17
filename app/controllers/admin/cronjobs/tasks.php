<?

require_once 'app/controllers/authenticated_controller.php';

class Admin_Cronjobs_TasksController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/admin/config/cronjobs');
        PageLayout::setTitle(_('Cronjob-Verwaltung') . ' - ' . _('Aufgaben'));

        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
    }

    public function index_action($page = 1)
    {
        $this->max_per_page = Config::get()->ENTRIES_PER_PAGE;
        $this->total        = CronjobTask::countBySql('1');
        $this->page         = max(1, min($page, ceil($this->total / $this->max_per_page)));

        $limit = sprintf(" LIMIT %u, %u", ($this->page - 1) * $this->max_per_page, $this->max_per_page);
        $this->tasks = CronjobTask::findBySQL('1' . $limit);

        // Infobox image was produced from an image by Robbert van der Steeg
        // http://www.flickr.com/photos/robbie73/5924985913/
        $this->setInfoboxImage(Assets::image_path('infobox/time.jpg'));

        // Navigation
        $cronjobs = sprintf('<a href="%s">%s</a>',
                            $this->url_for('admin/cronjobs/schedules'),
                            _('Cronjobs verwalten'));
        $this->addToInfobox(_('Navigation'), $cronjobs);

        $tasks = sprintf('<a href="%s"><strong>%s</strong></a>',
                         $this->url_for('admin/cronjobs/tasks'),
                         _('Aufgaben verwalten'));
        $this->addToInfobox(_('Navigation'), $tasks, 'icons/16/red/arr_1right');

        $logs = sprintf('<a href="%s">%s</a>',
                        $this->url_for('admin/cronjobs/logs'),
                        _('Logs anzeigen'));
        $this->addToInfobox(_('Navigation'), $logs);
    }
    
    public function activate_action($id, $page = 1)
    {
        $task = CronjobTask::find($id);
        $task->active = 1;
        $task->store();

        if (!Request::isXhr()) {
            // Report how many actual cronjobs were activated
            $activated = $task->schedules->filter(function ($schedule) { return $schedule->active; })->count();

            $message = sprintf(_('Die Aufgabe und %u Cronjob(s) wurden aktiviert.'), $activated);
            PageLayout::postMessage(MessageBox::success($message));
        }
        $this->redirect('admin/cronjobs/tasks/index/' . $page . '#task-' . $id);
    }

    public function deactivate_action($id, $page = 1)
    {
        $task = CronjobTask::find($id);
        $task->active = 0;
        $task->store();

        if (!Request::isXhr()) {
            // Report how many actual cronjobs were activated
            $deactivated = $task->schedules->filter(function ($schedule) { return $schedule->active; })->count();

            $message = sprintf(_('Die Aufgabe und %u Cronjob(s) wurden deaktiviert.'), $deactivated);
            PageLayout::postMessage(MessageBox::success($message));
        }
        $this->redirect('admin/cronjobs/tasks/index/' . $page . '#task-' . $id);
    }

    public function delete_action($id, $page = 1)
    {
        $task = CronjobTask::find($id);
        $deleted = $task->schedules->count();
        $task->delete();

        $message = sprintf(_('Die Aufgabe und %u Cronjob(s) wurden gel�scht.'), $deleted);
        PageLayout::postMessage(MessageBox::success($message));

        $this->redirect('admin/cronjobs/tasks/index/' . $page);
    }

    public function bulk_action($page = 1)
    {
        $action = Request::option('action');
        $ids    = Request::optionArray('ids');
        $tasks  = CronjobTask::findMany($ids);

        if ($action === 'activate') {
            $tasks = array_filter($tasks, function ($item) { return !$item->active; });
            foreach ($tasks as $task) {
                $task->active = 1;
                $task->store();
            }

            $n = count($tasks);
            $message = sprintf(ngettext('%u Aufgabe wurde aktiviert.', '%u Aufgaben wurden aktiviert.', $n), $n);
            PageLayout::postMessage(MessageBox::success($message));
        } else if ($action === 'deactivate') {
            $tasks = array_filter($tasks, function ($item) { return $item->active; });
            foreach ($tasks as $task) {
                $task->active = 0;
                $task->store();
            }

            $n = count($tasks);
            $message = sprintf(ngettext('%u Aufgabe wurde deaktiviert.', '%u Aufgaben wurden deaktiviert.', $n), $n);
            PageLayout::postMessage(MessageBox::success($message));
        } else if ($action === 'delete') {
            foreach ($tasks as $task) {
                $task->delete();
            }

            $n = count($tasks);
            $message = sprintf(ngettext('%u Aufgabe wurde gel�scht.', '%u Aufgaben wurden gel�scht.', $n), $n);
            PageLayout::postMessage(MessageBox::success($message));
        }

        $this->redirect('admin/cronjobs/tasks/index/' . $page);
    }

}