# Класс для конвертирования JPG/PNG в WebP
С помощью данного класса можно конвертировать изображения из JPG/PNG в WebP формат, а так же сделать "быстрый" тест, чтоб понять, поддерживает ли ваш хостин/сервер данную возможность.

## Установка проекта
1. Переходи в папку проекта, открываем терминал.
2. Клонируем проект `git clone https://github.com/xSaTaNxCreWx/image_to_webp.git`
3. Подключаем класс Image_to_webp к вашему проекту

## Использование
### Базовое использование
```php
// Подключение класса
include_once __DIR__ . '/Image_to_webp.php';

$to_webp = new Image_to_webp();

// Добавление url изображений для обработки
$to_webp->add_image(
    array(
        'https://www.site.ru/image.png', 
        'https://www.site.ru/ttt/image.png'
    )
);
$to_webp->add_image('https://www.site.ru/image.jpg');

// Конвертирование изображений
$t = $to_webp->convert();
```

### Дополнительные настройки класс
#### Изменение обрабатываемых изображений
```php
include_once __DIR__ . '/Image_to_webp.php';

$to_webp = new Image_to_webp();
// Будет обрабатываться только JPG формат
$to_webp->set_extensions(array( 'jpg', 'jpeg' ));
```

#### Установка папки с изображениями
```php
include_once __DIR__ . '/Image_to_webp.php';

$to_webp = new Image_to_webp();
// Будет установлена папка для изображений
$to_webp->set_base_dir('/path/to/dir');
```

#### Установка папки для сохранения WebP
```php
include_once __DIR__ . '/Image_to_webp.php';

$to_webp = new Image_to_webp();
// Будет установлена папка для сохранения WebP
$to_webp->set_webp_dir('/path/to/dir');
```

#### Установка папки для сохранения WebP
```php
include_once __DIR__ . '/Image_to_webp.php';

$to_webp = new Image_to_webp();
// Будет установлена папка для сохранения WebP
$to_webp->set_webp_dir('/path/to/dir');
```

#### Установка основного url сайта
```php
include_once __DIR__ . '/Image_to_webp.php';

$to_webp = new Image_to_webp();
// https://www.site.ru будет установлен как основной URL сайта
$to_webp->set_base_url('https://www.site.ru');
```

## Тестирование возможности создания WebP
```php
// Подключение класса
include_once __DIR__ . '/Image_to_webp.php';

// Передаём true для включения тестирования. На странице будет выведена тестовая информация. 
$to_webp = new Image_to_webp(true);
```