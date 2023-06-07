<?php

declare(strict_types=1);

namespace Sanitizer;

class Sanitizer {

	public array $data;
	public array $typeData;

	public function __construct($data, $typeData) {
		$this->data     = $data;
		$this->typeData = $typeData;
	}

	public function sanitize() {
		return $this->createResultData($this->data, $this->typeData);
	}

	private function createResultData($array, $typeData) {
		$acc = [];
		foreach ($array as $key => $value) {
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
