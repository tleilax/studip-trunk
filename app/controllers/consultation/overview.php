<?php
require_once __DIR__ . '/consultation_controller.php';

class Consultation_OverviewController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->current_user = User::findByUsername(Request::username('username', $GLOBALS['user']->username));
        $this->own_page     = $this->current_user->id === $GLOBALS['user']->id;
    }

    public function index_action()
    {
        $title = $this->own_page
               ? _('Meine Sprechstundentermine')
               : sprintf(_('Sprechstundentermine von %s'), $this->current_user->getFullName());

        Navigation::activateItem('/profile/consultation/overview');
        PageLayout::setTitle($title);
    }
}
