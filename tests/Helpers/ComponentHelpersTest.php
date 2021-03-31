<?php

namespace Tests\Unit\Helpers;

use EightshiftLibs\Exception\ComponentException;
use EightshiftLibs\Helpers\Components;
use Brain\Monkey\Functions;

use Brain\Monkey;
use function Tests\setupMocks;

beforeAll(function () {
	Monkey\setUp();
	setupMocks();
});

afterAll(function() {
	Monkey\tearDown();
});

/**
 * Components::ensureString tests
 */
test('Asserts ensure string returns a correct result', function ($args) {
	$this->assertIsString(Components::ensureString($args));
})->with('correctArguments');


test('Throws type exception if wrong argument type is passed to ensureString', function ($argument) {
	Components::ensureString($argument);
})
->throws(ComponentException::class)
->with('errorStringArguments');

test('Throws argument count exception if no argument is passed', function () {
	Components::ensureString();
})->throws(\ArgumentCountError::class);

/**
 * Components::classnames tests
 */
test('Asserts classnames returns a string', function ($args) {
	$this->assertIsString(Components::classnames($args));
})->with('classesArray');

test('Throws type exception if wrong argument type is passed to classnames',
	function ($argument) {
		Components::classnames($argument);
	})
	->throws(\TypeError::class)
	->with('errorStringArguments');


/**
 * Components::getManifest tests
 */
test('Asserts that reading manifest.json using getManifest will return an array', function () {
	$results = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button');

	$this->assertIsArray($results, 'The result is not an array');
	$this->assertArrayHasKey('componentName', $results, 'Missing a key from the manifest.json file');
});


test('Asserts that not specifying the path in getManifest will throw an exception', function () {
	Components::getManifest(dirname(__FILE__));
})->throws(ComponentException::class);


/**
 * Components::render tests
 */
test('Asserts that rendering a component works', function () {
	Functions\when('get_template_directory')->justReturn(dirname(__FILE__, 2) . '/data/frontend-libs');
	$results = Components::render('button', []);

	$this->assertNotEmpty($results, 'Component should be rendered here');
	$this->assertStringContainsString('Hello!', $results, 'Component should contain a string');
});

test('Asserts that rendering a component will output a wrapper if parentClass is provided', function () {
	Functions\when('get_template_directory')->justReturn(dirname(__FILE__, 2) . '/data/frontend-libs');
	$results = Components::render('button', ['parentClass' => 'test']);

	$this->assertNotEmpty($results, 'Component should be rendered here');
	$this->assertStringContainsString('Hello!', $results, 'Component should contain a string');
	$this->assertStringNotContainsString('test__button.php', $results, 'Component should contain a class name, not file type');
	$this->assertStringContainsString('test__button', $results, 'Component should contain a class name');
});

test('Asserts that providing a missing component will throw an exception without extension', function () {
	Components::render('component', []);
})->throws(ComponentException::class);

test('Asserts that providing a missing component will throw an exception', function () {
	Components::render('component-a.php', []);
})->throws(ComponentException::class);

test('Asserts that render used components defaults', function () {
	Functions\when('get_template_directory')->justReturn(dirname(__FILE__, 2) . '/data/frontend-libs');

	$results = Components::render('button', [], '', true);

	$this->assertNotEmpty($results, 'Component should be rendered here');
	$this->assertStringContainsString('Hello!', $results, 'Component should contain a string');
});

/**
 * Components::getDefaultRenderAttributes tests
 */
test('Asserts that getDefaultRenderAttributes will merge rendered attributes with manifest attributes that have default values', function () {
	$manifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button/');

	$output = Components::getDefaultRenderAttributes(
		$manifest,
		[
			'buttonContent' => 'test',
			'buttonColor' => 'black',
		]
	);

	$this->assertIsArray($output);
	$this->assertArrayHasKey('buttonContent', $output);
	$this->assertArrayHasKey('buttonColor', $output);
	$this->assertArrayHasKey('buttonSize', $output);
	$this->assertArrayNotHasKey('buttonId', $output);
});

/**
 * Components::responsiveSelectors tests
 */
test('Asserts that using responsive selectors will work', function () {
	$modifiers = ['mobile' => '12', 'tablet' => '12', 'desktop' => '6'];
	$modifiersAlt = ['mobile' => '12', 'tablet' => '12', 'desktop' => ''];

	$withModifier = Components::responsiveSelectors($modifiers, 'width', 'column', true);
	$withoutModifier = Components::responsiveSelectors($modifiers, 'width', 'column', false);
	$withEmptyString = Components::responsiveSelectors($modifiersAlt, 'width', 'column');

	$this->assertIsString($withModifier, 'Result should be a string');
	$this->assertIsString($withoutModifier, 'Result should be a string');
	$this->assertIsString($withEmptyString, 'Result should be a string');

	$this->assertEquals(
		'column__width-mobile--12 column__width-tablet--12 column__width-desktop--6'
		, $withModifier,
		'Strings are not equal in the case of modifiers added'
	);
	$this->assertEquals(
		'column__width-mobile column__width-tablet column__width-desktop',
		$withoutModifier,
		'Strings are not equal in the case there is no modifier'
	);
	$this->assertEquals(
		'column__width-mobile--12 column__width-tablet--12',
		$withEmptyString,
		'Strings are not equal when one option is empty'
	);
});


test('Asserts that providing wrong type to responsiveSelectors will throw an exception', function () {
	Components::responsiveSelectors('', false, true, '');
})->throws(\TypeError::class);


test('Asserts that providing wrong number of arguments to responsiveSelectors will throw an exception', function () {
	Components::responsiveSelectors([], 'true');
})->throws(\ArgumentCountError::class);


/**
 * Components::checkAttr tests
 */
test('Asserts that checkAttr works in case attribute is string', function () {
	$manifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button/');
	$attributes['buttonAlign'] = 'right';

	$results = Components::checkAttr('buttonAlign', $attributes, $manifest);

	$this->assertIsString($results, 'Result should be a string');
	$this->assertEquals('right', $results, "The set attribute should be {$attributes['buttonAlign']}");
});


test('Asserts that checkAttr works in case attribute is boolean', function () {
	$manifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button/');
	$attributes['buttonIsAnchor'] = true;

	$results = Components::checkAttr('buttonIsAnchor', $attributes, $manifest);

	$this->assertIsBool($results, 'THe result should be a boolean');
	$this->assertEquals(true, $results, "The set attribute should be {$attributes['buttonIsAnchor']}");
});


test('Asserts that checkAttr returns false in case attribute is boolean and default is not set', function () {
	$manifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button/');
	$attributes['buttonIsAnchor'] = true;

	$results = Components::checkAttr('buttonIsNewTab', $attributes, $manifest);

	$this->assertIsBool($results, 'THe result should be a boolean');
	$this->assertEquals(false, $results, "The set attribute should be false");
});


test('Asserts that checkAttr works in case attribute is array', function () {
	$manifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button/');
	$attributes['buttonAttrs'] = ['attr 1', 'attr 2'];

	$results = Components::checkAttr('buttonAttrs', $attributes, $manifest);

	$this->assertIsArray($results, 'The result should be an array');
	$this->assertEquals('attr 1', $results[0], 'The value in the array is not correct');
	$this->assertEquals('attr 2', $results[1], 'The value in the array is not correct');
});


test('Asserts that checkAttr returns empty array in case attribute is array or object and default is not set', function () {
	$manifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button/');
	$attributes['buttonSize'] = 'large';

	$results = Components::checkAttr('buttonAttrs', $attributes, $manifest);

	$this->assertIsArray($results, 'The result should be an empty array');
	$this->assertEquals([], $results, "The set attribute should be empty array");
});


test('Asserts that checkAttr returns default value', function () {
	$manifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button/');
	$attributes['title'] = 'Some attribute';

	$results = Components::checkAttr('buttonAlign', $attributes, $manifest, 'button');

	$this->assertIsString($results, 'The default value should be a string');
	$this->assertEquals('left', $results, 'The default value should be left');
});

test('Asserts that checkAttr throws exception if manifest key is not set', function () {
	$manifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button/');
	$attributes['title'] = 'Some attribute';

	Components::checkAttr('bla', $attributes, $manifest, 'button');
})->throws(\Exception::class, 'bla key does not exist in the button component. Please check your implementation.');


/**
 * Components::selector tests
 */
test('Asserts that selectorBlock returns the correct class when attributes are set', function () {
	$selector = Components::selector('button', 'button', 'icon', 'blue');

	$this->assertIsString($selector);
	$this->assertEquals('button__icon--blue', $selector);
});


test('Asserts that selector returns the correct class when only block class is set', function () {
	$selector = Components::selector('button', 'button');

	$this->assertIsString($selector);
	$this->assertEquals('button', $selector);
});


test('Asserts that selector returns the correct class when element is an empty string', function () {
	$selector = Components::selector('button', 'button', '    ');

	$this->assertIsString($selector);
	$this->assertEquals('button', $selector);
});

/**
 * Components::outputCssVariablesGlobal tests
 */
test('Asserts that outputCssVariablesGlobal returns the correct css variables from global manifest', function () {
	$globalManifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks');

	$output = Components::outputCssVariablesGlobal($globalManifest);

	$this->assertIsString($output);
	$this->assertStringContainsString('<style>', $output);
	$this->assertStringContainsString(':root {', $output);
	$this->assertStringContainsString('--global-colors-primary: #C3151B;', $output);
	$this->assertStringNotContainsString('--button-content:', $output);
});

test('Asserts that outputCssVariablesGlobal returns empty string if global manifest data is not provided', function () {
	$output = Components::outputCssVariablesGlobal([]);

	$this->assertIsString($output);
	$this->assertStringNotContainsString('<style>', $output);
});

/**
 * Components::outputCssVariablesGlobalInner tests
 */
test('Asserts that outputCssVariablesGlobalInner returns the correct css variable for color', function () {
	$globalManifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks');

	$output = Components::outputCssVariablesGlobalInner(
		$globalManifest['globalVariables']['colors'],
		'colors'
	);

	$this->assertIsString($output);
	$this->assertStringContainsString('--global-colors-primary: #C3151B;', $output);
});

test('Asserts that outputCssVariablesGlobalInner returns the correct css variable for gradients', function () {
	$globalManifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks');

	$output = Components::outputCssVariablesGlobalInner(
		$globalManifest['globalVariables']['gradients'],
		'gradients'
	);

	$this->assertIsString($output);
	$this->assertStringContainsString('--global-gradients-black: #000000;', $output);
});

test('Asserts that outputCssVariablesGlobalInner returns the correct css variable for fontSizes', function () {
	$globalManifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks');

	$output = Components::outputCssVariablesGlobalInner(
		$globalManifest['globalVariables']['fontSizes'],
		'fontSizes'
	);

	$this->assertIsString($output);
	$this->assertStringContainsString('--global-font-sizes-normal: normal;', $output);
});

test('Asserts that outputCssVariablesGlobalInner returns the correct css variable for generic value', function () {
	$globalManifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks');

	$output = Components::outputCssVariablesGlobalInner(
		$globalManifest['globalVariables']['gutters'],
		'gutters'
	);

	$this->assertIsString($output);
	$this->assertStringContainsString('--global-gutters-none: 0;', $output);
	$this->assertStringContainsString('--global-gutters-default: 1.25em;', $output);
	$this->assertStringContainsString('--global-gutters-big: 2.5em;', $output);
});

test('Asserts that outputCssVariablesGlobalInner provided data si wrong', function () {

	$output = Components::outputCssVariablesGlobalInner(
		[],
		''
	);

	$this->assertIsString($output);
	$this->assertStringNotContainsString('--global', $output);
});

/**
 * Components::outputCssVariables tests
 */
test('Asserts that outputCssVariables returns the correct css variables output', function () {
	$manifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button');

	$attributes = [
		'buttonSize' => 'default',
		'buttonColor' => 'primary',
		'buttonWidth' => 'default',
		'buttonAlign' => 'left',
	];

	$output = Components::outputCssVariables(
		$attributes,
		$manifest,
		'uniqueString'
	);

	$this->assertIsString($output);
	$this->assertStringContainsString('<style>', $output);
	$this->assertStringContainsString('--button-size: default;', $output);
	$this->assertStringContainsString('--button-color: var(--global-colors-primary);', $output);
	$this->assertStringNotContainsString('--button-content:', $output);
	$this->assertStringContainsString(".btn[data-id='uniqueString']", $output);
});

test('Asserts that outputCssVariables will not return css variables if data is empty', function () {
	$manifest = Components::getManifest(dirname(__FILE__, 2) . '/data/frontend-libs/src/Blocks/components/button');

	$attributes = [
		'buttonSize' => 'default',
		'buttonColor' => 'primary',
		'buttonWidth' => 'default',
		'buttonAlign' => 'left',
		'buttonContent' => 'left',
	];

	$output = Components::outputCssVariables(
		$attributes,
		$manifest,
		'uniqueString'
	);

	$this->assertStringNotContainsString('--button-content', $output);
});

test('Asserts that outputCssVariables will not return css variable if variable key is not set', function () {
	$output = Components::outputCssVariables(
		[],
		[],
		'uniqueString'
	);

	$this->assertStringNotContainsString('<style>', $output);
	$this->assertStringContainsString('', $output);
});

/**
 * Components::getUnique tests
 */
test('Asserts that getUnique returns the correct output', function () {
	$output = Components::getUnique();

	$this->assertIsString($output);
	$this->assertMatchesRegularExpression('/[a-z0-9]{1,32}/m', $output);
});

/**
 * Components::camelToKebabCase tests
 */
test('Asserts that camelToKebabCase returns the correct output', function () {
	$output = Components::camelToKebabCase('superCoolTestString');

	$this->assertEquals('super-cool-test-string', $output);
});

test('Asserts that camelToKebabCase returns the wrong output', function () {
	$output = Components::camelToKebabCase('super_CoolTest-String ivan');

	$this->assertNotEquals('super-cool-test-string-ivan', $output);
});
