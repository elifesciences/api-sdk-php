<?php

namespace eLife\ApiSdk\Model;

final class Date implements CastsToString
{
    private $year;
    private $month;
    private $day;

    /**
     * @internal
     */
    public function __construct(int $year, int $month = null, int $day = null)
    {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    /**
     * @internal
     */
    public static function fromString(string $string) : Date
    {
        $parts = explode('-', $string);

        return new self(...$parts);
    }

    public function toString() : string
    {
        $time = gmmktime(0, 0, 0, $this->month ?? 1, $this->day ?? 1, $this->year);

        if (null === $this->month) {
            return $this->year;
        } elseif (null === $this->day) {
            return gmdate('Y-m', $time);
        }

        return gmdate('Y-m-d', $time);
    }

    public function getYear() : int
    {
        return $this->year;
    }

    /**
     * @return int|null
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @return int|null
     */
    public function getDay()
    {
        return $this->day;
    }

    public function format() : string
    {
        $time = gmmktime(0, 0, 0, $this->month ?? 1, $this->day ?? 1, $this->year);

        if (null === $this->month) {
            return $this->year;
        } elseif (null === $this->day) {
            return gmdate('F Y', $time);
        }

        return gmdate('F j, Y', $time);
    }
}
