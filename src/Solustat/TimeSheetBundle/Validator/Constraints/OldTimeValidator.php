<?php
namespace Solustat\TimeSheetBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class OldTimeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $test = $value;
        if (($value < new \DateTime('now'))) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}