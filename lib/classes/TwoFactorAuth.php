<?php
final class TwoFactorAuth
{
    const MAX_TRIES = 3;

    const SESSION_KEY           = 'tfa/confirmed';
    const SESSION_REDIRECT      = 'tfa/redirect';
    const SESSION_ENFORCE       = 'tfa/enforce';
    const SESSION_DATA          = 'tfa/data';
    const SESSION_CONFIRMATIONS = 'tfa/confirmations';
    const SESSION_FAILED        = 'tfa/failed';

    const COOKIE_KEY = 'tfa/authentication';

    private static $instance = null;

    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private $secret;

    private function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new Exception('2FA requires a valid session');
        }

        if (!isset($_SESSION[self::SESSION_FAILED])) {
            $_SESSION[self::SESSION_FAILED] = [];
        } else {
            // Remove failed items after 5 minutes
            $_SESSION[self::SESSION_FAILED] = array_filter(
                $_SESSION[self::SESSION_FAILED],
                function ($timestamp) {
                    return $timestamp > strtotime('-5 minutes');
                }
            );
        }

        $user = User::findCurrent();
        if (!$user) {
            return;
        }

        $secret = TFASecret::find($user->id);
        if (!$secret) {
            return;
        }

        $this->secret = $secret;
    }

    public function secureSession()
    {
        // TODO: AJAX?
        if (Request::isXhr()) {
            return;
        }

        if (!$this->secret) {
            return;
        }

        $this->validateFromRequest();

        if (!$this->secret->confirmed) {
            return;
        }

        // User has already confirmed this session?
        if (isset($_SESSION[self::SESSION_KEY])) {
            list($code, $timeslice) = array_values($_SESSION[self::SESSION_KEY]);
            if ($this->secret->validateToken($code, 0, $timeslice, true)) {
                return;
            }
            unset($_SESSION[self::SESSION_KEY]);
        }

        // Trusted computer?
        if (isset($_COOKIE[self::COOKIE_KEY])) {
            list($code, $timeslice) = explode(':', $_COOKIE[self::COOKIE_KEY]);
            if ($this->secret->validateToken($code, 0, $timeslice, true)) {
                $this->registerSecretInSession();
                return;
            }

            // Remove cookie
            setcookie(
                self::COOKIE_KEY,
                '',
                strtotime('-1 year'),
                $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']
            );
        }

        $this->showConfirmationScreen('', [
            'global' => true,
        ]);
    }

    public function confirm($action, $text, array $data = [])
    {
        if (isset($_SESSION[self::SESSION_CONFIRMATIONS])
            && is_array($_SESSION[self::SESSION_CONFIRMATIONS])
            && in_array($action, $_SESSION[self::SESSION_CONFIRMATIONS]))
        {
            $_SESSION[self::SESSION_CONFIRMATIONS] = array_diff(
                $_SESSION[self::SESSION_CONFIRMATIONS],
                [$action]
            );
            return true;
        }

        $this->showConfirmationScreen($text, $data + [
            'confirm' => $action,
        ]);
    }

    public function showConfirmationScreen($text = '', array $data = [])
    {
        $data = array_merge(['global' => false], $data);

        $_SESSION[self::SESSION_DATA] = array_merge($data, [
            '__nonce'  => md5(uniqid('tfa-nonce', true)),
            '__return' => $_SERVER['REQUEST_URI'],
        ]);

        if ($this->secret->type === 'email') {
            StudipMail::sendMessage(
                $this->secret->user->email,
                'Ihr Zwei-Faktor-Token',
                'Bitte geben Sie dieses Token ein: ' . $this->secret->getToken()
            );
        }

        echo $GLOBALS['template_factory']->render(
            'tfa-validate.php',
            $_SESSION[self::SESSION_DATA] + [
                'secret'  => $this->secret,
                'text'    => $text,
                'blocked' => $this->isBlocked(),
            ],
            'layouts/base.php'
        );
        page_close();
        die;
    }

    private function registerSecretInSession()
    {
        $timeslice = mt_rand(0, PHP_INT_MAX);
        $_SESSION[self::SESSION_KEY] = [
            'code'      => $this->secret->getToken($timeslice),
            'timeslice' => $timeslice,
        ];
    }

    private function registerSecretInCookie()
    {
        $timeslice = mt_rand(0, PHP_INT_MAX);
        setcookie(
            self::COOKIE_KEY,
            implode(':', [$this->secret->getToken($timeslice), $timeslice]),
            strtotime('+30 days'),
            $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']
        );
    }

    private function validateFromRequest()
    {
        if (
            $this->isBlocked()
            || !Request::isPost()
            || !Request::submitted('tfacode-input')
            || !Request::submitted('tfa-nonce')
            || !isset($_SESSION[self::SESSION_DATA])
            || !is_array($_SESSION[self::SESSION_DATA])
        ) {
            return;
        }

        $data = $_SESSION[self::SESSION_DATA];
        if (Request::option('tfa-nonce') === $data['__nonce']) {
            $token = implode('', Request::intArray('tfacode-input'));

            if ($this->secret->validateToken($token)) {
                $_SESSION[self::SESSION_FAILED] = [];

                if ($data['global'] ?? false) {
                    $this->registerSecretInSession();

                    if (Request::int('trusted')) {
                        $this->registerSecretInCookie();
                    }
                }

                if ($data['confirm'] ?? false) {
                    if (!isset($_SESSION[self::SESSION_CONFIRMATIONS])) {
                        $_SESSION[self::SESSION_CONFIRMATIONS] = [];
                    }
                    $_SESSION[self::SESSION_CONFIRMATIONS][] = $data['confirm'];
                }
            } else {
                $_SESSION[self::SESSION_FAILED][] = time();

                PageLayout::postError('Invalid token');
            }

            unset($_SESSION[self::SESSION_DATA]);
        }

        page_close();
        header('Location: ' . URLHelper::getURL($data['__return']));
        die;
    }

    private function isBlocked()
    {
        return count($_SESSION[self::SESSION_FAILED]) >= self::MAX_TRIES
             ? min($_SESSION[self::SESSION_FAILED])
             : false;
    }
}
