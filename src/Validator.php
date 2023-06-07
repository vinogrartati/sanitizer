<?php

namespace Sanitizer;

class Validator {
	private ?array $objectKeys = null;

	/**
	 * @param mixed        $value
	 * @param array|string $type
	 */
	public function __construct(private mixed $value, private array|string $type) {
		$this->value = $value;
		$this->type  = $this->getType($type);
	}
	public function validate() {
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

	private function getType($type) {
		if (is_array($type)) {
			if ($this->isAssociative($type)) {
				$this->objectKeys = array_keys($type);
				return 'object';
			}

			return 'array';
		}

		return $type;
	}
	private function floatValidator() {
		if (!is_float($this->value)) {
			return $this->getErrorTypeMessage();
		} else {
			return (float)$this->value;
		}
	}

	private function integerValidator() {
		if (!is_numeric($this->value) || is_float($this->value)) {
			return $this->getErrorTypeMessage();
		}
		else {
			return (int)$this->value;
		}
	}

	private function stringValidator() {
		if (!is_string($this->value)) {
			return $this->getErrorTypeMessage();
		}
		else {
			return $this->value;
		}
	}

	private function phoneValidator() {
		if (!is_numeric($this->value) && !is_string($this->value)) {
			return $this->getErrorTypeMessage();
		}
		// /^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/ - регулярка с хабра
		if (!preg_match('/^(8|\+7|7|)(([\- (]\d{3}[\- )])|\d{3})(\d{3}[\- ]?\d{2}[\- ]?\d{2})$/', $this->value)) {
			return 'Значение не соответстует формату номера телефона.';
		}

		return preg_replace(["/^(8|\+7|7)?/", "/\D/"], ['7', ''], $this->value);
	}

	private function isAssociative($array) {
		foreach ($array as $key => $element)
		{
			if (is_numeric($key)) {
				return false;
			}
		}

		return true;
	}

	private function arrayValidator() {
		if (!is_array($this->value)) {
			return $this->getErrorTypeMessage();
		}
		if ($this->isAssociative($this->value)) {
			return 'Значение является структурой, а в типах данных указан массив';
		}

		return $this->value;
	}

	private function objectValidator() {
		if (!is_array($this->value)) {
			return $this->getErrorTypeMessage();
		}

		if (!$this->isAssociative($this->value)) {
			return 'Значение является массивом, а в типах данных указана структура';
		}

		$keys = array_keys($this->value);
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

	public function getUnknownTypeMessage() {
		return 'Неизвестный тип данных: ' . $this->type . '.';
	}

	public function getErrorTypeMessage() {
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