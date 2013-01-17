<?php
class CronjobLog extends SimpleORMap
{
    public function __construct($id = null)
    {
        $this->db_table = 'cronjobs_logs';

        $this->belongs_to['schedule'] = array(
            'class_name'  => 'CronjobSchedule',
            'foreign_key' => 'schedule_id',
        );

        $this->registerCallback('before_store', function ($item, $type) {
            $item->exception = serialize($item->exception ?: null);
        });
        $this->registerCallback('after_initialize', function ($item, $type) {
            $item->exception = unserialize($item->exception) ?: null;
        });

        parent::__construct($id);
    }
}