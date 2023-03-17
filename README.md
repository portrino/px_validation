# TYPO3 Extension `px_validation`

[![TYPO3 11](https://img.shields.io/badge/TYPO3-11-orange.svg)](https://get.typo3.org/version/10)
[![Latest Stable Version](https://poser.pugx.org/portrino/px_validation/v/stable)](https://packagist.org/packages/portrino/px_validation)
[![Monthly Downloads](https://poser.pugx.org/portrino/px_validation/d/monthly)](https://packagist.org/packages/portrino/px_validation)
[![License](https://poser.pugx.org/portrino/px_validation/license)](https://packagist.org/packages/portrino/px_validation)

> Extbase validation controlled/ overruled via TypoScript for TYPO3 extensions

## 1 Features

The **PxValidation** extension enables the possibility to define different validation configuration in
your TypoScript for each **Extbase-Controller-Action** without touching the affected extension itself. This makes 
it easy to change the default validation behaviour of vendor extensions without changing their code. But the greatest 
benefit is that it opens the option to declare multiple variants of validation rules within one page tree.
It is even possible to nest validation rules, so you can validate child objects.

* [Comprehensive documentation][1]

## 2 Usage

### 2.1 Installation

#### Installation using Composer

The **recommended** way to install the extension is using [Composer][2].

Run the following command within your Composer based TYPO3 project:

```
composer require portrino/px_validation
```

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install the [extension][3] with the extension manager module.

### 2.2 Setup

1) Include the static TypoScript of the extension.
2) Create some TypoScript in your e.g. "site_package" extension to override the validation rules of any other extension
   1) See example below:

#### Example:
##### PHP:
<pre>
<code class="php">
namespace VendorName\ExtensionName\Controller;

class FooController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
    
    /**
     * action create
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
        actionMethodName {
            fooBar {
                # (default 0) if 1, then the validation rules defined in the property, model or controller are NOT executed
                overwriteDefaultValidation = 1
                objectValidators {
                    0 = @TYPO3\CMS\Extbase\Annotation\Validate("VendorName\ExtensionName\Domain\Validator\FooValidator", options={"firstOption": value1, "secondOption": 123456})
                    1 = @TYPO3\CMS\Extbase\Annotation\Validate("VendorName\ExtensionName\Domain\Validator\BarValidator", options={"firstOption": value1})
                }
                propertyValidators {
                    foo {
                        0 = @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
                    }
                    bar {
                        0 = @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
                        1 = @TYPO3\CMS\Extbase\Annotation\Validate("StringLength", options={"minimum": 3, "maximum": 50})
                        2 = @TYPO3\CMS\Extbase\Annotation\Validate("VendorName\ExtensionName\Domain\Validator\CustomValidator", options={"firstOption": value1})
                    }
                    childObject {
                        propertyValidators {
                            subProperty1 {
                                0 = @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
                            }
                            subProperty2 {
                                0 = @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
                            }
                            #...
                        }
                    }
                }
            }
        }
    }
}
</code>
</pre>

## 3 Administration corner

### 3.1 Changelog

Please look into the [CHANGELOG file in the extension][4].

### 3.2 Release Management

**PxValidation** uses [**semantic versioning**][5], which means, that
* **bugfix updates** (e.g. 1.0.0 => 1.0.1) just includes small bugfixes or security relevant stuff without breaking changes,
* **minor updates** (e.g. 1.0.0 => 1.1.0) includes new features and smaller tasks without breaking changes,
* and **major updates** (e.g. 1.0.0 => 2.0.0) breaking changes wich can be refactorings, features or bugfixes.

| PxValidation | TYPO3      | PHP       | Support / Development                |
|--------------|------------|-----------|--------------------------------------|
| 3.0.x        | 11.5       | 7.4 - 8.2 | features, bugfixes, security updates |
| 2.0.x        | 9.5 - 10.4 | 7.2 - 7.4 | bugfixes, security updates           |
| 1.2.x        | 9.5        | 7.2 - 7.4 | none                                 |
| 1.1.x        | 6.2 - 8.7  | 5.5 - 7.4 | none                                 |

### 3.3 Contribution

**Pull Requests** are gladly welcome! Nevertheless, please don't forget to add an issue and connect it to your pull 
requests. This is very helpful to understand what kind of issue the **PR** is going to solve.

Bugfixes: Please describe what kind of bug your fix solve and give us feedback how to reproduce the issue. We're going
to accept only bugfixes if we can reproduce the issue.

## 4 Authors

* See the list of [contributors](https://github.com/portrino/px_dbsequencer/graphs/contributors) who participated in this project.

---

[1]: https://docs.typo3.org/typo3cms/extensions/px_validation/
[2]: https://getcomposer.org/
[3]: https://extensions.typo3.org/extension/px_validation
[4]: https://github.com/portrino/px_validation/blob/master/CHANGELOG.md
[5]: https://semver.org/
