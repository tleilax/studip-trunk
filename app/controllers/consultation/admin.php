<?php
require_once __DIR__ . '/consultation_controller.php';

class Consultation_AdminController extends ConsultationController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->current_user = User::findByUsername(Request::username('username', $GLOBALS['user']->username));

        if ($this->current_user !== $GLOBALS['user']->id && $GLOBALS['user']->perms !== 'root') {
            throw new AccessDeniedException();
        }

        if ($this->current_user->id !== $GLOBALS['user']->id) {
            URLHelper::addLinkParam('username', $this->current_user->username);
        }

        Navigation::activateItem('/profile/consultation/admin');
        PageLayout::setTitle(_('Verwaltung der Sprechstundentermine'));

        $this->setupSidebar();
    }

    public function index_action()
    {
        $this->blocks = ConsultationBlock::findByTeacher_id(
            $this->current_user->id,
            'ORDER BY start ASC'
        );
    }

    public function create_action()
    {
        PageLayout::setTitle(_('Neue Sprechstundenblöcke anlegen'));

        $courses = $this->current_user->course_memberships->filter(function ($member) {
            return in_array($member->status, ['tutor', 'dozent']);
        });
        if (count($courses) > 0) {
            $search_object = new MyCoursesSearch('Seminar_id', $this->current_user->perms, [
                'userid'    => $this->current_user->id,
                'semtypes'  => SemType::getGroupingSemTypes(),
                'exclude'   => '',
                'semesters' => array_keys(Semester::getAll()),
            ]);
            $this->course_search = new QuickSearch('course_id', $search_object);

            if ($course_id = Request::option('course_id')) {
                $this->course_search->defaultValue(
                    $course_id,
                    Course::find($course_id)->getFullName('number-name-semester')
                );
            }
        }

        // TODO: inst_default?
        $rooms = $this->current_user->institute_memberships->pluck('Raum');
        $rooms = array_filter($rooms);
        $this->room = $rooms ? reset($rooms) : '';
    }

    public function store_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        try {
            $blocks = ConsultationBlock::createBlocks(
                $this->current_user->id,
                $this->getDateAndTime('start'),
                $this->getDateAndTime('end'),
                Request::int('day-of-week'),
                Request::int('interval')
            );

            $stored = 0;
            foreach ($blocks as $block) {
                $block->room            = Request::get('room');
                $block->calendar_events = (bool) Request::int('calender-events');
                $block->note            = Request::get('note');
                $block->size            = Request::int('size', 1);
                $block->course_id       = Request::option('course_id') ?: null;

                $block->createSlots(Request::int('duration'));
                $stored += $block->store();
            }
        } catch (OverlapException $e) {
            $this->keepRequest();

            PageLayout::postError($e->getMessage(), $e->getDetails());
            $this->redirect('consultation/admin/create');
            return;
        } catch (Exception $e) {
            $this->keepRequest();

            PageLayout::postError($e->getMessage());
            $this->redirect('consultation/admin/create');
            return;
        }

        if ($stored === 0) {
            PostLayout::postError(_('In dem von Ihnen gewählten Zeitraum konnten für den gewählten Wochentag keine Termine erzeugt werden.'));
        } else {
            PageLayout::postSuccess(_('Die Sprechstundenblöcke wurden erfolgreich angelegt.'));
        }
        $this->relocate('consultation/admin');
    }

    public function note_action($block_id, $slot_id = null)
    {
        if ($slot_id) {
            PageLayout::setTitle(_('Anmerkung zu diesem Sprechstundentermin bearbeiten'));
        } else {
            PageLayout::setTitle(_('Anmerkung zu diesem Sprechstundenblock bearbeiten'));
        }

        $this->block   = $this->loadBlock($block_id);
        $this->slot_id = $slot_id;

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $note = trim(Request::get('note'));

            $changed = false;
            if ($slot_id) {
                $slot = $this->block->slots->find($slot_id);
                $slot->note = $note;
                $changed = $slot->store();
            } else {
                $this->block->note = $note;
                foreach ($this->block->slots as $slot) {
                    $slot->note = '';
                }
                $changed = $this->block->store();
            }
            if ($changed) {
                PageLayout::postSuccess(_('Der Sprechstundenblock wurde bearbeitet'));
            }

            $this->redirect("consultation/admin/index#block-{$block_id}");
        }
    }

    public function print_action($block_id)
    {
        $this->blocks = $block_id === 'bulk'
                      ? $this->loadBlock(Request::intArray('ids'))
                      : [$this->loadBlock($block_id)];
        $this->set_layout(null);
    }

    public function remove_action($block_id, $slot_id = null)
    {

    }

    public function book_action($block_id, $slot_id)
    {
        PageLayout::setTitle(_('Sprechstundentermin reservieren'));

        $this->slot = $this->loadSlot($block_id, $slot_id);

        $permissions = ['user', 'autor', 'tutor'];
        if (Config::get()->CONSULTATION_ALLOW_DOCENTS_RESERVING) {
            $permissions[] = 'dozent';
        }

        if ($this->slot->block->course_id) {
            $this->search_object = new PermissionSearch('user_in_sem', '', 'user_id', [
                'seminar_id' => $this->slot->block->course_id,
                'sem_perm'   => $permissions,
            ]);
        } else {
            $this->search_object = new PermissionSearch('user_id', '', 'user_id', [
                'permission'   => $permissions,
                'exclude_user' => '',
            ]);
        }

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            if (count($this->slot->bookings) >= $this->slot->block->size) {
                PageLayout::postError(_('Dieser Sprechstundentermin ist bereits belegt'));
            } else {
                $booking = new ConsultationBooking();
                $booking->slot_id = $this->slot->id;
                $booking->user_id = Request::option('user_id');
                $booking->reason  = trim(Request::get('reason'));
                $booking->store();

                PageLayout::postSuccess(_('Der Sprechstundentermin wurde reserviert'));
            }

            $this->redirect("consultation/admin#block-{$this->slot->block_id}");
        }
    }

    public function cancel_action($block_id, $slot_id)
    {
        PageLayout::setTitle(_('Sprechstundentermin absagen'));

        $this->slot = $this->loadSlot($block_id, $slot_id);

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $reason = trim(Request::get('reason'));
            foreach ($this->slot->bookings as $booking) {
                $this->sendMessage(
                    $booking->user,
                    $this->slot,
                    _('Sprechstundentermin abgesagt'),
                    $reason
                );

                $booking->delete();
            }

            if ($GLOBALS['user']->id !== $this->slot->block->teacher_id) {
                $this->sendMessage(
                    $this->slot->block->teacher,
                    $this->slot,
                    _('Sprechstundentermin abgesagt'),
                    $reason
                );
            }

            $this->redirect("consultation/admin#block-{$this->slot->block_id}");
        }
    }

    public function reason_action($block_id, $slot_id, $booking_id)
    {
        PageLayout::setTitle(_('Grund für die Sprechstundenbuchung bearbeiten'));

        $this->booking = $this->loadBooking($block_id, $slot_id, $booking_id);

        if (Request::isPost()) {
            CSRFProtection::tokenTag();

            $this->booking->reason = trim(Request::get('reason'));
            $this->booking->store();

            $this->sendMessage(
                $this->booking->user,
                $this->booking->slot,
                _('Grund des Sprechstundentermins bearbeitet'),
                $this->booking->reason
            );

            if ($GLOBALS['user']->id !== $this->booking->slot->block->teacher_id) {
                $this->sendMessage(
                    $this->booking->slot->block->teacher,
                    $this->booking->slot,
                    _('Grund des Sprechstundentermins bearbeitet'),
                    $this->booking->reason
                );
            }

            PageLayout::postSuccess(_('Der Grund für die Sprechstundenbuchung wurde bearbeitet'));
            $this->redirect("consultation/admin#block-{$this->booking->slot->block_id}");
        }
    }

    public function toggle_action($what, $state)
    {
        if ($what === 'messages') {
            UserConfig::get($this->current_user->id)->store(
                'CONSULTATION_SEND_MESSAGES',
                (bool) $state
            );
        }

        $this->redirect('consultation/admin');
    }

    private function setupSidebar()
    {
        $sidebar = Sidebar::get();

        $actions = $sidebar->addWidget(new ActionsWidget());
        $actions->addLink(
            _('Sprechstundenblöcke anlegen'),
            $this->url_for('consultation/admin/create'),
            Icon::create('add')
        )->asDialog('size=auto');

        $options = $sidebar->addWidget(new OptionsWidget());
        $options->addCheckbox(
            _('Benachrichtungen über Buchungen'),
            UserConfig::get($this->current_user->id)->CONSULTATION_SEND_MESSAGES,
            $this->url_for("consultation/admin/toggle/messages/1"),
            $this->url_for("consultation/admin/toggle/messages/0")
        );
    }

    private function getDateAndTime($index)
    {
        if (!Request::submitted("{$index}-date") || !Request::submitted("{$index}-time")) {
            throw new Exception("Date with index '{$index}' was not submitted properly");
        }

        return strtotime(implode(' ', [
            Request::get("{$index}-date"),
            Request::get("{$index}-time")
        ]));
    }

    private function loadBlock($block_id)
    {
        if (is_array($block_id)) {
            return array_map([$this, 'loadBlock'], $block_id);
        }

        $block = ConsultationBlock::find($block_id);

        if ($block->teacher_id !== $this->current_user->id) {
            throw new AccessDeniedException();
        }

        return $block;
    }

    private function loadSlot($block_id, $slot_id)
    {
        $block = $this->loadBlock($block_id);
        $slot  = $block->slots->find($slot_id);

        if (!$slot) {
            throw new Exception(_('Dieser Sprechstundentermin existiert nicht'));
        }

        return $slot;
    }

    private function loadBooking($block_id, $slot_id, $booking_id)
    {
        $slot    = $this->loadSlot($block_id, $slot_id);
        $booking = $slot->bookings->find($booking_id);

        if (!$booking) {
            throw new Exception(_('Dieser Sprechstundenbuchung existiert nicht'));
        }

        return $booking;
    }
}
