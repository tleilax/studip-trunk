<?php
/**
 * Representation of a block of consultation slots - defining metadata.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 */
class ConsultationBlock extends SimpleORMap
{
    /**
     * Configures the model.
     * @param array  $config Configuration
     */
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

        $config['additional_fields']['has_bookings']['get'] = function ($block) {
            $count = 0;
            foreach ($block->slots as $slot) {
                $count += count($slot->bookings);
            }
            return $count > 0;
        };

        parent::configure($config);
    }

    /**
     * Returns whether any blocks of a teacher exist that should be visible for
     * the given user.
     *
     * @param string $teacher_id User id of the teacher
     * @param string $user_id    Id of the user
     * @return bool
     */
    public static function existForTeacherAndUser($teacher_id, $user_id)
    {
        $query = "SELECT 1
                  FROM `consultation_blocks` AS cb
                  LEFT JOIN `seminare` AS s ON cb.`course_id` = s.`Seminar_id`
                  LEFT JOIN `seminar_user` AS su USING (`Seminar_id`)
                  WHERE `teacher_id` = :teacher_id
                    AND (
                        cb.`course_id` IS NULL
                        OR su.`user_id` = :user_id
                   )";
        return (bool) DBManager::get()->fetchColumn($query, [
            ':teacher_id' => $teacher_id,
            ':user_id'    => $user_id,
        ]);
    }

    /**
     * Generate blocks according to the given data.
     *
     * Be aware, that this is an actual generator that yields the results. You
     * cannot count the generated blocks without iterating over them.
     *
     * @param  string $user_id  Id of the user
     * @param  int    $start    Start of the time range as unix timestamp
     * @param  int    $end      End of the time range as unix timestamp
     * @param  int    $week_day Day of the week the blocks should be created
     *                          (0 = sunday, 1 = monday ...)
     * @param  int    $interval Week interval (skip $interval weeks between
     *                          blocks)
     */
    public static function generateBlocks($user_id, $start, $end, $week_day, $interval)
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

    /**
     * Checks if there any consultation slots already exist in the given
     * time range for the given user.
     *
     * @param  string $user_id Id of the user
     * @param  int    $start   Start of the time range as unix timestamp
     * @param  int    $end     End of the time range as unix timestamp
     * @return array of overlapping consultation slots
     */
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

    /**
     * Creates individual slots according to the defined data and given
     * duration.
     *
     * @param  int $duration Duration of a slot in minutes
     */
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

    /**
     * Returns whether this slot is visible for a user.
     *
     * @param  mixed $user_id Id of the user (optional, defaults to current user)
     * @return boolean defining whether the slot is visible
     */
    public function isVisibleForUser($user_id = null)
    {
        if ($user_id === null) {
            $user_id = $GLOBALS['user']->id;
        }

        return $this->teacher_id === $user_id
            || !$this->course_id
            || (bool) $this->course->members->findOneBy('user_id', $user_id);
    }
}
