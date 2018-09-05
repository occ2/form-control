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

namespace app\Base\controls\FormControl\events;

use app\Base\controls\Control\ControlEventData as BaseEvent;
use Nette\Forms\Container as Form;
use app\Base\controls\FormControl\FormControl;
use Nette\Utils\ArrayHash;
use Nette\Application\UI\Presenter;
use Nette\Application\InvalidPresenterException;

/**
 * form event data container
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class FormEvent extends BaseEvent
{

    /**
     * @var string | null
     */
    private $event;

    /**
     * @var \Nette\Forms\Container
     */
    private $form;

    /**
     * @var FormControl
     */
    protected $control;

    /**
     * @param Form $form
     * @param FormControl $control
     * @param string $event
     * @return void
     */
    public function __construct(Form $form, FormControl $control, string $event=null,$data=null)
    {
        $this->form = $form;
        $this->control = $control;
        parent::__construct($data, $control, $event);
        return;
    }

    /**
     * get values from form
     * @param bool $asArray
     * @return ArrayHash | array
     */
    public function getValues(bool $asArray=false)
    {
        return $this->form->getValues($asArray);
    }

    /**
     * get form
     * @param bool $fromControl if true return form from control if false return form from event
     * @return Form
     */
    public function getForm(bool $fromControl=true)
    {
        if($fromControl){
            return $this->control["form"];
        } else {
            return $this->form;
        }
    }

    /**
     * get whole control
     * @return FormControl
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * get presenter
     * @return Presenter
     */
    public function getPresenter()
    {
        $presenter=$this->control->getPresenter();
        if($presenter instanceof Presenter){
            return $presenter;
        } else {
            throw new InvalidPresenterException();
        }
    }

    /**
     * get event name
     * @return string | null
     */
    public function getEvent()
    {
        return $this->event;
    }
}
