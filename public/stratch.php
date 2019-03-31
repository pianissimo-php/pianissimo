<?php

/**
 * Returns all Controller classes
 */
private function findControllerClasses(): array
{
    //$match = preg_match('/Controller\\\\(.*)Controller/', $class);
    //return $match === 1;

    return [
        IndexController::class,
    ];
}

/*
if (isset($_SERVER['QUERY_STRING'])) {
    dump($_SERVER['QUERY_STRING']);
}
*/

/*
if (class_exists($annotationName) === false) {
    throw new Exception(sprintf("Annotation '%s' not found. Did u forget an use statement?", $annotationName));
}
*/