<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTOFactory\Data;

use Vtsot\CmsAdminClientBundle\DTO\Data\TokenDTO;

class TokenDTOFactory
{
    public function fromAPI(array $data): TokenDTO
    {
        return new TokenDTO(
            token: $data['token'],
            refreshToken: $data['refresh_token'],
            refreshTokenExpiration: $data['refresh_token_expiration'],
        );
    }
}
