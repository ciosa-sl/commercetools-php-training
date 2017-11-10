<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\TrainingBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class TrainingTestCase extends TestCase
{
    /**
     * @var Container
     */
    protected $container;
    private $app;

    public function setUp()
    {
        $this->app = new \AppKernel('test', true);
        $this->app->boot();

        $this->container = $this->app->getContainer();
    }
}
