// http://stackoverflow.com/a/10073788/1048589

define(function() {
	return function(n, width, z) {
		z = z || '0';
		n = n + '';
		return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
	};
});