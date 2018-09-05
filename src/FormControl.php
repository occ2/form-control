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

namespace occ2\FormControl;

use occ2\flashes\TFlashMessage;
use occ2\control\Control;
use occ2\FormControl\configurators\FormConfig;
use occ2\FormControl\builders\IFormBuilder;
use occ2\FormControl\builders\FormBuilder;
use occ2\FormControl\interfaces\IEventFactory;
use occ2\FormControl\factories\FormEventFactory;
use occ2\FormControl\interfaces\IFormFactory;
use Contributte\EventDispatcher\EventDispatcher;
use Contributte\Cache\ICacheFactory;
use Nette\Localization\ITranslator;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Form as NForm;
use Nette\Application\BadRequestException;
use Nette\Application\ApplicationException;
use Nette\Utils\ArrayHash;
use Nette\Forms\Controls\BaseControl;

/**
 * parent of autogenerated Forms
 *
 * @author Milan Onderka
 * @version 1.1.0
 */
abstract class FormControl extends Control
{
    use TFlashMessage;

    const FORM="form";
    const DEFAULT_ICON_PREFIX="fas fa-";
    const DEFAULT_TEMPLATE_PATH=__DIR__ . "/form.latte";

    /**
     * @var array
     */
    public static $defaultWrappers=[
        'form' => [
            'container' => null,
        ],
        'error' => [
            'container' => 'div class="row mb-3"',
            'item' => 'div class="col-12 text-danger"',
        ],
        'group' => [
            'container' => null,
            'label' => 'p class="h3 modal-header"',
            'description' => 'p class="pl-3 lead"',
    ],
    'controls' => [
            'container' => null,
        ],

        'pair' => [
            'container' => 'div class="form-group row"',
            '.required' => null,
            '.optional' => null,
            '.odd' => null,
            '.error' => null,
        ],
        'control' => [
            'container' => 'div class="col-lg-7 col-md-9 col-sm-12"',
            '.odd' => null,
            'description' => 'small class="form-text text-muted"',
            'requiredsuffix' => null,
            'errorcontainer' => 'div class="col-12 badge badge-danger"',
            'erroritem' => null,
            '.required' => null,
            '.text' => null,
            '.password' => null,
            '.file' => null,
            '.email' => null,
            '.number' => null,
            '.submit' => null,
            '.image' => null,
            '.button' => null,
        ],

        'label' => [
            'container' => 'div class="col-lg-5 col-md-3 text-md-right col-sm-12"',
            'suffix' => null,
            'requiredsuffix' => '*',
        ],

        'hidden' => [
            'container' => null,
        ],
    ];

    /**
     * @var null | array
     */
    public $onError;

    /**
     * @var null | array
     */
    public $onSubmit;

    /**
     * @var null | array
     */
    public $onSuccess;

    /**
     * @var null | array
     */
    public $onValidate;

    /**
     * @param IFormFactory $formFactory
     * @param EventDispatcher $eventDispatcher
     * @param ICacheFactory $cacheFactory
     * @param ITranslator $translator
     * @param string $formEventDataFactoryClass
     * @return void
     */
    public function __construct(IFormFactory $formFactory, EventDispatcher $eventDispatcher, ICacheFactory $cacheFactory,ITranslator $translator = null,$formEventDataFactoryClass=FormEventFactory::class)
    {
        // set base DI objects
        parent::__construct($eventDispatcher, $cacheFactory, $translator);
        // set default form settings
        $this->setIconPrefix(static::DEFAULT_ICON_PREFIX);
        $this->setRendererWrappers(self::$defaultWrappers);
        $this->setSimple(false);
        $this->setTemplatePath(static::DEFAULT_TEMPLATE_PATH);
        // set basic factories
        $this->c->eventDataFactory = new $formEventDataFactoryClass;
        $this->c->formFactory = $formFactory;
        // create annotation configurator
        $this->c->configurator = new FormConfig($this);
        // set links
        $this->setLinks($this->getConfigurator()->get("links", true));
    }

    /**
     * enable access to form direct thru $control->form
     * @param string $name
     * @return $this
     */
    public function &__get($name)
    {
        if ($name==static::FORM) {
            return $this[$name];
        }
        return parent::__get($name);
    }

    /**
     * alias for get column
     * @param string $name
     * @return BaseControl
     * @deprecated
     */
    public function getItem($name)
    {
        return $this->getColumn($name);
    }

    /**
     * enable direct access to form elements
     * @param string $name
     * @return BaseControl
     */
    public function getColumn($name)
    {
        $i = $this[static::FORM][$name];
        if($i instanceof BaseControl){
            return $i;
        } else {
            throw new ApplicationException();
        }
    }

    /**
     * set form AJAX
     * @param bool $ajax
     * @return $this
     */
    public function setAjax(bool $ajax=true)
    {
        $this->c->ajax = $ajax;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAjax(){
        return isset($this->c->ajax) ? $this->c->ajax : true;
    }

    /**
     * overide form title (usable by ajax)
     * @param string $text
     * @return $this
     */
    public function setTitle(string $text)
    {
        $this->c->title = $this->_($text);
        return $this;
    }

    /**
     * overide form comment (usable by ajax)
     * @param string $text
     * @return $this
     */
    public function setComment(string $text)
    {
        $this->c->comment = $this->_($text);
        return $this;
    }

    /**
     * overide form footer (usable by ajax)
     * @param string $text
     * @return $this
     */
    public function setFooter(string $text)
    {
        $this->c->footer = $this->_($text);
        return $this;
    }

    /**
     * @param ArrayHash $styles
     * @return $this
     */
    public function setStyles(ArrayHash $styles)
    {
        $this->c->styles = $styles;
        return $this;
    }

    /**
     * @return ArrayHash | null
     */
    public function getStyles()
    {
        if(isset($this->c->styles)){
            return $this->c->styles;
        } else {
            return $this->getConfigurator()->get("styles");
        }
    }

    /**
     * get control title (if set)
     * @return string | null
     */
    public function getTitle()
    {
        if (isset($this->c->title) && !empty($this->c->title)) {
            return $this->c->title;
        } else {
            return $this->getConfigurator()->get("title");
        }
    }

    /**
     * get control comment (if set)
     * @return string | null
     */
    public function getComment()
    {
        if (isset($this->c->comment) && !empty($this->c->comment)) {
            return $this->c->comment;
        } else {
            return $this->getConfigurator()->get("comment");
        }
    }

    /**
     * get control footer (if set)
     * @return string | null
     */
    public function getFooter()
    {
        if (isset($this->c->footer) && !empty($this->c->footer)) {
            return $this->c->footer;
        } else {
            return $this->getConfigurator()->get("footer");
        }
    }

    /**
     * disable annotation builder
     * @return $this
     */
    public function disableBuilder()
    {
        $this->c->disableBuilder = true;
        return $this;
    }

    /**
     * is builder enabled?
     * @return boolean
     */
    public function isBuilderEnabled()
    {
        if(isset($this->c->disableBuilder) && $this->c->disableBuilder==true){
            return false;
        } else {
            return true;
        }
    }

    /**
     * set load options callback
     * @param string $column
     * @param callable $callback
     */
    public function setLoadOptionsCallback(string $column, callable $callback)
    {
        $this->c->loadOptionsCallback[$column] = $callback;
        return $this;
    }

    /**
     * @return callable | null
     */
    public function getLoadOptionsCallback()
    {
        return isset($this->c->loadOptionsCallback) ? $this->c->loadOptionsCallback : null;
    }

    /**
     * set load values callback
     * @param callable $callback
     * @return $this
     */
    public function setLoadValuesCallback(callable $callback)
    {
        $this->c->loadValuesCallback = $callback;
        return $this;
    }

    /**
     * @return callable
     */
    public function getLoadValuesCallback()
    {
        return isset($this->c->loadValuesCallback) ? $this->c->loadValuesCallback : null;
    }

    /**
     * form factory
     * @return NForm
     */
    public function createComponentForm(): NForm
    {
        // set ajax from configuration
        $this->setAjax($this->getConfigurator()->get("ajax"));
        // create form
        $form = $this->getFormFactory()->create();
        // set renderer wrappers
        if(isset($form->getRenderer()->wrappers)){
            $form->getRenderer()->wrappers = $this->getRendererWrappers();
        }
        // set form ajax
        if ($this->getAjax()) {
            $form->getElementPrototype()->class('ajax');
        }
        // setup form settings
        $this->setupForm($form);
        // setup form events
        $this->setupEvents($form);
        return $form;
    }

    /**
     * setup form events
     * @param NForm $form
     * @return void
     */
    protected function setupEvents(NForm $form)
    {
        $this->setupOnError($form);
        $this->setupOnValidate($form);
        $this->setupOnSubmit($form);
        $this->setupOnSuccess($form);
        return;
    }

    /**
     * setup form error events
     * @param NForm $form
     * @return void;
     */
    protected function setupOnError(NForm $form)
    {
        $t = $this;
        // try to find event from annotations
        $event = $this->getConfigurator()->get("onError");
        // if found set event to be fired by Symfony event dispatcher
        if ($event!==null) {
            $form->onError[] = function (NForm $form) use ($t,$event) {
                $data = $t->getEventDataFactory()->create($form,$t,$event);
                $t->on($event, $data);
                return;
            };
        } else {
            // if not try to find control event and if found set it into form
            if(!empty($this->onError)){
                $form->onError = $this->onError;
            }
        }
        return;
    }

    /**
     * setup form on validate events
     * @param NForm $form
     * @return void
     */
    protected function setupOnValidate(NForm $form)
    {
        $t = $this;
        // try to find event from annotations
        $event = $this->getConfigurator()->get("onValidate");
        // if found set event to be fired by Symfony event dispatcher
        if ($event!==null) {
            $form->onValidate[] = function (NForm $form) use ($t,$event) {
                $data = $t->getEventDataFactory()->create($form,$t,$event);
                return $t->on($event, $data);
            };
        } else {
            // if not try to find control event and if found set it into form
            if(!empty($this->onValidate)){
                $form->onValidate = $this->onValidate;
            }
        }
        return;
    }

    /**
     * setup form on submit events
     * @param NForm $form
     * @return void
     */
    protected function setupOnSubmit(NForm $form)
    {
        $t = $this;
        // try to find event from annotations
        $event = $this->getConfigurator()->get("onSubmit");
        // if found set event to be fired by Symfony event dispatcher
        if ($event!==null) {
            $form->onSubmit[] = function (NForm $form) use ($t,$event) {
                $data = $t->getEventDataFactory()->create($form,$t,$event);
                return $t->on($event, $data);
            };
        } else {
            // if not try to find control event and if found set it into form
            if(!empty($this->onSubmit)){
                $form->onSubmit = $this->onSubmit;
            }
        }
        return;
    }

    /**
     * setup form on success events
     * @param NForm $form
     * @return void
     */
    protected function setupOnSuccess(NForm $form)
    {
        $t = $this;
        // try to find event from annotations
        $event = $this->getConfigurator()->get("onSuccess");
        // if found set event to be fired by Symfony event dispatcher
        if ($event!=null) {
            $form->onSuccess[] = function (NForm $form) use ($t,$event) {
                $data = $t->getEventDataFactory()->create($form,$t,$event);
                return $t->on($event, $data);
            };
        } else {
            // if not try to find control event and if found set it into form
            if(!empty($this->onSuccess)){
                $form->onSuccess = $this->onSuccess;
            }
        }
        return;
    }

    /**
     * overwrite by final classes
     * @param NForm $form
     * @return NForm
     */
    public function setupForm(NForm $form):NForm
    {
        // if some builder set use this
        if ($this->getBuilder() instanceof IFormBuilder) {
            $form = $this->getBuilder()
                         ->setObject($this)
                         ->setTranslator($this->getTranslator())
                         ->setOptionsCallbacks($this->getLoadOptionsCallback())
                         ->build($form);
        // if not check if builder enabled and if true create new instance
        } elseif ($this->isBuilderEnabled()) {
            $this->setBuilder(new FormBuilder);
            $form = $this->getBuilder()
                         ->setObject($this)
                         ->setTranslator($this->getTranslator())
                         ->setOptionsCallbacks($this->getLoadOptionsCallback())
                         ->build($form);
        // if not skip builders
        } else {}
        return $form;
    }

    /**
     * render control
     * @return void
     */
    public function render()
    {
        $this->template->styles = $this->getStyles();
        $this->template->title = $this->getTitle();
        $this->template->comment = $this->getComment();
        $this->template->footer = $this->getFooter();
        $this->template->links = $this->getLinks();
        $this->template->name = $this->getName();
        $this->template->simple = $this->getSimple();
        if($this->template instanceof ITemplate){
            $this->template->setFile($this->getTemplatePath());
            $this->template->render();
        }
        return;
    }

    /**
     * load values into form
     * @param mixed $id
     * @throws \Exception
     */
    public function loadValues($id)
    {
        if (!is_callable($this->getLoadValuesCallback())) {
            throw new \BadFunctionCallException;
        }
        try {
            $this->setValues(call_user_func_array($this->getLoadValuesCallback(), [$id]));
            if (!$this->getValues()) {
                throw new BadRequestException;
            }
            $this[static::FORM]->setDefaults($this->getValues());
        } catch (\Exception $exc) {
            throw $exc;
        }
        return $this;
    }

    /**
     * set default values
     * @param ArrayHash | array $values
     * @return $this
     */
    public function setDefaults($values)
    {
        $this[static::FORM]->setDefaults($values);
        return $this;
    }

    /**
     * clear values
     * @return $this
     */
    public function clearValues()
    {
        $this[static::FORM]->reset();
        return $this;
    }

    /**
     * load option
     * @param string $option
     * @return array
     */
    protected function loadOptions(string $option):array
    {
        return (array) call_user_func($this->getLoadOptionsCallback()[$option]);
    }

    /**
     * set form builder
     * @param IFormBuilder $builder
     * @return $this
     */
    public function setBuilder(IFormBuilder $builder)
    {
        $this->c->builder = $builder;
        return $this;
    }

    /**
     * get form builder
     * @return IFormBuilder | null
     */
    public function getBuilder()
    {
        return isset($this->c->builder) ? $this->c->builder : null;
    }

    /**
     * fire error to element
     * @param string $element
     * @param string $message
     * @return void
     */
    public function throwError(string $element, string $message)
    {
        if($this[static::FORM][$element] instanceof BaseControl){
            $this[static::FORM][$element]->addError($message);
            $this->reload();
        }

        return;
    }

    /**
     * reload form
     * @return void
     */
    public function reload()
    {
        $this->redrawControl(static::FORM);
        return;
    }

    /**
     * @param string $iconPrefix
     * @return $this
     */
    public function setIconPrefix($iconPrefix)
    {
        $this->c->iconPrefix = $iconPrefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getIconPrefix()
    {
        return $this->c->iconPrefix;
    }

    /**
     *  @return boolaen
     */
    public function getSimple()
    {
        return $this->c->simple;
    }

    /**
     * set form without Bootstrap Card
     * @param bool $simple
     * @return $this
     */
    public function setSimple($simple=true)
    {
        $this->c->simple = $simple;
        return $this;
    }

    /**
     * @return IFormFactory
     */
    public function getFormFactory()
    {
        return $this->c->formFactory;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return isset($this->c->links) && !empty($this->c->links) ? $this->c->links : [];
    }

    /**
     * @param array | null $links
     * @return $this
     */
    public function setLinks(?array $links)
    {
        $this->c->links = $links;
        return $this;
    }

    /**
     * @return IEventFactory
     */
    public function getEventDataFactory()
    {
        return $this->c->eventDataFactory;
    }

    /**
     * @param array $wrappers
     * @return $this
     */
    public function setRendererWrappers(array $wrappers)
    {
        $this->c->rendererWrappers = $wrappers;
        return $this;
    }

    /**
     * @return array
     */
    public function getRendererWrappers()
    {
        return isset($this->c->rendererWrappers) ? $this->c->rendererWrappers : [];
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setTemplatePath(string $path)
    {
        $this->c->templatePath = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->c->templatePath;
    }

    /**
     * set form name
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param array | null $values
     * @return $this
     */
    public function setValues(?array $values)
    {
        $this->c->values = $values;
        return $this;
    }

    /**
     * @return array | null
     */
    public function getValues()
    {
        return isset($this->c->values) ? $this->c->values : null;
    }

    /**
     * @param IFormBuilder $builder
     * @return $this
     */
    public function setFormBuilder(IFormBuilder $builder)
    {
        $this->c->formBuilder = $builder;
        return $this;
    }

    /**
     * @return IFormBuilder | null
     */
    public function getFormBuilder()
    {
        return isset($this->c->formBuilder) ? $this->c->formBuilder : null;
    }
}
