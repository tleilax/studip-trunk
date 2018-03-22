<?php
/**
 * show terms on first login and check if user accept them
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.2
 */
class TermsController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($GLOBALS['user']->cfg->TERMS_ACCEPTED) {
            $this->returnUser();
        }
    }

    public function index_action()
    {
        PageLayout::setTitle(_('Nutzungsbedingungen'));

        $this->return_to = Request::get('return_to');
    }

    public function answer_action()
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        if (Request::submitted('accept')) {
            $GLOBALS['user']->cfg->store('TERMS_ACCEPTED', 1);
            $this->returnUser();
        } else {
            $this->redirectUser('logout.php');
        }
    }

    private function redirectUser($target = null)
    {
        $target = $target ?: Request::get('return_to') ?: 'dispatch.php/start';
        $this->response->add_header('Location', URLHelper::getURL($target));
        $this->response->set_status(302);
        $this->render_nothing();
    }
}
