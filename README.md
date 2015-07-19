Soflomo\Purifier
===
Soflomo\Purifier is the [HTMLPurifier](http://htmlpurifier.org/) integration for Zend Framework 2. It provides a `Zend\Filter\FilterInterface` instance so you can use Soflomo\Purifier directly to filter your html input with your `Zend\InputFilter` classes. Furthermore, a view helper is provided to help purifying html on the fly in view scripts.

Installation
---
`Soflomo\Purifier` is available through composer. Add "soflomo/purifier" to your composer.json list. You can specify the latest stable version of `Soflomo\Purifier`:

```
"soflomo/purifier": ">=0.1.0"
```

To enable the module in your `config/application.config.php` file, add an entry `Soflomo\Purifier` to the list of enabled modules.

Usage
---
In your input filter configuration, add the `htmlpurifier` filter to filter the result. An example form:

```php
namespace Application\Form;

use Zend\InputFilter;
use Zend\Form\Form;

class Foo extends Form implements
    InputFilter\InputFilterProviderInterface
{
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->add(array(
            'name'    => 'title',
            'options' => array(
                'label' => 'Title'
            ),
        ));

        $this->add(array(
            'name'    => 'text',
            'options' => array(
                'label' => 'Text'
            ),
            'attributes' => array(
                'type'  => 'textarea',
            ),
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'title' => array(
                'required' => true,
                'filters'  => array(
                    array('name' => 'stringtrim'),
                ),
            ),
            'text'  => array(
                'required' => true,
                'filters'  => array(
                    array('name' => 'stringtrim'),
                    array('name' => 'htmlpurifier'),
                ),
            ),
        );
    }
}
```

If your form has the filter plugin manager injected, this should work out-of-the-box. If not, please read [how to inject the filter plugin manager](#inject-filter-manager).

When you are not able to filter the html when it is inserted into the database, you might want to filter the html in your view. Please be aware HTMLPurifier is not a very fast library and as such, filtering on every request can be a significant performance bottleneck. Be advised to use a caching mechanism to cache the output of the filtered html. The view helper is available under the key `htmlPurifier`:

```php
<?php echo $this->htmlPurifier()->purify($foo->getText())?>
```

And there is a shorthand available too:

```php
<?php echo $this->htmlPurifier($foo->getText())?>
```

Inject Filter Manager
---
If you instantiate your form with `new FormClass`, the plugin manager for filters is not injected. As such, you get a `ServiceNotFoundException`: "Zend\Filter\FilterPluginManager::get was unable to fetch or create an instance for htmlpurifier". This means the filter plugin manager does not know about the `htmlpurifier` plugin.

You can fix this by grabbing the filter plugin manager from the Service Manager and inject it into the form manually:

```php
use Zend\Filter\FilterChain;

$form    = new MyForm;

// $sl is the service locator
$plugins = $sl->get('FilterManager');
$chain   = new FilterChain;
$chain->setPluginManager($plugins);

$form->getFormFactory()->getInputFilterFactory()->setDefaultFilterChain($chain);
```

The library [Soflomo\Common](https://github.com/Soflomo/Common) has a utility class to inject the filter manager. If you require `soflomo\common` via composer, you can replace above code with this line:

```php
use Soflomo\Common\Form\FormUtils;

$form = new MyForm;

// $sl is the service locator
FormUtils::injectFilterPluginManager($form, $sl);
```

Speed up performance
---
HTMLPurifier is not the fastest library, but it is the most secure one to filter html in php. By default, HTMLPurifier uses a large number of classes and inclusion of all the files slows down performance during autoloading. You can [create a standalone version](http://htmlpurifier.org/live/INSTALL) of the HTMLPurifier class, where a single file contains most of the classes.

The script in `vendor/bin/purifier-generate-standalone` generates this file for you. The standalone file is created inside `vendor/ezyang/htmlpurifier/library` (there is nothing to configure about that) so make sure you can write in that directory. The Soflomo\Purifier library helps you to use this standalone version by the configuration option `soflomo_prototype.standalone`. In your `config/autoload/local.php`:

```php
return array(
    'soflomo_purifier' => array(
        'standalone' => true,
    ),
);
```

If your composer does not load the libraries into vendor/, you can update the path to the standalone version too:

```php
return array(
    'soflomo_purifier' => array(
        'standalone'      => true,
        'standalone_path' => 'something-else/ezyang/htmlpurifier/library/HTMLPurifier.standalone.php',
    ),
);
```

Configure HTMLPurifier
---
The HTMLPurifier has a class `HTMLPurifier_Config` where it is possible to configure the purifier rules. Most configuration rules are based on a key/value pair: `$config->set('HTML.Doctype', 'HTML 4.01 Transitional');`. This mapping is copied to Zend Framework 2 configuration files, so you can easily modify HTMLPurifiers configation:

```php
return array(
    'soflomo_purifier' => array(
        'config' => array(
            'HTML.Doctype' => 'HTML 4.01 Transitional'
        ),
    ),
);
```

The module configuration also accepts a `definitions` array to add custom definitions to the filter, as [documented here](http://htmlpurifier.org/docs/enduser-customize.html).

For example:

```php
return array(
    'soflomo_purifier' => array(
        'config' => array(
            'HTML.DefinitionID' => 'my custom definitions',
        ),
        'definitions' => array(
            'HTML' => array(
                'addAttribute' => array(
                    'a', 'target', 'Enum#_blank,_self,_target,_top'
                ),
            ),
        ),
    ),
);
```

This will add a `HTMLPurifier_AttrDef_Enum` definition for the `ŧarget` attribute of the `a` element.
Note that a `HTML.DefinitionID` config key is required to do so.
