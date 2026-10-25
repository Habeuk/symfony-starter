<?php
namespace App\Controller\Api\Auth;

use Habeuk\HbkSymfony\Controller\Api\BaseApiCrud;
use App\DTO\ {
  UserDto
};
use Habeuk\HbkSymfony\DTO\BaseDto;
use App\Entity\User;
use Habeuk\HbkSymfony\Enum\PermissionEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Habeuk\HbkSymfony\Service\ApiDatasMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Habeuk\HbkSymfony\Security\AnonymousUser;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Repository\UserRepository;
use Habeuk\HbkSymfony\Security\EmailVerifier;
use Webmozart\Assert\Assert;

#[Route('/api')]
class RegistrationController extends BaseApiCrud {

  /**
   * Recupere le formulaire d'une entité en JSON.
   */
  #[Route('/form-fields/UserAnonymousUser/create', name: 'api_form_user_fields', methods: [
    'GET'
  ])]
  public function getFormDefinitions(Request $request): JsonResponse {
    $entityName = 'User';
    $action = 'create';
    $anonymousUser = AnonymousUser::getUser();
    if (! $this->baseValidation($entityName)) {
      return $this->json([
        'error' => 'Entity/Form not found : ' . $entityName
      ], Response::HTTP_UNAUTHORIZED);
    }
    $datas = $this->baseFormEntityDefinition($action, $anonymousUser, 0);
    return $this->json($datas);
  }

  #[Route('/user/register', name: 'api_user_save', methods: [
    'POST',
    'OPTIONS'
  ])]
  function saveEntity(Request $request, ObjectMapperInterface $objectMapper, EmailVerifier $emailVerifier): JsonResponse {
    $entityType = "User";
    $user = $this->getUser() ?? AnonymousUser::getUser();
    if (! ($user instanceof User)) {
      throw new \ErrorException("Une erruer s'est produite, l'utilisateur inconu du systeme"); 
    }
    // on ne peut plus creer d'utilisateur via cette methode.
    if (! $this->baseValidation($entityType))
      return $this->json([
        'error' => 'Entity/Dto not found : ' . $entityType
      ], Response::HTTP_PRECONDITION_FAILED);
    if ($this->entityClass !== null && $this->entityDtoClass !== null) {
      $entityDtoClass = $this->entityDtoClass;
      $entityClass = $this->entityClass;
      $data = json_decode($request->getContent(), true);
      $id = $data['id'] ?? null;
      if ($id !== null) {
        $accessDefinition = $this->entityAccessChecker->canEditDefinition($entityClass, $user);
        if (! $accessDefinition->granted) {
          return $this->json([
            'error' => $accessDefinition->message
          ], $accessDefinition->statusCode);
        }
        $action = PermissionEnum::EDIT;
        $groups = [
          $action->value,
          BaseDto::REFERENCE
        ];
        $messageError = "Une erreur s'est produite lors de mise à jour de votre contenu";
      }
      else {
        $accessDefinition = $this->entityAccessChecker->canCreateDefinition($entityClass, $user);
        if (! $accessDefinition->granted) {
          return $this->json([
            'error' => $accessDefinition->message
          ], $accessDefinition->statusCode);
        }
        $action = PermissionEnum::CREATE;
        $groups = [
          $action->value
        ];
        $messageError = "Une erreur s'est produite lors de la mise à jour de votre contenu";
      }
      try {
        if ($user->isAdmin()) {
          $groups[] = BaseDto::ADMIN;
        }
        $dto = $this->apiDatasMapper->fromArrayToDto($data, $entityDtoClass, $groups);
        if ($action === PermissionEnum::CREATE) {
          $prepareEntity = $this->apiDatasMapper->fromDtoToNewEntity($dto, $entityClass);
          $this->entityResolver->resolve($dto, $prepareEntity, $data);
          /** @var User $entity */
          $entity = $this->apiDatasMapper->entityValidate($prepareEntity);
          // forcer la verification de compte pour les utilisateurs n'ont admin.
          if (! $user->isAdmin()) {
            $entity->setIsVerified(false);
            $entity->setStatus(false);
            $entity->setRoles([
              'ROLE_USER'
            ]);
          }
          $this->entityManager->persist($entity);
          $this->entityManager->flush();
          //
          if (! $entity->isVerified()) {
            $emailVerifier->sendEmailConfirmation('api_verify_email', $entity);
          }
        }
        elseif ($action === PermissionEnum::EDIT) {
          $entityOld = $this->entityManager->getRepository($this->entityClass)->find($data['id']);
          if ($entityOld === null)
            throw new \Exception("L'entité n'existe plus");
          $prepareEntity = $this->apiDatasMapper->fromDtoMergeToEntity($dto, $entityOld);
          $this->entityResolver->resolve($dto, $prepareEntity, $data);
          $entity = $this->apiDatasMapper->entityValidate($prepareEntity);
          $this->entityManager->flush();
        }
        return $this->json($this->loadContentArray($entity, $entityDtoClass));
      }
      catch (ValidationFailedException $e) {
        $messageError = "Certains champs ne sont pas bien remplie";
        return $this->json([
          'error' => $messageError,
          'form_errors' => $e->getViolations()
        ], Response::HTTP_PRECONDITION_FAILED);
      }
      catch (\Throwable $e) {
        return $this->error($e, $user, $messageError);
      }
    }
    throw new \Exception("Une erreur s'est produite");
  }

  #[Route('/verify/email', name: 'api_verify_email')]
  public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository, ParameterBagInterface $params,
    EmailVerifier $emailVerifier): Response {
    $frontendBase = $params->get('frontend_url');
    Assert::string($frontendBase, "L'url du frontend n'est pas configurée correctement");
    $uuid = $request->query->get('uuid');
    if (null === $uuid) {
      return $this->redirect($frontendBase . '/app/verification?status=error&code=invalid_params');
    }
    $user = $userRepository->findOneBy([
      'uuid' => $uuid
    ]);
    if (null === $user) {
      return $this->redirect($frontendBase . '/app/verification?status=error&code=invalid_link');
    }
    // Validate email confirmation link, sets User::isVerified=true and persists
    try {
      $emailVerifier->handleEmailConfirmation($request, $user);
      return $this->redirect($frontendBase . '/app/verification?status=success');
    }
    catch (VerifyEmailExceptionInterface $exception) {
      $errorMessage = $translator->trans($exception->getReason(), [], 'VerifyEmailBundle');
      return $this->redirect($frontendBase . '/app/verification?status=error&message=' . urlencode($errorMessage));
    }
  }
}
