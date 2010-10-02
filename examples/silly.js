
function doSomething(a, b)
{
	return a+b;
}

function doSomethingElse(a, b)
{
	var wtf = false;

	if (wtf)
	{
		alert('This won\'t be covered by any unit test!');
	}

	a++;

	return b*a;
}
