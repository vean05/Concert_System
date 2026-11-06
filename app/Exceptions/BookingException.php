<?php

namespace App\Exceptions;

use RuntimeException;

/** A booking or cancellation rule failed; the message is safe to show the user. */
class BookingException extends RuntimeException
{
}
