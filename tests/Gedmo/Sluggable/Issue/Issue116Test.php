<?php

/*
 * This file is part of the Doctrine Behavioral Extensions package.
 * (c) Gediminas Morkevicius <gediminas.morkevicius@gmail.com> http://www.gediminasm.org
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gedmo\Tests\Sluggable;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\Driver\DriverChain;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Tests\Sluggable\Fixture\Issue116\Country;
use Gedmo\Tests\Tool\BaseTestCaseORM;

/**
 * These are tests for Sluggable behavior
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
final class Issue116Test extends BaseTestCaseORM
{
    public const TARGET = Country::class;

    protected function setUp(): void
    {
        parent::setUp();

        $evm = new EventManager();
        $evm->addEventSubscriber(new SluggableListener());

        $this->getMockSqliteEntityManager($evm);
    }

    protected function getMetadataDriverImplementation()
    {
        $chain = new DriverChain();
        $chain->addDriver(
            new YamlDriver([__DIR__.'/../Fixture/Issue116/Mapping']),
            'Gedmo\Tests\Sluggable\Fixture\Issue116'
        );

        return $chain;
    }

    public function testSlugGeneration()
    {
        $country = new Country();
        $country->setOriginalName('New Zealand');

        $this->em->persist($country);
        $this->em->flush();

        static::assertSame('new-zealand', $country->getAlias());
    }

    protected function getUsedEntityFixtures()
    {
        return [
            self::TARGET,
        ];
    }
}
