<?php
/*
 * studip_controller.php - studip controller base class
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

abstract class StudipController extends Trails_Controller
{
    protected $with_session = false; //do we need to have a session for this controller
    protected $allow_nobody = true; //should 'nobody' allowed for this controller or redirected to login?

    public function before_filter(&$action, &$args)
    {
        $this->current_action = $action;
        // allow only "word" characters in arguments
        $this->validate_args($args);

        parent::before_filter($action, $args);

        if ($this->with_session) {
            # open session
            page_open(array(
                'sess' => 'Seminar_Session',
                'auth' => $this->allow_nobody ? 'Seminar_Default_Auth' : 'Seminar_Auth',
                'perm' => 'Seminar_Perm',
                'user' => 'Seminar_User'
            ));

            // show login-screen, if authentication is "nobody"
            $GLOBALS['auth']->login_if((Request::get('again') || !$this->allow_nobody) && $GLOBALS['user']->id == 'nobody');

            // Setup flash instance
            $this->flash = Trails_Flash::instance();

            // set up user session
            include 'lib/seminar_open.php';
        }

        // Set generic attribute that indicates whether the request was sent
        // via ajax or not
        $this->via_ajax = Request::isXhr();

        # Set base layout
        #
        # If your controller needs another layout, overwrite your controller's
        # before filter:
        #
        #   class YourController extends AuthenticatedController {
        #     function before_filter(&$action, &$args) {
        #       parent::before_filter($action, $args);
        #       $this->set_layout("your_layout");
        #     }
        #   }
        #
        # or unset layout by sending:
        #
        #   $this->set_layout(NULL)
        #
        $layout_file = Request::isXhr()
                     ? 'layouts/dialog.php'
                     : 'layouts/base.php';
        $layout = $GLOBALS['template_factory']->open($layout_file);
        $this->set_layout($layout);

        $this->set_content_type('text/html;charset=utf-8');
    }

    /**
     * Hooked perform method in order to inject body element id creation.
     *
     * In order to avoid clashes, these body element id will be joined
     * with a minus sign. Otherwise the controller "x" with action
     * "y_z" would be given the same id as the controller "x/y" with
     * the action "z", namely "x_y_z". With the minus sign this will
     * result in the ids "x-y_z" and "x_y-z".
     *
     * Plugins will always have a leading 'plugin-' and the decamelized
     * plugin name in front of the id.
     *
     * @param String $unconsumed_path Path segment containing action and
     *                                optionally arguments or format
     * @return Trails_Response from parent controller
     */
    public function perform($unconsumed_path)
    {
        // Extract action from unconsumed path segment
        list($action) = $this->extract_action_and_args($unconsumed_path);

        // Extract decamelized controller name from class name
        $controller = preg_replace('/Controller$/', '', get_class($this));
        $controller = Trails_Inflector::underscore($controller);

        // Build main parts of the body element id
        $body_id_parts = explode('/', $controller);
        $body_id_parts[] = $action;

        // If the controller is from a plugin, Inject plugin identifier
        // and name of plugin
        if (basename($this->dispatcher->trails_uri, '.php') === 'plugins') {
            $plugin = basename($this->dispatcher->trails_root);
            $plugin = Trails_Inflector::underscore($plugin);
            array_unshift($body_id_parts, $plugin);

            array_unshift($body_id_parts, 'plugin');
        }

        // Create and set body element id
        $body_id = implode('-', $body_id_parts);
        PageLayout::setBodyElementId($body_id);

        return parent::perform($unconsumed_path);
    }

    /**
     * Callback function being called after an action is executed.
     *
     * @param string Name of the action to perform.
     * @param array  An array of arguments to the action.
     *
     * @return void
     */
    public function after_filter($action, $args)
    {
        parent::after_filter($action, $args);

        if (Request::isXhr() && !isset($this->response->headers['X-Title']) && PageLayout::hasTitle()) {
            $this->response->add_header('X-Title', rawurlencode(PageLayout::getTitle()));
        }

        if ($this->with_session) {
            page_close();
        }
    }

    /**
     * Validate arguments based on a list of given types. The types are:
     * 'int', 'float', 'option' and 'string'. If the list of types is NULL
     * or shorter than the argument list, 'option' is assumed for all
     * remaining arguments. 'option' differs from Request::option() in
     * that it also accepts the charaters '-' and ',' in addition to all
     * word charaters.
     *
     * @param array   an array of arguments to the action
     * @param array   list of argument types (optional)
     */
    public function validate_args(&$args, $types = NULL)
    {
        foreach ($args as $i => &$arg) {
            $type = isset($types[$i]) ? $types[$i] : 'option';

            switch ($type) {
                case 'int':
                    $arg = (int) $arg;
                    break;

                case 'float':
                    $arg = (float) strtr($arg, ',', '.');
                    break;

                case 'option':
                    if (preg_match('/[^\\w,-]/', $arg)) {
                        throw new Trails_Exception(400);
                    }
            }
        }

        reset($args);
    }

    /**
     * Returns a URL to a specified route to your Trails application.
     * without first parameter the current action is used
     * if route begins with a / then the current controller ist prepended
     * if second parameter is an array it is passed to URLHeper
     *
     * @param  string   a string containing a controller and optionally an action
     * @param  strings  optional arguments
     *
     * @return string  a URL to this route
     */
    public function url_for($to = ''/* , ... */)
    {
        $args = func_get_args();
        if (is_array(end($args))) {
            $params = array_pop($args);
        } else {
            $params = array();
        }
        //preserve fragment
        list($to, $fragment) = explode('#', $to);
        if (!$to) {
            $to = '/' . ($this->parent_controller ? $this->parent_controller->current_action : $this->current_action);
        }
        if ($to[0] === '/') {
            $prefix = str_replace('_', '/', mb_strtolower(mb_strstr(get_class($this->parent_controller ?: $this), 'Controller', true)));
            $to = $prefix . $to;
        }
        $args[0] = $to;
        $url = preg_match('#^(/|\w+://)#', $to)
             ? $to
             : call_user_func_array('parent::url_for', $args);
        if ($fragment) {
            $url .= '#' . $fragment;
        }
        return URLHelper::getURL($url, $params);
    }

    /**
     * Returns an escaped URL to a specified route to your Trails application.
     * without first parameter the current action is used
     * if route begins with a / then the current controller ist prepended
     * if second parameter is an array it is passed to URLHeper
     *
     * @param  string   a string containing a controller and optionally an action
     * @param  strings  optional arguments
     *
     * @return string  a URL to this route
     */
    public function link_for($to = ''/* , ... */)
    {
        return htmlReady(call_user_func_array(array($this, 'url_for'), func_get_args()));
    }

    /**
     * Relocate the user to another location. This is a specialized version
     * of redirect that differs in two points:
     *
     * - relocate() will force the browser to leave the current dialog while
     *   redirect would refresh the dialog's contents
     * - relocate() accepts all the parameters that url_for() accepts so it's
     *   no longer neccessary to chain url_for() and redirect()
     *
     * @param String $to Location to redirect to
     */
    public function relocate($to)
    {
        $from_dialog = Request::isDialog();

        if (func_num_args() > 1 || $from_dialog) {
            $to = call_user_func_array(array($this, 'url_for'), func_get_args());
        }

        if ($from_dialog) {
            $this->response->add_header('X-Location', $to);
            $this->render_nothing();
        } else {
            parent::redirect($to);
        }
    }

    /**
     * Exception handler called when the performance of an action raises an
     * exception.
     *
     * @param  object     the thrown exception
     */
    public function rescue($exception)
    {
        throw $exception;
    }

    /**
     * render given data as json, data is converted to utf-8
     *
     * @param unknown $data
     */
    public function render_json($data)
    {
        $this->set_content_type('application/json;charset=utf-8');
        return $this->render_text(json_encode($data));
    }

    /**
     * relays current request to another controller and returns the response
     * the other controller is given all assigned properties, additional parameters are passed
     * through
     *
     * @param string $to_uri a trails route
     * @return Trails_Response
     */
    public function relay($to_uri/* , ... */)
    {
        $args = func_get_args();
        $uri = array_shift($args);
        list($controller_path, $unconsumed) = '' === $uri ? $this->dispatcher->default_route() : $this->dispatcher->parse($uri);

        $controller = $this->dispatcher->load_controller($controller_path);
        $assigns = $this->get_assigned_variables();
        unset($assigns['controller']);
        foreach ($assigns as $k => $v) {
            $controller->$k = $v;
        }
        $controller->layout = null;
        $controller->parent_controller = $this;
        array_unshift($args, $unconsumed);
        return call_user_func_array(array($controller, 'perform_relayed'), $args);
    }

    /**
     * perform a given action/parameter string from an relayed request
     * before_filter and after_filter methods are not called
     *
     * @see perform
     * @param string $unconsumed
     * @return Trails_Response
     */
    public function perform_relayed($unconsumed/* , ... */)
    {
        $args = func_get_args();
        $unconsumed = array_shift($args);

        list($action, $extracted_args, $format) = $this->extract_action_and_args($unconsumed);
        $this->format = isset($format) ? $format : 'html';
        $this->current_action = $action;
        $args = array_merge($extracted_args, $args);
        $callable = $this->map_action($action);

        if (is_callable($callable)) {
            call_user_func_array($callable, $args);
        } else {
            $this->does_not_understand($action, $args);
        }

        if (!$this->performed) {
            $this->render_action($action);
        }
        return $this->response;
    }

    /**
     * Renders a given template and returns the resulting string.
     *
     * @param string $template Name of the template file
     * @param mixed  $layout   Optional layout
     * @return string
     */
    public function render_template_as_string($template, $layout = null)
    {
        $template = $this->get_template_factory()->open($template);
        $template->set_layout($layout);
        $template->set_attributes($this->get_assigned_variables());
        return $template->render();
    }

}
