<?php

declare(strict_types=1);

namespace Sanitizer\Tests;
use PHPUnit\Framework\TestCase;
use Sanitizer\Sanitizer;
use Sanitizer\Validator;

/**
 * Тесты для обработчика данных.
 */
class SanitizerTest extends TestCase
{
	/**
	 * Тестирование данных с массивами.
	 *
	 * @return void
	 */
	public function testArray(): void {
		$data     = ['a' => [1, 2, 3]];
		$typeData = ['a' => ['integer']];
		$this->testHelper($data, $typeData, ['a' => 'string'], ['a' => 'double']);
	}

	/**
	 * Тестирование данных со вложенными массивами.
	 *
	 * @return void
	 */
	public function testNestedArray(): void {
		$data = ['a' => [['a', 'b'], ['123', '345', '567'], ['d', 'e', 'f']]];

		$wrongTypeData = ['a' => [['float']]];
		$result        = ['a' => [
			[
				'"a" является типом string. Для него был указан тип: float.',
				'"b" является типом string. Для него был указан тип: float.'
			],
			[
				'"123" является типом string. Для него был указан тип: float.',
				'"345" является типом string. Для него был указан тип: float.',
				'"567" является типом string. Для него был указан тип: float.',
			],
			[
				'"d" является типом string. Для него был указан тип: float.',
				'"e" является типом string. Для него был указан тип: float.',
				'"f" является типом string. Для него был указан тип: float.',
			],
		]];
		$this->assertEquals($result, (new Sanitizer($data, $wrongTypeData))->sanitize(), 'Не тот тип данных');

		$sanitizerV4 = new Sanitizer($data, [['key' => 'value']]);
		$expectation = ['a' => 'Значение является массивом, а в типах данных указана структура'];

		$this->assertEquals($expectation, $sanitizerV4->sanitize(), 'Передана структура, а не массив');
	}

	/**
	 * Тестирование данных со структурами.
	 *
	 * @return void
	 */
	public function testObject(): void {
		$data     = ['first' => ['a' => 'test', 'b' => 123, 'c' => [1, 2, 3]], 'second' => ['a' => 'test', 'b' => 123, 'c' => [1, 2, 3]]];
		$typeData = ['first' => ['a' => 'string', 'b' => 'integer', 'c' => ['integer']], 'second' => ['a' => 'string', 'b' => 'integer', 'c' => ['integer']]];

		$arrayTypeData   = ['first' => 'phone', 'second' => 'float'];
		$unknownTypeData = ['first' => 'phone123', 'second' => 'float123'];
		$this->testHelper($data, $typeData, $arrayTypeData, $unknownTypeData);

		$arrayTypeData = ['first' => ['integer'], 'second' => ['phone']];
		$sanitizerV4   = new Sanitizer($data, $arrayTypeData);
		$expectation   = [
			'first'  => 'Значение является структурой, а в типах данных указан массив',
			'second' => 'Значение является структурой, а в типах данных указан массив',
		];
		$this->assertEquals($expectation, $sanitizerV4->sanitize(), 'Передан массив, а не структура');
	}

	/**
	 * Тестирование данных со вложенными структурами.
	 *
	 * @return void
	 */
	public function testNestedObject(): void {
		$data = [
			'a' => [9502912600, 89502912600, 79502912600, "+7(950)291-26-00"],
			'b' => 234.5,
			'c' => [
				'aa' => "abc",
				"bb" => 234,
				"cc" => [1, 2, 3],
			],
			'd' => [123, 123, 234],
			'e' => [
				'array' => [123, 123, 123],
			]
		];

		$expectation = $data;
		$expectation['a'] = ['79502912600', '79502912600', '79502912600', '79502912600'];
		$typeData         = [
			'a' => ['phone'],
			'b' => 'float',
			'c' => ['aa' => 'string', 'bb'  => 'integer', 'cc'  => ['integer']],
			'd' => ['integer'],
			'e' => ['array' => ['integer']],
		];

		$sanitizerV1 = new Sanitizer($data, $typeData);
		$this->assertEquals($expectation, $sanitizerV1->sanitize(), 'Позитивный сценарий');


		$wrongTypeData = [
			'a' => ['phone'],
			'b' => 'float',
			'c' => ['aa' => 'float', 'bb'  => 'integer', 'cc'  => ['integer']],
			'd' => ['integer123'],
			'e' => ['array' => ['phone']],
		];

		$expectation = [
			'a' => ['79502912600', '79502912600', '79502912600', '79502912600'],
			'b' => 234.5,
			'c' => [
				'aa' => '"abc" является типом string. Для него был указан тип: float.',
				"bb" => 234,
				"cc" => [1, 2, 3],
			],
			'd' => [
			    'Неизвестный тип данных: integer123.',
			    'Неизвестный тип данных: integer123.',
			    'Неизвестный тип данных: integer123.',
			],
			'e' => [
				'array' => [
				    'Значение не соответстует формату номера телефона.',
				    'Значение не соответстует формату номера телефона.',
				    'Значение не соответстует формату номера телефона.',
				]
			]
		];

		$sanitizerV2 = new Sanitizer($data, $wrongTypeData);
		$this->assertEquals($expectation, $sanitizerV2->sanitize(), 'Негативный сценарий');
	}

	/**
	 * Метод с базовыми тест-кейсами для итеррируемых объектов.
	 *
	 * @param array $data            Данные
	 * @param array $typeData        Правильный тип данных
	 * @param array $wrongTypeData   Неправильный тип данных
	 * @param array $unknownTypeData Тип данных, которые не обрабатывается библиотекой
	 *
	 * @return void
	 */
	private function testHelper(array $data, array $typeData, array $wrongTypeData, array $unknownTypeData): void {
		$sanitizerV1 = new Sanitizer($data, $typeData);
		$this->assertEquals($data, $sanitizerV1->sanitize(), 'Позитивный сценарий');

		$sanitizerV2 = new Sanitizer($data, $wrongTypeData);
		$texts       = [];
		foreach ($data as $k => $v) {
			$texts[$k] = (new Validator($v, $wrongTypeData[$k]))->getErrorTypeMessage();
		}
		$this->assertEquals($texts, $sanitizerV2->sanitize(), 'Не тот тип данных');

		$sanitizerV3 = new Sanitizer($data, $unknownTypeData);
		$texts       = [];
		foreach ($data as $k => $v) {
			$texts[$k] = (new Validator($v, $unknownTypeData[$k]))->getUnknownTypeMessage();
		}
		$this->assertEquals($texts, $sanitizerV3->sanitize(), 'Неизвестный тип данных');
	}
}
