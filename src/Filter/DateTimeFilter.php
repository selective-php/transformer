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

            if ($value instanceof DateTimeImmutable) {
                if ($timezone) {
                    // This would only with only work with UTC as default time zone.
                    // https://3v4l.org/YlGWY
                    throw new ArrayTransformerException(
                        'Changing the DateTimeZone of an existing DateTimeImmutable object is not supported.'
                    );
                }

                return (string)$value->format($format);
            }

            return (new DateTimeImmutable($value, $timezone))->format($format);
        } catch (Exception $exception) {
            throw new ArrayTransformerException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
