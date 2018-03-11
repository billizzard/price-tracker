<?php
namespace App\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class HVFGrid extends Annotation
{
    public $sort = false;
}