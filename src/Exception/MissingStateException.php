<?php
/**
 * Copyright (c) Steven Nance <steven@devtrw.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Steven Nance <steven@devtrw.com>
 */
namespace Devtrw\StateBridgeBundle\Exception;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class MissingStateException
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Repx\TrackBundle\Exception
 */
class MissingStateException extends InvalidConfigurationException
{
    /**
     * @param string   $requestedState
     * @param string[] $availableStates
     */
    public function __construct($requestedState, array $availableStates)
    {
        $message = sprintf(
            'The requested state "%s" does not exist. Configured states are: %s',
            $requestedState,
            implode(', ', $availableStates)
        );
        parent::__construct($message);
    }
}
