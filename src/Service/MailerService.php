<?php

namespace App\Service;

use App\Manager\EntityManagerInterface;

class MailerService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, string $method)
    {
        $this->entityManager = $entityManager;
    }
}
