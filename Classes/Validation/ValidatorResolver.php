<?php

declare(strict_types=1);

namespace Portrino\PxValidation\Validation;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\NoServerRequestGivenException;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;

/**
 * Suppresses the built-in (attribute-based) base validator conjunction for
 * models that are flagged via TypoScript. This is the agnostic, trait-free
 * counterpart to "overwriteDefaultValidation": instead of replacing the
 * conjunction per controller action argument (which needs controller code),
 * built-in validation is switched off per model class.
 *
 *   plugin.tx_pxvalidation.settings {
 *       suppressBaseValidation {
 *           Vendor\Ext\Domain\Model\Foo = 1
 *       }
 *   }
 *
 * The base conjunction is produced per data type in getBaseValidatorConjunction()
 * and consumed by ActionController::initializeActionMethodValidators(), so this
 * works transparently for any controller.
 */
class ValidatorResolver extends \TYPO3\CMS\Extbase\Validation\ValidatorResolver
{
    public function getBaseValidatorConjunction(
        string $targetClassName,
        ?ServerRequestInterface $request = null
    ): ConjunctionValidator {
        if ($this->shouldSuppressBaseValidation($targetClassName)) {
            // empty conjunction -> the model's own #[Validate] attributes are not applied;
            // only the TypoScript driven validators (injected per argument) remain.
            return GeneralUtility::makeInstance(ConjunctionValidator::class);
        }

        return parent::getBaseValidatorConjunction($targetClassName, $request);
    }

    private function shouldSuppressBaseValidation(string $targetClassName): bool
    {
        $suppress = $this->getSettings()['suppressBaseValidation'] ?? null;
        if (!is_array($suppress)) {
            return false;
        }

        $needle = ltrim($targetClassName, '\\');
        foreach ($suppress as $className => $flag) {
            if (ltrim((string)$className, '\\') === $needle) {
                return filter_var($flag, FILTER_VALIDATE_BOOLEAN);
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function getSettings(): array
    {
        try {
            return GeneralUtility::makeInstance(ConfigurationManagerInterface::class)->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'PxValidation'
            );
        } catch (NoServerRequestGivenException) {
            return [];
        }
    }
}
