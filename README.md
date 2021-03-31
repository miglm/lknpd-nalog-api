# lknpd-nalog-api
Небольшая PHP-библиотека для формирования и отмены чеков для самозанятых (пользователей "Мой налог")

## Методы
### Подключение библиотеки
```php
spl_autoload('LkNpdNalogApi');

$api = new LkNpdNalogApi(
    'Логин для ЛК ФНС (обязательно)',
    'Пароль для ЛК ФНС (обязательно)',
        'Asia/Yekaterinburg' // Часовой пояс (необязательно, нужен для верного отображения времени в чеке). По умолчанию - Europe/Moscow
);
```

### Создание нового чека
```php
$args = [
    'name' => 'Название товара',
    'amount' => 149,
    'clientContactPhone' => null,
    'clientDisplayName' => null
];

$api->createReceipt($args);
```

### Отмена ранее выданного чека
```php
$args = [
    'reason' => 'CANCEL',
        // СANCEL - Чек сформирован ошибочно
        // REFUND - Возврат средств
    'receiptUuid' => '20aabbccdd' // ID чека
];

$api->cancelReceipt($args);
```

### Методы данных

```php
/**
* Наличие ошибок
* @return bool
*/
echo $api->error;

/**
* Сообщения об ошибке
* @return string
*/
echo $api->errorMessage;
echo $api->errorExceptionMessage;

/**
* Идентификатор чека (созданного или отмененного)
* @return string
*/
echo $api->receiptUuid;

/**
* Ссылка на печатуню форму чека
* @return string
*/
echo $api->receiptUrlPrint;

/**
* Ссылка на данные чека в JSON
* @return string
*/
echo $api->receiptUrlJson;

/**
* Ответ на запрос
* @return string
*/
echo $api->response;
```

### Валидация
```php
if (!$api->error){
    // Все в порядке
    echo "receiptUuid: {$api->receiptUuid}<br>";
}else{
    // Ошибка
    echo "errorMessage: {$api->errorMessage}<br>";
    echo "errorExceptionMessage: {$api->errorExceptionMessage}<br>";
}
```

### Пример использования
Примеры использования приведены в `index.php`
