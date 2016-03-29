<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 29.03.2016
 */

namespace Users\Components\Admin;

use App\Components\BaseControl;
use Users\Facades\UserFacade;
use Users\Query\RoleQuery;

class UsersRolesOverviewControl extends BaseControl
{
    /** @var UserFacade */
    private $userFacade;

    /** @var RoleQuery */
    private $roleQuery;


    public function __construct(
        RoleQuery $roleQuery,
        UserFacade $userFacade
    ) {
        $this->roleQuery = $roleQuery;
        $this->userFacade = $userFacade;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/usersRolesOverview.latte');

        $resultSet = $this->userFacade->fetchRoles($this->roleQuery);
        
        $template->roles = $resultSet->toArray();

        $template->render();
    }
}


interface IUsersRolesOverviewControlFactory
{
    /**
     * @param RoleQuery $roleQuery
     * @return UsersRolesOverviewControl
     */
    public function create(RoleQuery $roleQuery);
}