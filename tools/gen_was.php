#!/usr/bin/env php
<?php
/**
 * MIT License
 *
 * Copyright (c) Shannon Pekary spekary@gmail.com
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Command line script to recursively processes the given directory and outputs a PHP array
 * to send to the run_sed command that will help convert QCubed 3.x code to QCubed 4.0 code.
 * I originally tried to do this with sed for speed, but sed works differently on different OS's, so I am doing
 * it in PHP for consistency across OS's.
 *
 * Specifically this does the following:
 * - Hunts for @was docblock comments and makes a substituion for the new namespaced version of the function.
 * - Hunts for constants, and makes a substitution for a PascalCase version of the constant to the new constant.
 * - Outputs a var_dump format that can recreate the array of substitutions
 *
 * Use the resulting script carefully. Be sure to backup previous versions and compare changes before accepting them.
 *
 * The resulting script will be sent to stdout, so its expected you capture stdout to save it.
 *
 * Usage: gen_was dir > filename
 */

if (count($argv) > 2) {
    echo 'Usage: gen_was dir';
}

$__CONFIG_ONLY__ = true;
include 'qcubed.inc.php';
$loader = require dirname(dirname(dirname(__DIR__))) . '/autoload.php'; // load superclasses
$loader->addPsr4('QCubed\\', dirname(dirname(__DIR__)) . '/common/src'); // make sure common is included
$loader->addPsr4('QCubed\\Project\\',QCUBED_PROJECT_DIR . '/qcubed'); // make sure user side codegen is included
$loader->addPsr4('QCubed\\', dirname(__DIR__) . '/src'); // make sure I am included

// convert upper case name to camel name
function CamelName($uname) {
    $pieces = explode('_', $uname);

    $ret = ucfirst(strtolower(array_shift($pieces)));
    foreach($pieces as $piece) {
        $ret .= ucfirst(strtolower($piece));
    }
    return $ret;
}

$path = realpath($argv[1]);

$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
$filter = new RegexIterator($objects, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$classes = [];

foreach($filter as $name => $object) {
    // Use file name as class name
    $classes[basename($name, '.php')] = true;
    require_once ($name);
}

$a = get_declared_classes();    // get classes with namespace

// Filter list by the classes we care about;
$a = array_filter($a, function($val) use ($classes) {
   $endsWith = substr($val, strrpos($val, '\\') + 1);
   //echo $endsWith . "\n";;
   return (isset($classes[$endsWith]));
});

// $a now contains the full namespaced class names of everything in the files
echo ('<?php' . "\n");
foreach ($a as $fullClassName) {
    $rc = new ReflectionClass($fullClassName);
    $comment = $rc->getDocComment();
    $slashedClassName = addslashes($fullClassName);


    if (preg_match('/@was (\w+)/', $comment, $matches)) {
        $was = $matches[1];
        // use nowdoc to make an easy to see pattern
        // match a non-word letter, followed by an optional namespace specifier, followed by the class name, followed by a non-word letter


        echo '$a["class"][\'' . $was . '\'] = \'\\\\';


        echo $slashedClassName;
        echo "';\n";
    }

    // Fix up uses of constants, assuming the new constants are correctly UPPER_CASED
    $constants = $rc->getConstants();
    foreach ($constants as $constName=>$constValue) {
        $camelName = CamelName($constName);
        $pattern = $slashedClassName . '::' . $camelName;
        echo '$a["const"]["' . $pattern . '"] = "';
        echo $slashedClassName . '::' . $constName;
        echo "\";\n";
    }
}

echo 'return $a;' . "\n";
