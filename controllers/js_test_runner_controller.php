<?php

class JsTestRunnerController extends JsTestsAppController
{
	var $name = 'JsTestRunner';
	var $uses = array();
	var $components = array('TestHandler');

	function index()
	{
		$tests = $this->TestHandler->loadTests($this->activeProfile, $this->activeProfileData);
		debug($tests);

		$passed = $this->TestHandler->checkProfile(Configure::read('JsTests.Profiles.default'));
		debug($passed);
		$passed = $this->TestHandler->checkProfile(Configure::read('JsTests.Profiles.invalid'));
		debug($passed);

		$this->set('activeProfileData', $this->activeProfileData);
	}
}
