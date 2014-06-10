<?php
/* Copyright (c) Steven Nance <steven@devtrw.com> - All Rights Reserved
 *
 * Unauthorized reproduction of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\StateBridgeBundle\Tests\DependencyInjection;

use Devtrw\StateBridgeBundle\DependencyInjection\Configuration;
use Devtrw\StateBridgeBundle\DependencyInjection\DevtrwStateBridgeExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;


/**
 * Class MenuConfigurationTest
 *
 * @author Steven Nance <steven@devtrw.com>
 * @package Devtrw\TrackBundle\Tests\Menu
 */
class DevtrwStateBridgeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var ContainerBuilder
     */
    protected $container;

    public function setup()
    {
        $testConfig      = Yaml::parse(file_get_contents(__DIR__.'/../fixtures/states.yml'));
        $this->container = new ContainerBuilder();
        $extension       = new DevtrwStateBridgeExtension();
        $extension->load($testConfig, $this->container);
    }

    public function testRoutePrefixProcessing()
    {
        $parsed               = $this->container->getParameter('devtrw_states')['primary'];
        $parsedSubmenu        = $parsed['children']['primary_3_child_state'];
        $parsedSubitemSubmenu = $parsedSubmenu['children']['child_state_2'];

        $this->assertArrayNotHasKey(
            'route_prefix',
            $parsed,
            'The route prefix should be removed from the parsed config after being appended to the routes.'
        );
        $this->assertArrayNotHasKey(
            'route_prefix',
            $parsedSubmenu,
            'The route prefix should be removed from the parsed config of first level sub-menus.'
        );
        $this->assertArrayNotHasKey(
            'route_prefix',
            $parsedSubitemSubmenu,
            'The route prefix should be removed from the parsed config of second level sub-menus.'
        );

        $this->assertEquals(
            'primary_state_one',
            $parsed['children']['primary_1']['route'],
            'The route prefix is not properly being prefixed to routes on the base level'
        );
        $this->assertEquals(
            'primary_state_two',
            $parsed['children']['primary_2']['route'],
            'The route prefix is not properly being prefixed to routes on the base level'
        );
        $this->assertEquals(
            'primary_state_three_child_state_one',
            $parsedSubmenu['children']['child_state_1']['route'],
            'The route prefix is not properly being prefixed to routes on the first level sub menus'
        );
        $this->assertEquals(
            'primary_state_three_child_state_two_child_state_one',
            $parsedSubitemSubmenu['children']['child_state_1']['route'],
            'The route prefix is not properly being prefixed to routes on the second level sub menus'
        );
    }
}
