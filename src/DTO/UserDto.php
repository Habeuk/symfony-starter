<?php
declare(strict_types = 1);
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;
use Habeuk\HbkSymfony\Attribute\ColumnLabel;
use Habeuk\HbkSymfony\Enum\ColumnType;
use App\Entity\User;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Habeuk\HbkSymfony\DTO\BaseUserDto;

#[Map(target: User::class)]
class UserDto extends BaseUserDto {
}