<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class AuthController
{
    #[Route('/auth', name: 'api_login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('Cette méthode ne doit jamais être appelée directement. Le firewall json_login doit intercepter la requête.');
    }
}