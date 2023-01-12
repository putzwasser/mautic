<?php

namespace Mautic\CoreBundle\Tests\Twig;

use Mautic\CoreBundle\Templating\Twig\Extension\AppExtension;
use Mautic\CoreBundle\Templating\Twig\Extension\ClassExtension;
use Twig\Extension\ExtensionInterface;

/**
 * @see https://twig.symfony.com/doc/2.x/advanced.html#functional-tests
 */
class TwigIntegrationTest extends \Twig\Test\IntegrationTestCase
{
    /**
     * @return ExtensionInterface[]
     */
    public function getExtensions(): array
    {
        return [
            new ClassExtension(),
            new AppExtension(),
        ];
    }

    public function getFixturesDir()
    {
        return __DIR__.'/Fixtures/';
    }
}
