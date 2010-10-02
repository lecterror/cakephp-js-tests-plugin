<?php

class JsTestRunnerController extends JsTestsAppController
{
	var $name = 'JsTestRunner';
	var $uses = array();
	var $components = array('TestHandler');

	function index()
	{
		$passed = $this->TestHandler->checkProfile($this->activeProfileData);

		if (!$passed)
		{
			$tests = array();
		}
		else
		{
			$tests = $this->TestHandler->loadTests($this->activeProfileName, $this->activeProfileData);
		}

		$this->set('activeProfileName', $this->activeProfileName);
		$this->set('activeProfileData', $this->activeProfileData);
		$this->set(compact('tests'));
	}
}
