# CakePHP JsTests Plugin #

## About ##

JsTests is a [CakePHP][] plugin which tries to make it easy for you to run unit tests for your
JavaScript code. It uses [QUnit][] and [JSCoverage][] to test JavaScript files and provide
code coverage.

## Usage ##

First, obtain the plugin. If you're using Git, run this while in your app folder:

	git submodule add git://github.com/lecterror/cakephp-js-tests-plugin.git plugins/js_tests

Or visit [http://github.com/lecterror/cakephp-js-tests-plugin][github-repo] and download the
plugin manually to your `app/plugins/js_tests/` folder.

Next, make sure you have [JSCoverage][] somewhere on your system. On Ubuntu this is as simple as:

	sudo apt-get install jscoverage

If you're on Windows, download the Windows binaries and place them somewhere warm and comfy.

The best way to start using the plugin is to copy the examples from the plugin "examples"
folder to your `app/webroot/js/`. Additionally, create a `app/webroot/js_instrumented/` folder
and make it world-writable.

Next, copy the `js_tests/lib/core_config.php.default` to `js_tests/lib/core_config.php` and open
the file in your favourite editor, which, if not [Vim][], is inferior to Vim. Now, find the line
which says:

	'executable'	=> '/usr/bin/jscoverage'

and change the path to JSCoverage executable on your system.

You should now be ready to open the tests in your browser:

	[your app root]/js_tests/js_test_runner/run

If not, you've probably messed something up. The examples use [QUnit][] as test framework, and this
plugin has not been tested with anything else. So, good luck if you try it.

When you've figured out all the basic stuff, try creating your own test profiles in the
`js_tests/libs/core_config.php`. If you run into trouble, you can always revert to the default
profile, or submit a ticket if you think you've run into a bug.

## Contributing ##

If you'd like to contribute, clone the source on GitHub, make your changes and send me a pull request.
If you don't know how to fix the issue or you're too lazy to do it, create a ticket and we'll see
what happens next.

**Important**: If you're sending a patch, follow the coding style! If you don't, there is a great
chance I won't accept it. For example:

	// bad
	function drink() {
		return false;
	}

	// good
	function drink()
	{
		return true;
	}

## Licence ##

Why, [GPL][] of course.

[CakePHP]: http://cakephp.org/
[JSCoverage]: http://siliconforks.com/jscoverage/
[Vim]: http://www.vim.org/ "The Editor"
[QUnit]: http://docs.jquery.com/Qunit
[GPL]: http://www.gnu.org/licenses/gpl.html
[github-repo]: http://github.com/lecterror/cakephp-js-tests-plugin
