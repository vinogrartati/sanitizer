<?php

declare(strict_types=1);

namespace Sanitizer\Tests;

use PHPUnit\Framework\TestCase;
use Sanitizer\Validator;

/**
 * Тесты для валидатора.
 */
class ValidatorTest extends TestCase {
	/**
	 * Тестирование строк.
	 *
	 * @return void
	 */
	public function testString(): void {
		$data     = 'test';
		$typeData = 'string';
		$this->assertEquals($data, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий');

		$typeData = 'float';
		$text     = '"test" является типом string. Для него был указан тип: float.';
		$this->assertEquals($text, (new Validator($data, $typeData))->validate(), 'Неверный тип');

		$typeData = 'float1234';
		$text     = 'Неизвестный тип данных: float1234.';
		$this->assertEquals($text, (new Validator($data, $typeData))->validate(), 'Неизвестный тип');
	}

	/**
	 * Тестирование целых чисел.
	 *
	 * @return void
	 */
	public function testInteger(): void {
		$data     = 1234;
		$typeData = 'integer';
		$this->assertEquals($data, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий');

		$typeData = 'float';
		$text     = '1234 является типом integer. Для него был указан тип: float.';
		$this->assertEquals($text, (new Validator($data, $typeData))->validate(), 'Неверный тип');
	}

	/**
	 * Тестирование чисел с плавающей точкой.
	 *
	 * @return void
	 */
	public function testFloat(): void {
		$data     = 1234.0;
		$typeData = 'float';
		$this->assertEquals($data, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий');

		$data = 1234.450000;
		$this->assertEquals(1234.45, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий');

		$data     = 1234.0;
		$typeData = 'integer';
		$text     = '1234 является типом float. Для него был указан тип: integer.';
		$this->assertEquals($text, (new Validator($data, $typeData))->validate(), 'Неверный тип');
	}

	/**
	 * Тестирование номеров телефона.
	 *
	 * @return void
	 */
	public function testPhone(): void {
		$data        = '+7(999)333-22-55';
		$typeData    = 'phone';
		$expectation = '79993332255';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий v1');

		$data = '8(999)333-22-55';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий v2');

		$data = '8(999)3332255';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий v3');

		$data = '7-999-333-22-55';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий v4');

		$data = '79993332255';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий v5');

		$data = 79993332255;
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий v6');

		$data = 89993332255;
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий v7');

		$data        = '+89993332255';
		$expectation = 'Значение не соответстует формату номера телефона.';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Неверный формат телефона v1');

		$data = '+9993332255';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Неверный формат телефона v2');

		$data = '+7A9993332255';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Неверный формат телефона v3');

		$data = '+79993332-2-55';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Неверный формат телефона v4');

		$data = '+7993332255';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Неверный формат телефона v5');

		$data = '999)3332255';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Неверный формат телефона v6');

		$data = '79993332255d,msd,gd,g';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Неверный формат телефона v7');

		$data = '799933322551';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Неверный формат телефона v8');

		$data = '12131479993332255d,msd,gd,g';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Неверный формат телефона v9');
	}

	/**
	 * Тестирование массивов.
	 *
	 * @return void
	 */
	public function testArray(): void {
		$data     = ['test1', 'test2', 'test3'];
		$typeData = 'array';
		$this->assertEquals($data, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий');

		$typeData    = 'integer';
		$expectation = '["test1","test2","test3"] является типом array. Для него был указан тип: integer.';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Не тот тип данных');

		$data        = ['key' => 'value', 'key2' => 'value2'];
		$typeData    = 'array';
		$expectation = 'Значение является структурой, а в типах данных указан массив';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Передана структура');
	}

	/**
	 * Тестирование структур.
	 *
	 * @return void
	 */
	public function testObject(): void {
		$data     = ['key' => 'value', 'key2' => 'value2'];
		$typeData = ['key' => 'string', 'key2' => 'value2'];
		$this->assertEquals($data, (new Validator($data, $typeData))->validate(), 'Позитивный сценарий');

		$typeData    = 'phone';
		$expectation = '{"key":"value","key2":"value2"} является типом object. Для него был указан тип: phone.';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Не тот тип данных');

		$data        = [1, 2, 3];
		$expectation = 'Значение является массивом, а в типах данных указана структура';
		$this->assertEquals($expectation, (new Validator($data, 'object'))->validate(), 'Передана структура');

		$data        = ['key' => 'value', 'key2' => 'value2'];
		$typeData    = ['key' => 'string', 'key2' => 'value2', 'key3' => 'wrong key'];
		$expectation = 'Ключи структуры не совпадают с ключами в типах данных. В типах данных отсутствуют ключи: key3.';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Отсутствует ключ в типах данных');

		$data        = ['key' => 'value', 'key2' => 'value2', 'missing' => 'value'];
		$typeData    = ['key' => 'string', 'key2' => 'value2'];
		$expectation = 'Ключи структуры не совпадают с ключами в типах данных. В структуре отсутствуют ключи: missing.';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Отсутствует ключ в структуре');


		$data        = ['key' => 'value', 'key2' => 'value2', 'missingKeyFirst' => 'value', 'missingKeySecond' => 'value'];
		$typeData    = ['key' => 'string', 'key2' => 'value2', 'typeKeyFirst' => 'wrong key', 'typeKeySecond' => 'wrong key'];
		$expectation = 'Ключи структуры не совпадают с ключами в типах данных. ' .
			'В структуре отсутствуют ключи: missingKeyFirst, missingKeySecond. ' .
			'В типах данных отсутствуют ключи: typeKeyFirst, typeKeySecond.';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate(), 'Несоответствие ключей');
	}

	/**
	 * Тестирование неизвестных типов
	 *
	 * @return void
	 */
	public function testUnknownType(): void {
		$data        = 123;
		$typeData    = 'double';
		$expectation = 'Неизвестный тип данных: ' . $typeData . '.';
		$this->assertEquals($expectation, (new Validator($data, $typeData))->validate());
	}
}
