<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Artwork\Subscriber;

use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\StateMachine\Loader\InitialStateIdLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ArtworkSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        private InitialStateIdLoader $initialStateIdLoader
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [];
    }
}
