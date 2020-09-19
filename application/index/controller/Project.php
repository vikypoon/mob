<?php
namespace app\index\controller;

class Project extends Base
{
    public function index()
    {
        return $this->fetch();
    }
}
