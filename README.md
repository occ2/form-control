# FormControl
## 1. Introduction
Form control is pretty nice and easy way to create, setup and use Forms in Nette applications. Form is encapsulated into Bootstrap4 card and use Bootstrap4 layout and Font Awesome 5 icons. It use annotation based settings of form and its controls.
#### Installation
_composer require occ2/form-control_

Form control has some important dependecies.   

_contributte/event-dispatcher_ - required
_contributte/recaptcha_ - required
_kdyby/translation_ - optional
_aleswita/formrenderer_ - required
and whole Nette framework.   

All text that are used are untranslated anchors. If you dont want to use translation you may use simple texts
## 2. Creating FORM
#### a. Create your form class - for example in MyForm.php
	<?php
	namespace MyApp\Controls;
	use occ2\form-control\FormControl;

	/**
	  *	@ajax
	  *	@title app.myForm.title
	  *	@comment app.myForm.comment
	  *	@styles (headerBackground="light",headerText="dark",size="w-100")
	  */
	final class MyForm extends FormControl{
		/**
		 *	@type hidden
		 */
		public $id;

		/**
		  * @leftAddon app.myForm.username
		  * @rightIcon user
		  * @type text
		  * @cols 20
		  * @validator (type=':filled',message='user.error.requiredUsername')
		  * @validator (type=':minLength',message='app.myForm.error.minLengthUsername',value=4)
		  * @description app.myForm.usernameDescription
		  */
		public $username;

		/**
		  * @leftAddon app.myForm.password
		  * @rightIcon key
		  * @type password
		  * @cols 20
		  * @validator (type=':filled',message='user.error.requiredPassword')
		  * @validator (type=':minLength',message='app.myForm.error.minLengthPassword',value=8)
		  * @validator (type=':pattern',message='user.error.patternPassword',value='.*(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*')
		  * @description app.myForm.passwordDescription
		  */
		public $password;

		/**
		  * @type recaptcha
		  */
		public $recaptcha;

		/**
		  * @label app.myForm.submit
		  * @type submit
		  */
		public $submit;
	}
#### b. Create control factory interface for example in IMyForm.php
	<?php
	namespace MyApp\Controls;

	interface IMyForm{
		/**
		  *  @return \MyApp\Controls\MyForm
		  */
		public function create();
	}
#### c. Register factory of Form and Renderer in your config.neon
	services:
		formFactory:	\app\Base\controls\FormControl\factories\FormFactory(@Nette\Localization\ITranslator)
		-	\MyApp\Controls\IMyForm

#### d. Now you can use factory in your presenter -  for example in UserPresenter.php
	<?php
	namespace MyApp\Presenters;
	use Nette\Application\UI\Presenter as NPresenter,
		Nette\Application\UI\Form as NForm;

	final class UserPresenter extends NPresenter{
		/**
		  *	@inject @var \MyApp\Controls\IMyForm
		  */
		public $myFormFactory;

		/**
		  * @return  \MyApp\Controls\MyForm
		  */
		public function createComponentMyForm{
			$t = $this;
			$f = $this->myFormFactory->create();
			$f->onSuccess[] = function(NForm $form)use($t){
				$values = $form->getValues();
				......
				if($t->isAjax()){
					$t["myForm"]->flashMessage("Success..");
					$t["myForm"]->reload();
				}
				else{
					$t->redirect("this");
				}
			};
			return $f;
		}
	}
#### e. Add your form into template - for example default.latte
	{block content}
	{control myForm}
## 3. Setup FORM structure
Structure of form is set by properties. One form control = one property (property name =control name)).  All settings, and layout of form can be set by class annotations. All settings and layout of cntrols are set by property annotations. Be carefull that some property names are reserved ($name, $parent,$presenter, $params, $snippetMode, $linkCurrent, $template and all that begins by $_) and cannot be used as control name.
#### a. FORM settings and layout
These are set by annotation of form class. Here is list of them..   
	_@ajax - AJAXify form (set form class ajax)_  
	_@title - set form card heading (in Bootstrap4 card) - translatable text_  
	_@comment - set form comment (placed between form controls and header)- translatable text_  
	_@footer - set form footer (placed under form controls)- translatable text_  
	_@styles - array of form Bootstrap card CSS classes_  
	_@rControl - array of control renderer wrappers_  
	_@rForm - array of form renderer wrappers_  
	_@rError - array of error renderer wrappers_  
	_@rGroup - array of group renderer wrappers_  
	_@rControls - array of controls renderer wrappers_  
	_@rPair - array of pair renderer wrappers_  
	_@rLabel - array of label renderer wrappers_  
	_@rHidden - array of hidden renderer wrappers_  
	_@links- add link to footer (array contains link, class and text)_  
	_@onSubmit - Symfony event fired while click on submit button_  
	_@onError - Symfony event fired while error add_  
  _@onValidate - Symfony event fired while validation needed_  
	_@onSuccess - Symfony event fired while valid form send_

#### b. CONTROLs settings and layout
These are set by annotations of form properties. Property name is set as control name. Here is list of annotation settings.   
	_@type - set type of control
	Avalilable type are hidden, text email, number, password, textarea, select, multiselect, checkbox, checkboxlist, radiolist, upload, multiupload, submit, recaptcha_  
	_@label - set control label (translatable)_  
	_@cols - set number of cols in control - available for TextInput and TextArea_  
	_@rows - set number of rows - available for TextArea_  
	_@description - set translatable control description (small text under contol)_  
	_@leftAddon - set left addon translatable text in control_  
	_@leftIcon - set left addon icon in control - conflicts with leftAddon_  
	_@rightAddon - set right addon translatable text in control_  
	_@rightIcon - set right addon icon in control - conflicts with rightAddon_	   
	_@placeholder - set translatable control placeholder_  
	_@maxlength - set maximal length of control text_  
	_@caption - set translatable caption text to checkbox_  
	_@multiple - set multiple uploader_  
	_@required - set required for recaptcha_  
	_@message . set message for recaptcha_  
	_@validator - set control validator (array with type, message and value) - multiple validators supported_  

* Notice: Conditions not yet supported. If you need that you must add conditions manually

#### c. CONTROLs options and values
Select, Multiselect, CheckboxList and Radiolist controls need filling with options. Easiest way is throw callbacks. FormControl has method _setLoadOptionsCallback_
Example:

	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->setLoadOptionsCallback('someSelect', function()use($t){
			return (array) $t->someModel->findAll()->fetchPairs();
		});

		...
		return $f;
	}
	...'

## 5. FORM processing
There are two ways how to process form.
#### a. Use standard Nette Form events
onSubmit, onSuccess, onError, onValidate
Example 1 - using callback :

	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->onSuccess[] = function(NForm $form)use($t){
			$values = $form->getValues();
			$t->model->save($values);
			if($t->isAjax(){
				$t["myForm]->flashMessage("Saved !!");
				$t["myForm]->reload();
			}
			else{
				$t->redirect("this");
			}
		};
		$f->onError[] = function (NForm $form) use( $t){
			...
		};
		...
		return $f;
	}
	...'

Example 2 - using method:

	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->onSuccess[] = [$this,"processMyForm"];
		...
		return $f;
	}

	public function processMyForm(NForm $form){
		$values = $form->getValues();
		$t->model->save($values);
		if($t->isAjax(){
			$t["myForm]->flashMessage("Saved !!");
			$t["myForm]->reload();
		}
		else{
			$t->redirect("this");
		}
	}
	...'

#### b. Use Symfony Events
If you want to use Symfony events on Form you have two options how to do it. First one is siplier by adding @onSuccess (onError, onSubmit, onValidate) annotation on Form class where value is event name registered in subscriber and data container is instance of FormEventData class.

	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		... // dont use onSuccess, onSubmit etc.
		return $f;
	}

form specification
	<?php
	namespace app\Controls\MyControl;

	@onSubmit MyForm.submit
	@onError MyForm.error
	final class MyForm extends FormControl{

	}

and subscriber (for example MyFormSubscriber.php))..

	<?php
	namespace MyApp\Events;
	use Contributte\EventDispatcher\EventSubscriber;

	final class MyFormSubscriber implements EventSubscriber{

		protected $model;

		public static function getSubscribedEvents(){
			return [
				"MyForm.submit"=>"onSubmit"
				"MyForm.error"=>"onError"
				...
			];
		}

		public function onSubmit(FormEventData $event){
			$values = $event->form->getValues();
			// do something with data
			$this->model->save($values);
			if($event->presenter->isAjax()){
				$event->presenter["myForm"]->flashMessage("Saved");
				$event->presenter["myForm"]->reload();
			}
			else{
				$event->presenter->redirect("this");
			}
		}

		public function onError(FormEventData $event){
			.... // do something else
		}
	}

Second way is use setEvent(array $events)
method, where _$event_ sis array of events
...
public function createComponentMyForm(){
	$t = $this;
	$f = $this->myFormFactory->create();
	$f->setEvents([
		"success"=>"MyForm.success"
		]);
	... // dont use onSuccess, onSubmit etc.
	return $f;
}


## 6. Set form values
There are two ways to fill form controls with values. First one by _setDefaults_ method, second one by predefined callback
#### a. Use _setDefaults()_
Easiest way if use setDefaults($values).
Example:

 	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->onSuccess[] = function(NForm $form)use($t){
			$values = $form->getValues();
			$t->model->save($values);
			if($t->isAjax(){
				$t["myForm]->flashMessage("Saved !!");
				$t["myForm]->reload();
			}
			else{
				$t->redirect("this");
			}
		};
		$f->onError[] = function (NForm $form) use( $t){
			...
		};
		...
		return $f;
	}
	...

	public function handleFillInForm($id){
		$data = (array) $this->model->get($id); // data must be array
		$this["myForm"]->setDefaults($data);
		$this["myForm"]->reload();
		return;
	}

 b. Use callback
 You can use callback that loads data and fill in form with then. Setup callback in factory and call in handler
 Example:

  	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->setLoadValuesCallback(function($id) use($t){
			return (array) $t->model->get($id);
		});
		$f->onSuccess[] = function(NForm $form)use($t){
			$values = $form->getValues();
			$t->model->save($values);
			if($t->isAjax(){
				$t["myForm]->flashMessage("Saved !!");
				$t["myForm]->reload();
			}
			else{
				$t->redirect("this");
			}
		};
		return $f;
	}

 	...

	public function handleFillInForm($id){
		$this["myForm"]->laodValues($id));
		$this["myForm"]->reload();
		return;
	}

__!! IMPORTANT !! Be careful that loadValues() and setDefaults() must be call in handle or action method of presenter not in createComponent factory__

## 7. Tips and tricks
#### a. How to access to form container?
You can find container in _form_ property of FormControl
Example:

  	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$container =  $f->form;
		$container->addText(...);
		...
		return $f;
	}

#### b. How to access to form controls?
You can use _form["controlName"]_ or simplier  getItem("controlName") method
Example:

  	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$control =  $f->form["controlName"];
		// or
		$control = $f->getItem("controleName");
		return $f;
	}

#### c. How to disable form annotation builder?
You can use disableBuilder() method in factory.
Example:

  	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->disableBuilder();
		$container =  $f->form;
		$container->addText(...);
		...
		return $f;
	}

#### d. How to override form text in handlers and actions
You can override form title, comment and footer in handlers and actions without changing annotations in form class
Example:

 	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->onSuccess[] = function(NForm $form)use($t){
			$values = $form->getValues();
			$t->model->save($values);
			if($t->isAjax(){
				$t["myForm]->flashMessage("Saved !!");
				$t["myForm]->reload();
			}
			else{
				$t->redirect("this");
			}
		};
		...
		return $f;
	}
	...

	public function handleChangeTexts(){
		$this["myForm"]->setTitle("newTitle"));
		$this["myForm"]->setComment("newComment"));
		$this["myForm"]->setFooter("newFooter"));
		$this["myForm"]->reload();
		return;
	}

#### e. How to clear values in filled form?
You can use clearValues() method
 Example:

  	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->setLoadValuesCallback(function($id) use($t){
			return (array) $t->model->get($id);
		});
		$f->onSuccess[] = function(NForm $form)use($t){
			$values = $form->getValues();
			$t->model->save($values);
			if($t->isAjax(){
				$t["myForm]->flashMessage("Saved !!");
				$t["myForm]->reload();
			}
			else{
				$t->redirect("this");
			}
		};
		return $f;
	}

 	...

	public function handleFillInForm($id){
		$this["myForm"]->laodValues($id));
		$this["myForm"]->reload();
		return;
	}

	public function handleResetForm(){
		$this["myForm"]->clearValues();
		$this["myForm"]->reload();
		return;
	}

#### f. Do you want to use your own builder? No problem..
Your builder must implement IFormBuilder interface

Example:

  	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->setBuilder(new MyOwnBuilder);
		...
		return $f;
	}


#### g. Is easy way to add error to control?
Yes. You can use error() method
 Example:

  	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();

		$f->onSuccess[] = function(NForm $form)use($t){

			if(..something){
				$t["myForm]->error("someControl","Error message");
			}
			else{
				$t["myForm"]->flashMessage("Saved !!");
			}
			$t["myForm]->flashMessage("Saved !!");
			if($t->isAjax(){
				$t["myForm]->reload();
			}
			else{
				$t->redirect("this");
			}
		};
		return $f;
	}

#### h. Can I use my own latte template instead of predefined bootstrap template?
Yes. You can use yout own template and override default settings by setTemplate() method
Example:

  	...
	public function createComponentMyForm(){
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->setTemplate("myTemplate.latte");
		...
		return $f;
	}


And file myTemplate.latte
Example:


	{snippet form}
		{snippetArea flashes}
			{include '../../templates/flashes.latte'}
		{/snippetArea}
		<form n:name=myForm class=form>
			<p><label n:name=user>Username: <input n:name=user size=20></label>
			<p><label n:name=password>Password: <input n:name=password></label>
			<p><input n:name=send class="btn btn-default">
		</form>   
	{/snippet}

#### i. Can I use another iconset then Fon Awesome 5?
Yes you can override _iconPrefix static property
Example:

  	...
	public function createComponentMyForm(){
		MyForm::$_iconPrefix = "fa fa-";
		$t = $this;
		$f = $this->myFormFactory->create();
		$f->setTemplate("myTemplate.latte");
		...
		return $f;
	}
