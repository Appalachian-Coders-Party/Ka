<?php
class KaWpDispatcher
{
    private $controller;    // the controller object from the url (string)
    private $action;        // the action from the url (string)
    private $params;        // the rest of the parameters from the url (array)

    public function __construct()
    {
        // Add 'controller' and 'action' strings
        $this->addPrepends();

        // Setters
        $this->setController();
        $this->setAction();
        $this->setParams();


        // Test to see if the controller and action exist
        try 
        {
            if (!class_exists($this->controller))
            {
                throw new Exception("We don't like the controller, ".$this->controller.", you requested.");
            } else {
                $controller=new $this->controller;
            }

            if (!method_exists($controller,$this->action))
            {
                throw new Exception("We don't like the action you requested.");
            }

        } catch(Exception $e) {
            include(KA.'/error_docs/Exception.php');
            exit();
        }
    }

    public function setParams()
    {
        $this->params=array();

		unset($_GET['controller']);
		unset($_GET['action']);
		unset($_GET['page']);

		$this->params=$_GET;
    }

    public function setController()
    {
        $this->controller=$_GET['controller'];
    }

    public function setAction()
    {
        $this->action=$_GET['action'];
    }

    private function addPrepends()
    {
		// if no controller then set default
		if (isset($_GET['controller']))
		{
			$_GET['controller']=ucwords($_GET['controller']).'Controller';
		} else {
			$_GET['controller']='DefaultController';
		}

		if (isset($_GET['action']))
		{
			$_GET['action']='action'.ucwords($_GET['action']);
		} else {
			$_GET['action']='actionDefault';
		}

    }


    public function go()
    {
        $controller=new $this->controller;
        call_user_func(array($controller, $this->action),$this->params);
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getParams()
    {
        return $this->params;
    }
}
?>
