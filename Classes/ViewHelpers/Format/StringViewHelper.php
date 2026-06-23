<?php

namespace Portrino\PxValidation\ViewHelpers\Format;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Formats a string by passing different methods as command parameter
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <r:format.string format="camelCaseToLowerCaseUnderscored">CamelCaseToLowerUnderScored</r:format.string>
 * </code>
 * <output>
 * camel_case_to_lower_under_scored
 * </output>
 *
 * <code title="Defaults">
 * <r:format.string format="camelCaseToLowerCaseHyphenated">CamelCaseToLowerHyphenated</r:format.string>
 * </code>
 * <output>
 * camel-case-to-lower-hyphenated
 * </output>
 */
class StringViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('format', 'string', 'the format in to which should be converted', true);
    }

    /**
     * @return string the formatted string
     */
    public function render(): string
    {
        if (!isset($this->arguments['format'])) {
            return '';
        }
        $content = $this->renderChildren();
        switch ($this->arguments['format']) {
            case 'camelCaseToLowerCaseUnderscored':
                $content = str_replace(
                    ['\\'],
                    ['.'],
                    GeneralUtility::camelCaseToLowerCaseUnderscored($content)
                );
                break;
            case 'camelCaseToLowerCaseHyphenated':
                $content = str_replace(
                    ['_', '\\'],
                    ['-', ' '],
                    GeneralUtility::camelCaseToLowerCaseUnderscored($content)
                );
                break;
            case 'underscoredToLowerCamelCase':
                $content = GeneralUtility::underscoredToLowerCamelCase($content);
                break;
            case 'underscoredToUpperCamelCase':
                $content = GeneralUtility::underscoredToUpperCamelCase($content);
                break;
            case 'lcfirst':
                trigger_error(
                    'Please use TYPO3\CMS\Fluid\ViewHelpers\Format\CaseViewHelper instead,' .
                    'e.g.: {foo -> f:format.case(mode: \'uncapital\')}',
                    E_USER_DEPRECATED
                );
                break;
            case 'strtolower':
                trigger_error(
                    'Please use TYPO3\CMS\Fluid\ViewHelpers\Format\CaseViewHelper instead,' .
                    'e.g.: {foo -> f:format.case(mode: \'lower\')}',
                    E_USER_DEPRECATED
                );
                break;
            case 'strtoupper':
                trigger_error(
                    'Please use TYPO3\CMS\Fluid\ViewHelpers\Format\CaseViewHelper instead,' .
                    'e.g.: {foo -> f:format.case(mode: \'upper\')}',
                    E_USER_DEPRECATED
                );
                break;
            case 'removePropertyResultsTempKey':
                // this is useful when you want to remove temporary keys from the property path
                // in the validation results array containing
                // irre/ child properties
                // (e.g. questions.000000000245747c000000007da51573.answers.0000000002457457000000007da51573.title
                // turns into questions.answers.title)
                $content = preg_replace('/\w{32}\./i', '', $content);
                break;
        }
        return $content;
    }
}
