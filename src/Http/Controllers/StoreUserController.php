<?php

namespace Osana\Challenge\Http\Controllers;

use Osana\Challenge\Services\Local\LocalUsersRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Middleware\BodyParsingMiddleware;

class StoreUserController
{
    /** @var LocalUsersRepository */
    private $localUsersRepository;

    public function __construct(LocalUsersRepository $localUsersRepository)
    {
        $this->localUsersRepository = $localUsersRepository;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $res = $request->getParsedBody();
        
        $id = new Id('CSV');
        $login = new Login($res['login']);
        $type = Type::setType()->setValue($res['type']);
        $name = new Name($res['profile']['name']);
        $company = new Company($res['profile']['company']);
        $location = new Location($res['profile']['location']);
        $profile = new Profile($name, $company, $location);

        $user = new User($id, $login, $type, $profile);

        $response = $this->localUsersRepository->add($user);

        return $response;
    }
}
