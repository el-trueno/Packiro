<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Content\Artwork;

final class ArtworkStates
{
    public const STATE_MACHINE = 'pc_artwork.state';
    public const STATE_OPEN = 'open';
    public const STATE_IN_PROGRESS = 'in_progress';
    public const STATE_COMPLETED = 'completed';
    public const STATE_CANCELLED = 'cancelled';
}
