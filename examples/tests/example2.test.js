
module('Sample module 2');

test('Some kind of sample test', function() {
	ok(meaningOfLife(3, 2) == 42, 'sample test 1');
	ok(meaningOfLife(8, 2) == 42, 'sample test 2');
});

test('This may be a failing test', function() {
	equal(meaningOfLife(3, 2), 42, 'sample test 3');
	equal(whatDoYouExpect(), 'tea and cookies', 'NOBODY EXPECTS THE');
});
