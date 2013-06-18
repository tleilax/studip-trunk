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
            $this->ruleTemplate = ($this->via_ajax ? 
                utf8_encode($this->rule->getTemplate()) : 
                $this->rule->getTemplate());
        }
    }

    public function save_action($ruleType, $ruleId='') {
        $rules = AdmissionRule::getAvailableAdmissionRules();
        $this->rule = new $ruleType($ruleId);
        $requestData = Request::getInstance();
        if ($this->via_ajax) {
            $decoded = array();
            foreach ($requestData as $name => $entry) {
                $decoded[$name] = is_array($entry) ? array_map('utf8_decode', $entry) : utf8_decode($entry);
            }
            $this->rule->setAllData($decoded);
        } else {
            $this->rule->setAllData($requestData);
        }
        //$this->rule->store();
    }

    public function delete_action($ruleType, $ruleId) {
        $rules = AdmissionRule::getAvailableAdmissionRules();
        $rule = new $ruleType($ruleId);
        $rule->delete();
    }

    public function validate_action($ruleType) {
        $rules = AdmissionRule::getAvailableAdmissionRules();
        $rule = new $ruleType($ruleId);
        $this->errors = $rule->validate(Request::getInstance());
    }

}

?>