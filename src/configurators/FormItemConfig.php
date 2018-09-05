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

namespace app\Base\controls\FormControl\configurators;

use app\Base\controls\Control\IConfigurator;
use app\Base\controls\FormControl\exceptions\FormBuilderException;
use app\Base\controls\FormControl\FormControl;
use Nette\Caching\Cache;
use Nette\Reflection\ClassType;
use Nette\Reflection\Property;

/**
 * FormItemConfig
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.1.0
 */
class FormItemConfig implements IConfigurator
{
    const CONFIG_ITEMS=[
        "label",
        "type",
        "cols",
        "maxlength",
        "rows",
        "validator",
        "size",
        "caption",
        "multiple",
        "leftAddon",
        "rightAddon",
        "placeholder",
        "description",
        "required",
        "message",
        "leftIcon",
        "rightIcon"
    ];

    const CACHE_PREFIX="forms";

    /**
     * @var array
     */
    public $config=[
        "label"=>"",
        "type"=>"text",
        "cols"=>10,
        "maxlength"=>255,
        "rows"=>5,
        "size"=>1,
        "validator"=>[],
        "caption"=>null,
        "multiple"=>null,
        "leftAddon"=>null,
        "rightAddon"=>null,
        "placeholder"=>null,
        "description"=>null,
        "required"=>null,
        "message"=>null,
        "leftIcon"=>null,
        "rightIcon"=>null
    ];

    /**
     * @var Property
     */
    protected $property;

    /**
     * @var array
     */
    protected $annotations;

    /**
     * @var string
     */
    public $name;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param Property $property
     * @param FormControl $parent
     * @return void
     */
    public function __construct(Property $property, FormControl $parent)
    {
        $this->property = $property;
        $this->name = $property->getName();
        $this->cache = $parent->getCacheFactory()->create(static::CACHE_PREFIX);

        $classType = ClassType::from($parent);
        
        $this->annotations = $this->cache->load($classType->getName() . "." . $this->name);
        if($this->annotations===null){
            $this->annotations = $property->getAnnotations();
            $this->cache->save($classType->getName() . "." . $this->name, $this->annotations,[
                Cache::FILES => $classType->getFileName()
            ]);
        }
        return;
    }

    /**
     * @param string $name
     * @param bool $multiple
     * @return array
     * @throws FormBuilderException
     */
    public function get(string $name,bool $multiple=false)
    {
        if($name=="name"){
            return $this->name;
        }
        if (!in_array($name, static::CONFIG_ITEMS)) {
            throw new FormBuilderException("Error: invalid config item", FormBuilderException::INVALID_CONFIG_ITEM);
        }
        if (!isset($this->annotations[$name])) {
            return isset($this->config[$name]) ? $this->config[$name] : null;
        } else {
            return $multiple==true ? $this->annotations[$name] :  $this->annotations[$name][0];
        }
    }
}
