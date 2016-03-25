<?php
/**
 * Created by PhpStorm.
 * User: aleš tichava
 * Date: 25.3.2016
 * Time: 14:41
 */

namespace Users\Components\Admin;

use App\Components\BaseControl;
use Users\Facades\UserFacade;
use Users\Query\UserQuery;

class UsersOverviewControl extends BaseControl
{
    /** @var UserQuery */
    private $userQuery;

    /** @var UserFacade */
    private $userFacade;


    public function __construct(
        UserQuery $userQuery,
        UserFacade $userFacade
    ) {
        $this->userQuery = $userQuery;
        $this->userFacade = $userFacade;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/usersOverview.latte');

        $resultSet = $this->userFacade->fetchUsers($this->userQuery);

        $template->users = $resultSet->toArray();

        $template->render();
    }
}


interface IUsersOverviewControlFactory
{
    /**
     * @param UserQuery $userQuery
     * @return UsersOverviewControl
     */
    public function create(UserQuery $userQuery);
}
