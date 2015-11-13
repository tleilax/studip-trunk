<?php
/**
 * Description
 */
class OpenGraph
{
    const REGEXP = '/(?<=\s|^|\>)(?:(?:\[([^\n\f\]]+?)\])?)(\w+?:\/\/.+?)(?=\s|$)/ms';

    /**
     * 
     */
    public static function extract($string)
    {
        $collection = new OpenGraphURLCollection;

        $regexp = StudipFormat::getStudipMarkups()['links']['start'];
        $matched = preg_match_all('/' . $regexp . '/ms', $string, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $url = $match[2];

            if (!$url) {
                continue;
            }

            if (!isLinkIntern($url)) {
                $og_url = OpenGraphURL::fromURL($url);
                if ($og_url && !$collection->find($og_url->id)) {
                    $og_url->store();

                    $collection[] = $og_url;
                }
            }
        }

        return $collection;
    }
}
