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
 * Class MissingStateConfigurationException
 *
 * @author  Steven Nance <steven@devtrw.com>
 * @package Repx\TrackBundle\Exception
 */
class MissingStateConfigurationException extends InvalidConfigurationException
{
    /**
     * @param string   $missingConfigKey
     * @param array<string,array>  $actualConfiguration
     */
    public function __construct($missingConfigKey, array $actualConfiguration)
    {
        $message = sprintf(
            'The requested state is missing an expected configuration value for the ' .
            'config parameter "%s". The configured values were: %s',
            $missingConfigKey,
            implode(', ', array_keys($actualConfiguration))
        );
        parent::__construct($message);
    }
}
