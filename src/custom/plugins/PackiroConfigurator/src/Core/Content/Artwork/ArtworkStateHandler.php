<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Artwork;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use Shopware\Core\System\StateMachine\Exception\IllegalTransitionException;
use Shopware\Core\System\StateMachine\Exception\StateMachineInvalidEntityIdException;
use Shopware\Core\System\StateMachine\Exception\StateMachineInvalidStateFieldException;
use Shopware\Core\System\StateMachine\Exception\StateMachineNotFoundException;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;

class ArtworkStateHandler
{
    public function __construct(
        private StateMachineRegistry $stateMachineRegistry
    ) {
    }

    /**
     * @throws InconsistentCriteriaIdsException
     * @throws StateMachineNotFoundException
     * @throws IllegalTransitionException
     * @throws StateMachineInvalidEntityIdException
     * @throws StateMachineInvalidStateFieldException
     */
    public function reopen(string $artworkId, Context $context): void
    {
        $this->stateMachineRegistry->transition(
            new Transition(
                ArtworkDefinition::ENTITY_NAME,
                $artworkId,
                StateMachineTransitionActions::ACTION_REOPEN,
                'stateId'
            ),
            $context
        );
    }
}
