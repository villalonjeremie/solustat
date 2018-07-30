<?php
namespace Solustat\TimeSheetBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
* @Annotation
*/
class OldTime extends Constraint
{
public $message = 'The date is passed, please select present time or futur time for starting date';
}