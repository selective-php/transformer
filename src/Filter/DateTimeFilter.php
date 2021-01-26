<?php

namespace Selective\Transformer\Filter;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Selective\Transformer\Exceptions\ArrayTransformerException;

/**
 * Filter.
 */
final class DateTimeFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value
     * @param string|null $format
     * @param DateTimeZone|null $timezone
     *
     * @throws Exception
     *
     * @return mixed The value
     */
    public function __invoke($value, string $format = null, DateTimeZone $timezone = null)
    {
        try {
            $format = $format ?? 'Y-m-d H:i:s';

            if (is_string($value)) {
                return (string)(new DateTimeImmutable($value, $timezone))->format($format);
            }

            if ($value instanceof DateTimeImmutable) {
                if ($timezone) {
                    $value->setTimezone($timezone);
                }

                return (string)$value->format($format);
            }

            return null;
        } catch (Exception $exception) {
            throw new ArrayTransformerException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
