<?php

/**
 * Handling the ajax Callbacks for the ImbaAdmin module Administration
 */
class AjaxAdministration extends AjaxBase {

    public function __construct() {
        parent::__construct();
    }

    public function getNavigation() {
        /**
         * Define Navigation
         */
        $navigation = new ImbaContentManager();

        /**
         * Set module name
         */
        $navigation->setName("Administration");
        $navigation->setClassname(get_class($this));

        $navigation->setComment("Hier kann der IMBAdmin konfiguriert werden.");


        /**
         * Set when the module should be displayed (logged in 1/0)
         */
        $navigation->setShowLoggedIn(true);
        $navigation->setShowLoggedOff(false);

        /**
         * Set the minimal user role needed to display the module
         */
        $navigation->setMinUserRole(3);

        /**
         * Set tabs
         */
        $navigation->addElement("viewUsers", "Users", "Edit user roles and user details");
        $navigation->addElement("viewRoles", "Roles", "Manage Roles");
        $navigation->addElement("viewGames", "Games", "Manage Games");
        $navigation->addElement("viewGameCategory", "Game Categories", "Manage Game Categories");
        $navigation->addElement("viewPortalOverview", "Portals", "Manage Portal (Site URL)");
        $navigation->addElement("viewPortalEntries", "Portal Entries", "Manage Navigation Entries");

        return $navigation;
    }

    /**
     * gets the portal overview
     */
    public function viewPortalOverview() {
        $smartyPortals = array();
        foreach ($this->managerPortal->selectAll() as $portal) {
            array_push($smartyPortals, array(
                "id" => $portal->getId(),
                "name" => $portal->getName(),
                "icon" => $portal->getIcon(),
                "comment" => $portal->getComment()
            ));
        }
        $this->smarty->assign("portals", $smartyPortals);
        $this->smarty->display("IMBAdminModules/AdminPortalOverview.tpl");
    }

    /**
     * adds a portal
     * @param type $params ({"icon":"iconurl", "name":"Name", "comment":"Comment"})
     */
    public function addPortal($params) {
        $icon = $params->icon;
        $name = $params->name;
        $comment = $params->comment;

        $newPortal = new ImbaPortal();
        $newPortal->setIcon($icon);
        $newPortal->setName($name);
        $newPortal->setComment($comment);
        $this->managerPortal->insert($newPortal);
        echo "Ok";
    }

    /**
     * updates a portal
     * @param type $params ({"portalid":"1", "icon":"iconurl", "name":"Name", "comment":"Comment"})
     */
    public function updatePortal($params) {
        $portalid = $params->portalid;
        $icon = $params->icon;
        $icon = $params->icon;
        $name = $params->name;
        $comment = $params->comment;

        $portal = new ImbaPortal();
        $portal = $this->managerPortal->selectById($portalid);        
        $portal->setIcon($icon);
        $portal->setName($name);
        $portal->setComment($comment);
        $this->managerPortal->update($portal);
        echo "Ok";
    }

    /**
     * delets a portal
     * @param type $params ({"portalid":"1"})
     */
    public function deletePortal($params) {
        $portalid = $params->portalid;
        $this->managerPortal->delete($portalid);
        echo "Ok";
    }

    /**
     * views a portal
     * @param type $params ({"portalid":"1"})
     */
    public function viewPortalDetail($params) {
        $portalid = $params->portalid;
        $portal = $this->managerPortal->selectById($portalid);

        $this->smarty->assign("id", $portal->getId());
        $this->smarty->assign("name", $portal->getName());
        $this->smarty->assign("comment", $portal->getComment());
        $this->smarty->assign("icon", $portal->getIcon());

        $this->smartyPortalEntries = array();
        if ($portal->getPortalEntries() != null) {
            foreach ($portal->getPortalEntries() as $portalentry) {
                array_push($this->smartyPortalEntries, array(
                    "id" => $portalentry->getId(),
                    "name" => $portalentry->getName()
                ));
            }
        }
        $this->smarty->assign("portalentries", $this->smartyPortalEntries);

        $this->smartyPortalAliases = array();
        if ($portal->getAliases() != null) {
            foreach ($portal->getAliases() as $alias) {
                array_push($this->smartyPortalAliases, $alias);
            }
        }
        $this->smarty->assign("aliases", $this->smartyPortalAliases);

        $this->smarty->display('IMBAdminModules/AdminPortalDetail.tpl');
    }

    /**
     * view portal entries
     */
    public function viewPortalEntries() {
        $portalEntries = $this->managerPortalEntry->selectAll();
        $this->smartyPortalEntries = array();

        foreach ($portalEntries as $naventry) {
            array_push($this->smartyPortalEntries, array(
                "id" => $naventry->getId(),
                "handle" => $naventry->getHandle(),
                "name" => $naventry->getName(),
                "target" => $naventry->getTarget(),
                "url" => $naventry->getUrl(),
                "comment" => $naventry->getComment(),
                "loggedin" => $naventry->getLoggedin(),
                "role" => $naventry->getRole()
            ));
        }

        $this->smarty->assign("entries", $this->smartyPortalEntries);
        $this->smarty->display('IMBAdminModules/AdminPortalEntriesOverview.tpl');
    }

    /**
     * adds a portal entry
     * @param type $params ({"handle":"abc", "name":"abc", "target":"abc", "url":"abc", "comment":"abc", "loggedin":"abc", "role":"abc" })
     */
    public function addPortalEntry($params) {
        $handle = $params->handle;
        $name = $params->name;
        $target = $params->target;
        $url = $params->url;
        $comment = $params->comment;
        $loggedin = $params->loggedin;
        $role = $params->role;

        $newPortalEntry = new ImbaPortalEntry();
        $newPortalEntry->setHandle($handle);
        $newPortalEntry->setName($name);
        $newPortalEntry->setTarget($target);
        $newPortalEntry->setUrl($url);
        $newPortalEntry->setComment($comment);
        $newPortalEntry->setLoggedin($loggedin);
        $newPortalEntry->setRole($role);

        $this->managerPortalEntry->insert($newPortalEntry);
        echo "Ok";
    }

    /**
     * updates a portal entry
     * @param type $params ({"portalentryid":"1", "portalentrycolumn":"abc", "value":"abc"})
     */
    public function updatePortalEntry($params) {
        $portalentryid = $params->portalentryid;
        $portalentrycolumn = $params->portalentrycolumn;
        $value = $_POST["value"];

        $portalentry = new ImbaPortalEntry();
        $portalentry = $this->managerPortalEntry->selectById($portalentryid);

        switch ($portalentrycolumn) {
            case "Name":
                $portalentry->setName($value);
                break;

            case "Interner Handle":
                $portalentry->setHandle($value);
                break;

            case "Target":
                $portalentry->setTarget($value);
                break;

            case "Url":
                $portalentry->setUrl($value);
                break;

            case "Comment":
                $portalentry->setComment($value);
                break;

            case "Only show if logged in":
                $portalentry->setLoggedin($value);
                break;

            case "Which role is allowed":
                $portalentry->setRole($value);
                break;

            default:
                break;
        }

        $this->managerPortalEntry->update($portalentry);
        echo $value;
    }

    /**
     * delets a portal entry
     * @param type $params ({"portalentryid":"1"})
     */
    public function deletePortalEntry($params) {
        $portalentryid = $params->portalentryid;
        $this->managerPortalEntry->delete($portalentryid);
        echo "Ok";
    }

    /**
     * view roles
     */
    public function viewRoles() {
        $roles = $this->managerRole->selectAll();

        $this->smarty_roles = array();
        foreach ($roles as $role) {
            array_push($this->smarty_roles, array(
                "id" => $role->getId(),
                "handle" => $role->getHandle(),
                "role" => $role->getRole(),
                "name" => $role->getName(),
                "icon" => $role->getIcon(),
                "smf" => $role->getSmf(),
                "wordpress" => $role->getWordpress()
            ));
        }
        $this->smarty->assign('roles', $this->smarty_roles);
        $this->smarty->display('IMBAdminModules/AdminRole.tpl');
    }

    /**
     * adds a role
     * @param type $params ({"handle":"abc", "role":"1", "name":"abc", "smf":"1", "wordpress":"abc", "icon":"abc"})
     */
    public function addRole($params) {
        $handle = $params->handle;
        $role = $params->role;
        $name = $params->name;
        $smf = $params->smf;
        $wordpress = $params->wordpress;
        $icon = $params->icon;

        $role = $this->managerRole->getNew();
        $role->setHandle($handle);
        $role->setRole($role);
        $role->setName($name);
        $role->setSmf($smf);
        $role->setWordpress($wordpress);
        $role->setIcon($icon);
        $this->managerRole->insert($role);
    }

    /**
     * updates a update Role
     * @param type $params ({"roleid":"1", "rolecolumn":"abc"}) 
     * and a $_POST["value"]
     */
    public function updateRole($params) {
        $roleid = $params->roleid;
        $rolecolumn = $params->rolecolumn;
        $value = $_POST["value"];

        $role = $this->managerRole->selectById($roleid);

        switch ($rolecolumn) {
            case "Role":
                $role->setRole($value);
                break;

            case "Handle":
                $role->setHandle($value);
                break;

            case "Name":
                $role->setName($value);
                break;

            case "Icon":
                $role->setIcon($value);
                break;

            case "SMF":
                $role->setSmf($value);
                break;

            case "Wordpress":
                $role->setWordpress($value);
                break;

            default:
                break;
        }

        $this->managerRole->update($role);
        echo $value;
    }

    /**
     * delets a Role
     * @param type $params ({"roleid":"1"})
     */
    public function deleteRole($params) {
        $roleid = $params->roleid;
        $this->managerRole->delete($roleid);
    }

    /**
     * View all games
     */
    public function viewGames() {
        $games = $this->managerGame->selectAll();

        $this->smarty_categories = array();
        $this->smarty_games = array();
        foreach ($games as $game) {
            array_push($this->smarty_games, array(
                "id" => $game->getId(),
                "name" => $game->getName(),
                "comment" => $game->getComment(),
                "icon" => $game->getIcon(),
                "url" => $game->getUrl(),
                "forumlink" => $game->getForumlink()
            ));
        }
        $this->smarty->assign('games', $this->smarty_games);
        $this->smarty->display('IMBAdminModules/AdminGame.tpl');
    }

    /**
     * views a game
     * @param type $params ({"gameid":"1"})
     */
    public function viewGameDetail($params) {
        $gameid = $params->gameid;

        $game = $this->managerGame->selectById($gameid);

        $this->smarty->assign("id", $game->getId());
        $this->smarty->assign("name", $game->getName());
        $this->smarty->assign("comment", $game->getComment());
        $this->smarty->assign("icon", $game->getIcon());
        $this->smarty->assign("url", $game->getUrl());
        $this->smarty->assign("forumlink", $game->getForumlink());

        $this->smarty_categories = array();
        foreach ($this->managerGameCategory->selectAll() as $category) {
            $selected = "false";
            foreach ($game->getCategories() as $selCategory) {
                if ($selCategory->getId() == $category->getId()) {
                    $selected = "true";
                }
            }

            array_push($this->smarty_categories, array(
                'id' => $category->getId(),
                'name' => $category->getName(),
                'selected' => $selected
            ));
        }
        $this->smarty->assign('categories', $this->smarty_categories);

        $this->smarty_properties = array();
        foreach ($game->getProperties() as $property) {
            array_push($this->smarty_properties, array(
                'id' => $property->getId(),
                'name' => $property->getProperty()
            ));
        }
        $this->smarty->assign('properties', $this->smarty_properties);

        $this->smarty->display('IMBAdminModules/AdminGameDetail.tpl');
    }

    /**
     * adds a property to a game
     * @param type $params ({"gameid":"1", "property":"abc"})
     */
    public function addGameProperty($params) {
        $gameid = $params->gameid;
        $propertyName = $params->property;

        $property = $this->managerGameProperty->getNew();
        $property->setGameId($gameid);
        $property->setProperty($propertyName);
        $this->managerGameProperty->insert($property);
        echo "Ok";
    }

    /**
     * delets a property to a game
     * @param type $params ({"gamepropertyid":"1"})
     */
    public function deleteGameProperty($params) {
        $gamepropertyid = $params->gamepropertyid;

        $this->managerGameProperty->delete($gamepropertyid);
        echo "Ok";
    }

    /**
     * adds a game
     * @param type $params ({ "name":"abc", "icon":"abc", "comment":"abc", "url":"abc", "forumlink":"abc", "gamecategories:["1","2","3","4"]"})
     */
    public function addGame($params) {
        $name = $params->name;
        $icon = $params->icon;
        $comment = $params->comment;
        $url = $params->url;
        $forumlink = $params->forumlink;

        $game = $this->managerGame->getNew();
        $game->setName($name);
        $game->setIcon($icon);
        $game->setComment($comment);
        $game->setUrl($url);
        $game->setForumlink($forumlink);

        $this->managerGame->insert($game);
    }

    /**
     * updates a game
     * @param type $params ({"gameid":"1", "name":"abc", "icon":"abc", "comment":"abc", "url":"abc", "forumlink":"abc", "gamecategories":"["1","2","3","4"]"})
     */
    public function updateGame($params) {
        $gameid = $params->gameid;
        $name = $params->name;
        $icon = $params->icon;
        $comment = $params->comment;
        $url = $params->url;
        $forumlink = $params->forumlink;
        $gamecategories = $params->gamecategories;

        $game = $this->managerGame->selectById($gameid);
        $game->setName($name);
        $game->setIcon($icon);
        $game->setComment($comment);
        $game->setUrl($url);
        $game->setForumlink($forumlink);
        $categories = array();
        foreach ($gamecategories as $categoryId) {
            array_push($categories, $this->managerGameCategory->selectById($categoryId));
        }
        $game->setCategories($categories);

        $this->managerGame->update($game);
        echo "Ok";
    }

    /**
     * delets a game
     * @param type $params ({"gameid":"1"})
     */
    public function deleteGame($params) {
        $gameid = $params->gameid;

        $this->managerGame->delete($gameid);
    }

    /**
     * views game catergory
     */
    public function viewGameCategory() {
        $categories = $this->managerGameCategory->selectAll();

        $this->smarty_categories = array();
        foreach ($categories as $category) {
            array_push($this->smarty_categories, array(
                'id' => $category->getId(),
                'name' => $category->getName()
            ));
        }
        $this->smarty->assign('categories', $this->smarty_categories);
        $this->smarty->display('IMBAdminModules/AdminGameCategory.tpl');
    }

    /**
     * adds a game category
     * @param type $params ({"name":"1"})
     */
    public function addGameCategory($params) {
        $name = $params->name;

        $category = $this->managerGameCategory->getNew();
        $category->setName($name);
        $this->managerGameCategory->insert($category);
    }

    /**
     * adds a game category
     * @param type $params ({"categoryid":"1", "categorycolumn":"abc", "value":"abc"})
     */
    public function updateGameCategory($params) {
        $categoryid = $params->categoryid;
        $categorycolumn = $params->categorycolumn;
        $value = $_POST["value"];

        $category = $this->managerGameCategory->selectById($categoryid);

        switch ($categorycolumn) {
            case "Name":
                $category->setName($value);
                break;

            default:
                break;
        }

        $this->managerGameCategory->update($category);
        echo $value;
    }

    /**
     * deletes a game category
     * @param type $params ({"categoryid":"1"})
     */
    public function deleteGameCategory($params) {
        $categoryid = $params->categoryid;

        $this->managerGameCategory->delete($categoryid);
    }

    /**
     * views users
     */
    public function viewUsers() {
        $users = $this->managerUser->selectAllUser(ImbaUserContext::getOpenIdUrl());

        $this->smarty_users = array();
        foreach ($users as $user) {
            array_push($this->smarty_users, array(
                'nickname' => $user->getNickname(),
                'userid' => $user->getId(),
                'lastonline' => ImbaSharedFunctions::getNiceAge($user->getLastonline()),
                'role' => $this->managerRole->selectByRole($user->getRole())->getName()
            ));
        }
        $this->smarty->assign('susers', $this->smarty_users);

        $this->smarty->display('IMBAdminModules/AdminUserOverview.tpl');
    }

    /**
     * views a user
     * @param type $params ({"userid":"1"})
     */
    public function viewUserDetail($params) {
        $user = $this->managerUser->selectById($params->userid);

        $this->smarty->assign('userid', $user->getId());
        $this->smarty->assign('nickname', $user->getNickname());
        $this->smarty->assign('lastname', $user->getLastname());
        $this->smarty->assign('shortlastname', substr($user->getLastname(), 0, 1) . ".");
        $this->smarty->assign('firstname', $user->getFirstname());
        $this->smarty->assign('birthday', $user->getBirthday() . "." . $user->getBirthmonth() . "." . $user->getBirthyear());
        $this->smarty->assign('icq', $user->getIcq());
        $this->smarty->assign('msn', $user->getMsn());
        $this->smarty->assign('skype', $user->getSkype());
        $this->smarty->assign('email', $user->getEmail());
        $this->smarty->assign('website', $user->getWebsite());
        $this->smarty->assign('motto', $user->getMotto());
        $this->smarty->assign('usertitle', $user->getUsertitle());
        $this->smarty->assign('avatar', $user->getAvatar());
        $this->smarty->assign('signature', $user->getSignature());
        $this->smarty->assign('openid', $user->getOpenid());
        $this->smarty->assign('lastonline', ImbaSharedFunctions::getNiceAge($user->getLastonline()));

        if (strtolower($user->getSex()) == "m") {
            $this->smarty->assign('sex', 'Images/male.png');
        } else if (strtolower($user->getSex()) == "w" || strtolower($user->getSex()) == "f") {
            $this->smarty->assign('sex', 'Images/female.png');
        } else {
            $this->smarty->assign('sex', '');
        }

        $allroles = array();
        foreach ($this->managerRole->selectAll() as $role) {
            array_push($allroles, array("id" => $role->getId(), "name" => $role->getName(), "role" => $role->getRole()));
        }
        $this->smarty->assign('allroles', $allroles);
        $this->smarty->assign('role', $user->getRole());

        $this->smarty->display('IMBAdminModules/AdminViewedituser.tpl');
    }

    /**
     * User Management
     * @param type $params ({"openid":"abc", "sex":"abc", "motto":"abc", 
     * "role":"1", "birthday":"11.11.1111", "lastname":"abc", "firstname":"abc", 
     * "usertitle":"abc", "avatar":"abc", "website":"abc", "nickname":"abc", 
     * "email":"abc", "skype":"abc", "icq":"abc", "msn":"abc", "signature":"abc",
     *  "id":"1"})
     */
    public function updateUser($params) {
        $user = new ImbaUser();
        $user = $this->managerUser->selectById($params->id);
        $user->setSex($params->sex);
        $user->setMotto($params->motto);

        $user->setRole($params->role);

        $birthdate = explode(".", $params->birthday);
        $user->setBirthday($birthdate[0]);
        $user->setBirthmonth($birthdate[1]);
        $user->setBirthyear($birthdate[2]);
        $user->setLastname($params->lastname);
        $user->setFirstname($params->firstname);
        $user->setUsertitle($params->usertitle);
        $user->setAvatar($params->avatar);
        $user->setWebsite($params->website);
        $user->setNickname($params->nickname);
        $user->setEmail($params->email);
        $user->setSkype($params->skype);
        $user->setIcq($params->icq);
        $user->setMsn($params->msn);
        $user->setSignature($params->signature);
        $this->managerUser->update($user);
        echo "Ok";
    }

}

?>
