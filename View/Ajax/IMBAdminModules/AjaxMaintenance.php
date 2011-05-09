<?php

/**
 * Handling the ajax Callbacks for the ImbaAdmin module Maintenance
 */
class AjaxMaintenance extends AjaxBase {

    public function __construct() {
        parent::__construct();
    }

    public function getNavigation() {
        /**
         * Define Navigation
         */
        $navigation = new ImbaContentNavigation();

        /**
         * Set module name
         */
        $navigation->setName("Maintenance");
        $navigation->setClassname(get_class($this));
        $navigation->setComment("Hier werden Wartungsarbeten am System durchgef&uuml;hrt.");

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
        $navigation->addElement("viewLogs", "Log", "View and clar log");
        $navigation->addElement("viewStatistics", "Statistics", "Statistics");
        $navigation->addElement("viewMaintenance", "Maintenance Jobs", "System maintenance jobs");
        $navigation->addElement("viewSettings", "Settings", "System wide settings");

        return $navigation;
    }

    /**
     * view settings
     */
    public function viewSettings() {
        $managerDatabase = ImbaManagerDatabase::getInstance();
        $settings = array();
        $managerDatabase->query("SELECT * FROM %s;", array(ImbaConstants::$DATABASE_TABLES_SYS_SETTINGS));
        while ($row = $managerDatabase->fetchRow()) {
            array_push($settings, array('name' => $row["name"], 'value' => $row["value"]));
        }
        $this->smarty->assign('settings', $settings);
        $this->smarty->display('IMBAdminModules/MaintenanceSettings.tpl');
    }

    /**
     * adds a setting
     * @param type $params ({"name":"abc", "value":"abc"})
     */
    public function addSetting($params) {
        $managerDatabase = ImbaManagerDatabase::getInstance();
        $managerDatabase->query("INSERT INTO %s SET name='%s', value='%s';", array(ImbaConstants::$DATABASE_TABLES_SYS_SETTINGS, $params->name, $params->value));
    }

    /**
     * updates a setting
     * @param type $params ({"name":"abc", "value":"abc"})
     */
    public function updateSetting($params) {
        $managerDatabase = ImbaManagerDatabase::getInstance();
        $managerDatabase->query("UPDATE %s SET value='%s' WHERE name='%s';", array(ImbaConstants::$DATABASE_TABLES_SYS_SETTINGS, $_POST["value"], $params->name));
        echo $_POST["value"];
    }

    /**
     * deletes a setting
     * @param type $params ({"name":"abc"})
     */
    public function deleteSetting($params) {
        $managerDatabase = ImbaManagerDatabase::getInstance();
        $managerDatabase->query("DELETE FROM %s WHERE name='%s';", array(ImbaConstants::$DATABASE_TABLES_SYS_SETTINGS, $params->name));
    }

    /**
     * Views the maintenance
     */
    public function viewMaintenance() {
        $maintenenceJobs = array();
        $dbJobs = array();
        $userJobs = array();
        $debugJobs = array();

        array_push($maintenenceJobs, array('handle' => 'clearLog', 'name' => 'Clear System Messages'));
        array_push($maintenenceJobs, array('handle' => 'findUnusedRoles', 'name' => 'Analyze User Roles'));
        array_push($maintenenceJobs, array('handle' => 'showSettings', 'name' => 'Show the $SETTINGS array'));
        array_push($maintenenceJobs, array('handle' => 'showProxyLogs', 'name' => 'Show Proxy Logs'));
        array_push($maintenenceJobs, array('handle' => 'deleteProxyLogs', 'name' => 'Delete Proxy Logs'));
        array_push($dbJobs, array('handle' => 'backupDatabase', 'name' => 'Create Database dump'));
        array_push($dbJobs, array('handle' => 'showDatabaseBackups', 'name' => 'Show Database backups'));
        array_push($userJobs, array('handle' => 'findIncompleteUsers', 'name' => 'Find incomplete User Profiles'));
        array_push($userJobs, array('handle' => 'fakeUsersOnline', 'name' => 'Fake some Users online status'));
        array_push($userJobs, array('handle' => 'kickAllOffline', 'name' => 'Kick all Users offline'));
        array_push($debugJobs, array('handle' => 'toggleDebug', 'name' => 'Toggle Debug'));

        $this->smarty->assign('maintenanceJobs', $maintenenceJobs);
        $this->smarty->assign('dbJobs', $dbJobs);
        $this->smarty->assign('userJobs', $userJobs);
        $this->smarty->assign('debugJobs', $debugJobs);
        $this->smarty->display('IMBAdminModules/MaintenanceMaintenance.tpl');
    }

    /**
     * runs a maintenance job
     * @param type $params ({"jobHandle":"abc"})
     */
    public function runMaintenanceJob($params) {
        $jobHandle = $params->jobHandle;

        $log = $this->managerLog->getNew();
        $log->setModule("Maintenance");
        switch ($jobHandle) {
            case "findUnusedRoles":
                $log->setMessage("Analyze User Roles");
                $this->smarty->assign('name', $log->getMessage());
                $log->setLevel(2);

                $users = $this->managerUser->selectAllUser();
                $roles = $this->managerRole->selectAll();
                $tmpRoles = array();
                $counts = array();
                foreach ($users as $user) {
                    if (!in_array($user->getRole(), $tmpRoles)) {
                        array_push($tmpRoles, $user->getRole());
                        $counts[$user->getRole()] = 1;
                    }
                    $counts[$user->getRole()]++;
                }

                $return = "";
                foreach ($roles as $role) {
                    if ($counts[$role->getRole()]) {
                        $count = $counts[$role->getRole()];
                    } else {
                        $count = 0;
                    }
                    $return .= $role->getRole() . " " . $role->getName() . ": " . $count . "<br />";
                }

                $this->managerLog->insert($log);
                $this->smarty->assign('message', $return);
                break;

            case "findIncompleteUsers":
                $log->setMessage("Find incomplete User Profiles");
                $this->smarty->assign('name', $log->getMessage());
                $log->setLevel(2);

                $return = "<b>These Members are missing at least one of the following fields:</b><br />";
                $return .= "<i>Nickname, Firstname, Lastname, OpenId</i><br /><br />";
                //$incompleteUsers = array();
                foreach ($this->managerUser->selectAllUser() as $user) {
                    $count = 0;

                    if ($user->getNickname() == null)
                        $count++;
                    if ($user->getFirstname() == null)
                        $count++;
                    if ($user->getLastname() == null)
                        $count++;
                    if ($user->getOpenId() == null)
                        $count++;

                    if ($count > 0) {
                        $return .= $user->getNickname() . ": " . $count . "<br />";
                    }
                }



                $this->managerLog->insert($log);
                $this->smarty->assign('message', $return);
                break;

            case "clearLog":
                $this->managerLog = ImbaManagerLog::getInstance();
                $this->managerLog->clearAll();

                $this->smarty->assign('name', 'Clear System Messages');
                $this->smarty->assign('message', 'Messages cleared!<br />');
                break;

            case "showDatabaseBackups":
                $backupPath = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER["PHP_SELF"]) . "/Backup/";

                $tmpOut = "<h4>Files in " . $backupPath . ":</h4>";
                if ($handle = opendir($backupPath)) {
                    $filesArray = array();
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != ".htaccess" && $file != ".gitignore") {
                            array_push($filesArray, '<a href="Backup/' . $file . '">' . $file . '</a>');
                        }
                    }
                    closedir($handle);
                    rsort($filesArray);
                    $firstBool = true;
                    foreach ($filesArray as $file) {
                        if ($firstBool)
                            $tmpOut .= "&gt; <b>";
                        else
                            $tmpOut .= "&nbsp;&nbsp;&nbsp;";
                        $tmpOut .= $file;
                        if ($firstBool) {
                            $tmpOut .= "</b> &lt;";
                            $firstBool = false;
                        }
                        $tmpOut .= "<br />";
                    }
                }

                $this->smarty->assign('name', 'Show Database backups');
                $this->smarty->assign('message', $tmpOut);
                break;

            case "backupDatabase":
                $backupPath = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER["PHP_SELF"]) . "/Backup/";
                $backupFile = ImbaConstants::$DATABASE_DB . "_" . date("Y-m-d-H-i-s") . '.gz';
                $command = "mysqldump --opt" .
                        " -h " . ImbaConstants::$DATABASE_HOST .
                        " -u " . ImbaConstants::$DATABASE_USER .
                        " -p" . ImbaConstants::$DATABASE_PASS .
                        " " . ImbaConstants::$DATABASE_DB . " | gzip > /tmp/" . $backupFile;
                system($command);
                system("mv /tmp/" . $backupFile . " " . $backupPath);

                $this->smarty->assign('name', 'Backup Database');
                $this->smarty->assign('message', 'You can download the actual dump from here:<br /><a href="Backup/' . $backupFile . '">' . $backupFile . '</a>');
                break;

            case "showSettings":
                $this->smarty->assign('name', 'Show the $SETTINGS array');
                ImbaConstants::loadSettings();
                $message = "";
                foreach (ImbaConstants::$SETTINGS as $key => $value) {
                    $message .= $key . ": " . $value . "<br />";
                }

                $this->smarty->assign('message', $message);
                break;

            case "showProxyLogs":
                touch("Logs/ImbaProxyLog.log");
                $this->smarty->assign('name', 'Show Proxy Logs');
                $this->smarty->assign('message', "<pre>" . file_get_contents("Logs/ImbaProxyLog.log") . "</pre>");
                break;

            case "deleteProxyLogs":
                unlink("Logs/ImbaProxyLog.log");
                touch("Logs/ImbaProxyLog.log");
                $this->smarty->assign('name', 'Show Proxy Logs');
                $this->smarty->assign('message', "Proxy log cleared");
                break;

            case "toggleDebug":
                $oldState = ImbaUserContext::getDebug();
                ImbaConstants::loadSettings();
                $newState = null;

                function debugSwitch($what) {
                    if ($what == "false") {
                        return "true";
                    } else {
                        return "false";
                    }
                }

                if (empty($oldState)) {
                    $oldState = ImbaConstants::$SETTINGS['ENABLE_JS_DEBUG'];
                }
                ImbaUserContext::setDebug(debugSwitch($oldState));

                $this->smarty->assign('name', 'Toggle Session Debug');
                $this->smarty->assign('message', "Debug is now set to: " . ImbaUserContext::getDebug() . "<br />Please reload page!");
                break;

            default:
                $this->smarty->assign('name', $jobHandle);
                $this->smarty->assign('message', 'unknown job: ' . $jobHandle);
        }
        $this->smarty->display('IMBAdminModules/MaintenanceMaintenanceRunJob.tpl');
    }

    /**
     * views the statistics
     */
    public function viewStatistics() {
        $this->smarty->assign('users', count($this->managerUser->selectAllUser()));
        $this->smarty->assign('userroles', count($this->managerRole->selectAll()));

        $logCount = 0;
        $sessions = array();
        foreach ($this->managerLog->selectAll() as $logEntry) {
            if (!in_array($logEntry->getSession(), $sessions)) {
                array_push($sessions, $logEntry->getSession());
                $logCount++;
            }
        }
        $this->smarty->assign('usersessions', $logCount);

        $this->smarty->assign('messages', $this->managerMessage->returnNumberOfMessages());
        $this->smarty->assign('logs', count($this->managerLog->selectAll()));

        $this->smarty->display('IMBAdminModules/MaintenanceStatistics.tpl');
    }

    /**
     * views the logs
     */
    public function viewLogs() {
        $logs = $this->managerLog->selectAll();

        $this->smarty_logs = array();
        foreach ($logs as $log) {
            if ($log->getLevel() <= 1) {
                $tmpUser = $this->managerUser->selectById($log->getUser());
                if ($tmpUser != null) {
                    $username = $tmpUser->getNickname();
                } else {
                    $username = "Anonymous";
                }

                if (count($log->getMessage() > 40)) {
                    $tmpMessage = substr($log->getMessage(), 0, 37) . "...";
                } else {
                    $tmpMessage = $log->getMessage();
                }

                array_push($this->smarty_logs, array(
                    'id' => $log->getId(),
                    'timestamp' => $log->getTimestamp(),
                    'age' => ImbaSharedFunctions::getAge($log->getTimestamp()),
                    'user' => $username,
                    'module' => $log->getModule(),
                    'message' => $tmpMessage,
                    'lvl' => $log->getLevel()
                ));
            }
        }
        $this->smarty->assign('logs', $this->smarty_logs);

        $this->smarty->display('IMBAdminModules/MaintenanceLog.tpl');
    }

    /**
     * views a logdetail
     * @param type $params ({"logid":"1"})
     */
    public function viewLogDetail($params) {
        /**
         * Get log entry
         */
        $log = $this->managerLog->selectId($params->logid);

        /**
         * Get user
         */
        if ($log->getUser() == null) {
            $user = "Anonymous";
        } else {
            $user = $this->managerUser->selectById($log->getUser())->getNickname();
        }

        /**
         * Get city trough GeoIP
         */
        include("Libs/GeoIP/GeoIP.php");
        // uncomment for Shared Memory support
        // geoip_load_shared_mem("/usr/local/share/GeoIP/GeoIPCity.dat");
        // $gi = geoip_open("/usr/local/share/GeoIP/GeoIPCity.dat",GEOIP_SHARED_MEMORY);
        $geoIpFilename = "/usr/local/share/GeoIP/GeoIPCity.dat";
        if (file_exists($geoIpFilename)) {
            $gi = geoip_open($geoIpFilename, GEOIP_STANDARD);
            $record = geoip_record_by_addr($gi, $log->getIp());
            $this->smarty->assign('city', $record->city . ", " . $record->country_name);
            geoip_close($gi);
            $this->smarty->assign('ip', $log->getIp());
        } else {
            $this->smarty->assign('ip', "GeoIP nicht konfiguriert.");
        }

        $this->smarty->assign('date', ImbaSharedFunctions::genTime($log->getTimestamp()));
        $this->smarty->assign('age', ImbaSharedFunctions::getAge($log->getTimestamp()));
        $this->smarty->assign('openid', $log->getUser());
        $this->smarty->assign('id', $log->getId());
        $this->smarty->assign('user', $user);
        $this->smarty->assign('module', $log->getModule());
        $this->smarty->assign('session', $log->getSession());
        $this->smarty->assign('message', $log->getMessage());
        $this->smarty->assign('level', $log->getLevel());

        $sessionLogs = $this->managerLog->selectAll();
        $this->smarty_logs = array();
        foreach ($sessionLogs as $sessionLog) {
            if ($sessionLog->getSession() == $log->getSession()) {
                $username = "Anonymous";
                if ($sessionLog->getUser() != "") {
                    $username = $this->managerUser->selectById($sessionLog->getUser())->getNickname();
                }

                array_push($this->smarty_logs, array(
                    'id' => $sessionLog->getId(),
                    'date' => ImbaSharedFunctions::getAge($sessionLog->getTimestamp()),
                    'module' => $sessionLog->getModule(),
                    'message' => $sessionLog->getMessage(),
                    'level' => $sessionLog->getLevel()
                ));
            }
        }
        $this->smarty->assign('logs', $this->smarty_logs);

        $this->smarty->display('IMBAdminModules/MaintenanceLogViewdetail.tpl');
    }

}

?>
