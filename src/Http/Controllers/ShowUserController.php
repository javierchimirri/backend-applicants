<?php

namespace Osana\Challenge\Http\Controllers;

use Osana\Challenge\Domain\Users\Login;
use Osana\Challenge\Domain\Users\Type;
use Osana\Challenge\Services\Local\LocalUsersRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ShowUserController
{
    /** @var LocalUsersRepository */
    private $localUsersRepository;

    public function __construct(LocalUsersRepository $localUsersRepository)
    {
        $this->localUsersRepository = $localUsersRepository;
    }

    public function __invoke(Request $request, Response $response, array $params): Response
    {
        $type = new Type($params['type']);
        $login = new Login($params['login']);

        $localUsers = $this->localUsersRepository->getByLogin($login, $type);

        $user = 
            [
                'id' => $localUsers->getId()->getValue(),
                'login' => $localUsers->getLogin()->getValue(),
                'type' => $localUsers->getType()->getValue(),
                'profile' => [
                    'name' => $localUsers->getProfile()->getName()->getValue(),
                    'company' => $localUsers->getProfile()->getCompany()->getValue(),
                    'location' => $localUsers->getProfile()->getLocation()->getValue(),
                ]
            ];

        $response->getBody()->write(json_encode($user));

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(200, 'OK');
        
        return $response;
    }
}
