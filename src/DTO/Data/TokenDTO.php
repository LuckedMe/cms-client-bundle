<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTO\Data;

use Vtsot\CmsAdminClientBundle\DTOFactory\Data\TokenDTOFactory;

/**
 * @see TokenDTOFactory
 */
readonly class TokenDTO
{
    public function __construct(
        public string $token,
        public string $refreshToken,
        public int $refreshTokenExpiration,
    ) {
    }
}
