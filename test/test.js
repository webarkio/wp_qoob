// var assert = require('assert');
// describe('Array', function() {
//   describe('#indexOf()', function () {
//     it('should return -1 when the value is not present', function () {
//       assert.equal(-1, [1,2,3].indexOf(5));
//       assert.equal(-1, [1,2,3].indexOf(0));
//     });
//   });
// });

// describe('IsNaN', function () {
//     context("when value is NaN", function () {
//         it('should return true', function () {
//             assert(isNaN(NaN));
//         });
//     });
// });

var assert = require('assert');
var loader = require('../assets/js/builder-loader');
var builder = require('../assets/js/builder');

console.log(loader);
console.log(builder);

describe('#Builder create()', function () {
	it('Initialize page builder', function () {
		

		var load = new loader.BuilderLoader();
		console.log(load);

		var qoob = new builder.Builder();
		console.log(qoob);
		assert.equal(qbs,"3");

	});
});

