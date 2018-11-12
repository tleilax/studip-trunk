<?php

class QuestionnaireAnonymousAnswer extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'questionnaire_anonymous_answers';
        $config['belongs_to']['questionnaire'] = array(
            'class_name' => 'Questionnaire'
        );
        parent::configure($config);
    }

    /**
     * Return a storage object (an instance of the StoredUserData class)
     * enriched with the available data of a given user.
     *
     * @param User $user User object to acquire data for
     * @return array of StoredUserData objects
     */
    public static function getUserdata(User $user)
    {
        $storage = new StoredUserData($user);
        $sorm = self::findBySQL("user_id = ?", [$user->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData('questionnaire_anonymous_answers', $field_data, $user);
            }
        }
        return [_('Fragebögen anonyme Antworten') => $storage];
    }
}
