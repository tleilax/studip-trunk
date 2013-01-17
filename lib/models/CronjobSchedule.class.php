<?php
/**
 *
 */
class CronjobSchedule extends SimpleORMap
{
    const PRIORITY_LOW    = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH   = 'high';
    
    public static function getPriorities()
    {
        $mapping = array();
        $mapping[self::PRIORITY_LOW]    = _('niedrig');
        $mapping[self::PRIORITY_NORMAL] = _('normal');
        $mapping[self::PRIORITY_HIGH]   = _('hoch');
        
        return $mapping;
    }
    
    public static function describePriority($priority)
    {
        $mapping = self::getPriorities();

        if (!isset($mapping[$priority])) {
            throw new RuntimeException('Access to unknown priority "' . $priority . '"');
        }

        return $mapping[$priority];
    }

    /**
     *
     */
    public function getValue($field)
    {
        $value = parent::getValue($field);
        return $field === 'title'
            ? ($value ?: $this->task->name)
            : $value;
    }

    /**
     *
     */
    public function __construct($id = null)
    {
        $this->db_table = 'cronjobs_schedules';

        $this->belongs_to['task'] = array(
            'class_name'  => 'CronjobTask',
            'foreign_key' => 'task_id',
        );

        $this->has_many['logs'] = array(
            'class_name' => 'CronjobLog',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        );

        $this->registerCallback('before_store', function ($item, $type) {
            $item->parameters = json_encode($item->parameters ?: array());
        });
        $this->registerCallback('after_store after_initialize', function ($item, $type) {
            $item->parameters = json_decode($item->parameters, true) ?: array();
        });

        parent::__construct($id);
    }

    /**
     *
     */
    public function store()
    {
        if ($this->task === null) {
            $message = sprintf('A task with the id "%s" does not exist.', $this->task_id);
            throw new InvalidArgumentException($message);
        }

        parent::store();

        return $this;
    }

    /**
     *
     */
    public function activate()
    {
        $this->active         = 1;
        $this->next_execution = $this->calculateNextExecution();
        $this->store();

        return $this;
    }

    /**
     *
     */
    public function deactivate()
    {
        $this->active = 0;
        $this->store();

        return $this;
    }

    /**
     *
     */
    public function execute($force = false)
    {
        if (!$force && !$this->active) {
            throw new RuntimeException('Execution aborted. Schedule is not active');
        }
        if (!$this->task->active) {
            throw new RuntimeException('Execution aborted. Associated task is not active');
        }

        $this->last_execution   = time();
        $this->execution_count += 1;
        $this->next_execution   = $this->calculateNextExecution();
        $this->store();

        $this->task->execution_count += 1;
        $this->task->store();

        $result = $this->task->engage($this->last_result, $this->parameters);

        $this->last_result = $result;
        $this->store();

        return $result;
    }

    /**
     *
     */
    public function shouldExecute($now = null)
    {
        return ($now ?: time()) >= $this->next_execution;
    }

    /**
     *
     */
    public function calculateNextExecution($now = null)
    {
        $now = $now ?: time();

        if ($this->type === 'once') {
            return $now <= $this->next_execution
                ? $this->next_execution
                : false;
        }

        $result  = $now;
        $result -= $result % 60;

        $i = 366 * 24 * 60; // Maximum: A year
        $offset = 60;

        do {
            $result += $offset;

            // TODO: Performance - Adjust result according to conditions
            // See http://coderzone.org/library/PHP-PHP-Cron-Parser-Class_1084.htm
            $valid  = $this->testTimestamp($result, $this->minute, 'i')
                   && $this->testTimestamp($result, $this->hour, 'H')
                   && $this->testTimestamp($result, $this->day, 'd')
                   && $this->testTimestamp($result, $this->month, 'm')
                   && $this->testTimestamp($result, $this->day_of_week, 'N');

        } while (!$valid && $i-- > 0);

        if ($i <= 0) {
            throw new Exception('No result, current: ' . date('d.m.Y H:i', $result));
        }

        $this->next_execution = $result;
        return $result;
    }

    /**
     *
     */
    protected function testTimestamp($timestamp, $condition, $format)
    {
        if ($condition === null) {
            return true;
        }

        $probe     = (int) date($format, $timestamp);
        $condition = (int) $condition;

        if ($condition < 0) {
            return ($probe % abs($condition)) === 0;
        }

        return $probe === $condition;
    }
}
