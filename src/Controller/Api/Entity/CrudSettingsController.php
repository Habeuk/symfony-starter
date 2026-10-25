<?php
namespace App\Controller\Api\Entity;

use App\Entity\ {
  User
};
use Habeuk\HbkSymfony\EntityPolicy\EntityAccessChecker;
use Habeuk\HbkSymfony\Contract\OwnerInterface;
use Habeuk\HbkSymfony\Controller\Api\BaseApiCrud;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Habeuk\HbkSymfony\Enum\PermissionEnum;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Habeuk\HbkSymfony\Service\ {
  EntityReferenceResolver,
  FrontendFormConfig,
  ApiDatasMapper
};
use Habeuk\HbkSymfony\DTO\BaseDto;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

#[IsGranted('ROLE_USER')]
#[Route('/api/settings/crud')]
final class CrudSettingsController extends BaseApiCrud {
}
