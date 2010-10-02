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
			$tests = $this->TestHandler->loadTests($this->activeProfile, $this->activeProfileData);
		}

		$this->set(compact('activeProfileData', 'tests'));
	}
}
