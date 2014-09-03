<?php
class Icon
{
    const SVG = 1;
    const PNG = 2;
    const CSS_BACKGROUND = 4;
    
    const DEFAULT_SIZE = 16;
    const DEFAULT_COLOR = 'blue';
    
    public static $icon_colors = array(
        'black', 'blue', 'green', 'grey', 'lightblue', 'red', 'white', 'yellow',
    );

    public static function create($source, $size = Icon::DEFAULT_SIZE, $color = Icon::DEFAULT_COLOR, $icon = false, $attributes = array())
    {
        // Extend arguments if not all are given
        if (func_num_args() === 2 && is_array($size)) {
            $attributes = $size;
            $size = Icon::DEFAULT_SIZE;
        } else if (func_num_args() === 3 && is_array($color)) {
            $attributes = $color;
            $color = Icon::DEFAULT_COLOR;
        } else if (func_num_args() === 4 && is_array($icon)) {
            $attributes = $extra;
            $icon = false;
        }

        // Try to guess correct order of passed arguments
        $defined = array_filter(compact(words('size color icon')));
        $defined = self::rearrange($defined);
        $icon = $defined['icon'];
        unset($defined['icon']);

        $opts = self::rearrange($source, $defined, $defined['icon']);

        $opts['source'] = preg_replace('/\.(png|svg)$/', '', $opts['icon']);

        return new self($opts['source'], $opts['size'], $opts['color'], $attributes);
    }
    
    protected static function rearrange($input, $defaults = array(), $extra = false)
    {
        if (!is_array($input)) {
            $input = str_replace(Assets::url('images/'), '', $input);
            if (strpos($input, 'http') !== false) {
                echo '<pre>';var_dump($input, Assets::url('images/'));die;
            }
            $input = preg_replace('~^icons/~S', '', $input);
            $input = preg_replace('/\.png$/S', '', $input);
            $input = explode('/', $input);
        }
        
        $result = array_merge(array(
            'size' => Icon::DEFAULT_SIZE,
            'color' => Icon::DEFAULT_COLOR,
            'icon' => array(),
        ), $defaults); 

        foreach ($input as $chunk) {
            if (is_int($chunk) || ctype_digit($chunk)) {
                $result['size'] = $chunk;
            } elseif (in_array($chunk, self::$icon_colors)) {
                $result['color'] = $chunk;
            } else {
                $result['icon'][] = $chunk;
            }
        }

        if (count($result['icon']) === 1 && $extra) {
            array_unshift($result['icon'], $extra);
        }

        $result['icon'] = join('/', $result['icon']);
        
        return $result;
    }

    protected $icon;
    protected $size;
    protected $color;
    protected $attributes;

    public function __construct($icon, $size = Icon::DEFAULT_SIZE, $color = Icon::DEFAULT_COLOR, $attributes = array())
    {
        $this->icon       = $icon;
        $this->size       = $size;
        $this->color      = $color;
        $this->attributes = $attributes;
    }

    public function __toString()
    {
        return $this->render_svg();
    }

    public function render($type = Icon::SVG)
    {
        if ($type === Icon::SVG) {
            return $this->render_svg();
        }
        if ($type === Icon::PNG) {
            return $this->render_png();
        }
        if ($type === Icon::CSS_BACKGROUND) {
            return $this->render_css_background();
        }
        throw new Exception('Unknown type');
    }
    
    protected function render_svg()
    {
        $png_attributes = array(
            'xlink:href' => $this->get_asset(Icon::SVG),
            'src' => $this->get_asset(Icon::PNG),
            'alt' => $this->attributes['alt'] ?: $this->attributes['title'] ?: basename($this->icon),
            'width'  => $this->get_size(),
            'height' => $this->get_size(),
        );
        unset($this->attributes['alt']);

        $svg_attributes = array_merge($this->attributes, array(
            'width'  => $this->get_size(),
            'height' => $this->get_size(),
        ));

        return sprintf('<svg %s><image %s></svg>',
                              $this->tag_options($svg_attributes),
                              $this->tag_options($png_attributes));
    }
    
    protected function render_png()
    {
        $attributes = array_merge($this->attributes, array(
            'src' => $this->get_asset(Icon::PNG),
            'alt' => $this->attributes['alt'] ?: $this->attributes['title'] ?: basename($this->icon),
            'width'  => $this->get_size(),
            'height' => $this->get_size(),
        ));
        
        return sprintf('<img %s>', $this->tag_options($attributes));
    }

    protected function render_css_background()
    {
        return sprintf('background-image:url(%1$s);background-image:none,url(%2$s);background-size:%3$upx %3$upx;',
                       $this->get_asset(Icon::PNG),
                       $this->get_asset(Icon::SVG),
                       $this->get_size());
    }

    protected function get_asset($type)
    {
        if ($type === Icon::SVG) {
            return Assets::url('images/icons/' . $this->color . '/' . $this->icon . '.svg');
        }
        if ($type === Icon::PNG) {
            $size = $this->size;
            if ($GLOBALS['auth']->auth['devicePixelRatio'] > 1.2) {
                $size *= 2;
            }
            return Assets::url('images/icons/' . $size . '/' . $this->color . '/' . $this->icon . '.png');
        }
        throw new Exception('Unknown type');
    }

    protected function get_size()
    {
        $size = $this->size;
        if (isset($this->attributes['size'])) {
            list($size, $temp) = explode('@', $this->attributes['size'], 2);
            unset($this->attributes['size']);
        }
        return $size;
    }

    protected function tag_options($options)
    {
        $result = array();
        foreach ($options as $key => $value) {
            $result[] = sprintf('%s="%s"', $key, htmlReady($value));
        }
        return join(' ', $result);
    }

}