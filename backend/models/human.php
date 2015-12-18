<?php

/**
 * Created by PhpStorm.
 * User: Enthusiasmus
 * Date: 03.08.14
 * Time: 15:17
 */
class Human
{
    protected $prename;
    protected $lastname;
    protected $mobile;
    protected $phone;
    protected $room;
    protected $department;
    protected $type;
    protected $id;
    protected $email;
    protected $state;
    protected $pictureUrlFhs;
    protected $pictureUrlLocal;

    public static function fromOverview($prename, $lastname, $department, $type, $id)
    {
        $human = new self();

        $human->prename = $prename;
        $human->lastname = $lastname;
        $human->department = $department;
        $human->type = $type;
        $human->id = $id;

        return $human;
    }

    public static function fromDetails($id, $email, $pictureUrlFhs, $phone, $mobile, $room, $state)
    {
        $human = new self();

        $human->id = $id;
        $human->email = $email;
        $human->pictureUrlFhs = $pictureUrlFhs;
        $human->phone = $phone;
        $human->mobile = $mobile;
        $human->room = $room;
        $human->state = $state;

        return $human;
    }

    public function getFullText()
    {
        $addition = "";

        //TODO add other departments and shorten names
        if (strpos($this->email, "mma") !== false) {
            $addition = "MultiMediaArt";
        }
        elseif(strpos($this->department, "MultiMediaArt") !== false){
            $addition = "mma";
        }

        if (strpos($this->email, "mmt") !== false) {
            $addition = "MultiMediaTechnology";
        }
        elseif(strpos($this->department, "MultiMediaTechnology") !== false){
            $addition = "mmt";
        }

        $state = str_replace(",", "", $this->state);
        $state = str_replace("\n", " ", $state);

        return $this->prename . " " . $this->lastname . " " . $this->department . " " . $addition .
            " " . $this->email . " " . $this->id . " " . $this->mobile . " " . $this->phone . " " .
            $this->room . " " . $this->type . " " . $state;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($value)
    {
        $this->lastname = $value;
    }

    public function getDepartment()
    {
        return $this->department;
    }

    public function setDepartment($value)
    {
        $this->department = $value;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getPictureUrlFhs()
    {
        return $this->pictureUrlFhs;
    }

    public function setPictureUrlFhs($value)
    {
        $this->pictureUrlFhs = $value;
    }

    public function getPictureUrlLocal()
    {
        return $this->pictureUrlLocal;
    }

    public function setPictureUrlLocal($value)
    {
        $this->pictureUrlLocal = $value;
    }

    public function getPrename()
    {
        return $this->prename;
    }

    public function setPrename($value)
    {
        $this->prename = $value;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($value)
    {
        $this->phone = $value;
    }

    public function getMobile()
    {
        return $this->mobile;
    }

    public function setMobile($value)
    {
        $this->mobile = $value;
    }

    public function getRoom()
    {
        return $this->room;
    }

    public function setRoom($value)
    {
        $this->room = $value;
    }

}