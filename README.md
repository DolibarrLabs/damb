DAMB
====

DAMB stands for **D**olibarr **A**dvanced **M**odule **B**uilder, unlike [dolibase](https://github.com/AXeL-dev/dolibase), DAMB don't need any dependencies & integrates with [dolibarr](https://github.com/Dolibarr/dolibarr) as a regular module.

### Advantages

- Easily integrates with native modules.
- Outputs clean & structured code.
- Brings advanced building tools.

### Features

#### 1) Simplicity over complexity

DAMB comes with multiple libraries & functions cut into small pieces/files to allow an easy integration with the other modules, example:

To include the `module` library into a module class simply use

```php
dol_include_once('mymodule/lib/module.lib.php');
```

Then call any function that you want to use from this library.

#### 2) Flexibility

In libraries, every function is surrounded by an if condition that verifies its existence before implementing it, so if you are not satisfied with a function behavior, just override it! Example:

To override `print_header` function of page library, create a `page_overrides.lib.php` file then include it before the page library inclusion

```php
dol_include_once('mymodule/lib/page_overrides.lib.php'); // just before the main lib inclusion
dol_include_once('mymodule/lib/page.lib.php');
```

All you have to do now is to write your new function(s) inside `page_overrides.lib.php`.

**Exception**: For `module` & `widget` libraries, since module classes are all included at once on dolibarr modules page, your override(s) may not work. To fix this, just change the override function(s) name, example: name your function `my_function_2` instead of `my_function`.

#### 3) Easy debugging

There is a `debug` library compatible with the [debugbar module](https://github.com/AXeL-dev/dolibarr-debugbar-module) that you can use to easily debug your modules, example:

To print a debug message use

```php
dol_include_once('mymodule/lib/debug.lib.php');

debug('my message');
```

To measure execution time

```php
dol_include_once('mymodule/lib/debug.lib.php');

start_time_measure('my_measure', 'Execution time of my code');

// Write some code...

stop_time_measure('my_measure');
```

### Installation

Download the latest release from [Dolistore](https://www.dolistore.com/en/modules/1121-Advanced-Module-Builder.html).

### Contributing

Read [contributing guidelines](CONTRIBUTING.md).

### License

DAMB is licensed under the [GPL](LICENSE) license.
