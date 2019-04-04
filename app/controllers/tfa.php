<?php
class TfaController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/profile/settings/tfa');
        PageLayout::setTitle(_('Zwei-Faktor-Authentisierung'));

        $this->secret = new TFASecret(User::findCurrent()->id);
    }

    public function index_action()
    {
        if ($this->secret->isNew()) {
            $this->render_action('setup');
        } elseif (!$this->secret->confirmed) {
            $this->render_action('confirm');
        }
    }

    public function setup_action()
    {
    }

    public function create_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $this->secret->type = Request::option('type', 'email');
        $this->secret->store();

        PageLayout::postSuccess(_('Die Zwei-Faktor-Authorisierung wurde eingerichtet'));
        $this->redirect('tfa/confirm');
    }

    public function confirm_action()
    {
        if ($this->secret->isNew()) {
            $this->redirect('tfa/index');
            return;
        }

        TwoFactorAuth::get()->confirm(
            '2fa',
            _('Bitte bestätigen Sie die Aktivierung.'),
            ['global' => true]
        );

        PageLayout::postSuccess(_('Die Authorisierung wurde bestätigt.'));
        $this->redirect('tfa/index');
    }

    public function revoke_action()
    {
        TwoFactorAuth::get()->confirm(
            '2fa-revoke',
            _('Bestätigen Sie das Aufheben der Methode')
        );

        $this->secret->delete();

        PageLayout::postSuccess(_('Die Authorisierung wurde aufgehoben.'));
        $this->redirect('tfa/index');
    }
}
