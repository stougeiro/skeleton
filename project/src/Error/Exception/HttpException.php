<?php declare(strict_types=1);

namespace Sourcerer\Error\Exception;


/**
 * A generic HTTP error raised by the application when a status is known and
 * the error is not one of the named cases (404, 405, ...).
 */
final class HttpException extends HttpExceptionAbstracted
{
}
