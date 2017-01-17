<?php
namespace eTask;

/**
 * eTask conforming assignment definition
 *
 * @property int id database column
 * @property int test_id database column
 * @property string range_type database column
 * @property string range_id database column
 * @property string type database column
 * @property string start database column
 * @property string end database column
 * @property int active database column
 * @property string options database column
 */
class Assignment extends \SimpleORMap
{
    use ConfigureTrait;
    use CreatedChangedTrait;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_assignments';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['test'] = [
            'class_name' => $config['relationTypes']['Test'],
            'foreign_key' => 'test_id'
        ];

        $config['has_many']['attempts'] = [
            'class_name' => $config['relationTypes']['Assignment'],
            'assoc_foreign_key' => 'assignment_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['has_many']['ranges'] = [
            'class_name' => $config['relationTypes']['AssignmentRange'],
            'assoc_foreign_key' => 'assignment_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['has_many']['responses'] = [
            'class_name' => $config['relationTypes']['Response'],
            'assoc_foreign_key' => 'assignment_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }

    public function getStart()
    {
        return date('c', strtotime($this->content['start']));
    }

    public function getEnd()
    {
        return date('c', strtotime($this->content['end']));
    }
}
