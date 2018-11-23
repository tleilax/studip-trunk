<?php

use Grading\Definition;
use Grading\Instance;

trait GradebookTemplateHelpers
{
    public function formatAsPercent($value)
    {
        return (float) (round($value * 1000) / 10);
    }

    public function getNormalizedWeight(Definition $definition)
    {
        return $this->sumOfWeights ? $definition->weight / $this->sumOfWeights : 0;
    }

    protected function getSumOfWeights($gradingDefinitions)
    {
        $sumOfWeights = 0;
        foreach ($gradingDefinitions as $def) {
            $sumOfWeights += $def->weight;
        }

        return $sumOfWeights;
    }

    protected function getGroupedDefinitions($gradingDefinitions)
    {
        $groupedDefinitions = [];
        foreach ($gradingDefinitions as $def) {
            if (!isset($groupedDefinitions[$def->category])) {
                $groupedDefinitions[$def->category] = [];
            }
            $groupedDefinitions[$def->category][] = $def;
        }

        return $groupedDefinitions;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getCurrentUser()
    {
        return \User::findCurrent();
    }

    protected function viewerIsStudent()
    {
        return $this->viewerHasPerm('autor') && !$this->viewerHasPerm('dozent');
    }

    protected function viewerIsLecturer()
    {
        return $this->viewerHasPerm('dozent');
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function viewerHasPerm($perm)
    {
        $currentUserId = $GLOBALS['user'] ? $GLOBALS['user']->id : 'nobody';
        $currentContextId = \Context::getId();

        return $GLOBALS['perm']->have_studip_perm($perm, $currentContextId, $currentUserId);
    }

    protected function setDefaultPageTitle()
    {
        \PageLayout::setTitle(Context::getHeaderLine().' - Gradebook');
    }

    protected function getCategories(\Course $course)
    {
        return Definition::getCategoriesByCourse($course);
    }
}
