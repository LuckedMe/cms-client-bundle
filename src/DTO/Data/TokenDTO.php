<?php

declare(strict_types=1);

namespace LuckedMe\CmsClientBundle\DTO\Data;

use LuckedMe\CmsClientBundle\DTOFactory\Data\TokenDTOFactory;

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
