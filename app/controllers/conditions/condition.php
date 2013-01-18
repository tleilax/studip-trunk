<?php

require_once('app/controllers/authenticated_controller.php');
require_once('lib/classes/admission/StudipCondition.class.php');
require_once('lib/classes/admission/ConditionField.class.php');

class Conditions_ConditionController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        if (Request::isXhr()) {
            $this->via_ajax = true;
            $this->set_layout(null);
        } else {
            $layout = $GLOBALS['template_factory']->open('layouts/base');
            $this->set_layout($layout);
            PageLayout::setTitle(_('Auswahlbedingungen'));
            Navigation::activateItem('/tools/coursesets');
        }
    }

    public function configure_action($conditionId='') {
        $this->conditionFields = ConditionField::getAvailableConditionFields();
        if ($conditionId) {
            $this->condition = new StudipCondition($conditionId);
        }
    }

    public function add_action() {
        $condition = new StudipCondition();
        $conditionFields = ConditionField::getAvailableConditionFields();
        $fields = Request::getArray('field');
        $compareOps = Request::getArray('compare_operator');
        $values = Request::getArray('value');
        $data = array();
        for ($i=0 ; $i<sizeof($fields) ; $i++) {
            $current = $fields[$i];
            if ($conditionFields[$current]) {
                $field = new $current();
                $field->setCompareOperator($compareOps[$i]);
                $field->setValue($values[$i]);
                $condition->addField($field);
            }
        }
        $startdate = Request::get('startdate');
        if ($startdate) {
            $starthour = Request::get('starthour');
            $startminute = Request::get('startminute');
            $parsed = date_parse($startdate.' '.$starthour.':'.$startminute);
            $timestamp = mktime($parsed['hour'], $parsed['minute'], 0, $parsed['month'], $parsed['day'], $parsed['year']);
            $condition->setStartTime($timestamp);
        }
        $enddate = Request::get('enddate');
        if ($enddate) {
            $endhour = Request::get('endhour');
            $endminute = Request::get('endminute');
            $parsed = date_parse($enddate.' '.$endhour.':'.$endminute);
            $timestamp = mktime($parsed['hour'], $parsed['minute'], 0, $parsed['month'], $parsed['day'], $parsed['year']);
            $condition->setEndTime($timestamp);
        }
        $this->condition = $condition;
    }

}

?>