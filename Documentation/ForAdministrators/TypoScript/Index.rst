.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt



.. _typoscript-configuration:

TypoScript Configuration
^^^^^^^^^^^^^^^^^^^^^^^^

Include HybridAuth in Template
''''''''''''''''''''''''''''''

Please include the static template of *PxValidation* either through an include or through the general options.

| To include the PxValidation TS directly in your TS please use the following code:
| ``<INCLUDE_TYPOSCRIPT: source="FILE:EXT:px_validation/Configuration/TypoScript/setup.txt">``

TypoScript values
'''''''''''''''''

The typoScript keys are defined by the controller, action and argument tripel.

===============================================================================  ===========  ===========================================================================================  =======
TypoScript value                                                                 Data type    Description                                                                                  Default
===============================================================================  ===========  ===========================================================================================  =======
settings.[controllerName]                                                        string       Name (**namespace syntax!**) of the controller class which should be validate
settings.[controllerName].[actionMethodName]                                     string       Name of the action method which should be validate
settings.[controllerName].[actionMethodName].[argumentName]                      string       Name of the argument which should be validate
settings.[controllerName].[actionMethodName].[argumentName].objectValidators     array        List of ObjectValidators
settings.[controllerName].[actionMethodName].[argumentName].propertyValidators   array        List of PropertyValidators
===============================================================================  ===========  ===========================================================================================  =======

Schema
~~~~~~

::

    plugin.tx_pxvalidation.settings {
        controllerName {
            actionMethodName {
                argumentName {
                    objectValidators {
                        0 = VendorName\ExtensionName\Domain\Validator\CustomObject1Validator
                        1 = VendorName\ExtensionName\Domain\Validator\CustomObject2Validator(firstOption=optionValue) # with one option
                        2 = VendorName\ExtensionName\Domain\Validator\CustomObjectt3Validator(firstOption=optionValue, secondOption=optionValue) # with multiple options
                    }
                    propertyValidators {
                        propertyName1 {
                            0 = ExtbaseValidator
                        }
                        propertyName2 {
                            0 = ExtbaseValidator # extbase default validators / shorthand syntax
                            1 = ExtbaseValidatorWithOption(firstOption=optionValue, secondOption=optionValue) # extbase default validators with options
                            2 = VendorName\ExtensionName\Domain\Validator\CustomPropertyValidator(firstOption=optionValue) # custom property validator
                        }
                        propertyName3 {
                            0 = ExtbaseValidator1, ExtbaseValidator2 # comma separated list
                        }
                    }
                }
            }
            actionMethodName2 {
                # ...
            }
        }
        controllerName2 {
            # ...
        }
    }

.. hint::

    You can use the same syntax for validator definitions as you use in the ``@validate`` annotation within domain model classes.

Example
~~~~~~~

::

    plugin.tx_pxvalidation.settings {
        VendorName\ExtensionName\Controller\FooController {
            createAction {
                fooBar {
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
                    }
                }
            }
            editAction {
                # ...
            }
        }
        VendorName\ExtensionName\Controller\BarController {
            # ...
        }
    }

