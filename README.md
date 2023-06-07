# Sanitizer

Предположим, что перед нами стоит задача обработать HTTP-запрос от клиента или ответ от
стороннего API, содержащий данные в формате JSON.
Нужно написать библиотеку "санитайзер", которая занимается валидацией и нормализацией
данных в соответствии с переданной спецификацией.
## Пример

 ```
    $data     = ['a' => [9502900655, 89502900655, 79502900655, "+7(950)290-06-55"]];
	$typeData = ['a' => ['phone']];

	$sanitizerV1 = new Sanitizer($data, $typeData);
	$sanitizerV1->sanitize()
	
	// результат
	['a' => ["79502900655", "79502900655", "79502900655", "79502900655"]]
```

Санитайзер принимает на входе данные и типы для них в формате:  ['key' => value].
Метод sanitize возвращает результат валидации данных.

## Поддерживаемые типы данных
* string
* integer
* float
* phone
* array  - передаётся массив с типом данных внутри. Например, ['string']
* object - передаётся ассоциативный массив, где ключи совпадают с ключами данных, а значения это типы данных. Например, ['a' => 'string']

В массивы и объекты могут быть вложены другие массивы, другие объекты и т.д.


## Запуск тестов
```sh
$ composer run-script test
```
