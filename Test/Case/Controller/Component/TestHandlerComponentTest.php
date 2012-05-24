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

App::uses('Controller', 'Controller');
App::uses('Component', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Hash', 'Utility');

App::uses('TestHandlerComponent', 'JsTests.Controller/Component');


define('JS_TEST_PLUGIN_ROOT', App::pluginPath('JsTests'));
define('JS_TESTDATA', JS_TEST_PLUGIN_ROOT.'Test'.DS.'data'.DS);

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


class TestHandlerComponentTestController extends Controller
{
	public $uses = null;
}


class TestHandlerComponentTest extends CakeTestCase
{

	/**
	 *
	 * @var Controller
	 */
	public $Controller = null;

	/**
	 *
	 * @var TestHandlerComponent
	 */
	public $Component = null;


	public function setUp()
	{
		parent::setUp();

		$request = new CakeRequest('/');
		$response = new CakeResponse();
		$this->Controller = new TestHandlerComponentTestController($request, $response);
		$this->Controller->constructClasses();
		$this->Component = new TestHandlerComponent($this->Controller->Components);
	}


	public function tearDown()
	{
		unset($this->Component);
		unset($this->Controller);

		parent::tearDown();
	}

	function testLoadTests()
	{
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

		$result = $this->Component->loadTests('default', $testData);
		$diff = (Hash::diff($result, $expected));

		$this->assertTrue(empty($diff));
	}

	function testCheckProfile()
	{
		$result = $this->Component->checkProfile(Configure::read('JsTests.Profiles.default'));
		$this->assertTrue($result);

		$result = $this->Component->checkProfile(Configure::read('JsTests.Profiles.ze-empty'));
		$this->assertFalse($result);

		try
		{
			$result = $this->Component->checkProfile(Configure::read('JsTests.Profiles.invalid'), true);
			$this->assertTrue(false);
		}
		catch (Exception $ex)
		{
			$this->assertTrue(true);
		}
	}

	function testInstrument()
	{
		Configure::write('JsTests.JSCoverage', array('executable' => '/usr/bin/notajscoverage'));

		try
		{
			$result = $this->Component->instrument(Configure::read('JsTests.Profiles.default'));
			$this->assertTrue(false);
		}
		catch (Exception $ex)
		{
			$this->assertTrue(true);
		}

		#$testJSCoveragePath = '/usr/bin/jscoverage';
		$testJSCoveragePath = 'c:\\usr\\bin\\jscoverage-0.5.1\\jscoverage.exe';

		$this->skipIf(!file_exists($testJSCoveragePath));
		Configure::write('JsTests.JSCoverage', array('executable' => $testJSCoveragePath));

		$result = $this->Component->instrument(Configure::read('JsTests.Profiles.default'));
		$expected = array('output' => array(), 'exitCode' => null);

		$this->assertEqual($result, $expected);
	}
}
