<?php

namespace Sanitizer\Tests;

use PHPUnit\Framework\TestCase;
use Sanitizer\Sanitizer;
use Sanitizer\Validator;

class SanitizerTest extends TestCase
{
	/**
	 * Хэлпер с базовыми тест-кейсами для итеррируемых объектов.
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
		$texts = [];
		foreach ($data as $k => $v) {
			$texts[$k] = (new Validator($v, $wrongTypeData[$k]))->getErrorTypeMessage();
		}
		$this->assertEquals($texts, $sanitizerV2->sanitize(), 'Не тот тип данных');

		$sanitizerV3 = new Sanitizer($data, $unknownTypeData);
		$texts = [];
		foreach ($data as $k => $v) {
			$texts[$k] = (new Validator($v, $unknownTypeData[$k]))->getUnknownTypeMessage();
		}
		$this->assertEquals($texts, $sanitizerV3->sanitize(), 'Неизвестный тип данных');
	}

	/**
	 * Тестирование данных с массивами.
	 *
	 * @return void
	 */
	public function testArray(): void {
		$data = ['a' => [1, 2, 3]];
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
		$result = ['a' => [
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
			'a' => [9502912685, 89502912685, 79502912685, "+7(950)291-26-85"],
			'b' => 234.5,
			'c' => [
				'aa' => "abc",
				"bb" => 234,
				"cc" => [1, 2, 3]
			],
			'd' => [
				[
					'aaa' => 'abc',
					'bbb' => 234,
					'ccc' => [1, 2, 3]
				],
				[
					'aaa' => 'abc',
					'bbb' => 234,
					'ccc' => [1, 2, 3]
				],
				[
					'aaa' => 'abc',
					'bbb' => 234,
					'ccc' => [1, 2, 3]
				]
			],
			'e' => [123, 123, 234],
			'j' => [
				'array' => [123, 123, 123]
			]
		];

		$expectation = $data;
		$expectation['a'] = ['79502912685', '79502912685', '79502912685', '79502912685'];
		$typeData         = [
			'a' => ['phone'],
			'b' => 'float',
			'c' => ['aa'    => 'string', 'bb'  => 'integer', 'cc'  => ['integer']],
			'd' => [['aaa'  => 'string', 'bbb' => 'integer', 'ccc' => ['integer']]],
			'e' => ['integer'],
			'j' => ['array' => ['integer']],
		];

		$sanitizerV1 = new Sanitizer($data, $typeData);
		$this->assertEquals($expectation, $sanitizerV1->sanitize(), 'Позитивный сценарий');
	}
}