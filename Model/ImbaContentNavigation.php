<?php

/**
 * FIXME: No integration of user roles for security!
 */

/**
 * Base class for all navigations
 */
class ImbaContentNavigation {

    private $Name = null;
    private $Options = array();
    private $ShowLoggedIn = false;
    private $ShowLoggedOff = false;
    private $MinUserRole = 99;
    private $Comment = null;
    private $classname = null;

    public function getName() {
        return $this->Name;
    }

    public function setName($Name) {
        $this->Name = $Name;
    }

    public function getShowLoggedIn() {
        return $this->ShowLoggedIn;
    }

    public function setShowLoggedIn($ShowLoggedIn) {
        $this->ShowLoggedIn = $ShowLoggedIn;
    }

    public function getShowLoggedOff() {
        return $this->ShowLoggedOff;
    }

    public function setShowLoggedOff($ShowLoggedOff) {
        $this->ShowLoggedOff = $ShowLoggedOff;
    }

    public function getMinUserRole() {
        return $this->MinUserRole;
    }

    public function setMinUserRole($MinUserRole) {
        $this->MinUserRole = $MinUserRole;
    }

    public function getComment() {
        return $this->Comment;
    }

    public function getOptions() {
        return $this->Options;
    }

    public function setComment($Comment) {
        $this->Comment = $Comment;
    }

    public function getElements() {
        $elements = array();
        foreach ($this->Options as $Option) {
            array_push($elements, $Option->getIdentifier());
        }
        return $elements;
    }

    public function addElement($Identifier, $Name, $Comment) {
        $newElement = new ImbaContentNavigationOption();
        $newElement->setName($Name);
        $newElement->setComment($Comment);
        $newElement->setIdentifier($Identifier);
        array_push($this->Options, $newElement);
    }

    public function getElementName($Identifier) {
        foreach ($this->Options as $Option) {
            if ($Option->getIdentifier() == $Identifier) {
                return $Option->getName();
            }
        }
    }

    public function getElementComment($Identifier) {
        foreach ($this->Options as $Option) {
            if ($Option->getIdentifier() == $Identifier) {
                return $Option->getComment();
            }
        }
    }

    public function getElement($Identifier) {
        foreach ($this->Options as $Option) {
            if ($Option->getIdentifier() == $Identifier) {
                return $Option->get();
            }
        }
    }

    public function getClassname() {
        return $this->classname;
    }

    public function setClassname($classname) {
        $this->classname = $classname;
    }

}

/**
 * Class for navigation options
 */
class ImbaContentNavigationOption {

    /**
     * Fields for class ImbaContentNavigationOption
     */
    protected $Identifier = null;
    protected $Name = null;
    protected $Comment = null;

    public function getIdentifier() {
        return $this->Identifier;
    }

    public function setIdentifier($Identifier) {
        $this->Identifier = $Identifier;
    }

    public function getName() {
        return $this->Name;
    }

    public function setName($Name) {
        $this->Name = $Name;
    }

    public function getComment() {
        return $this->Comment;
    }

    public function setComment($Comment) {
        $this->Comment = $Comment;
    }

    public function get() {
        return $this;
    }

}

?>