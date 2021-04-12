<?php

namespace Osana\Challenge\Services\Local;

use Osana\Challenge\Domain\Users\Company;
use Osana\Challenge\Domain\Users\Id;
use Osana\Challenge\Domain\Users\Location;
use Osana\Challenge\Domain\Users\Login;
use Osana\Challenge\Domain\Users\Name;
use Osana\Challenge\Domain\Users\Profile;
use Osana\Challenge\Domain\Users\Type;
use Osana\Challenge\Domain\Users\User;
use Osana\Challenge\Domain\Users\UsersRepository;
use Osana\Challenge\Domain\Users\UserNotFoundException;
use Tightenco\Collect\Support\Collection;
use League\Csv\Reader;
use League\Csv\Statement;

class LocalUsersRepository implements UsersRepository
{
    public function findByLogin(Login $login, int $limit = 0): Collection
    {
        $ruta = explode('\\src\\',__DIR__);

        //load the CSV document from a file path
        $csvUsers = Reader::createFromPath($ruta[0] . '\data\users.csv', 'r');
        $csvUsers->setDelimiter(';');
        $csvUsers->setHeaderOffset(0);

        //build a statement
        $stmtUsers = Statement::create()
            ->offset(0)
            ->limit(101);

        //query records from the document
        $recordsUsers = $stmtUsers->process($csvUsers);

        //load the CSV document from a file path
        $csvProfiles = Reader::createFromPath($ruta[0] . '\data\profiles.csv', 'r');
        $csvProfiles->setDelimiter(';');
        $csvProfiles->setHeaderOffset(0);

        //build a statement
        $stmtProfiles = Statement::create()
            ->offset(0)
            ->limit(101);

        //query records from the document
        $recordsProfiles = $stmtProfiles->process($csvProfiles);
        $i = 0;
        foreach ($recordsUsers as $recordUser) {
            $itemUser = explode(',',$recordUser['id,login,type']);

            $find = strripos($itemUser[1], $login->getValue(), 0);
            if($find === 0){
                $id = new Id($itemUser[0]);
                $login = new Login($itemUser[1]);
                $type = Type::Local();
                foreach ($recordsProfiles as $recordProfile) {
                    $itemProfile = explode(',',$recordProfile['id,company,location,name']);

                    //compare the id's
                    if($itemUser[0] == $itemProfile[0]){
                        $name = new Name($itemProfile[3]);
                        $company = new Company($itemProfile[1]);
                        $location = new Location($itemProfile[2]);
                        $profile = new Profile($name, $company, $location);

                        $user = new User($id, $login, $type, $profile);

                        $users[] = $user;
                        
                        if($i >= $limit){
                            break;
                        }else{
                            $i++;
                        }
                    }
                }
            }
        }

        if(isset($users) && count($users) > 0){
            return new Collection($users);
        }else{
            return new Collection(null);
        }
    }

    public function getByLogin(Login $login, Type $type): User
    {
        $ruta = explode('\\src\\',__DIR__);

        //load the CSV document from a file path
        $csvUsers = Reader::createFromPath($ruta[0] . '\data\users.csv', 'r');
        $csvUsers->setDelimiter(';');
        $csvUsers->setHeaderOffset(0);

        //build a statement
        $stmtUsers = Statement::create()
            ->offset(0)
            ->limit(101);

        //query records from the document
        $recordsUsers = $stmtUsers->process($csvUsers);

        //load the CSV document from a file path
        $csvProfiles = Reader::createFromPath($ruta[0] . '\data\profiles.csv', 'r');
        $csvProfiles->setDelimiter(';');
        $csvProfiles->setHeaderOffset(0);

        //build a statement
        $stmtProfiles = Statement::create()
            ->offset(0)
            ->limit(101);

        //query records from the document
        $recordsProfiles = $stmtProfiles->process($csvProfiles);
        $i = 0;
        foreach ($recordsUsers as $recordUser) {
            $itemUser = explode(',',$recordUser['id,login,type']);
            if(strtolower($itemUser[2]) == $type->Local() && $itemUser[1] == $login->getValue()){
                $id = new Id($itemUser[0]);
                $login = new Login($itemUser[1]);
                $type = Type::Local();
                foreach ($recordsProfiles as $recordProfile) {
                    $itemProfile = explode(',',$recordProfile['id,company,location,name']);

                    //compare the id's
                    if($itemUser[0] == $itemProfile[0]){
                        $name = new Name($itemProfile[3]);
                        $company = new Company($itemProfile[1]);
                        $location = new Location($itemProfile[2]);
                        $profile = new Profile($name, $company, $location);

                        $user = new User($id, $login, $type, $profile);
                        break;
                    }
                }
            }
        }

        return isset($user) ? $user : null;
    }

    public function add(User $user): void
    {
        $ruta = explode('\\src\\',__DIR__);

        //load the CSV document from a file path
        $csvUsers = Reader::createFromPath($ruta[0] . '\data\users.csv', 'r');
        $csvUsers->setDelimiter(';');
        $csvUsers->setHeaderOffset(0);

        //build a statement
        $stmtUsers = Statement::create()
            ->offset(0)
            ->limit(101);

        //query records from the document
        $recordsUsers = $stmtUsers->process($csvUsers);

        foreach ($recordsUsers as $recordUser) {
            $itemUser = explode(',',$recordUser['id,login,type']);
            $id = explode("V",$itemUser[0]);
            $nuevoId = intval($id[1]) + 1;
        }


        $recordsUser = [
            [$nuevoId, $user->getLogin()->getValue(), $localUsers->getType()->getValue()],
        ];

        //load the CSV document from a string
        $csvUser = Writer::createFromString($ruta[0] . '\data\users.csv');
        
        //insert all the records
        $csvUser->insertAll($recordsUser);

        $recordsProfile = [
            [$nuevoId, $user->getProfile()->getCompany()->getValue(), $user->getProfile()->getLocation()->getValue(),$user->getProfile()->getName()->getValue()],
        ];

        //load the CSV document from a string
        $csvProfile = Writer::createFromString($ruta[0] . '\data\profiles.csv');
        
        //insert all the records
        $csvProfile->insertAll($recordsProfile);

        echo $csv->toString(); //returns the CSV document as a string
    }
}
