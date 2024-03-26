<?php

declare(strict_types=1);

namespace Kuniva\PackiroConfigurator\Core\Framework\BusinessDayCalculator;

// https://codereview.stackexchange.com/questions/51895/calculate-future-date-based-on-business-days
class BusinessDayCalculator
{
    public const MONDAY    = 1;
    public const TUESDAY   = 2;
    public const WEDNESDAY = 3;
    public const THURSDAY  = 4;
    public const FRIDAY    = 5;
    public const SATURDAY  = 6;
    public const SUNDAY    = 7;

    public function __construct(
        protected \DateTime $date,
        protected array $holidays = [],
        protected array $nonBusinessDays = []
    ) {
    }

    public function addBusinessDays($howManyDays): void
    {
        $i = 0;
        while ($i < $howManyDays) {
            $this->date->modify("+1 day");
            if ($this->isBusinessDay($this->date)) {
                $i++;
            }
        }
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getDateTimeImmutable(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable($this->date);
    }

    private function isBusinessDay(\DateTime $date): bool
    {
        if (in_array((int)$date->format('N'), $this->nonBusinessDays)) {
            return false; //Date is a nonBusinessDay.
        }
        foreach ($this->holidays as $day) {
            if ($date->format('Y-m-d') === $day->format('Y-m-d')) {
                return false; //Date is a holiday.
            }
        }
        return true; //Date is a business day.
    }
}
