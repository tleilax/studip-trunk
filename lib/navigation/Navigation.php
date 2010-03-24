<?php
/*
 * Navigation.php - Stud.IP navigation base class
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * This is the navigation base class that maintains the global
 * navigation structure. All navigation objects are stored in a
 * tree and can be accessed by their "path names", just like file
 * names in a normal file system. The "root" of the tree is '/'.
 *
 * So you can do for example:
 *
 * $navigation = new Navigation('Home', 'index.php');
 * $profilenav = new Navigation('Profile', 'profile.php');
 *
 * $navigation->addSubNavigation('profile', $profilenav);
 *
 * Navigation::addItem('/home', $navigation);
 * Navigation::activateItem('/home/profile');
 */
class Navigation implements IteratorAggregate
{
    private static $root;

    protected $active;
    protected $enabled;

    protected $image;
    protected $params;
    protected $subnav;
    protected $title;
    protected $url;

    /**
     * Mark the navigation item at the given path as active.
     * This is just a shortcut for doing:
     *
     *   Navigation::getItem($path)->setActive(true)
     *
     * @param string $path       path of navigation item
     */
    public static function activateItem($path)
    {
        self::getItem($path)->setActive(true);
    }

    /**
     * Add a new navigation item at the given path. If there is
     * already an item with this path, the old one is replaced
     * by the new item.
     *
     * @param string $path       path of new navigation item
     * @param object $navigation navigation item to add
     */
    public static function addItem($path, Navigation $navigation)
    {
        $nav = self::getItem(strtr(dirname($path), '\\', '/'));
        $nav->addSubNavigation(basename($path), $navigation);
    }

    /**
     * Add a new navigation item at the given path. The new
     * item is inserted immediately before the item with the
     * name given by $where (at the same level in the tree).
     *
     * @param string $path       path of new navigation item
     * @param object $navigation navigation item to add
     * @param string $where      insert it before this item
     */
    public static function insertItem($path, Navigation $navigation, $where)
    {
        $nav = self::getItem(strtr(dirname($path), '\\', '/'));
        $nav->insertSubNavigation(basename($path), $navigation, $where);
    }

    /**
     * Remove the navigation item at the given path (if there
     * is an item with this path).
     *
     * @param string $path       path of item to remove
     */
    public static function removeItem($path)
    {
        $nav = self::getItem(strtr(dirname($path), '\\', '/'));
        $nav->removeSubNavigation(basename($path));
    }

    /**
     * Return the navigation item at the given path.
     *
     * @param string $path   path of navigation item
     *
     * @throws InvalidArgumentException  if the item cannot be found
     */
    public static function getItem($path)
    {
        $node = self::$root;

        foreach (explode('/', $path) as $name) {
            if ($name === '') {
                continue;
            }

            $subnav = $node->getSubNavigation();
            $node = $subnav[$name];

            if (!isset($node)) {
                throw new InvalidArgumentException("navigation item '$path' not found");
            }
        }

        return $node;
    }

    /**
     * Test whether there is a navigation item at the given path.
     *
     * @param string $path   path of navigation item
     */
    public static function hasItem($path)
    {
        try {
            self::getItem($path);
            return true;
        } catch (InvalidArgumentException $ex) {
            return false;
        }
    }

    /**
     * Set the root of the navigation tree. Must be called before
     * any further items can be added to the tree.
     *
     * @param object $navigation root navigation item
     */
    public static function setRootNavigation(Navigation $navigation)
    {
        self::$root = $navigation;
    }

    /**
     * Initialize a new Navigation instance with the given title
     * and URL (optional).
     */
    public function __construct($title, $url = NULL, $params = NULL)
    {
        $this->setTitle($title);
        $this->setURL($url, $params);
        $this->setEnabled(true);
    }

    /**
     * Return the current image attributes associated with this
     * navigation item. Attributes are returned as an array with
     * at least the 'src' key set.
     *
     * @return array   image attributes of item or NULL (no image)
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Return the current title associated with this navigation item.
     *
     * @return string   title of item or NULL (no title set)
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Return the current URL associated with this navigation item.
     * If not URL is set but there are subnavigation items, the URL
     * of the first visible subnavigation item is returned.
     *
     * @return string   url of item or NULL (no URL set)
     */
    public function getURL()
    {
        if (isset($this->url)) {
            if (isset($this->params)) {
                return URLHelper::getURL($this->url, $this->params);
            } else {
                return $this->url;
            }
        }

        foreach ($this->getSubNavigation() as $nav) {
            $url = $nav->getURL();

            if (isset($url)) {
                return $url;
            }
        }

        return NULL;
    }

    /**
     * Determine whether this navigation item is active.
     */
    public function isActive()
    {
        if (isset($this->active)) {
            return $this->active;
        }

        return (boolean) $this->activeSubNavigation();
    }

    /**
     * Return whether this navigation item is enabled.
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Return whether this navigation item is visible.
     *
     * @param boolean $needs_image  requires an image
     */
    public function isVisible($needs_image = false)
    {
        if ($needs_image && !isset($this->image)) {
            return false;
        }

        $url = $this->getURL();

        return isset($url);
    }

    /**
     * Set the active status of this item. This can be used to
     * override heuristics used by the class to determine this
     * automatically.
     *
     * @param boolean $active  new active status
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Set the enabled status of this item. Disabled items are
     * still visible but cannot be clicked.
     *
     * @param boolean $enabled  new enabled status
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Set the image of this navigation item. Additional HTML
     * attributes can be passed using the $options parameter
     * (like 'title', 'style' or 'onclick').
     *
     * @param string $image    path to image file
     * @param array  $options  additional image attributes
     */
    public function setImage($image, $options = array())
    {
        if (file_exists($image)) {
            $options['src'] = $image;
        } else {
            $options['src'] = Assets::image_path($image);
        }

        $this->image = $options;
    }

    /**
     * Set the title of this navigation item.
     *
     * @param string $title    display title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Set the URL of this navigation item. Additional URL
     * parameters can be passed using the (optional) second
     * parameter.
     *
     * @param string $title    display title
     * @param array  $params   additional URL parameters
     */
    public function setURL($url, $params = NULL)
    {
        $this->url = $url;
        $this->params = $params;
    }

    /**
     * Get the active subnavigation item of this navigation
     * (if there is one). Returns NULL if the subnavigation
     * has no active item.
     */
    public function activeSubNavigation()
    {
        if (isset($this->subnav)) {
            foreach ($this->subnav as $nav) {
                if ($nav->isActive()) {
                    return $nav;
                }
            }
        }

        return NULL;
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    protected function initSubNavigation()
    {
        $this->subnav = array();
    }

    /**
     * Add the given item to the subnavigation of this object.
     * This also assigns a name to this subnavigation item. If
     * there is already a subitem with this name, the old one
     * is replaced by the new item.
     *
     * @param string $name       name of new navigation item
     * @param object $navigation navigation item to add
     */
    public function addSubNavigation($name, Navigation $navigation)
    {
        if (!isset($this->subnav)) {
            $this->initSubNavigation();
        }

        $this->subnav[$name] = $navigation;
    }

    /**
     * Add the given item to the subnavigation of this object.
     * The new item is inserted immediately before the item with
     * the name given by $where (if there is one, it is appended
     * to the end otherwise). This also assigns a name to this
     * subnavigation item.
     *
     * @param string $name       name of new navigation item
     * @param object $navigation navigation item to add
     * @param string $where      insert it before this item
     */
    public function insertSubNavigation($name, Navigation $navigation, $where)
    {
        foreach ($this->getSubNavigation() as $key => $nav) {
            if ($key == $where) {
                $subnav[$name] = $navigation;
                $done = true;
            }

            $subnav[$key] = $nav;
        }

        if (!$done) {
            $subnav[$name] = $navigation;
        }

        $this->subnav = $subnav;
    }

    /**
     * Return the list of subnavigation items of this object.
     */
    public function getSubNavigation()
    {
        if (!isset($this->subnav)) {
            $this->initSubNavigation();
        }

        return $this->subnav;
    }

    /**
     * Remove the given item from the subnavigation of this
     * object (if there is an item with this name).
     *
     * @param string $name       name of item to remove
     */
    public function removeSubNavigation($name)
    {
        if (!isset($this->subnav)) {
            $this->initSubNavigation();
        }

        unset($this->subnav[$name]);
    }

    /**
     * IteratorAggregate: Create interator for request parameters.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getSubNavigation());
    }
}
