<?php
class ConsultationBooking extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'consultation_bookings';

        $config['belongs_to']['slot'] = [
            'class_name'  => ConsultationSlot::class,
            'foreign_key' => 'slot_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id',
        ];
        $config['has_one']['event'] = [
            'class_name'        => EventData::class,
            'foreign_key'       => 'student_event_id',
            'assoc_foreign_key' => 'event_id',
            'on_delete'         => 'delete',
        ];

        $config['registered_callbacks']['before_create'][] = function ($booking) {
            $event = $booking->slot->createEvent($booking->user);
            $event->category_intern = 1;
            $event->summary = sprintf(
                _('Sprechstundentermin bei %s'),
                $booking->slot->block->teacher->getFullName()
            );
            $event->description = $booking->reason;
            $event->store();

            $booking->student_event_id = $event->id;
        };

        $config['registered_callbacks']['after_store'][] = function ($booking) {
            if ($booking->event) {
                $booking->event->description = $booking->reason;
                $booking->event->store();
            }

            $booking->slot->updateEvent();
        };

        $config['registered_callbacks']['after_delete'][] = function ($booking) {
            $booking->slot->updateEvent();
        };

        parent::configure($config);
    }
}
