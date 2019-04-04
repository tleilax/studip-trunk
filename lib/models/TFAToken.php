<?php
class TFAToken extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'users_tfa_tokens';

        parent::configure($config);
    }
}
