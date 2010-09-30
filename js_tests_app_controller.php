<?php

App::import('Lib', 'JsTests.CoreConfig');
App::import('Core', 'Set');

class JsTestsAppController extends AppController
{
	var $activeProfile = null;
	var $activeProfileData = null;

	function beforeFilter()
	{
		parent::beforeFilter();

		$this->activeProfile = Configure::read('JsTests.ActiveProfile');
		$this->activeProfileData = Configure::read(sprintf('JsTests.Profiles.%s', $this->activeProfile));

		if (empty($this->activeProfileData))
		{
			trigger_error('Error: JsTests profile not properly configured!');
			return;
		}
	}
}
