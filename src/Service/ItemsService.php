<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;


use Tpg\HeadlessBundle\Exception\NotFoundException;
use Tpg\HeadlessBundle\Exception\ValidationException;
use Tpg\HeadlessBundle\Extension\PageableContextBuilder;
use Tpg\HeadlessBundle\Extension\PageableExtension;
use Tpg\HeadlessBundle\Query\Fields;
use Tpg\HeadlessBundle\Query\Page;
use Tpg\HeadlessBundle\Query\Pageable;
use Tpg\HeadlessBundle\Request\ModifyItemRequest;
use Tpg\HeadlessBundle\Security\Subject\Operation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


final class ItemsService
{
    private AstFactory $astFactory;
    private ExecutorORM $astExecutorORM;
    private DataHydrator $dataExtractor;
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private SecurityService $securityService;
    private SchemaService $schemaService;

    public function __construct(
        AstFactory $astFactory, ExecutorORM $astExecutorORM, DataHydrator $dataExtractor,
        ValidatorInterface $validator, EntityManagerInterface $entityManager, SecurityService $securityService,
        SchemaService $schemaService
    )
    {
        $this->astFactory = $astFactory;
        $this->astExecutorORM = $astExecutorORM;
        $this->dataExtractor = $dataExtractor;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->securityService = $securityService;
        $this->schemaService = $schemaService;
    }

    public function getMany(string $collection, Fields $fields, array $context=[]):array
    {
        $collectionAst = $this->securityService->filterAst($this->astFactory->fromFields($collection,$fields));
        return $this->astExecutorORM->getMany($collectionAst,$context);
    }

    public function getOne(string $collection, string $id, Fields $fields, array $context=[]):array
    {
        return $this->astExecutorORM->getOne(
            $this->securityService->filterAst($this->astFactory->fromFields($collection,$fields)),
            $id,
            $context
        );
    }

    public function getPage(string $collection, Fields $fields, Pageable $pageable,array $context=[]):Page
    {
        $context = (new PageableContextBuilder())->withContext($context)->withPageable($pageable)->toArray();
        $result = $this->getMany($collection,$fields,$context);
        $count = $this->astExecutorORM->getCount($collection,$context);
        return new Page(
            $result,
            $pageable,
            $count
        );
    }


    public function create(string $collection, ModifyItemRequest $request):object
    {
        if(!$this->securityService->isCollectionGranted($collection,Operation::CREATE)){
            throw new \RuntimeException('Access denied');
        }
        $item = $this->dataExtractor->createObject(
            $collection,
            $request->getData()
        );

        $violations = $this->validator->validate($item);

        ValidationException::assertValid($violations);;

        if(!$this->securityService->isItemGranted($collection, $item, Operation::CREATE)){
            throw new \RuntimeException('Access denied');
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();
        return $item;
    }

    /**
     * @throws ValidationException
     */
    public function update(string $collection, string $id, array $data):object
    {
        if(!$this->securityService->isCollectionGranted($collection,Operation::UPDATE)){
            throw new \RuntimeException('Access denied');
        }

        $item = $this->entityManager->find($this->schemaService->getCollection($collection)->class,$id);

        if($item===null){
            throw new NotFoundException();
        }

        $item = $this->dataExtractor->hydrateObject(
            $collection,
            $item,
            $this->securityService->filterEntityData($collection, $data, Operation::UPDATE)
        );

        $violations = $this->validator->validate($item);

        ValidationException::assertValid($violations);

        if(!$this->securityService->isItemGranted($collection, $item, Operation::UPDATE)){
            throw new \RuntimeException('Access denied');
        }

        $this->entityManager->flush();
        return $item;
    }

    public function delete(string $collection, string $id):void
    {
        if(!$this->securityService->isCollectionGranted($collection,Operation::DELETE)){
            throw new \RuntimeException('Access denied');
        }
        $item = $this->entityManager->find($this->schemaService->getCollection($collection)->class,$id);

        if($item===null){
            return;
        }

        if(!$this->securityService->isItemGranted($collection, $item, Operation::DELETE)){
            throw new \RuntimeException('Access denied');
        }

        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }

}