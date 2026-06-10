<?php

namespace Modules\CA\Exceptions;

use Exception;

class AIParsingException extends Exception
{
    // Thrown when the AI provider successfully returns a response but it is not valid JSON
    // or does not match the expected schema.
}
