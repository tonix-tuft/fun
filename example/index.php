<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Fun\Fun;

echo PHP_EOL;
echo json_encode(
    [
        'Fun::arrayEvery()',
        Fun::arrayEvery([1, 2, 3], function ($v, $k) {
            return $v === $k + 1;
        }),
    ],
    JSON_PRETTY_PRINT
);
echo PHP_EOL;

function a(...$args) {
    return 'a(' . implode(', ', $args) . ")";
}

function b(...$args) {
    return 'b(' . implode(', ', $args) . ")";
}

function c(...$args) {
    return 'c(' . implode(', ', $args) . ")";
}

$compositionResult = Fun::compose('a', 'b', 'c')(1, 2, 3);

echo PHP_EOL;
echo json_encode(["Fun::compose()", $compositionResult], JSON_PRETTY_PRINT);
echo PHP_EOL;

echo PHP_EOL;
