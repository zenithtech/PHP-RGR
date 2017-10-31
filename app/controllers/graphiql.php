<?php

class Graphiql extends Controller
{

    public function __construct()
    {
    }

    public function index()
    {
		$this->view('graphiql/index');
    }

}
