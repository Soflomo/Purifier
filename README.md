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

If your form has the filter plugin manager injected, this should work out-of-the-box. If now, please read [how to inject the filter plugin manager](#inject-filter-manager).

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

