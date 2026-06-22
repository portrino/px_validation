<?php

declare(strict_types=1);

namespace Portrino\PxValidation\Reflection;

use TYPO3\CMS\Extbase\Reflection\ClassSchema as CoreClassSchema;
use TYPO3\CMS\Extbase\Reflection\Exception\UnknownClassException;

/**
 * Replaces the Extbase ReflectionService so that every class schema it builds is
 * a px_validation ClassSchema. Registered as an XClass in ext_localconf.php.
 *
 * Only buildClassSchema() is overridden; everything else (caching, serialization)
 * is inherited unchanged.
 */
class ReflectionService extends \TYPO3\CMS\Extbase\Reflection\ReflectionService
{
    protected function buildClassSchema($className): CoreClassSchema
    {
        try {
            $classSchema = new ClassSchema($className);
        } catch (\ReflectionException $e) {
            throw new UnknownClassException($e->getMessage() . '. Reflection failed.', 1782113158, $e);
        }

        $this->classSchemata[$className] = $classSchema;
        $this->dataCacheNeedsUpdate = true;

        return $classSchema;
    }
}
