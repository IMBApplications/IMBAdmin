<?php

/**
 * Handling the ajax callbacks for 'Users'.
 */
class AjaxUser extends AjaxBase {

    public function __construct() {
        parent::__construct();
    }

    public function getContentManager() {
// create the contentManager to be filled with informations about this module
        $contentManager = new ImbaContentManager();

// set required informations: name, classname and the comment for the module
        $contentManager->setName("Mitglieder");
        $contentManager->setClassname(get_class($this));
        $contentManager->setComment("Hier kannst du dich &uuml;ber Mitglieder informieren sowie das eigene Profil editiert werden.");

// set access conditions
// users logged in must be able to access this module
        $contentManager->setShowLoggedIn(true);
// users not logged in must not be able to access this module
        $contentManager->setShowLoggedOff(false);

// set required user role level to get access
// nearly all users are able to access this module
        $contentManager->setMinUserRole(1);


// add elements
// element for user overview 
        $contentManager->addElement("viewUsers", "Mitglieder &Uuml;bersicht", "Hier kannst du alles &uuml;ber unsere anderen Mitglieder erfahren.");
// element for editing user's profile
        $contentManager->addElement("viewEditMyProfile", "Mein Profil Editieren", "Hier kannst du dein Profil editieren.");
// element for editing user's game participations
        $contentManager->addElement("viewMyGames", "Meine Spiele Editieren", "Hier kannst du deine Spiele editieren.");
// element to view user's profile
        $contentManager->addElement("viewmyprofile", "Mein Profil Ansehen", "Hier kannst du dein Profil so ancheuen.");

        return $contentManager;
    }

    /**
     * Gets a list of online users as JSON
     */
    public function loadUsersOnline() {
        $users = $this->managerUser->selectAllUserButme(ImbaUserContext::getOpenIdUrl());
        $result = array();
        $msgCountMin = -1;
        $msgCountMax = -1;

        foreach ($users as $user) {
//Ich finde das unlogin, cernu
//if (date("d-m-Y") == date("d-m-Y", $user->getLastonline())) {
//show all users which were onilne within tle last 36 houres
            if ($user->getLastonline() > (time() - (60 * 60 * 24 * 3))) {
// Setting the color, depending on time
// < 5 min => lime
// < 10min => orange
// < 30min => yellow
// default => white
                $timediff = date("U") - $user->getLastonline();

                if ($timediff <= (5 * 60)) {
                    $color = "lime";
                } else if ($timediff <= (10 * 60)) {
                    $color = "orange";
                } else if ($timediff <= (20 * 60)) {
                    $color = "yellow";
                } else if ($timediff <= (30 * 60)) {
                    $color = "white";
                } else {
                    $color = "gray";
                }

                $msgCount = $this->managerMessage->selectMessagesCount($user->getId());

                if ($msgCount > $msgCountMax || $msgCountMax == -1)
                    $msgCountMax = $msgCount;
                if ($msgCount < $msgCountMin || $msgCountMin == -1)
                    $msgCountMin = $msgCount;

                array_push($result, array("name" => $user->getNickname(), "id" => $user->getId(), "fontsize" => "8", "color" => $color, "msgCount" => $msgCount));
            }
        }

        $hundredPercent = $msgCountMax - $msgCountMin;
        if ($hundredPercent == 0)
            $hundredPercent = $msgCountMax;

        foreach ($result as $key => $user) {
            $tmpMsgCount = $user["msgCount"] - $msgCountMin;
            if ($hundredPercent < 1) {
                $hundredPercent = 1;
            }
            $tmpPercent = round(100 / $hundredPercent * $tmpMsgCount, 0);
            $result[$key]["fontsize"] = min(20, round(6 / 100 * $tmpPercent) + 8);
            $result[$key]["fontsize"] = max(8, $result[$key]["fontsize"]);
        }

        echo json_encode($result);
    }

    /**
     * Gets a list of users as JSON
     */
    public function loadUserList() {
        $users = $this->managerUser->selectAllUserButme(ImbaUserContext::getOpenIdUrl());
        $result = array();
        foreach ($users as $user) {
            array_push($result, array("name" => $user->getNickname(), "openid" => $user->getOpenId(), "lastonline" => $user->getLastonline()));
        }

        echo json_encode($result);
    }

    /**
     * Gets a list of users as JSON, with starting like
     * @param type $param ({"startwith":"ABC"})
     */
    public function loadUsersStartwith($param) {
        $startwith = $param->startwith;
        if (trim($startwith) != "") {
            $users = $this->managerUser->selectAllUserStartWith($startwith);
            $result = array();
            foreach ($users as $user) {
                array_push($result, array("user" => true, "name" => $user->getNickname(), "id" => $user->getId()));
            }

            echo json_encode($result);
        }
    }

    /**
     * Checks if current user is logged in, or needs to register
     */
    public function getCurrentUserStatus() {
        if (ImbaUserContext::getLoggedIn()) {
            echo $this->managerUser->selectMyself()->getNickname();
        } elseif (ImbaUserContext::getNeedToRegister()) {
            echo "Need to register";
        } else {
            echo "Not logged in";
        }
    }

    /**
     * Return currently logged in User
     */
    public function returnMyself() {
        $user = $this->managerUser->selectMyself();
        if ($user != null) {
            echo json_encode(array("id" => $user->getId(), "openid" => $user->getOpenId(), "name" => $user->getNickname()));
        } else {
            echo json_encode(array("id" => -1, "openid" => "not logged in", "name" => "not logged in"));
        }
    }

    /**
     * Generate visual representation to view all users.
     */
    public function viewUsers() {
// get all users (without the actual user itself)
        $users = $this->managerUser->selectAllUserButme(ImbaUserContext::getOpenIdUrl());

        $smarty_users = array();
// copy and convert the user object into the smarty formattation
        foreach ($users as $user) {
            array_push($smarty_users, array(
                'id' => $user->getId(),
                'nickname' => $user->getNickname(),
                'openid' => $user->getOpenID(),
                'lastonline' => ImbaSharedFunctions::getNiceAge($user->getLastonline()),
                'jabber' => "",
                'games' => $user->getGamesStr()
            ));
        }
        $this->smarty->assign('susers', $smarty_users);
        $this->smarty->display('IMBAdminModules/UserOverview.tpl');
    }

    public function viewMyProfile() {
        $params->id = ImbaUserContext::getUserId();
        $this->viewUserProfile($params);
    }

    /**
     * Views a user profile
     * @param type $param ({"id":"1"})
     */
    public function viewUserProfile($params) {
        $user = $this->managerUser->selectById($params->id);

        $this->smarty->assign('nickname', $user->getNickname());
        $this->smarty->assign('lastname', substr($user->getLastname(), 0, 1) . ".");
        $this->smarty->assign('firstname', $user->getFirstname());
        $this->smarty->assign('birthday', $user->getBirthday());
        $this->smarty->assign('birthmonth', $user->getBirthmonth());
        $this->smarty->assign('birthyear', $user->getBirthyear());
        $this->smarty->assign('icq', $user->getIcq());
        $this->smarty->assign('msn', $user->getMsn());
        $this->smarty->assign('skype', $user->getSkype());
        $this->smarty->assign('website', $user->getWebsite());
        $this->smarty->assign('motto', $user->getMotto());
        $this->smarty->assign('avatar', $user->getAvatar());
        $this->smarty->assign('openid', $user->getOpenid());
        $this->smarty->assign('usertitle', $user->getUsertitle());
        $this->smarty->assign('signature', $user->getSignature());
        $this->smarty->assign('lastonline', ImbaSharedFunctions::getNiceAge($user->getLastonline()));

        if (strtolower($user->getSex()) == "m") {
            $this->smarty->assign('sex', 'Images/male.png');
        } else if (strtolower($user->getSex()) == "w" || strtolower($user->getSex()) == "f") {
            $this->smarty->assign('sex', 'Images/female.png');
        } else {
            $this->smarty->assign('sex', '');
        }

        $role = $this->managerRole->selectByRole($user->getRole());

        $this->smarty->assign('role', $role->getName());
        $this->smarty->assign('roleIcon', $role->getIcon());
        $this->smarty->assign('myownprofile', false);
        $this->smarty->display('IMBAdminModules/UserViewprofile.tpl');
    }

    /**
     * Views the edit my profile
     */
    public function viewEditMyProfile() {
        $user = $this->managerUser->selectById(ImbaUserContext::getUserId());

        $this->smarty->assign('userid', $user->getId());
        $this->smarty->assign('nickname', $user->getNickname());
        $this->smarty->assign('lastname', $user->getLastname());
        $this->smarty->assign('shortlastname', substr($user->getLastname(), 0, 1) . ".");
        $this->smarty->assign('firstname', $user->getFirstname());
        $this->smarty->assign('birthday', $user->getBirthday());
        $this->smarty->assign('birthmonth', $user->getBirthmonth());
        $this->smarty->assign('birthyear', $user->getBirthyear());
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

        $role = $this->managerRole->selectByRole($user->getRole());

        $this->smarty->assign('role', $role->getName());
        $this->smarty->assign('roleIcon', $role->getIcon());


        $this->smarty->display('IMBAdminModules/UserMyprofile.tpl');
    }

    /**
     * User Management
     * @param type $params ({"openid":"abc", "sex":"abc", "motto":"abc", 
     * "role":"1", "birthday":"11.11.1111", "lastname":"abc", "firstname":"abc", 
     * "usertitle":"abc", "avatar":"abc", "website":"abc", "nickname":"abc", 
     * "email":"abc", "skye":"abc", "icq":"abc", "msn":"abc", "signature":"abc",
     *  "id":"1"})
     */
    public function updateMyProfile($params) {
        $user = new ImbaUser();
        $user = $this->managerUser->selectById(ImbaUserContext::getUserId());
        $user->setMotto($params->motto);
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

    /**
     * Update my Password
     */
    public function updateMyPassword($params) {
        $user = new ImbaUser();
        $user = $this->managerUser->selectById(ImbaUserContext::getUserId());

        if ((!empty($params->oldPassword)) && (!empty($params->newPassword1)) && (!empty($params->newPassword2))) {
            if (md5($params->oldPassword) == $user->getPassword()) {
                //old password is ok
                if ($params->newPassword1 == $params->newPassword2) {
                    $newPassword = trim($params->newPassword1);
                    //new passwords are the same
                    //check password for strengts
                    $checkResult = ImbaSharedFunctions::checkPassword($newPassword);
                    if ($checkResult === true) {
                        $user->setPassword(md5($newPassword));
                        $this->managerUser->update($user);
                        echo "Ok";
                    } else {
                        echo $checkResult;
                    }
                } else {
                    echo "The new passwords do not match.";
                }
            } else {
                echo "Wrong old password submited.";
            }
        } else {
            echo "Please fill out all the fields.";
        }
    }

    /**
     * Views my games
     */
    public function viewMyGames() {
        $user = $this->managerUser->selectById(ImbaUserContext::getUserId());
        $games = $this->managerGame->selectAll();

        $this->smarty_games = array();
        foreach ($games as $game) {
// fetch the games
            $selected = "false";
            foreach ($user->getGames() as $usrGame) {
                if ($usrGame != null) {
                    if ($usrGame->getId() == $game->getId()) {
                        $selected = "true";
                    }
                }
            }

// fetch all available properties
            $properties = array();
            foreach ($game->getProperties() as $property) {
                array_push($properties, array(
                    'id' => $property->getId(),
                    'property' => $property->getProperty()
                ));
            }

// fetch all properties with value
            $propertyValues = array();
            foreach ($user->getGamesPropertyValues() as $property) {
                if ($property != null) {
                    if ($game->getId() == $property->getProperty()->getGameId())
                        array_push($propertyValues, array(
                            'id' => $property->getId(),
                            'property' => $property->getProperty()->getProperty(),
                            'value' => $property->getValue()
                        ));
                }
            }

            array_push($this->smarty_games, array(
                'id' => $game->getId(),
                'name' => $game->getName(),
                'selected' => $selected,
                'properties' => $properties,
                'propertyValues' => $propertyValues
            ));
        }
        $this->smarty->assign('games', $this->smarty_games);

        $this->smarty->display('IMBAdminModules/UserMyGames.tpl');
    }

    /**
     * Adds a property to my games
     * @param type $params ({"gamesIPlay":[{"gameid":"1", "checked":"true|false"}]})
     */
    public function updateMyGames($params) {
        $user = new ImbaUser();
        $user = $this->managerUser->selectById(ImbaUserContext::getUserId());
        $user->setGames(array());
        foreach ($params->gamesIPlay as $gameIPlay) {
            if ($gameIPlay->checked == true) {
                $game = $this->managerGame->selectById($gameIPlay->gameid);
                $user->addGame($game);
            }
        }

        $this->managerUser->update($user);
        echo "Ok";
    }

    /**
     * Adds a property to my games
     * @param type $params ({"propertyid":"1", "propertyvalue":"abc"})
     */
    public function addPropertyToMyGames($params) {
        $user = new ImbaUser();
        $user = $this->managerUser->selectById(ImbaUserContext::getUserId());

        $gamesPropertyValue = new ImbaGamePropertyValue();
        $gamesPropertyValue->setProperty($this->managerGameProperty->selectById($params->propertyid));
        $gamesPropertyValue->setUser($user);
        $gamesPropertyValue->setValue($params->propertyvalue);

        $user->addGamesPropertyValues($gamesPropertyValue);

        $this->managerUser->update($user);
        echo "Ok";
    }

}

?>
