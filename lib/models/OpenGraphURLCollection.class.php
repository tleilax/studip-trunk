<?php
/**
 * Description
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class OpenGraphURLCollection extends SimpleORMapCollection
{
    public function getClassName()
    {
        return 'OpenGraphURL';
    }

    public function render($with_wrapper = true)
    {
        if (!Config::Get()->OPENGRAPH_ENABLE || count($this) === 0) {
            return '';
        }

        $rendered_urls = $this->sendMessage('render');
        $rendered_urls = array_filter($rendered_urls);

        if ($with_wrapper) {
            $template = $GLOBALS['template_factory']->open('shared/opengraph-container.php');
            $template->urls = $rendered_urls;
            $result = $template->render();
        } else {
            $result = implode("\n", $rendered_urls);
        }
        
        return $result;
    }
}
