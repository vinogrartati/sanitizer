<?php

declare(strict_types=1);

namespace Sanitizer;

/**
 * Обработчик переданных пользователем данных.
 */
class Sanitizer {
	/**
	 * @param array $data     Данные
	 * @param array $typeData Типы данных
	 */
	public function __construct(public array $data, public array $typeData) {}

	/**
	 * Метод для запуска обработки данных.
	 *
	 * @return array
	 */
	public function sanitize(): array {
		return $this->createResultData($this->data, $this->typeData);
	}

	/**
	 * Обработка и валидация переданных данных.
	 *
	 * @param array $data     Данные
	 * @param array $typeData Типы данных
	 *
	 * @return array
	 */
	private function createResultData(array $data, array $typeData): array {
		$acc = [];
		foreach ($data as $key => $value) {
			$typeValue = $typeData[$key] ?? $typeData[0] ?? null;
			$validateResult = (new Validator($value, $typeValue))->validate();

			if (is_array($value)) {
				if ($validateResult !== $value) {
					$acc[$key] = $validateResult;
				} else {
					$acc[$key] = $this->createResultData($value, $typeValue);
				}
			} else {
				$acc[$key] = $validateResult;
			}
		}
		return $acc;
	}
}
