<?php
namespace App\Controller\Api\Entity;

use Habeuk\HbkSymfony\DTO\BaseDto;
use App\Entity\User;
use Habeuk\HbkSymfony\Enum\PermissionEnum;
use Habeuk\HbkSymfony\Controller\Api\BaseApiCrud;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[IsGranted('ROLE_USER')]
#[Route('/api/entity-collection/{companyId}')]
final class CollectionController extends BaseApiCrud {

  #[Route('/{entityType}/{page}/{limit}', name: 'api_load_entities', methods: [
    'GET'
  ])]
  public function loadEntities(int $companyId, string $entityType, Request $request, #[CurrentUser] User $user, int $page = 1, int $limit = 30): JsonResponse {
    if (! $this->baseValidation($entityType))
      return $this->json([
        'error' => 'Entity/Dto not found : ' . $entityType
      ], Response::HTTP_PRECONDITION_FAILED);

    if ($this->entityClass !== null && $this->entityDtoClass !== null) {
      $access = $this->entityAccessChecker->check($this->entityClass, $user, PermissionEnum::VIEW);
      if (! $access->granted) {
        return $this->json([
          'error' => $access->message
        ], $access->statusCode);
      }
      $baseRepository = $this->entityManager->getRepository($this->entityClass);
      if (! ($baseRepository instanceof \Habeuk\HbkSymfony\Repository\Interfaces\BaseRepositoryInterface))
        return $this->json([
          'error' => "L'entite n'est configuré pour l'affichage dynamique. Il manque l'interface \App\Repository\Interfaces\BaseRepositoryInterface"
        ], 404);
      $params = [
        'sorts' => [],
        'filters' => [],
        'search' => null,
        'search_fields' => []
      ];
      $sorts = $request->query->get('sorts', null);
      $filters = $request->query->get('filters', null);
      $search = $request->query->get('search', null);
      if ($sorts !== null) {
        $params['sorts'] = json_decode($sorts, true);
      }
      if ($filters !== null) {
        $params['filters'] = json_decode($filters, true);
      }
      if ($search !== null) {
        $groups = [
          BaseDto::LIST,
          BaseDto::VIEW
        ];
        $params['search_fields'] = $this->frontendFormConfig->getColumnLabels($this->entityDtoClass, $user, $groups);
        $params['search'] = $search;
      }
      $entityDtoClass = $this->entityDtoClass;
      $results = $this->baseLoadEntities($baseRepository, $page, $limit, $user, $params, $entityDtoClass);
      return $this->json($results);
    }
    throw new \Exception("Une erreur s'est produite");
  }
}
