<?php
/*
 * Copyright (c) Steven Nance <steven@devtrw.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Devtrw\StateBridgeBundle;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @author Steven Nance <steven@devtrw.com>
 */
class StateBridge
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $security;

    /**
     * @param array                    $configuration
     * @param SecurityContextInterface $security
     * @param RequestStack             $requestStack
     */
    public function __construct(array $configuration, SecurityContextInterface $security, RequestStack $requestStack)
    {
        $this->configuration = $configuration;
        $this->security      = $security;
        $this->requestStack  = $requestStack;
    }

    /**
     * Constructs the state bridge from the processed configuration file.
     *
     * @param string $stateName
     *
     * @return Array
     * @author Steven Nance <steven@devtrw.com>
     */
    public function getBridgedState($stateName)
    {
        $bridgedState = $this->getStateConfig($stateName);
        $bridgedState['name'] = $stateName;

        return $this->processChildElements($bridgedState);
    }

    /**
     * Gets the initial state config by name from the processed configuration files
     * @param $stateName
     *
     * @return array
     *
     * @throws MissingMandatoryParametersException
     * TODO Raise exception on invalid state name
     *
     * @author Steven Nance <steven@devtrw.com>
     */
    protected function getStateConfig($stateName)
    {
        if (!array_key_exists($stateName, $this->configuration)) {
            throw new InvalidConfigurationException(
                sprintf(
                    'The requested state "%s" does not exist. Configured states are: ',
                    $stateName,
                    implode(', ', array_keys($this->configuration))
                )
            );
        }
        return $this->configuration[$stateName];
    }

    /**
     * Recursively format the state array tree
     *
     * @param array $bridgedState
     *
     * @return array
     * @author Steven Nance <steven@devtrw.com>
     */
    private function processChildElements(array &$bridgedState)
    {
        if (empty($bridgedState['children'])) {
            return $bridgedState;
        }

        /**
         * Move array keys into "key" attribute of config which greatly simplifies processing outside of PHP
         * It's escaping me but I'm pretty sure you can do this in one line with some combination of
         * array_flip/array_keys
         */
        $initialChildren = $bridgedState['children'];
        $bridgedState['children'] = [];
        foreach($initialChildren as $name => $config) {
            $processedChild = array_merge(
                ['name' => $name],
                $config
            );
            if (0 < count($processedChild['children'])) {
                $this->processChildElements($processedChild);
            }
            $bridgedState['children'][] = $processedChild;
        }

        return $bridgedState;
    }
}
