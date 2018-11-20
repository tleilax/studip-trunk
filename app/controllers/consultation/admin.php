<?php
class Consultation_AdminController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    public function index_action()
    {
        Navigation::activateItem('/profile/consultation/admin');
        PageLayout::setTitle(_('Verwaltung der Sprechstundentermine'));
    }
}
