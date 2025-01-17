<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Condition;

use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface ConditionInterface
{
    /**
     * ConditionInterface constructor.
     *
     */
    public function __construct(LoggerInterface $logger);

    /**
     *
     * @return bool
     */
    public function check(
        ConditionDefinitionInterface $conditionDefinition,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    );

    /**
     *
     * @return string
     */
    public function getDbCondition(ConditionDefinitionInterface $conditionDefinition);

    /**
     * @param mixed $setting
     *
     * @return ConditionDefinitionInterface
     */
    public static function createConditionDefinitionFromEditmode($setting);

    /**
     *
     * @return array
     */
    public static function getDataForEditmode(ConditionDefinitionInterface $conditionDefinition);
}
