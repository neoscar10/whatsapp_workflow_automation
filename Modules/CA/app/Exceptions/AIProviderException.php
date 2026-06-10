<?php

namespace Modules\CA\Exceptions;

use Exception;

class AIProviderException extends Exception
{
    // Thrown when an AI provider returns an API error, rate limit, timeout, etc.
}
