<?php

/**
 * Handling the ajax Callbacks for Messages
 */
class AjaxMessenger extends AjaxBase {

    public function __construct() {
        parent::__construct();
    }

    public function getContentManager() {

        /**
         * Define Navigation
         */
        $navigation = new ImbaContentManager();

        /**
         * Set module name
         */
        $navigation->setName("Nachrichten");
        $navigation->setClassname(get_class($this));
        $navigation->setComment("Hier kannst du deine Chateinstellungen anpassen und vergangene Gespr&auml;che ansehen.");

        /**
         * Set when the module should be displayed (logged in 1/0)
         */
        $navigation->setShowLoggedIn(true);
        $navigation->setShowLoggedOff(false);

        /**
         * Set the minimal user role needed to display the module
         */
        $navigation->setMinUserRole(1);

        /**
         * Set tabs
         */
        $navigation->addElement("viewMessageOverview", "Nachrichten &Uuml;bersicht", "Hier kannst vergangene Konversationen ansehen.");
        $navigation->addElement("viewchatoverview", "Chat &Uuml;bersicht", "Hier kannst du vergangene Chats ansehen.");

        return $navigation;
    }

    /**
     * This method creats a complete refresh JSON, with
     * - new messages
     * - current users in open chats
     * - new messages in open chats
     * - ping open chats
     * @param type $params ({"channelids":["1","2","3","4"], "channelsinces":["1","2","3","4"]})
     */
    public function getAllNewsForMe($params) {
        // load new messages
        $resultConversations = array();
        foreach ($this->managerMessage->selectMyNewMessages() as $conversations) {
            $conversation = $this->managerMessage->selectAllByOpponentId($conversations["id"], 1);

            $newMessages = array();

            foreach ($conversation as $message) {
                $time = date("d.m.y H:i:s", $message->getTimestamp());
                $sender = $message->getSender()->getNickname();
                $msg = $message->getMessage();

                array_push($newMessages, array("time" => $time, "sender" => $sender, "message" => $msg));
            }

            $resultConversations[$this->managerUser->selectById($conversations["id"])->getId()] = $newMessages;
        }

        // ping chats first so users are not listed, if they are toooo long away
        $this->managerChatChannel->channelPing($params->channelids);

        // do things for chatting
        $resultUsersInChannel = array();
        $resultMessagesInChannel = array();
        for ($i = 0; $i < count($params->channelids); $i++) {
            $channelId = $params->channelids[$i];
            $channelSince = $params->channelsinces[$i];
            $channel = $this->managerChatChannel->selectById($channelId);

            // load current users in open chats
            $usersInChannel = $this->managerChatChannel->channelUsers($channelId);
            $resultUsersInChannel[$channelId] = $usersInChannel;

            // load new messages in open chats
            $newMessages = array();
            foreach ($this->managerChatMessage->selectAllByChannel($channel, $channelSince) as $message) {
                array_push($newMessages, array(
                    "id" => $message->getId(),
                    "time" => date("d.m.y H:i:s", $message->getTimestamp()),
                    "sender" => $message->getSender()->getNickname(),
                    "message" => $message->getMessage()
                ));
            }
            $resultMessagesInChannel[$channelId] = $newMessages;
        }

        $result = array("newmessages" => $resultConversations, "usersinchannel" => $resultUsersInChannel, "newchatmessages" => $resultMessagesInChannel);

        /*
          echo "<pre>";
          print_r($result);
          echo "</pre>";
          // */
        echo json_encode($result);
    }

    /**
     * Got something new for user?
     */
    public function gotNewMessages() {
        echo json_encode($this->managerMessage->selectMyNewMessages());
    }

    /**
     * Send a Message
     * @param type $params ({"reciever":"1", "message":"Hello, World"})
     */
    public function sendMessage($params) {
        $reciever = $params->reciever;
        $messageTxt = $params->message;

        $message = new ImbaMessage();
        $message->setSender($this->managerUser->selectById(ImbaUserContext::getUserId()));
        $message->setReceiver($this->managerUser->selectById($reciever));
        $message->setMessage($messageTxt);
        $message->setTimestamp(date("U"));
        $message->setXmpp(0);
        $message->setNew(1);
        $message->setSubject("AJAX GUI");

        $id = $this->managerMessage->insert($message);
        $this->managerUser->setMeOnline();
        echo "Ok" . $id;
    }

    /**
     * Set read for messages by Reciever
     * @param type $param ({"reciever":"1"})
     */
    public function setReadByReciever($param) {
        $reciever = $param->reciever;
        $this->managerMessage->setMessageRead($reciever);
    }

    /**
     * Recieve Messages
     * @param type $param ({"reciever":"1"})
     */
    public function loadMessages($params) {
        $reciever = $params->reciever;
        $conversation = $this->managerMessage->selectAllByOpponentId($reciever, -1);

        $result = array();
        foreach ($conversation as $message) {
            $time = date("d.m.y H:i:s", $message->getTimestamp());
            $sender = $message->getSender()->getNickname();
            $msg = $message->getMessage();

            array_push($result, array("time" => $time, "sender" => $sender, "message" => $msg));
        }
        echo json_encode($result);
    }

    /**
     * loads all channels
     */
    public function loadChannels() {
        $result = array();
        foreach ($this->managerChatChannel->selectAll() as $channel) {
            array_push($result, array("user" => false, "channel" => $channel->getName(), "channelId" => $channel->getId()));
        }
        echo json_encode($result);
    }

    /**
     * Gets the Users in a channel
     * @param type $param ({"channelid":"1"})
     */
    public function getUsersInChannel($params) {
        $channelid = $params->channelid;
        echo json_encode($this->managerChatChannel->channelUsers($channelid));
    }

    /**
     * init the ChatMessages
     * @param type $param ({"channelid":"1"})
     */
    public function initChat($params) {
        $channelid = $params->channelid;
        $result = array();
        $channel = $this->managerChatChannel->selectById($channelid);
        foreach ($this->managerChatMessage->selectAllByChannel($channel, -1) as $message) {
            array_push($result, array(
                "id" => $message->getId(),
                "time" => date("d.m.y H:i:s", $message->getTimestamp()),
                "nickname" => $message->getSender()->getNickname(),
                "message" => $message->getMessage()
            ));
        }
        $result = array_reverse($result);
        echo json_encode(array("users" => $this->managerChatChannel->channelUsers($channelid), "messages" => $result));
    }

    /**
     * Send a ChatMessages
     * @param type $param ({"channelid":"1", "message":"Hallo, Welt"})
     */
    public function sendChatMessage($params) {
        $channelid = $params->channelid;
        $messageTxt = $params->message;

        if (trim($messageTxt) != "") {
            $channel = $this->managerChatChannel->selectById($channelid);
            $message = new ImbaChatMessage();
            $message->setChannel($channel);
            $message->setMessage($messageTxt);

            $id = $this->managerChatMessage->insert($message);
            echo "Ok" . $id;
        } else {
            echo "No Message";
        }
    }

    /**
     * Close a Chat
     * @param type $param ({"channelid":"1"})
     */
    public function closeChat($param) {
        $channelid = $params->channelid;
        $this->managerChatChannel->channelClose($channelid);
    }

    /**
     * Pings the User into a channel
     * @param type $param ({"channelids":["1","2","3","4"]})
     */
    public function pingChats($params) {
        $channelids = $params->channelids;
        $this->managerChatChannel->channelPing($channelids);
    }

    /**
     * Gets the Users in a channel
     * @param type $param ({"channelid": "1"})
     */
    public function getChannelUsers($params) {
        echo json_encode($this->managerChatChannel->channelUsers($params->channelid));
    }

    /**
     * views the chat history
     */
    public function viewChatHistory() {
        $smartyMessages = array();

        array_push($smartyMessages, array(
            "openid" => "",
            "nickname" => "",
            "timestamp" => "",
            "message" => ""
        ));

        $this->smarty->assign("messages", $smartyMessages);

        $this->smarty->display('IMBAdminModules/MessagingChatHistory.tpl');
    }

    /**
     * views the Message overview
     */
    public function viewMessageOverview() {
        $newList = array();
        foreach ($this->managerUser->selectAllUserButme(ImbaUserContext::getUserId()) as $user) {
            $timestamp = $this->managerMessage->selectLastMessageTimestamp($user->getOpenId());
            array_push($newList, array("timestamp" => $timestamp, "id" => $user->getId()));
        }

        $smartyConversations = array();
        foreach ($newList as $item) {
            if ($this->managerMessage->selectMessagesCount($item['id']) > 0) {
                $tmpUser = $this->managerUser->selectById($item['id']);
                $tmpNickname = $tmpUser->getNickname();
                array_push($smartyConversations, array(
                    "id" => $item['id'],
                    "nickname" => $tmpNickname,
                    "lastmessagets" => $item['timestamp'],
                    "lastmessagestr" => ImbaSharedFunctions::getNiceAge($item['timestamp']),
                    "nummessages" => $this->managerMessage->selectMessagesCount($item['id'])
                ));
            }
        }

        $this->smarty->assign("users", $smartyConversations);
        $this->smarty->display('IMBAdminModules/MessagingMessageOverview.tpl');
    }

    /**
     * views the message history between me and a user
     * @param type $param ({"reciever":"1"})
     */
    public function viewMessageHistory($params) {
        $smartyMessages = array();
        foreach ($this->managerMessage->selectAllByOpponentId($params->reciever, 0) as $myMessage) {
            $myTimestamp = $myMessage->getTimestamp();
            $myTimestring = ImbaSharedFunctions::getNiceAge($myTimestamp);
            array_push($smartyMessages, array(
                "userid" => $myMessage->getSender()->getId(),
                "nickname" => $myMessage->getSender()->getNickname(),
                "timestamp" => $myTimestamp,
                "timestring" => $myTimestring,
                "message" => $myMessage->getMessage()
            ));
        }

        $this->smarty->assign("messages", $smartyMessages);
        $this->smarty->display('IMBAdminModules/MessagingMessageHistory.tpl');
    }

    /**
     * views the Chat Overview
     */
    public function viewChatOverview() {
        $smartyChatschannels = array();

        foreach ($this->managerChatChannel->selectAll() as $tmpChannel) {
            array_push($smartyChatschannels, array(
                "id" => $tmpChannel->getId(),
                "name" => $tmpChannel->getName(),
                "lastmessage" => "Fixme",
                "nummessages" => "Fixme"
            ));
        }

        $this->smarty->assign("channels", $smartyChatschannels);
        $this->smarty->display('IMBAdminModules/MessagingChatOverview.tpl');
    }

}

?>
