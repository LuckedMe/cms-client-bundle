<?php

declare(strict_types=1);

namespace LuckedMe\CmsClientBundle\DTOFactory\Data;

use LuckedMe\CmsClientBundle\DTO\Data\TokenDTO;

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
