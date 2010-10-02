<?php

class JsTestRunnerController extends JsTestsAppController
{
	var $name = 'JsTestRunner';
	var $uses = array();
	var $components = array('RequestHandler', 'TestHandler');

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

	function instrument()
	{
		if (!$this->RequestHandler->isPost())
		{
			$this->redirect($this->referer());
		}

		$profile = $this->data['profile'];
		$profileData = Configure::read(sprintf('JsTests.Profiles.%s', $profile));

		if (!$this->TestHandler->checkProfile($profileData))
		{
			$this->Session->setFlash('Instrumentation failed: profile not configured correctly!');
			$this->redirect($this->referer());
		}

		$output = $this->TestHandler->instrument($profileData);

		if ($output['exitCode'] != 0)
		{
			$this->Session->setFlash(sprintf('Instrumentation failed: JSCoverage returned a status of %s', $output['exitCode']));
			$this->Session->write('JSCoverage.output', serialize($output['output']));
			$this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash('Instrumentation successfull');
			$this->redirect($this->referer());
		}
	}
}
