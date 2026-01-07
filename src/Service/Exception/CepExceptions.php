<?php
declare(strict_types=1);

namespace App\Service\Exception;

use RuntimeException;

class CepNotFoundException extends RuntimeException {}
class UpstreamTimeoutException extends RuntimeException {}
class UpstreamUnavailableException extends RuntimeException {}
class UpstreamInvalidResponseException extends RuntimeException {}
