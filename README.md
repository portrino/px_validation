# px_validation 1.1.1
Extbase Validation via Typoscript

The **PxValidation** extension enables the possibility to define different validation configuration in
your TypoScript for each **Extbase-Controller-Action** without touching the affected extension itself. This makes 
it easy to change the default validation behaviour of vendor extensions without changing their code. But the greatest 
benefit is that it opens the option to declare multiple variants of validation rules within one page tree.
It is even possible to nest validation rules, so you can validate child objects.

Before you start: __Include Static Template Files!__

### Example:
##### PHP:
<pre>
<code class="php">
namespace VendorName\ExtensionName\Controller;

class FooController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
    
    /**
     * action create
     *
     *
     * @param \VendorName\ExtensionName\Domain\Model\FooBar $fooBar
     * @return void
     */
    public function createAction(\VendorName\ExtensionName\Domain\Model\FooBar $fooBar) {
        ...       
    }
}
</code>
</pre>

##### TypoScript:
<pre>
<code class="typoscript">
plugin.tx_pxvalidation.settings {
    VendorName\ExtensionName\Controller\FooController {
        createAction {
            fooBar {
                overwriteDefaultValidation = 1 # validation rules defined in the property, model or controller are NOT executed
                objectValidators {
                    0 = VendorName\ExtensionName\Domain\Validator\FooValidator(firstOption=value1, secondOption=123456)
                    1 = VendorName\ExtensionName\Domain\Validator\BarValidator(firstOption=value1)
                }
                propertyValidators {
                    foo {
                        0 = NotEmpty
                    }
                    bar {
                        0 = NotEmpty
                        1 = StringLength(minimum=3)
                        2 = VendorName\ExtensionName\Domain\Validator\CustomValidator(firstOption=value1)
                    }
                    childObject {
                        propertyValidators {
                            subProperty1 {
                                0 = NotEmpty
                            }
                            subProperty2 {
                                0 = NotEmpty
                            }
                        }
                    }
                }
            }
        }
    }
}
</code>
</pre>