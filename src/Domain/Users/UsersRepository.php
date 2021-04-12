<?php

namespace Osana\Challenge\Domain\Users;

use Tightenco\Collect\Support\Collection;

interface UsersRepository
{
    public function findByLogin(Login $login, int $limit = 0): Collection;

    public function getByLogin(Login $login, Type $type): User;

    public function add(User $user): void;
}
