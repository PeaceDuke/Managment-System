# Managment-System

## Общая информация

Для работы с системой необходима авторизация.
Пользователи длятся на два типа: Администратор и Пользователь.
Функции каждого типа одинаковы, за исключением того, что Администратор имеет возможность создавать новых пользователей,
изменять их данные и удалять, в то время как обычный пользователь может работать лишь со своими данными.

Авторизация происходит при помощи Basic access authentication. Пароль хешируется при момощи алгоритма sha1.

В папке лежит файл с запросами для постмана.

## Управление пользователями

### Посмотреть список всех пользователей

Доступно только пользователям со статусом "Admin".
Получение списка происходит через GET запрос.
> Запрос: GET/managementsystem/users/
В виде ответа приходит строка с данными всех пользоваетлей.

### Добавление пользователей

Доступно только пользователям со статусом "Admin".
Создание происходит через POST запрос, с обязательным указанием имени и фамилии, почты, пароля и т.д
> Запрос: POST/managementsystem/users/<
В виде ответа приходит строка с данными добавленного пользователя.

### Посмотреть информацию о пользователе

Получение информации происходит через GET запрос.
> Запрос: GET/managementsystem/users/{userId}<
В виде ответа приходит строка с данными пользоваетля.

### Обновление пользователей

Обновлеине происходит через PUT запрос, с указанием полей которые необходимо изменить (не обязательно все).
> Запрос: PUT/managementsystem/users/{userId}<
В виде ответа приходит строка с новыми данными пользователя.

### Удаление пользователя

Удаление происходит через DELETE запрос.
> Запрос: DELETE/managementsystem/users/{userId}<
В виде ответа приходит сообщение о удалении пользователя с именем и фамилией.

## Управление товарами

### Посмотреть список всех товаров пользователя

Получение списка происходит через GET запрос.
> Запрос: GET/managementsystem/items/<
В виде ответа приходит строка со списком всех товаров пользоваетля.

### Добавление товара

Создание происходит через POST запрос, с обязательным указанием названия, типа товара, цены и размера.
> Запрос: POST/managementsystem/items/<
В виде ответа приходит строка с данными добавленного товара.

### Посмотреть информацию о товаре

Получение информации происходит через GET запрос.
> Запрос: GET/managementsystem/items/{itemId}<
В виде ответа приходит строка с данными товара.

### Обновление товара

Обновлеине происходит через PUT запрос, с указанием полей которые необходимо изменить (не обязательно все).
> Запрос: PUT/managementsystem/items/{itemId}<
В виде ответа приходит строка с данными товара.

### Удаление товара

Удаление происходит через DELETE запрос.
> Запрос: DELETE/managementsystem/items/{itemId}<
В виде ответа приходит сообщение о удалении товара с названием.

### Поиск товара на складах

Получение информации происходит через GET запрос.
> Запрос: GET/managementsystem/items/{itemId}/find<
В виде ответа приходит список складов, на которых найден этот товар, а так же его колиичество и общая стоимость.

### Посмотреть информацию о всех перемещениях товара

Получение информации происходит через GET запрос.
> Запрос: GET/managementsystem/items/{itemId}/history/movement<
В виде ответа приходит список всех перемещений товара между склазами.

### Посмотреть информацию о колличестве товара на складах на определенную дату

Получение информации происходит через GET запрос с параметром, в котором записана дата.
> Запрос: GET/managementsystem/items/{itemId}/history/state?date={YYYY-MM-DD hh:mm:ss}<
В виде ответа приходит список складов, на которых был товар на ту дату, а так же его колиичество и общая стоимость.

## Управление складами

### Посмотреть список всех складов пользователя

Получение списка происходит через GET запрос.
> Запрос: GET/managementsystem/warehouses/<
В виде ответа приходит строка со списком всех складов пользоваетля.

### Добавление склада

Создание происходит через POST запрос, с обязательным указанием адреса, и размера.
> Запрос: POST/managementsystem/warehouses/<
В виде ответа приходит строка с данными добавленного склада.

### Посмотреть информацию о складе

Получение информации происходит через GET запрос.
> Запрос: GET/managementsystem/warehouses/{warehouseId}<
В виде ответа приходит строка с данными склада, а так же всеми товарами на нем.

### Обновление товара

Обновлеине происходит через PUT запрос, с указанием полей которые необходимо изменить (не обязательно все).
> Запрос: PUT/managementsystem/warehouses/{warehouseId}<
В виде ответа приходит строка с новыми данными склада.

### Удаление товара

Удаление происходит через DELETE запрос.
> Запрос: DELETE/managementsystem/warehouses/{warehouseId}<
В виде ответа приходит сообщение о удалении склада с адресом.

### Запрос товара на склад

Удаление происходит через PUT запрос.
> Запрос: PUT/managementsystem/warehouses/{warehouseId}/request<
В виде ответа приходит сообщение о том сколько товара было запрошено.

### Экспорт товара

Удаление происходит через PUT запрос.
> Запрос: PUT/managementsystem/warehouses/{warehouseId}/export<
В виде ответа приходит сообщение о том сколько товара было отправлено.

### Перемещение товара

Удаление происходит через PUT запрос.
> Запрос: PUT/managementsystem/warehouses/{warehouseId}/transfer<
В виде ответа приходит сообщение о том сколько товара было перемещено.

### Посмотреть информацию о всех связанных со складом перемещениях

Получение информации происходит через GET запрос.
> Запрос: GET/managementsystem/warehouses/{warehouseId}/history/movement<
В виде ответа приходит список всех перемещений товара между склазами.

### Посмотреть информацию о состоянии склада на определенную дату

Получение информации происходит через GET запрос с параметром, в котором записана дата.
> Запрос: GET/managementsystem/warehouses/{warehouseId}/history/state?date={YYYY-MM-DD hh:mm:ss}<
В виде ответа приходит строка с данными склада, а так же всеми товарами на нем на указанную дату.
