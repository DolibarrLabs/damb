DAMB
====

DAMB stands for **D**olibarr **A**dvanced **M**odule **B**uilder, unlike [dolibase](https://github.com/AXeL-dev/dolibase), DAMB don't need any dependencies & integrates with [dolibarr](https://github.com/Dolibarr/dolibarr) as a regular module.

## Features

- Simple & easy to use.
- Integrates with native modules.
- Supports old versions of Dolibarr (starting from 3.8.x).
- Outputs clean & structured code (less boilerplate).
- Brings advanced building options.

## Pros

- **Simplicity over complexity**

DAMB comes with multiple libraries containing simple separated functions to allow an easy integration with the other modules, example:

To include the module library, copy the `module.lib.php` file to your module `lib` folder, then in your module class simply include the library as below:

```php
dol_include_once('mymodule/lib/module.lib.php');
```

Now, you can call any function that you want to use from this library.

**Note**: When using DAMB to create a new module, it will automatically include all the files/libraries required by your module. However, some libraries like the `debug` library still need to be included manually.

- **Flexibility**

In libraries, every function is surrounded by an if condition which verifies its existence before implementing it, so if you are not satisfied with a function behavior, just override it! Example:

To override `print_header` function of the page library, create a `page_overrides.lib.php` file then include it before the page library inclusion like this:

```php
dol_include_once('mymodule/lib/page_overrides.lib.php'); // just before the main lib inclusion
dol_include_once('mymodule/lib/page.lib.php');
```

All you have to do now is to write your new function(s) inside `page_overrides.lib.php`.

**Exception**: This may not work as expected for `module` & `widget` libraries, because dolibarr's default behavior on modules & widgets list pages is to include all the module classes at once, so your override(s) may be overrided by functions having the same name but from other modules.

**Solution**: Just change the overrided function(s) name, example: name your function `my_function_2` instead of `my_function`.

- **Easy debugging**

There is a `debug` library compatible with the [debugbar module](https://github.com/AXeL-dev/dolibarr-debugbar-module) that you can use to easily debug your modules, example:

To print a debug message use:

```php
dol_include_once('mymodule/lib/debug.lib.php');

debug('my message');
```

To measure execution time:

```php
dol_include_once('mymodule/lib/debug.lib.php');

start_time_measure('my_measure', 'Execution time of my code');

// Write some code...

stop_time_measure('my_measure');
```

## Known Cons

- **Libraries files are not centralised**

Instead of that they are duplicated/cloned on each new module you create. This can be considered as a good point as well, since you don't need any additional module, framework or dependency to handle libraries separately.

## Installation

Download the latest release from [Dolistore](https://www.dolistore.com/en/modules/1121-Advanced-Module-Builder.html).

## Documentation

Find the documentation [here](https://axel-dev.github.io/dolibarr-modules-docs/#/modules/damb/doc). You can improve it by sending pull requests to [this repository](https://github.com/AXeL-dev/dolibarr-modules-docs).

## Contributing

Read [contributing guidelines](CONTRIBUTING.md).

## License

DAMB is licensed under the [GPL](LICENSE) license.
