<?php

/*
 * Copyright (c) 2020 Anton Bagdatyev (Tonix)
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Fun;

/**
 * Final class containing only static methods as PHP does not autoload functions.
 */
final class Fun {
    /**
     * Invokes a callable or instantiates a class with the given parameters passed to its constructor.
     *
     * @param callable|string $callableOrInstantiableClass A callable or string which represents the function to call or the class to instantiate.
     * @param mixed[] $params Optional parameters to pass to the callable, function or constructor.
     * @return mixed The return value of the callable or function, or the instance/object if `$callableOrInstantiableClass` is a class.
     */
    public static function invoke($callableOrInstantiableClass, $params = []) {
        return class_exists($callableOrInstantiableClass) &&
            !is_callable($callableOrInstantiableClass)
            ? new $callableOrInstantiableClass(...$params)
            : $callableOrInstantiableClass(...$params);
    }

    /**
     * Composes other functions and/or instances of classes, returning a higher order function which represents the composition of all the given functions
     * or instances of the classes given as parameter.
     *
     * @param callable[]|string[] ...$fns Functions (callable) and/or instantiable classes to compose
     *                                    (each class being a string representing the fully qualified name of the class).
     * @return callable A function which represents the composition of the given functions and/or instantiable classes.
     *
     *                  Example:
     *
     *                  require_once __DIR__ . '/vendor/autoload.php';
     *
     *                  use Fun\Fun;
     *
     *                  // $compositionResult = a(b(c(1, 2, 3)));
     *                  $compositionResult = Fun::compose('a', 'b', 'c')(1, 2, 3);
     *
     *                  // If 'b' is a class:
     *                  // $compositionResult = a(new b(c(1, 2, 3)));
     *                  $compositionResult = Fun::compose('a', 'b', 'c')(1, 2, 3);
     *
     *                  // Which is the same as:
     *                  // $compositionResult = a(new b(c(1, 2, 3)));
     *                  $compositionResult = Fun::compose('a', b::class, 'c')(1, 2, 3);
     *
     */
    public static function compose(...$fns) {
        return function (...$args) use (&$fns) {
            return array_reduce(
                array_reverse($fns),
                function ($carry, $item) {
                    return [Fun::invoke($item, $carry)];
                },
                $args
            )[0];
        };
    }

    /**
     * Flat map.
     *
     * @param callable $fn A callable.
     * @param array $array An array.
     * @return array A flattened and mapped array (only the first dimension is flattened).
     */
    public static function flatMap(callable $fn, $array) {
        return array_merge(
            [],
            ...array_map(function (...$args) use (&$fn) {
                $ret = $fn(...$args);
                if (!is_array($ret)) {
                    return [$ret];
                }
                return $ret;
            }, $array)
        );
    }

    /**
     * Tests that every element of the array passes the test implemented by the given `$fn` callable.
     *
     * @param array $array An array.
     * @param callable $fn A callable receiving the element and the key of the element in the array which should return TRUE
     *                     in order to continue to the next element or FALSE to stop the iteration.
     * @return bool TRUE if and only if all the test implemented in by the given `$fn` callback has returned TRUE for all the elements,
     *              otherwise FALSE if at least for one element the test is falsy.
     */
    public static function arrayEvery($array, callable $fn) {
        foreach ($array as $key => $element) {
            if (!$fn($element, $key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns a function which, if called, returns the value of the callable given as argument.
     *
     * @param callable $fn A callable.
     * @return callable The anonymous function returning the value of the given callable.
     */
    public static function fnReturn(callable $fn) {
        return function () use (&$fn) {
            return $fn();
        };
    }

    /**
     * Returns a function which, if called, returns an instance of the class given as argument,
     * with the optional `$constructorParams` passed to its constructor.
     *
     * @param string $class The class to instantiate (a string representing the fully qualified name of the class).
     * @param mixed[] ...$constructorParams Eventual constructor parameters.
     * @return callable The anonymous function returning the instance of the given class.
     */
    public static function fnReturnNew($class, ...$constructorParams) {
        return function () use ($class, $constructorParams) {
            return new $class(...$constructorParams);
        };
    }
}
