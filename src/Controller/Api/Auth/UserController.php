<?php
namespace App\Controller\Api\Auth;

use Habeuk\HbkSymfony\Controller\Api\BaseApiCrud;
use App\DTO\ {
  UserDto
};
use Habeuk\HbkSymfony\DTO\BaseDto;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/user')]
class UserController extends BaseApiCrud {

  #[Route('/me', name: 'api_user_me', methods: [
    'GET'
  ])]
  #[IsGranted('ROLE_USER')]
  public function me(#[CurrentUser] User $user): JsonResponse {
    // Le token est automatiquement vérifié par LexikJWTAuthenticationBundle
    // Si on arrive ici, le token est valide et l'utilisateur est authentifié
    $groups = [
      BaseDto::REFERENCE,
      BaseDto::LIST
    ];
    $entity = $this->loadContentArray($user, UserDto::class, true, $groups);

    return $this->json([
      'user' => $entity
    ]);
  }
}