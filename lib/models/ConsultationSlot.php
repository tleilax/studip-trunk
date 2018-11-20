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
            'class_name'  => EventData::class,
            'foreign_key' => 'teacher_event_id',
        ];
        $config['has_many']['bookings'] = [
            'class_name'        => ConsultationBooking::class,
            'assoc_foreign_key' => 'slot_id',
            'on_delete'         => 'delete',
        ];

        parent::configure($config);
    }
}
