<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Exception;


use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationException extends \RuntimeException
{
    public ConstraintViolationListInterface $violationList;

    public function __construct(ConstraintViolationListInterface $errors, string $message = 'Invalid data in request body', int $code = 422)
    {
        parent::__construct($message, $code);
        $this->violationList = $errors;
    }

    /**
     * @throws ValidationException
     */
    public static function assertValid(ConstraintViolationListInterface $violationList,string $message = 'Invalid data in request body', int $code = 422):void
    {
        if($violationList->count()>0){
            throw new self($violationList,$message,$code);
        }
    }

}