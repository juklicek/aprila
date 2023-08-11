<?php

/**
 * Test: Aprila\Arrays
 */

use Aprila\Utils\Arrays;
use	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$testArray = [
	2 => 'jahoda',
	3 => 'meloun',
	4 => 'banan',
	10 => 'mrkev',
	'x' => 'porek',
	'y' => 'malina'
];

Assert::count(6, $testArray);

// previous key
Assert::same('x', Arrays::getPreviousKey($testArray, 'y'));
Assert::same(3, Arrays::getPreviousKey($testArray, 4));
Assert::same('y', Arrays::getPreviousKey($testArray, 2));

// next key
Assert::same('y', Arrays::getNextKey($testArray, 'x'));
Assert::same(10, Arrays::getNextKey($testArray, 4));
Assert::same(2, Arrays::getNextKey($testArray, 'y'));

// previous value
Assert::same('jahoda', Arrays::getPreviousValue($testArray, 'meloun'));
Assert::same('mrkev', Arrays::getPreviousValue($testArray, 'porek'));
Assert::same('malina', Arrays::getPreviousValue($testArray, 'jahoda'));

// next value
Assert::same('mrkev', Arrays::getNextValue($testArray, 'banan'));
Assert::same('porek', Arrays::getNextValue($testArray, 'mrkev'));
Assert::same('jahoda', Arrays::getNextValue($testArray, 'malina'));

$testNestedArray = [
	2 => ['x','1'],
	3 => ['c','4'],
	33 => ['d','5'],
	'a' => ['f','7'],
	'b' => ['g','9'],
];

// next value
Assert::same(['c','4'], Arrays::getNextValue($testNestedArray, ['x','1']));
Assert::same(['x','1'], Arrays::getNextValue($testNestedArray, ['g','9']));
Assert::same(['f','7'], Arrays::getNextValue($testNestedArray, ['d','5']));

// previous value
Assert::same(['c','4'], Arrays::getPreviousValue($testNestedArray, ['d','5']));
Assert::same(['g','9'], Arrays::getPreviousValue($testNestedArray, ['x','1']));
Assert::same(['d','5'], Arrays::getPreviousValue($testNestedArray, ['f','7']));
