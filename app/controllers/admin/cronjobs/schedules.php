<?php
/**
 * Admin_Cronjobs_SchedulesController
 *
 * Controller class for the schedules of cronjobs
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     GPL2 or any laterversion
 * @category    Stud.IP
 * @since       2.4
 */
class Admin_Cronjobs_SchedulesController extends AuthenticatedController
{
    protected $_autobind = true;

    /**
     * Set up this controller.
     *
     * @param String $action Name of the action to be invoked
     * @param Array  $args   Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/admin/config/cronjobs');
        PageLayout::setTitle(_('Cronjob-Verwaltung') . ' - ' . _('Cronjobs'));

        if (empty($_SESSION['cronjob-filter'])) {
            $_SESSION['cronjob-filter'] = [
                'where'  => '1',
                'values' => [],
            ];
        }

        // Setup sidebar
        $sidebar = Sidebar::Get();
        $sidebar->setTitle(_('Cronjobs'));
        $sidebar->setImage('sidebar/date-sidebar.png');

        // Aktionen
        $views = $sidebar->addWidget(new ViewsWidget());
        $views->addLink(
            _('Cronjobs verwalten'),
            $this->url_for('admin/cronjobs/schedules')
        )->setActive(true);
        $views->addLink(
            _('Aufgaben verwalten'),
            $this->url_for('admin/cronjobs/tasks')
        );
        $views->addLink(
            _('Logs anzeigen'),
            $this->url_for('admin/cronjobs/logs')
        );

        $actions = $sidebar->addWidget(new ActionsWidget());
        $actions->addLink(
            _('Neuen Cronjob registrieren'),
            $this->editURL(),
            Icon::create('add')
        );
    }

    /**
     * Displays all available schedules according to the set filters.
     *
     * @param int $page Which page to display
     */
    public function index_action($page = 0)
    {
        $filter = $_SESSION['cronjob-filter'];

        $this->total = CronjobSchedule::countBySql('1');

        $this->pagination = Pagination::create(
            CronjobSchedule::countBySql($filter['where']),
            $page
        );

        $this->schedules = $this->pagination->loadSORMCollection(
            CronjobSchedule::class,
            $filter['where']
        );

        // Filters
        $this->tasks  = CronjobTask::findBySql('1');
        $this->filter = $filter['values'];
    }

    /**
     * Displays a schedule.
     *
     * @param CronjobSchedule $schedule Schedule to display
     */
    public function display_action(CronjobSchedule $schedule)
    {
        if (!$this->schedule) {
            PageLayout::postError(_('Es gibt keinen Cronjob mit dieser Id.'));
            $this->redirect('admin/cronjobs/schedules');
            return;
        }

        $title = sprintf(_('Cronjob "%s" anzeigen'), $this->schedule->title);

        PageLayout::setTitle($title);
    }

    /**
     * Sets the filters for the schedule view.
     * Filters are stored in the session.
     */
    public function filter_action()
    {
        $filter     = array_filter(Request::optionArray('filter'));
        $conditions = [];

        if (!empty($filter['type'])) {
            $conditions[] = "type = " . DBManager::get()->quote($filter['type']);
        }
        if (!empty($filter['status'])) {
            $active = (int)($filter['status'] === 'active');
            $conditions[] = "active = " . DBManager::get()->quote($active);
        }
        if (!empty($filter['task_id'])) {
            $conditions[] = "task_id = " . DBManager::get()->quote($filter['task_id']);
        }

        $_SESSION['cronjob-filter'] = [
            'where'  => implode(' AND ' , $conditions) ?: '1',
            'values' => $filter,
        ];
        $this->redirect('admin/cronjobs/schedules');
    }

    /**
     * Edits a schedule.
     *
     * @param String $id   Id of the schedule in question (null to create)
     * @param int    $page Return to this page after editing (optional)
     */
    public function edit_action(CronjobSchedule $schedule = null, $page = 0)
    {
        if (Request::submitted('store')) {
            $parameters = Request::getArray('parameters');

            $schedule->title       = Request::get('title');
            $schedule->description = Request::get('description');
            $schedule->active      = Request::int('active', 0);
            if ($schedule->isNew()) {
                $schedule->task_id = Request::option('task_id');
            }
            $schedule->parameters  = $parameters[$schedule->task_id];
            $schedule->type        = Request::option('type') === 'once'
                                   ? 'once'
                                   : 'periodic';

            if ($schedule->type === 'once') {
                $temp = Request::getArray('once');
                $schedule->next_execution = strtotime($temp['date'] . ' ' . $temp['time']);
            } else {
                $temp = Request::getArray('periodic');
                $schedule->minute      = $this->extractCronItem($temp['minute']);
                $schedule->hour        = $this->extractCronItem($temp['hour']);
                $schedule->day         = $this->extractCronItem($temp['day']);
                $schedule->month       = $this->extractCronItem($temp['month']);
                $schedule->day_of_week = mb_strlen($temp['day_of_week']['value'])
                                       ? (int) $temp['day_of_week']['value']
                                       : null;

                if ($schedule->active) {
                    $schedule->next_execution = $schedule->calculateNextExecution();
                }
            }
            $schedule->store();

            PageLayout::postSuccess(_('Die Änderungen wurden gespeichert.'));
            $this->redirect('admin/cronjobs/schedules/index/' . $page);
            return;
        }

        PageLayout::setTitle(_('Cronjob-Verwaltung') . ' - ' . _('Cronjob bearbeiten'));

        $actions = Sidebar::get()->addWidget(new ActionsWidget());
        $actions->addLink(
            _('Zurück zur Übersicht'),
            $this->indexURL($page),
            Icon::create('link-intern')
        );

        $this->page  = $page;
        $this->tasks = CronjobTask::findBySql('1');
    }

    /**
     * Extracts a cron value from a request item.
     *
     * @param Array $item Request item consisting of a type (either empty,
     *                    'once' or 'periodic') and a value (either null
      *                   or a signed int)
     * @return mixed Null if type is empty, a negative number if type is
     *               'periodic' or a positive number or 0 if type is 'once'
     */
    private function extractCronItem($item)
    {
        if ($item['type'] === '') {
            return null;
        }
        $value = (int) $item['value'];
        return $item['type'] === 'periodic'
             ? $value * -1
             : $value;
    }

    /**
     * Activates a schedule.
     *
     * @param CronjobSchedule $schedule Schedule to activate
     * @param int             $page Return to this page after activating (optional)
     */
    public function activate_action(CronjobSchedule $schedule, $page = 0)
    {
        $schedule->activate();

        if (!Request::isXhr()) {
            PageLayout::postSuccess(_('Der Cronjob wurde aktiviert.'));
        }
        $this->redirect("admin/cronjobs/schedules/index/{$page}#job-{$schedule->id}");
    }

    /**
     * Deactivates a schedule.
     *
     * @param CronjobSchedule $schedule Schedule to deactivate
     * @param int             $page Return to this page after deactivating (optional)
     */
    public function deactivate_action(CronjobSchedule $schedule, $page = 0)
    {
        $schedule->deactivate();

        if (!Request::isXhr()) {
            PageLayout::postSuccess(_('Der Cronjob wurde deaktiviert.'));
        }
        $this->redirect("admin/cronjobs/schedules/index/{$page}#job-{$schedule->id}");
    }

    /**
     * Cancels/deletes a schedule.
     *
     * @param CronjobSchedule $schedule Schedule to cancel
     * @param int             $page Return to this page after canceling (optional)
     */
    public function cancel_action(CronjobSchedule $schedule, $page = 0)
    {
        $schedule->delete();

        PageLayout::postSuccess(_('Der Cronjob wurde gelöscht.'));
        $this->redirect("admin/cronjobs/schedules/index/{$page}");
    }

    /**
     * Performs a bulk operation on a set of schedules. Operation can be
     * either activating, deactivating or canceling/deleting.
     *
     * @param int    $page Return to this page afterwarsd (optional)
     */
    public function bulk_action($page = 0)
    {
        $action    = Request::option('action');
        $ids       = Request::optionArray('ids');
        $schedules = CronjobSchedule::findMany($ids);

        if ($action === 'activate') {
            $schedules = array_filter($schedules, function ($item) { return !$item->active; });
            $failed = 0;
            foreach ($schedules as $schedule) {
                if ($schedule->task->active) {
                    $schedule->activate();
                } else {
                    $failed += 1;
                }
            }

            if ($failed > 0) {
                $message = ngettext('%u Cronjob konnte nicht aktiviert werden, da die entsprechende Aufgabe deaktiviert ist.',
                                    '%u Cronjob(s) konnte(n) nicht aktiviert werden, da die entsprechende Aufgabe deaktiviert ist.',
                                    $failed);
                $message = sprintf($message, $failed);
                PageLayout::postInfo($message);
            }

            $n = count($schedules) - $failed;
            $message = sprintf(ngettext('%u Cronjob wurde aktiviert.', '%u Cronjobs wurden aktiviert.', $n), $n);
            PageLayout::postSuccess($message);
        } else if ($action === 'deactivate') {
            $schedules = array_filter($schedules, function ($item) { return $item->active; });
            foreach ($schedules as $schedule) {
                $schedule->deactivate();
            }

            $n = count($schedules);
            $message = sprintf(ngettext('%u Cronjob wurde deaktiviert.', '%u Cronjobs wurden deaktiviert.', $n), $n);
            PageLayout::postSuccess($message);
        } else if ($action === 'cancel') {
            foreach ($schedules as $schedule) {
                $schedule->delete();
            }

            $n = count($schedules);
            $message = sprintf(ngettext('%u Cronjob wurde gelöscht.', '%u Cronjobs wurden gelöscht.', $n), $n);
            PageLayout::postSuccess($message);
        }

        $this->redirect("admin/cronjobs/schedules/index/{$page}");
    }
}
