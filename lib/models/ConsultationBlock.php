<?php
class ConsultationBlock extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'consultation_blocks';

        $config['belongs_to']['teacher'] = [
            'class_name'  => User::class,
            'foreign_key' => 'teacher_id',
        ];
        $config['has_many']['slots'] = [
            'class_name'        => ConsultationSlot::class,
            'assoc_foreign_key' => 'block_id',
            'on_store'          => 'store',
            'on_delete'         => 'delete',
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'course_id',
        ];

        parent::configure($config);
    }

    public static function createBlocks($user_id, $start, $end, $week_day, $interval)
    {
        $start_time = date('H:i', $start);
        $end_time   = date('H:i', $end);

        // Adjust current date to match week of day
        $current = $start;
        while (date('w', $current) != $week_day) {
            $current = strtotime('+1 day', $current);
        }

        while ($current <= $end) {
            $temp    = holiday($current);
            $holiday = is_array($temp) && $temp['col'] === 3;

            if (!$holiday) {
                if ($overlaps = self::checkOverlaps($user_id, $start, $end)) {
                    $details = [];
                    foreach ($overlaps as $overlap) {
                        $details[] = sprintf(
                            _('%s bis %s von %s bis %s Uhr'),
                            strftime('%x', $overlap->start),
                            strftime('%x', $overlap->end),
                            date('H:i', $overlap->start),
                            date('H:i', $overlap->end)
                        );
                    }

                    throw new OverlapException(
                        _('Die Zeiten überschneiden sich mit anderen bereits von Ihnen definierten Sprechstunden'),
                        $details
                    );
                }

                $block = new self();
                $block->teacher_id = $user_id;
                $block->start      = strtotime("today {$start_time}", $current);
                $block->end        = strtotime("today {$end_time}", $current);

                yield $block;
            }

            $current = strtotime("+{$interval} weeks", $current);
        }
    }

    protected static function checkOverlaps($user_id, $start, $end)
    {
        $query = "SELECT DISTINCT `block_id`
                  FROM `consultation_slots`
                  JOIN `consultation_blocks` USING (`block_id`)
                  WHERE `teacher_id` = :teacher_id
                    AND `start_time` <= :start
                    AND `end_time` >= :end";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':teacher_id', $user_id);
        $statement->bindValue(':start', $start);
        $statement->bindValue(':end', $end);
        $statement->execute();
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return self::findMany($ids);
    }

    public function createSlots($duration)
    {
        $start = $this->start;
        while ($start < $this->end) {
            $slot = new ConsultationSlot();
            $slot->block_id   = $this->id;
            $slot->start_time = $start;
            $slot->end_time   = strtotime("+{$duration} minutes", $start);

            $this->slots[] = $slot;

            $start = $slot->end_time;
        }
    }
}
