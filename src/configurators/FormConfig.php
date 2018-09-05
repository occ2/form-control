<?php
/*
 * The MIT License
 *
 * Copyright 2018 Milan Onderka <milan_onderka@occ2.cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace occ2\FormControl\configurators;

use occ2\FormControl\FormControl;
use occ2\control\IConfigurator;
use Nette\Reflection\ClassType;
use Nette\DI\Config\Helpers;
use Nette\Caching\Cache;

/**
 * FormConfig
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class FormConfig implements IConfigurator
{
    const CACHE_PREFIX="forms";

    const TEXTS=[
        "title","comment","footer"
    ];

    const CONFIGS=[
        "ajax"=>"setAjax",
        "modal"=>"setModal",
        "events"=>"setEvents"
    ];

    /**
     * @var array
     */
    protected $renderers=[
        "form"=>"rForm",
        "error"=>"rError",
        "group"=>"rGroup",
        "controls"=>"rControls",
        "pair"=>"rPair",
        "control"=>"rControl",
        "label"=>"rLabel",
        "hidden"=>"rHidden"
    ];

    /**
     * @var array
     */
    protected $annotations;

    /**
     * @var FormControl
     */
    protected $parent;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param FormControl $parent
     * @return void
     */
    public function __construct(FormControl $parent)
    {
        $classType = ClassType::from($parent);
        $this->cache = $parent->getCacheFactory()->create(static::CACHE_PREFIX);
        $this->annotations = $this->cache->load($classType->getName());
        if($this->annotations===null){
            $this->annotations = $classType->getAnnotations();
            $this->cache->save($classType->getName(), $this->annotations,[
                Cache::FILES => $classType->getFileName()
            ]);
        }
        $this->parent = $parent;
        $this->renderer();
        return;
    }

    /**
     * @return void
     */
    protected function renderer()
    {
        foreach ($this->renderers as $key=>$anchor) {
            if (isset($this->annotations[$anchor])) {
                $wrapper = $this->parent->getRendererWrappers();
                $wrapper[$key] = Helpers::merge((array) $this->annotations[$anchor][0], $wrapper[$key]);
                $this->parent->setRendererWrappers($wrapper);
            }
        }
        return;
    }

    /**
     * get annotation key
     * @param string $name
     * @param bool $multiple
     * @return mixed | null
     */
    public function get(string $name,bool $multiple=false)
    {
        if(array_key_exists($name, $this->annotations)){
            if($multiple==true){
                return (array) $this->annotations[$name];
            } else {
                return $this->annotations[$name][0];
            }
        } else {
            return null;
        }
    }
}
