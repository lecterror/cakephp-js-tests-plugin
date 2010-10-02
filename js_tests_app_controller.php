<?php

App::import('Lib', 'JsTests.CoreConfig');
App::import('Core', 'Set');

class JsTestsAppController extends AppController
{
	var $activeProfileName = null;
	var $activeProfileData = null;

	function beforeFilter()
	{
		parent::beforeFilter();

		if (isset($this->passedArgs['profile']))
		{
			$exists = Set::check(Configure::read('JsTests.Profiles'), sprintf('%s', $this->passedArgs['profile']));

			if (!$exists)
			{
				trigger_error(sprintf('Profile "%s" does not exist!', $this->passedArgs['profile']));
			}
			else
			{
				Configure::write('JsTests.ActiveProfile', $this->passedArgs['profile']);
			}
		}

		$this->activeProfileName = Configure::read('JsTests.ActiveProfile');
		$this->activeProfileData = Configure::read(sprintf('JsTests.Profiles.%s', $this->activeProfileName));

		if (empty($this->activeProfileData))
		{
			trigger_error('Error: JsTests profile not properly configured!');
			return;
		}
	}
}
