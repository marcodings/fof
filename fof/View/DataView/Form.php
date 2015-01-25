<?php
/**
 * @package     FOF
 * @copyright   2010-2015 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\View\DataView;

use FOF30\Form\Form as FormObject;
use FOF30\Model\DataModel;
use FOF30\Render\RenderInterface;
use FOF30\View\Exception\AccessForbidden;

defined('_JEXEC') or die;

class Form extends Html implements DataViewInterface
{
	/** @var  FormObject  The form to render */
	protected $form;

	/**
	 * Displays the view
	 *
	 * @param   string  $tpl  The template to use
	 *
	 * @return  boolean|null False if we can't render anything
	 *
	 * @throws  \Exception
	 */
	public function display($tpl = null)
	{
		/** @var DataModel $model */
		$model = $this->getModel();

		// Get the form
		$this->form = $model->getForm();
		$this->form->setModel($model);
		$this->form->setView($this);

		$eventName = 'onBefore' . ucfirst($this->doTask);
		$result = $this->triggerEvent($eventName);

		if (!$result)
		{
			throw new AccessForbidden;
		}

		try
		{
			$templateResult = $this->loadTemplate($tpl);
		}
		catch (\Exception $e)
		{
			$templateResult = $this->getRenderedForm();
		}

		$eventName = 'onAfter' . ucfirst($this->doTask);
		$result = $this->triggerEvent($eventName);

		if (!$result)
		{
			throw new AccessForbidden;
		}

		if (is_object($templateResult) && ($templateResult instanceof \Exception))
		{
			throw $templateResult;
		}
		else
		{
			if ($this->doPreRender)
			{
				$this->preRender();
			}

			echo $templateResult;

			if ($this->doPostRender)
			{
				$this->postRender();
			}

			return true;
		}
	}

	/**
	 * Returns the HTML rendering of the F0FForm attached to this view. Very
	 * useful for customising a form page without having to meticulously hand-
	 * code the entire form.
	 *
	 * @return  string  The HTML of the rendered form
	 */
	public function getRenderedForm()
	{
		$html = '';
		$renderer = $this->container->renderer;

		if ($renderer instanceof RenderInterface)
		{
			// Load CSS and Javascript files defined in the form
			$this->form->loadCSSFiles();
			$this->form->loadJSFiles();

			/** @var  DataModel  $model */
			$model = $this->getModel();

			// Get the form's HTML
			$html = $renderer->renderForm($this->form, $model);
		}

		return $html;
	}
} 