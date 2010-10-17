<?php
/**
	CakePHP JsTests Plugin - JavaScript unit tests and code coverage

	Copyright (C) 2010-3827 dr. Hannibal Lecter / lecterror
	<http://lecterror.com/>

	Multi-licensed under:
		MPL <http://www.mozilla.org/MPL/MPL-1.1.html>
		LGPL <http://www.gnu.org/licenses/lgpl.html>
		GPL <http://www.gnu.org/licenses/gpl.html>
*/

App::import('Component', 'JsTests.TestHandler');
define('JS_TEST_PLUGIN_ROOT', App::pluginPath('JsTests'));
define('JS_TESTDATA', JS_TEST_PLUGIN_ROOT.'tests'.DS.'data'.DS);

if (!defined('CAKEPHP_UNIT_TEST_EXECUTION'))
{
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

Configure::write('JsTests.Profiles',
	array
	(
		'default' => array
		(
			'dir' => array
			(
				'normal_root'			=> JS_TESTDATA.'js'.DS,
				'normal_tests'			=> JS_TESTDATA.'js'.DS.'tests'.DS,
				'instrumented_root'		=> JS_TESTDATA.'js_instrumented'.DS,
				'instrumented_tests'	=> JS_TESTDATA.'js_instrumented'.DS.'tests'.DS
			),
			'url' => array
			(
				'normal_root'			=> 'js/',
				'normal_tests'			=> 'js/tests/',
				'instrumented_root'		=> 'js_instrumented/',
				'instrumented_tests'	=> 'js_instrumented/tests/',
			),
			'params' => array
			(
				'tests'		=> '*.test.html',
				'name'		=> '^(?P<name>[a-zA-Z_\-0-9]+).test.html$',
				'files'		=> array('%name%.test.js', '%name%.lib.js'),
			),
			'instrumentation' => array
			(
				'noInstrument'		=> array('tests'/*, 'jquery'*/),
				'exclude'			=> array('.svn'),
			),
		),
		'ze-empty' => array(),
		'invalid' => array('dir' => array('normal-root' => '')),
	)
);

class TestRunnerTestCase extends CakeTestCase
{
	function testLoadTests()
	{
		$component = new TestHandlerComponent();

		if (DIRECTORY_SEPARATOR != '\\' && function_exists('posix_getpwuid'))
		{
			$currentUser = exec('whoami');
			$fileowner = posix_getpwuid(fileowner(JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.test.html'));
			$fileowner = $fileowner['name'];

			$this->skipIf($currentUser != $fileowner, 'Test data files are not owned by Apache user ('.$currentUser.')');
		}

		@touch(JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.test.html', strtotime('-1 minute'));
		@touch(JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.test.js', strtotime('-1 minute'));
		@touch(JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.lib.js', strtotime('-1 minute'));
		@touch(JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.test.html', strtotime('+1 minute'));
		@touch(JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.test.js', strtotime('+1 minute'));
		@touch(JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.lib.js', strtotime('+1 minute'));
		@unlink(JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-other-library.test.html');

		$testData = Configure::read('JsTests.Profiles.default');
		$expected = array
			(
				'some-library' => array
				(
					'mainTestFile' => 'some-library.test.html',
					'normalTestPath' => JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.test.html',
					'instrumentedTestPath' => JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.test.html',
					'normalRelatedTestFiles' => array
					(
						JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.test.js',
						JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.lib.js'
					),
					'instrumentedRelatedTestFiles' => array
					(
						JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.test.js',
						JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.lib.js'
					),
					'instrumentedExists' => true,
					'instrumentedIsUpdated' => true,
					'normalTestUrl' => '/js/tests/some-library.test.html',
					'instrumentedTestUrl' => '/js_instrumented/jscoverage.html?u=/js_instrumented/tests/some-library.test.html',
				),
				'some-other-library' => array
				(
					'mainTestFile' => 'some-other-library.test.html',
					'normalTestPath' => JS_TESTDATA.'js'.DS.'tests'.DS.'some-other-library.test.html',
					'instrumentedTestPath' => JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-other-library.test.html',
					'normalRelatedTestFiles' => array(),
					'instrumentedRelatedTestFiles' => array(),
					'instrumentedExists' => false,
					'instrumentedIsUpdated' => false,
					'normalTestUrl' => '/js/tests/some-other-library.test.html',
					'instrumentedTestUrl' => '/js_instrumented/jscoverage.html?u=/js_instrumented/tests/some-other-library.test.html',
				)
			);

		$result = $component->loadTests('default', $testData);
		$diff = (Set::diff($result, $expected));

		$this->assertTrue(empty($diff));
	}

	function testCheckProfile()
	{
		$component = new TestHandlerComponent();

		$result = $component->checkProfile(Configure::read('JsTests.Profiles.default'));
		$this->assertTrue($result);

		$result = $component->checkProfile(Configure::read('JsTests.Profiles.ze-empty'));
		$this->assertFalse($result);

		// well this is rather silly.. why can't we say expectError(13)?
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$this->expectError();
		$result = $component->checkProfile(Configure::read('JsTests.Profiles.invalid'), true);
		$this->assertFalse($result);
	}

	function testInstrument()
	{
		$component = new TestHandlerComponent();

		Configure::write('JsTests.JSCoverage', array('executable' => '/usr/bin/notajscoverage'));

		$this->expectError();
		$result = $component->instrument(Configure::read('JsTests.Profiles.default'));

		$this->skipIf(!file_exists('/usr/bin/jscoverage'));
		Configure::write('JsTests.JSCoverage', array('executable' => '/usr/bin/jscoverage'));
		$result = $component->instrument(Configure::read('JsTests.Profiles.default'));
		$expected = array('output' => array(), 'exitCode' => null);

		$this->assertEqual($result, $expected);
	}
}
