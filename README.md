# Sanitizer
[![Maintainability](https://api.codeclimate.com/v1/badges/cd7d9d95af19f850541d/maintainability)](https://codeclimate.com/github/vinogrartati/sanitizer/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/cd7d9d95af19f850541d/test_coverage)](https://codeclimate.com/github/vinogrartati/sanitizer/test_coverage)

## Техническое задание
Предположим, что перед нами стоит задача обработать HTTP-запрос от клиента или ответ от
стороннего API, содержащий данные в формате JSON.
Нужно написать библиотеку "санитайзер", которая занимается валидацией и нормализацией
данных в соответствии с переданной спецификацией.

Требования:
- Самостоятельное выполнение задания без оглядки на существующие решения
- Язык PHP/Python/JavaScript/TypeScript без использования сторонних библиотек (кроме библиотек для тестирования)
- Поддержка следующих типов данных:
    - Строка
    - Целое число
    - Число с плавающей точкой
    - Российский федеральный номер телефона
    - Структура (ассоциативный массив с заранее известными ключами)
    - Массив из однотипных элементов
- Должна быть возможность указать типы элементов массивов и структур
- Значения элементов в структурах и массивах могут быть любого из поддерживаемых типов (в том числе другие массивы, структуры, массивы структур, структуры массивов, массивы массивов структур и так далее)
- Генерация списка всех ошибок для некорректных значений. Формат описания ошибок должен предоставлять возможность сопоставить каждую ошибку с исходным значением. Например, если входные данные были сгенерированы на основе HTML-формы с вложенными (табличными) полями, должно быть технически возможно сопоставить каждую ошибку конкретному полю формы
- Тесты

Примеры:

1) из JSON '{"foo": "123", "bar": "asd", "baz": "8 (950) 288-56-23"}' при указанных программистом типах полей "целое число", "строка" и "номер телефона" соответственно должен получиться ассоциативный массив с тремя полями: целочисленным foo = 123, строковым bar = "asd" и строковым "baz" = "79502885623"

2) при указании для строки "123абв" типа "целое число" должна быть сгенерирована ошибка

3) при указании для строки "260557" типа "номер телефона" должна быть сгенерирована ошибка


## Реализация

 ```
$data     = ['a' => [9502900655, 89502900655, 79502900655, "+7(950)290-06-55"]];
$typeData = ['a' => ['phone']];

$sanitizerV1 = new Sanitizer($data, $typeData);
$sanitizerV1->sanitize();
	
// Результат
// ['a' => ["79502900655", "79502900655", "79502900655", "79502900655"]]
```


```	
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
$typeData = [
	'a' => ['phone'],
	'b' => 'float',
	'c' => ['aa'    => 'float', 'bb'  => 'integer', 'cc'  => ['integer']],
	'd' => ['integer123'],
	'e' => ['array' => ['phone']],
];

$sanitizerV2 = new Sanitizer($data, $typeData);
$sanitizerV2->sanitize();
// Результат
// [
//      'a' => ['79502912600', '79502912600', '79502912600', '79502912600'],
//      'b' => 234.5,
//      'c' => [
//      	'aa' => '"abc" является типом string. Для него был указан тип: float.',
//      	"bb" => 234,
//      	"cc" => [1, 2, 3],
//      ],
//      'd' => [
//          'Неизвестный тип данных: integer123.',
//          'Неизвестный тип данных: integer123.',
//          'Неизвестный тип данных: integer123.',
//      ],
//      'e' => [
//      	'array' => [
//      	    'Значение не соответстует формату номера телефона.',
//      	    'Значение не соответстует формату номера телефона.',
//      	    'Значение не соответстует формату номера телефона.',
//      	]
//      ]
//	];
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


### Запуск тестов
```sh
$ make test
```
