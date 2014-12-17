<?php
class ResponsiveHelper
{
    public static function getNavigationArray()
    {
        $navigation = array();

        foreach (Navigation::getItem('/')->getSubNavigation() as $path => $nav) {
            if (!$nav->isVisible(true)) {
                continue;
            }

            $image = $nav->getImage();

            $item = array(
                'icon'   => $image ? self::getAssetsURL($image['src']) : false,
                'title'  => $nav->getTitle(),
                'url'    => self::getURL($nav->getURL()),
                'active' => $nav->isActive(),
            );
            
            if ($nav->getSubnavigation()) {
                $item['children'] = self::getChildren($nav, $path);
            }
            
            $navigation[$path] = $item;
        }
        
        return $navigation;
    }
    
    protected static function getChildren(Navigation $navigation, $path)
    {
        $children = array();

        foreach ($navigation->getSubNavigation() as $subpath => $subnav) {
            if (!$subnav->isVisible()) {
                continue;
            }

            $item = array(
                'title'  => $subnav->getTitle(),
                'url'    => self::getURL($subnav->getURL()),
                'active' => $subnav->isActive(),
            );
            
            if ($subnav->getSubNavigation()) {
                $item['children'] = self::getChildren($subnav, $path . '_' . $subpath);
            }
            
            $children[$path . '_' . $subpath] = $item;
        }
        
        return $children;
    }
    
    protected static function getURL($url)
    {
        return str_replace($GLOBALS['ABSOLUTE_URI_STUDIP'], '', $url);
    }

    protected static function getAssetsURL($url)
    {
        return str_replace($GLOBALS['ASSETS_URL'], '', $url);
    }
}