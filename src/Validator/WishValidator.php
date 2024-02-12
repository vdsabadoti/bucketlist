<?php

namespace App\Validator;

use App\Entity\Wish;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class WishValidator
{
    public static function validate(Wish $wish, ExecutionContextInterface $context) : void
    {
        $bannedUsers = ['krooks', 'garmin'];

        if (\in_array($wish->getAuthor(), $bannedUsers ) && !$wish->isIsPublished()){
            $context->buildViolation('This user is banned !')
                ->atPath('author')
                ->addViolation();
        }
    }


}