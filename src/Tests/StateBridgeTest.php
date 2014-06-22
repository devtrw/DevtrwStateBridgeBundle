<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com> - All Rights Reserved
 * Unauthorized reproduction of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\StateBridgeBundle\Tests;

use Devtrw\StateBridgeBundle\DependencyInjection\DevtrwStateBridgeExtension;
use Devtrw\StateBridgeBundle\Exception\MissingStateConfigurationException;
use Devtrw\StateBridgeBundle\StateBridge;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\Serializer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class StateBridgeTest
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\StateBridgeBundle\Tests
 */
class StateBridgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Array
     */
    private static $stateConfigFixture;

    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $omMock;
    /**
     * @var Serializer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    public static function setupBeforeClass()
    {
        $config = Yaml::parse(file_get_contents(__DIR__ . '/fixtures/states.yml'));
        $container = new ContainerBuilder();
        $extension = new DevtrwStateBridgeExtension();
        $extension->load($config, $container);
        self::$stateConfigFixture = $container->getParameter('devtrw_state_bridge.states');
    }

    public function setUp()
    {
        $this->omMock         = $this->getMock(ObjectManager::class);
        $this->serializerMock = $this->getMockBuilder(Serializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize'])
            ->getMock()
        ;
    }

    private function createStateBridge()
    {
        $securityMock = $this->getMock(SecurityContextInterface::class);

        return new StateBridge(
            self::$stateConfigFixture,
            $securityMock,
            $this->serializerMock,
            $this->omMock
        );
    }

    public function testExportedStateIsFormattedCorrectly()
    {
        $bridge       = $this->createStateBridge();
        $primaryState = $bridge->getBridgedState('primary');

        $this->assertEquals(
            true,
            $primaryState['abstract'],
            'The abstract property should pass through to the resulting config'
        );
        $this->assertEquals(
            false,
            $primaryState['static'],
            'The static property should default to false be included in the state'
        );
        $this->assertEquals(
            'primary',
            $primaryState['name'],
            'The state names should be set as the property "name".'
        );
        $this->assertCount(
            3,
            $primaryState['children'],
            'All children should be present when there is no access restriction.'
        );

        $childStateWithChildren = $primaryState['children'][2];
        $this->assertEquals(false, $childStateWithChildren['abstract'], 'The abstract property should be included in the state');
        $this->assertEquals(true, $childStateWithChildren['static'], 'The static property should be included in the state');
        $this->assertEquals(
            'primary_3_child_state',
            $childStateWithChildren['name'],
            'The state names should be set as the property "name".'
        );
        $this->assertCount(
            2,
            $childStateWithChildren['children'],
            'All children should be present when there is no access restriction.'
        );

        $this->assertEquals(
            'child_state_1',
            $childStateWithChildren['children'][0]['name'],
            'Children of other children should be converted from key => value pairs the same way as
            root elements'
        );
        $this->assertEquals(
            'child_state_2',
            $childStateWithChildren['children'][1]['name'],
            'Children of other children should be converted from key => value pairs the same way as
            root elements'
        );
    }

    public function testExceptionRaisedWhenAssociatedEntityNotConfiguredAndEntityIdPassedIn()
    {
        $this->setExpectedException(MissingStateConfigurationException::class);
        $bridge = $this->createStateBridge();
        $bridge->getBridgedState('primary', 1234);
    }

    public function testBridgedStateWithAssociatedEntity()
    {
        $entityId  = 123;
        $stateName = 'with_entity';
        $expectedSerializedEntity = ['a' => 'serialized', 'entity'];

        // So we don't have to create a fixture/mock for the entity we
        // just return $this from the object manager
        $this->omMock
            ->expects($this->once())
            ->method('find')
            ->with('Devtrw\SomeBundle\Entity\SomeEntity', $entityId)
            ->will($this->returnValue($this))
        ;
        $this->serializerMock
            ->expects($this->once())
            ->method('serialize')
            ->with($this)
            ->will($this->returnValue(json_encode($expectedSerializedEntity)))
        ;

        $bridge       = $this->createStateBridge();
        $companyState = $bridge->getBridgedState($stateName, $entityId);
        $this->assertEquals(
            $stateName . '_' . $entityId,
            $companyState['name'],
            'states resolved with an ID should have the id attribute appended to the state name'
        );
        $this->assertArrayHasKey(
            'entity',
            $companyState,
            'states resolved with an ID should return the associated entity details'
        );
        $this->assertSame(
            $expectedSerializedEntity,
            $companyState['entity'],
            'The entity returned from the object manager should serialized to an array and be set in the entity field'
        );
    }
}
