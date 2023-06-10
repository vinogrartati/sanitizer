<?php

declare(strict_types=1);

namespace Sanitizer;

/**
 * Валидатор типов данных.
 */
class Validator {
	/** @var string[] Ключи структуры  */
	private array $objectKeys;

	/**
	 * @param mixed        $value Значение для проверки
	 * @param array|string $type  Тип значения
	 */
	public function __construct(private mixed $value, private array|string $type) {
		$this->type = $this->getType($type);
	}

	/**
	 * Главный метод валидирования.
	 *
	 * @return string|array|int|float
	 */
	public function validate(): string|array|int|float {
		return match ($this->type) {
			'string'  => $this->stringValidator(),
			'integer' => $this->integerValidator(),
			'float'   => $this->floatValidator(),
			'phone'   => $this->phoneValidator(),
			'array'   => $this->arrayValidator(),
			'object'  => $this->objectValidator(),
			default   => $this->getUnknownTypeMessage(),
		};
	}

	/**
	 * Присвоение типа данных.
	 *
	 * @param array|string $type Тип, переданный в класс
	 *
	 * @return string
	 */
	private function getType(array|string $type): string {
		if (is_array($type)) {
			if ($this->isAssociative($type)) {
				$this->objectKeys = array_keys($type);
				return 'object';
			}

			return 'array';
		}

		return $type;
	}

	/**
	 * Валидатор данных типа число с плавающей точкой.
	 *
	 * @return float|string
	 */
	private function floatValidator(): float|string {
		if (!is_float($this->value)) {
			return $this->getErrorTypeMessage();
		} else {
			return (float)$this->value;
		}
	}

	/**
	 * Валидатор данных типа целое число.
	 *
	 * @return int|string
	 */
	private function integerValidator(): int|string {
		if (!is_numeric($this->value) || is_float($this->value)) {
			return $this->getErrorTypeMessage();
		}
		else {
			return (int)$this->value;
		}
	}

	/**
	 * Валидатор данных типа строка.
	 *
	 * @return string
	 */
	private function stringValidator(): string {
		if (!is_string($this->value)) {
			return $this->getErrorTypeMessage();
		}
		else {
			return $this->value;
		}
	}

	/**
	 * Валидатор данных типа номер телефона.
	 *
	 * @return string
	 */
	private function phoneValidator(): string {
		if (!is_numeric($this->value) && !is_string($this->value)) {
			return $this->getErrorTypeMessage();
		}
		if (!preg_match('/^(8|\+7|7|)(([\- (]\d{3}[\- )])|\d{3})(\d{3}[\- ]?\d{2}[\- ]?\d{2})$/', (string)$this->value)) {
			return 'Значение не соответстует формату номера телефона.';
		}

		return preg_replace(["/^(8|\+7|7)?/", "/\D/"], ['7', ''], (string)$this->value);
	}

	/**
	 * Является ли массив структурой.
	 *
	 * @param array $data Массив данных
	 *
	 * @return bool
	 */
	private function isAssociative(array $data): bool {
		foreach ($data as $key => $element)
		{
			if (is_numeric($key)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Валидатор данных типа массив.
	 *
	 * @return array|string
	 */
	private function arrayValidator(): array|string {
		if (!is_array($this->value)) {
			return $this->getErrorTypeMessage();
		}
		if ($this->isAssociative($this->value)) {
			return 'Значение является структурой, а в типах данных указан массив';
		}

		return $this->value;
	}

	/**
	 * Валидатор данных типа структура.
	 *
	 * @return array|string
	 */
	private function objectValidator(): array|string {
		if (!is_array($this->value)) {
			return $this->getErrorTypeMessage();
		}

		if (!$this->isAssociative($this->value)) {
			return 'Значение является массивом, а в типах данных указана структура';
		}

		$keys       = array_keys($this->value);
		$keysDiff   = array_diff($keys, $this->objectKeys);
		$objectDiff = array_diff($this->objectKeys, $keys);
		if (count($keysDiff) || count($objectDiff)) {
			return 'Ключи структуры не совпадают с ключами в типах данных. ' .
				(count($keysDiff)   ? 'В структуре отсутствуют ключи: ' . implode(', ', $keysDiff) . '.' : '') .
				(count($keysDiff) && count($objectDiff) ? ' ' : '') . // todo проще сделать
				(count($objectDiff) ? 'В типах данных отсутствуют ключи: ' . implode(', ', $objectDiff) . '.' : '');
		}

		return $this->value;
	}

	/**
	 * Получить ошибку при передаче неизвестного типа данных.
	 *
	 * @return string
	 */
	public function getUnknownTypeMessage(): string {
		return 'Неизвестный тип данных: ' . $this->type . '.';
	}

	/**
	 * Получить ошибку типа данных.
	 *
	 * @return string
	 */
	public function getErrorTypeMessage(): string {
		$valueType = gettype($this->value);
		if (is_float($this->value)) {
			$valueType = 'float';
		}
		if (is_array($this->value) && $this->isAssociative($this->value)) {
			$valueType = 'object';
		}

		return json_encode($this->value) . ' является типом ' . $valueType . '. Для него был указан тип: ' . $this->type . '.';
	}
}