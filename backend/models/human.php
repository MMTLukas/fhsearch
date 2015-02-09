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
    protected $department;
    protected $type;
    protected $id;
    protected $email;
    protected $pictureUrlFhs;
    protected $pictureUrlLocal;

    public static function fromOverview($prename, $lastname, $department, $type, $id){
        $human = new self();

        $human->prename = $prename;
        $human->lastname = $lastname;
        $human->department = $department;
        $human->type = $type;
        $human->id = $id;

        return $human;
    }

    public static function fromDetails($id, $email, $pictureUrlFhs) {
        $human = new self();

        $human->id = $id;
        $human->email = $email;
        $human->pictureUrlFhs = $pictureUrlFhs;

        return $human;
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

}