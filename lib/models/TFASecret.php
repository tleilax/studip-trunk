<?php
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

class TFASecret extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'users_tfa';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
        ];
        $config['has_many']['tokens'] = [
            'class_name' => TFAToken::class,
            'on_delete'  => 'delete',
        ];

        parent::configure($config);
    }

    public function setNew($is_new)
    {
        if ($is_new) {
            if (!$this->isNew()) {
                return;
            }
            $this->secret    = TOTP::create()->getSecret();
            $this->confirmed = false;
        }

        return parent::setNew($is_new);
    }

    public function restore()
    {
        $result = parent::restore();
        if ($result && !$this->mayAccess()) {
            throw new AccessDeniedException('You are not allowed to access this secret');
        }
        return $result;
    }

    private function mayAccess()
    {
        return $this->user_id
            && (
                $this->user_id === User::findCurrent()->id
                || $GLOBALS['user']->perms === 'root'
            );
    }

    public function getToken($timestamp = null)
    {
        return $this->getTOTP($this->secret)->at($timestamp ?? time());
    }

    public function validateToken($token, $discrepancy = 1, $timestamp = null, $allow_reuse = false)
    {
        if (!$token || !ctype_digit($token)) {
            return false;
        }

        if (!$allow_reuse && TFAToken::exists([$this->user_id, $token])) {
            return false;
        }

        // TODO: Actual validation

        if ($this->getTOTP()->verify($token, $timestamp, $discrepancy)) {
            if (!$this->confirmed) {
                $this->confirmed = true;
                $this->store();
            }

            if (!$allow_reuse) {
                TFAToken::create([
                    'user_id' => $this->user_id,
                    'token'   => $token,
                ]);
            }

            return true;
        }

        return false;
    }

    private function getTOTP()
    {
        return TOTP::create($this->secret);
    }

    public function getProvisioningUri()
    {
        $totp = $this->getTOTP();
        $totp->setLabel($this->user->email);
        $totp->setIssuer(Config::get()->UNI_NAME_CLEAN);
        return $totp->getProvisioningUri();
    }
}
