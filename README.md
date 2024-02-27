# langchain-text-splitter

A simple text splitter that splits text into chunks of a given size with a given overlap.

This library is based on https://github.com/kambo-1st/langchain-php

Includes multi language support.

## Installation

```bash
php composer require mathsgod/langchain-text-splitter
```


## Example

```php

use Langchain\TextSplitter\RecursiveCharacterTextSplitter;

require_once __DIR__ . '/vendor/autoload.php';

$ts = new RecursiveCharacterTextSplitter([
    "chunk_size" => 10,
    "chunk_overlap" => 2
]);

$text = "財政司長陳茂波明日公布新一份財政預算案，焦點之一是會否全面取消樓市逆周期措施。瑞銀發報告認為，在財赤及樓市疲軟下，預料港府會就樓市全面「撤辣」，但按2019年及去年的經驗，相信隨之而來的利好情緒只會維持4至16周，並續料今年樓價會下挫，最新預測會跌5%至10%，而早前的估計是跌少於10%。";

$chunks = $ts->splitText($text);

print_r($chunks);
/*
Array
(
    [0] => 財政司長陳茂波明日公
    [1] => 日公布新一份財政預算
    [2] => 預算案，焦點之一是會
    [3] => 是會否全面取消樓市逆
    [4] => 市逆周期措施。瑞銀發
    [5] => 銀發報告認為，在財赤
    [6] => 財赤及樓市疲軟下，預
    [7] => ，預料港府會就樓市全
    [8] => 市全面「撤辣」，但按
    [9] => 但按2019年及去年
    [10] => 去年的經驗，相信隨之
    [11] => 隨之而來的利好情緒只
    [12] => 緒只會維持4至16周
    [13] => 6周，並續料今年樓價
    [14] => 樓價會下挫，最新預測
    [15] => 預測會跌5%至10%
    [16] => 0%，而早前的估計是
    [17] => 計是跌少於10%。
)
*/
```