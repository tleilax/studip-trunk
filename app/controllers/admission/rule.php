<?php

require_once('app/controllers/authenticated_controller.php');
require_once('lib/classes/admission/AdmissionRule.class.php');

class Admission_RuleController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        if (Request::isXhr()) {
            $this->via_ajax = true;
            $this->set_layout(null);
        } else {
            $layout = $GLOBALS['template_factory']->open('layouts/base');
            $this->set_layout($layout);
            PageLayout::setTitle(_('Anmeldesets'));
            Navigation::activateItem('/tools/coursesets');
        }
    }

    public function configure_action($ruleType='', $ruleId='') {
        $this->ruleTypes = AdmissionRule::getAvailableAdmissionRules();
        if ($ruleType) {
            $this->ruleType = $ruleType;
            $this->rule = new $ruleType($ruleId);
        }
    }

    public function add_action() {
        $rules = AdmissionRule::getAvailableAdmissionRules();
        $ruleType = Request::option('ruletype');
        $this->rule = new $ruleType();
        $this->rule->setAllData(Request::getInstance());
    }

}

?>