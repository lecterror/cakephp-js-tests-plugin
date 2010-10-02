<?php
/*
	CakePHP JsTests Plugin - JavaScript unit tests and code coverage
	Copyright (C) 2010-3827 dr. Hannibal Lecter (http://lecterror.com/)

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
