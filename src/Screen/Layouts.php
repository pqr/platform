<?php

declare(strict_types=1);

namespace Orchid\Screen;

/*
 * Class Layouts.
 *
 * @method static Layouts blank(array $name)
 * @method static Layouts tabs(array $name)
 * @method static Layouts columns(array $name)
 * @method static Layouts modals(array $name)
 * @method static Layouts div(array $name)
 * @method static Layouts view(string $name)
 */

class Layouts
{
    /**
     * @var null
     */
    public $active = null;

    /**
     * @var array
     */
    public $templates = [
        'blank'   => 'platform::container.layouts.blank',
        'tabs'    => 'platform::container.layouts.tabs',
        'columns' => 'platform::container.layouts.columns',
        'modals'  => 'platform::container.layouts.modals',
        'div'     => 'platform::container.layouts.div',
    ];

    /**
     * @var array
     */
    public $layouts = [];

    /**
     * @var array
     */
    public $asyncMethod;

    /**
     * @var array
     */
    public $asyncRoute;

    /**
     * @var bool
     */
    public $async = false;

    /**
     * @var bool
     */
    public $asyncSaveAjax = false;

    /**
     * @var
     */
    public $slug;

    /**
     * @var array
     */
    public $compose = [];

    /**
     * Layouts constructor.
     */
    public function __construct()
    {
        $this->slug = sha1(serialize($this));
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $new = new self();
        $new->active = $name;

        return call_user_func_array([$new, 'setLayouts'], $arguments);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (is_null($name)) {
            $this->active = $name;

            return call_user_func_array([$this, 'setLayouts'], $arguments);
        }

        $this->compose[$name] = array_shift($arguments);

        return $this;
    }

    /**
     * @param $property
     *
     * @return mixed
     */
    protected function setLayouts($property)
    {
        $this->layouts = $property;
        $this->slug = sha1(serialize($this));

        return $this;
    }

    /**
     * @param $post
     *
     * @throws \Throwable
     *
     * @return array
     */
    public function build(Repository $post, $async = false)
    {
        foreach ($this->layouts as $key => $layouts) {
            foreach ($layouts as $layout) {
                if (! is_object($layout)) {
                    $layout = new $layout();
                }
                $build[$key][] = $layout->build($post);
            }
        }

        return view($async ? 'platform::container.layouts.async' : $this->templates[$this->active], [
            'manyForms' => $build ?? [],
            'compose'   => $this->compose,
            'templateSlug'  => $this->slug,
            'templateAsync' => $this->async,
            'templateAsyncMethod' => $this->asyncMethod,
            'templateAsyncRoute'  => $this->asyncRoute,
            'templateSaveAjax'    => $this->asyncSaveAjax,
        ]);
    }

    /**
     * @param string $method
     * @param bool   $async
     *
     * @return \Orchid\Screen\Layouts
     */
    public function async(string $method, string $route = null, $async = true, $saveajax = false) : self
    {
        $this->async = $async;
        $this->asyncMethod = $method;
        $this->asyncRoute = $route;
        $this->asyncSaveAjax = $saveajax;

        return $this;
    }
}