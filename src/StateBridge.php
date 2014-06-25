<?php
/*
 * Copyright (c) Steven Nance <steven@devtrw.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Devtrw\StateBridgeBundle;

use Devtrw\StateBridgeBundle\Exception\MissingStateConfigurationException;
use Devtrw\StateBridgeBundle\Exception\MissingStateException;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\Serializer;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @author Steven Nance <steven@devtrw.com>
 */
class StateBridge
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $security;
    /**
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     * @param array                    $configuration
     * @param SecurityContextInterface $security
     * @param Serializer               $serializer
     * @param ObjectManager            $om
     */
    public function __construct(
        array $configuration,
        SecurityContextInterface $security,
        Serializer $serializer,
        ObjectManager $om
    ) {
        $this->configuration = $configuration;
        $this->security      = $security;
        $this->om            = $om;
        $this->serializer    = $serializer;
    }

    /**
     * Constructs the state bridge from the processed configuration file.
     *
     * @param string   $stateName
     * @param null|int $entityId        The ID of the entity for which the
     *                                  state is being requested
     *
     * @return Array
     * @author Steven Nance <steven@devtrw.com>
     */
    public function getBridgedState($stateName, $entityId = null)
    {
        $bridgedState         = $this->getStateConfig($stateName);
        $bridgedState['name'] = $stateName;

        $this->processEntityDetails($entityId, $bridgedState);

        return $this->processChildElements($bridgedState);
    }

    /**
     * Gets the initial state config by name from the processed configuration files
     *
     * @param string $stateName
     *
     * @return array
     * @throws MissingStateException
     * @author Steven Nance <steven@devtrw.com>
     */
    protected function getStateConfig($stateName)
    {
        if (!array_key_exists($stateName, $this->configuration)) {
            throw new MissingStateException($stateName, array_keys($this->configuration));
        }

        return $this->configuration[$stateName];
    }

    /**
     * Recursively format the state array tree
     *
     * @param array<array> $bridgedState
     *
     * @return array<array>
     *
     * @author Steven Nance <steven@devtrw.com>
     */
    protected function processChildElements(array &$bridgedState)
    {
        if (true === empty($bridgedState['children'])) {
            return $bridgedState;
        }

        /**
         * Move array keys into "key" attribute of config which greatly simplifies processing outside of PHP
         * It's escaping me but I'm pretty sure you can do this in one line with some combination of
         * array_flip/array_keys
         */
        $initialChildren          = $bridgedState['children'];
        $bridgedState['children'] = [];
        foreach ($initialChildren as $name => $config) {
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

    /**
     * @param int   $entityId
     * @param array &$stateConfig
     *
     * @throws MissingStateConfigurationException
     * @author Steven Nance <steven@devtrw.com>
     */
    protected function processEntityDetails($entityId, array &$stateConfig)
    {
        if (null === $entityId) {
            return;
        } else {
            if (null === $stateConfig['entity']) {
                throw new MissingStateConfigurationException('entity', $stateConfig);
            }
        }

        $entity                = $this->om->find($stateConfig['entity'], $entityId);
        $stateConfig['name']   = $stateConfig['name'] . '_' . $entityId;
        $stateConfig['entity'] = $this->serializeEntityToArray($entity);
    }

    /**
     * Serializes the entity to json using the JMS serializer. Then converts it to an associative array using
     * json_decode. The dual conversion is necessary since the JMS Serializer does not currently support serializing
     * to an array and we don't want to nest a json string within the output response.
     *
     * @param Object $entity
     *
     * @return array
     * @author Steven Nance <steven@devtrw.com>
     */
    protected function serializeEntityToArray($entity)
    {
        $asJson = $this->serializer->serialize($entity, 'json');

        return json_decode($asJson, true);
    }
}
