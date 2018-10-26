<?php
# Lifter007: TEST

/**
 * Migration.php - abstract base class for migrations
 *
 * This class serves as the abstract base class for all migrations.
 *
 * @author    Marcus Lunzenauer <mlunzena@uos.de>
 * @copyright 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 * @license   GPL2 or any later version
 * @package   migrations
 */

class Migration
{
    /**
     * use verbose output
     *
     * @var boolean
     */
    private $verbose;

    /**
     * Initalize a Migration object (optionally using verbose output).
     *
     * @param boolean $verbose verbose output (default FALSE)
     */
    public function __construct($verbose = false)
    {
        $this->verbose = (bool) $verbose;
    }

    /**
     * Abstract method describing this migration step.
     * This method should be implemented in a migration subclass.
     *
     * @return string migration description
     */
    public function description()
    {
    }

    /**
     * Abstract method performing this migration step.
     * This method should be implemented in a migration subclass.
     */
    protected function up()
    {
    }

    /**
     * Abstract method reverting this migration step.
     * This method should be implemented in a migration subclass.
     */
    protected function down()
    {
    }

    /**
     * Perform or revert this migration, depending on the indicated direction.
     *
     * @param string $direction migration direction (either 'up' or 'down')
     */
    public function migrate($direction)
    {
        switch ($direction) {
            case 'up':
                $this->announce('migrating');
                break;
            case 'down':
                $this->announce('reverting');
                break;
            default:
            return;
        }

        $result = $this->$direction();

        $action = $direction === 'up' ? 'migrated' : 'reverted';
        $this->announce($action);

        $this->write();

        return $result;
    }

    /**
     * Print the given string (if verbose output is enabled).
     *
     * @param string $text text to print
     */
    private function write($text = '')
    {
        if ($this->verbose) {
            echo "{$text}\n";
        }
    }

    /**
     * Print the given formatted string (if verbose output is enabled).
     * Output always includes the migration's class name.
     *
     * @param string $format,... printf-style format string and parameters
     */
    private function announce($format /* , ... */)
    {
        # format message
        $args = func_get_args();
        $message = vsprintf(array_shift($args), $args);
        $text = sprintf('== %s: %s ', get_class($this), $message);

        return $this->write($text . ((mb_strlen($text)) < 79 ? str_repeat('=', 79 - mb_strlen($text)) : ''));
    }

    /**
     * Convenience method for creating config entries.
     *
     * @param  string  $field       Name of the config entry
     * @param  string  $description Description for the config entry
     * @param  string  $type        Type of the config entry (defaults to bool)
     * @param  boolean $value       Default value of the config entry (defaults to false)
     * @param  array   $data        Additional data like range or section
     * @return Config object that has been created
     */
    protected function createConfig($field, $description, $type = 'boolean', $value = false, array $data = [])
    {
        $defaults = [
            'range'   => 'global',
            'section' => '',
        ];

        return Config::get()->create($field, array_merge(
            $defaults,
            $data,
            compact('description', 'type', 'value')
        ));
    }

    /**
     * Convenience method for creating user config entries.
     *
     * @param  string  $field       Name of the config entry
     * @param  string  $description Description for the config entry
     * @param  string  $type        Type of the config entry (defaults to bool)
     * @param  boolean $value       Default value of the config entry (defaults to false)
     * @param  array   $data        Additional data like section
     * @return Config object that has been created
     */
    protected function createUserConfig($field, $description, $type = 'boolean', $value = false, array $data = [])
    {
        $data['range'] = 'user';
        return $this->createConfig($field, $description, $type, $value, $data);
    }

    /**
     * Convenience method for removing a config entry
     *
     * @param  string $field Name of the config entry
     * @return int number of deleted db rows
     */
    protected function removeConfig($field)
    {
        return Config::get()->delete($field);
    }
}
