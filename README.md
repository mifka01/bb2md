# bb2md

A class to translate a text **from BBCode to Markdown**.

The BBCode to Markdown converter is able to convert: bold, italic, underline and strikethrough texts, lists,
urls, images, quotes, BB2 images and even code blocks (snippets).

## Composer Installation

To install bb2md, you first need to install [Composer](http://getcomposer.org/)

## BB2MD Installation

Once you have installed Composer, it's easy install Converter.

```
composer require mifka01/bb2md "@dev"
```

2. Run the following command in your project root dir:

```sh
composer update
```

## Usage

BBCode to Markdown conversion:

```php
$converter = new Converter\BBCodeConverter();
$converted = $converter->toMarkdown($text);
```

## Requirements

- PHP 7.4.0 or above.

## Authors

Filippo F. Fadda - <filippo.fadda@programmazione.it> - <http://www.linkedin.com/in/filippofadda>
Radim Mifka - <radimmifka@gmail.com>

## License

Converter is licensed under the Apache License, Version 2.0 - see the LICENSE file for details.
