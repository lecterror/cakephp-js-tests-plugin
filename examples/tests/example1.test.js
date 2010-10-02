
module('Sample module');

test('Some kind of sample test', function() {
	ok(doSomething(3, 2) == 5, 'sample test 1');
	ok(doSomething(8, 2) == 10, 'sample test 2');
});

test('Other kind of sample test', function() {
	ok(doSomethingElse(3, 2) == 8, 'sample test 3');
	ok(doSomethingElse(8, 2) == 18, 'sample test 4');
});

test('A failing test', function() {
	ok(doSomethingElse(3, 2) == 81, 'sample test 5');
	ok(doSomethingElse(8, 2) == 8, 'sample test 6');
	ok(doSomething(5, 2) == 7, 'sample test 7');
});
