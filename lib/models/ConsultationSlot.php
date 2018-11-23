<?php
class ConsultationSlot extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'consultation_slots';

        $config['belongs_to']['block'] = [
            'class_name'  => ConsultationBlock::class,
            'foreign_key' => 'block_id',
        ];
        $config['has_one']['event'] = [
            'class_name'        => EventData::class,
            'foreign_key'       => 'teacher_event_id',
            'assoc_foreign_key' => 'event_id',
            'on_delete'         => 'delete',
        ];
        $config['has_many']['bookings'] = [
            'class_name'        => ConsultationBooking::class,
            'assoc_foreign_key' => 'slot_id',
            'on_store'          => 'store',
            'on_delete'         => 'delete',
        ];

        $config['registered_callbacks']['before_create'][] = function ($slot) {
            if ($slot->block->calendar_events) {
                $slot->teacher_event_id = $slot->createEvent($slot->block->teacher)->id;
                $slot->updateEvent();
            }
        };

        parent::configure($config);
    }

    public function isOccupied($user_id = null)
    {
        return $user->id === null
             ? count($this->bookings) >= $this->block->size
             : (bool) $this->bookings->fineOneBy('user_id', $user_id);
    }

    public function createEvent(User $user)
    {
        $event = new EventData();
        $event->uid = $this->createEventId($user);
        $event->author_id = $user->id;
        $event->editor_id = $user->id;
        $event->start     = $this->start_time;
        $event->end       = $this->end_time;
        $event->class     = 'PRIVATE';
        $event->priority  = 0;
        $event->location  = $this->block->room;
        $event->rtype     = 'SINGLE';
        $event->store();

        $calendar_event = new CalendarEvent();
        $calendar_event->range_id     = $user->id;
        $calendar_event->group_status = 0;
        $calendar_event->event_id     = $event->id;
        $calendar_event->store();

        return $event;
    }

    protected function createEventId(User $user)
    {
        $rand_id = md5(uniqid(self::class, true));
        return "Termin{$rand_id}-{$user->id}";
    }

    public function updateEvent()
    {
        if (!$this->event) {
            return;
        }

        if (count($this->bookings) === 0) {
            $this->event->category_intern = 9;
            $this->event->summary         = _('Freier Sprechstundentermin');
            $this->event->description     = _('Dieser Sprechstundentermin ist noch nicht belegt.');
        } else {
            $this->event->category_intern = 1;

            if (count($this->bookings) === 1) {
                $booking = $this->bookings->first();

                $this->event->summary     = sprintf(
                    _('Sprechstundentermin mit %s'),
                    $booking->user->getFullName()
                );
                $this->event->description = $booking->reason;
            } else {
                $this->event->summary     = sprintf(
                    _('Sprechstundentermin mit %u Personen'),
                    count($this->bookings)
                );
                $this->event->description = implode("\n\n----\n\n", $this->bookings->map(function ($booking) {
                    return "- {$booking->user->getFullName()}:\n{$booking->reason}";
                }));
            }
        }

        $this->event->store();
    }
}
