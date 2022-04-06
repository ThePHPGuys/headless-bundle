<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;


use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Query\Fields;

final class SecuredAstFactory
{

    private AstFactory $astFactory;
    private SecurityService $securityService;

    public function __construct(AstFactory $astFactory, SecurityService $securityService)
    {
        $this->astFactory = $astFactory;
        $this->securityService = $securityService;
    }

    public function createCollectionAstFromFields(string $collection, Fields $fields, string $accessOperation):Collection
    {
        return $this->securityService->filterCollectionAst(
            $this->astFactory->createCollectionAstFromFields($collection,$fields),
            $accessOperation
        );
    }
}