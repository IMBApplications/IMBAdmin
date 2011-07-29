<?php

/**
 * Handling the ajax Callbacks for the ImbaAdmin module Welcome
 */
class AjaxWelcome extends AjaxBase {

    private $tips = array();

    public function __construct() {
        parent::__construct();
        //FIXME: This is temporary code! should this be in the database?
        array_push($this->tips, "Strg + H macht den IMBAdmin unsichtbar.");
        array_push($this->tips, "Don't stand in the fire!");
        array_push($this->tips, "Klicke den Lichtbrunnen!");
        array_push($this->tips, "<a href='http://www.youtube.com/watch?v=QH2-TGUlwu4' target='_top'>&Uuml;berraschung!</a>");
        array_push($this->tips, "Minecraft Rockt!");
        array_push($this->tips, "Han shot first!");
    }

    public function getContentManager() {
        /**
         * Define Navigation
         */
        $navigation = new ImbaContentManager();

        /**
         * Set module name
         */
        $navigation->setName("Willkommen");
        $navigation->setClassname(get_class($this));
        $navigation->setComment("Dies ist die Willkommens Site.");

        /**
         * Set when the module should be displayed (logged in 1/0)
         */
        $navigation->setShowLoggedIn(false);
        $navigation->setShowLoggedOff(false);

        /**
         * Set the minimal user role needed to display the module
         */
        $navigation->setMinUserRole(1);

        /**
         * Set tabs
         */
        $navigation->addElement("viewHome", "Home", "Hier siehst du eine einfache &Uml;bersicht der Module.");
        //$navigation->addElement("viewUsermap", "Karte", "Hier siehst du eine Karte, von wo sich die User einloggen.");
        $navigation->addElement("viewWelcomeIndexed", "Indexierte &Uuml;bersicht", "Hier siehst du eine komplette &Uml;bersicht der Module.");
        return $navigation;
    }

    /**
     * return online color for the timestamp
     * @param type $timestamp 
     */
    private function genColor($timestamp) {
        if ($timestamp > (time() - 300)) {
            return "green";
        } elseif ($timestamp > (time() - 600)) {
            return "white";
        } elseif ($timestamp > (time() - 1200)) {
            return "yellow";
        } else {
            return "grey";
        }
    }

    /**
     * viewUsermap
     */
    public function viewUsermap() {
        $this->managerUser->setMeOnline();

        $myUsers = array();
        $allUsers = $this->managerUser->selectAllUser();

        /**
         * Get location infos trough GeoIP
         */
        include("Libs/GeoIP/GeoIP.php");
        $lats = array();
        $lons = array();
        $geoIpFilename = "/usr/local/share/GeoIP/GeoIPCity.dat";
        if (file_exists($geoIpFilename)) {
            $gi = geoip_open($geoIpFilename, GEOIP_STANDARD);

            foreach ($allUsers as $user) {
                $lastip = $user->getLastip();

                if (!empty($lastip)) {
                    $record = geoip_record_by_addr($gi, $lastip);

                    if ((!empty($record->latitude)) && (!empty($record->longitude))) {
                        if (!empty($record->city)) {
                            $location = $record->city . " (" . $record->country_code . ")";
                        } else {
                            $location = "" . $record->country_name;
                        }

                        array_push($myUsers, array(
                            "user" => $user->getNickname(),
                            "lastonline" => $user->getLastonline(),
                            "name" => $location,
                            "lat" => $record->latitude,
                            "lon" => $record->longitude
                        ));

                        array_push($lats, $record->latitude);
                        array_push($lons, $record->longitude);
                    }
                }
            }
            geoip_close($gi);

            natsort($lats);
            $latMin = current($lats);
            $latMax = end($lats);
            
            natsort($lons);
            $lonMin = current($lons);
            $lonMax = end($lons);
            
            $this->smarty->assign("latMin", $latMin);
            $this->smarty->assign("latMax", $latMax);
            $this->smarty->assign("lonMin", $lonMin);
            $this->smarty->assign("lonMax", $lonMax);

            $this->smarty->assign("latCenter", ($latMax + $latMin) / 2);
            $this->smarty->assign("lonCenter", ($lonMax + $lonMin) / 2);

            $userList = "";
            foreach ($myUsers as $user) {
                $key = hash("crc32", $user["lat"] . $user["lon"]);
                if (empty($userList[$key]["name"])) {
                    $userList[$key]["name"] = $user["name"];
                    $userList[$key]["lat"] = $user["lat"];
                    $userList[$key]["lon"] = $user["lon"];
                    $userList[$key]["userstr"] = $user["user"];
                    $userList[$key]["lastonline"] = $user["lastonline"];
                    $userList[$key]["count"] = 1;
                } else {
                    $userList[$key]["userstr"] .= ", " . $user["user"];
                    $userList[$key]["count"]++;
                    if ($user["lastonline"] > $userList[$key]["lastonline"])
                        $userList[$key]["lastonline"] = $user["lastonline"];
                }
                $userList[$key]["color"] = $this->genColor($user["lastonline"]);
            }

            $this->smarty->assign('locations', $userList);
        }
        $this->smarty->display('IMBAdminModules/WelcomeViewUsermap.tpl');
    }

    public function viewHome() {
        $this->managerUser->setMeOnline();
        $myself = $this->managerUser->selectMyself();
        $allUsers = $this->managerUser->selectAllUser();
        $smartyPortlets = array();
        $this->smarty->assign('nickname', $myself->getNickname());
        $this->smarty->assign("today", date("d") . "." . date("m") . " " . date("Y"));
        $this->smarty->assign("thrustRoot", urlencode(ImbaSharedFunctions::getSiteDomainUrl()));
        $this->smarty->assign("niceDomain", urldecode(ImbaSharedFunctions::getSiteDomainUrl()));
        $this->smarty->assign("tip", $this->tips[rand(1, count($this->tips))]);
        /*
         * TODO: Portlets to be done:
         * $events
         * $todo
         * $paypal
         * picoftheday
         */
        /*
         * Fill Navigation $navs
         */

        $this->smarty->assign('nickname', $myself->getNickname());
        $topNavigation = array();
        $navigations = AjaxBase::getModulesNavigation("IMBAdminModules");

        foreach ($navigations as $navigation) {
            if (ImbaUserContext::getUserRole() >= $navigation->getMinUserRole()) {
                $showMe = false;
                if (ImbaUserContext::getLoggedIn() && $navigation->getShowLoggedIn()) {
                    $showMe = true;
                } elseif ((!ImbaUserContext::getLoggedIn()) && $navigation->getShowLoggedOff()) {
                    $showMe = true;
                }

                if ($showMe) {
                    array_push($topNavigation, array(
                        "module" => $navigation->getClassname(),
                        "name" => $navigation->getName(),
                        "comment" => $navigation->getComment()
                    ));
                }
            }
        }
        $this->smarty->assign('navs', $topNavigation);

        /**
         * Fill $birthdays
         */
        $return = "";
        $birthdays = array();
        $todayMagicNumber = (date("n") * 31) + date("j");
        foreach ($allUsers as $user) {
            $magicNumber = ($user->getBirthmonth() * 31) + $user->getBirthday();
            $birthdayStr = $user->getNickname() . ": " . $user->getBirthday() . "." . $user->getBirthmonth() . " (" . (date("Y") - $user->getBirthyear()) . ")<br />";
            if ($magicNumber > 0) {
                $birthdays[$magicNumber] .= $birthdayStr;
            }
        }
        $count = 0;
        ksort($birthdays);
        foreach ($birthdays as $birthday => $string) {
            if ($birthday >= $todayMagicNumber) {
                if ($todayMagicNumber == $birthday) {
                    $return .= "<b>" . $string . "</b>";
                } else {
                    $return .= $string;
                }
                $count++;
                if ($count > 2) {
                    break;
                }
            }
        }
        if (!empty($return)) {
            array_push($smartyPortlets, array("name" => "N&auml;chste Geburtstage", "content" => $return));
        }

        /**
         * Fill $newMembers
         */
        $return = "";
        $newUsers = array();
        foreach ($allUsers as $user) {
            $newUsers[$user->getId()] = $user->getNickname();
        }
        krsort($newUsers);
        $count = 0;
        foreach ($newUsers as $id => $nickName) {
            if ($count > 2) {
                break;
            } else {
                $return .= $nickName . "<br />";
                $count++;
            }
        }
        if (!empty($return)) {
            array_push($smartyPortlets, array("name" => "Neue Mitglieder", "content" => $return));
        }

        $this->smarty->assign("portlets", $smartyPortlets);
        /**
         * Display the site
         */
        $this->smarty->display('IMBAdminModules/WelcomeViewHome.tpl');
    }

    /**
     * views the Welcome indexed
     */
    public function viewWelcomeIndexed() {
        $this->managerUser->setMeOnline();
        $navigations = AjaxBase::getModulesNavigation("IMBAdminModules");
        $topNavigation = array();

        foreach ($navigations as $navigation) {
            if (ImbaUserContext::getUserRole() >= $navigation->getMinUserRole()) {
                $showMe = false;
                if (ImbaUserContext::getLoggedIn() && $navigation->getShowLoggedIn()) {
                    $showMe = true;
                } elseif ((!ImbaUserContext::getLoggedIn()) && $navigation->getShowLoggedOff()) {
                    $showMe = true;
                }

                if ($showMe) {
                    $subNavigation = array();
                    foreach ($navigation->getOptions() as $subnav) {

                        array_push($subNavigation, array(
                            "module" => $navigation->getClassname(),
                            "ajaxmethod" => $subnav->getIdentifier(),
                            "name" => $subnav->getName(),
                            "comment" => $subnav->getComment()
                        ));
                    }

                    array_push($topNavigation, array(
                        "module" => $navigation->getClassname(),
                        "name" => $navigation->getName(),
                        "comment" => $navigation->getComment(),
                        "subnavs" => $subNavigation
                    ));
                }
            }
        }

        $this->smarty->assign('topnavs', $topNavigation);
        $this->smarty->display('IMBAdminModules/WelcomeIndex.tpl');
    }

}

?>
