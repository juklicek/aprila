<?php
/**
 * @author Honza Cerny (http://honzacerny.com)
 */

namespace Aprila;

use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{

    private \Nette\DI\Container $context;

    public function injectContext(\Nette\DI\Container $context)
    {
        $this->context = $context;
    }

    public function getContext(): \Nette\DI\Container
    {
        return $this->context;
    }

	protected function beforeRender()
	{
		parent::beforeRender();

		// TODO add excaption when paramets[site] not exists
		$this->template->production = !$this->context->parameters['site']['develMode'];
		$this->template->version = $this->context->parameters['site']['version'];

		// TODO remove (it's from nette examples)
		$this->template->viewName = $this->view;
		$this->template->root = isset($_SERVER['SCRIPT_FILENAME']) ? @realpath(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) : NULL;

		$a = strrpos($this->name, ':');
		if ($a === FALSE) {
			$this->template->moduleName = '';
			$this->template->presenterName = $this->name;
		} else {
			$this->template->moduleName = substr($this->name, 0, $a + 1);
			$this->template->presenterName = substr($this->name, $a + 1);
		}
	}

}