<?

class StepRaumZeitEndOffset extends Migration
{
    function description()
    {
        return _('Fügrt eine neue Spalte hinzu, die Semesterwochhe für das Ende zu speicher!');
    }

    function up()
    {
        DBManager::get()->exec('ALTER TABLE `seminar_cycle_dates` ADD COLUMN `end_offset` TINYINT(3) NULL AFTER `week_offset`');
    }

    function down()
    {
        DBManager::get()->exec('ALTER TABLE `seminar_cycle_dates` DROP COLUMN `end_offset`');
    }
}