<?php
class KaAPI
{
    private $uri;           // the entire passed uri
    private $controller;    // the controller object from the url (string)
    private $action;        // the action from the url (string)
    private $params;        // the rest of the parameters from the http body (php input)
	private $api_program_name;

    public function __construct($urlUri, $program_name)
    {
		// Get the api program
		$this->api_program_name=$program_name;

		// Turn of the templates for API use
		$GLOBALS['use_template']=0;
	
		// Set the params from the php://input
		$this->setParams(file_get_contents('php://input'));

		// Parse the passed URI into an array
		$urlArray=$this->parseUri($urlUri);

		// Add 'controller' and 'action' strings
		$urlArray=$this->addPrepends($urlArray);

		// Setters
		$this->setController($urlArray);
		$this->setAction($urlArray);
		// Test to see if the controller and action exist
		try 
		{
			if (!class_exists($this->controller))
			{
				throw new Exception($this->controller."We don't like the url you requested.");
			} else {
				$controller=new $this->controller;
			}
			
			if (!method_exists($controller,$this->action)) {
				throw new Exception($this->action."We don't like the url you requested.");
			}
		} catch(Exception $e) {
			include(KA.'/error_docs/Exception.php');
			exit();
		}
    }

	public function setParams($http_body)
	{
		// this must be a json string
		$http_body=json_decode($http_body, true);
		if (json_last_error() == JSON_ERROR_NONE)
		{
			$this->params=$http_body;
		}
	}

    public function setController($urlArray)
    {
		$this->controller=$urlArray[0];
    }

    public function setAction($urlArray)
    {
        $this->action=$urlArray[1];
    }

    private function addPrepends($urlArray)
    {
        // Get the controller and create 'controllerParam'
        $urlArray[0]=ucfirst($urlArray[0]).'Controller';

        // Get the action and create 'actionParam'
		if (!is_numeric($urlArray[1]))
		{
			$urlArray[1]='action'.ucfirst($urlArray[1]);
		}

        return $urlArray;
    }

    private function parseUri($urlUri)
    {
		// Split in case of parameters after the ?
		$split=explode('?', $urlUri);
		if (isset($split[1]))
		{
			parse_str($split[1], $query);
			if (is_array($this->params))
			{
				$this->params=$this->params+$query;
			} else{
				$this->params=$query;
			}
			$urlUri=$split[0];
		}

        // Caste as string and strip any shitty characters
        $this->uri=(string)preg_replace('/\?.+$/i','',$urlUri);

        // Break the string into an array
        $urlArray=explode('/', $this->uri);

        // Remove the empty first element
        array_shift($urlArray);

		// Check to see if this is a test api
		if ($urlArray[0]=='test')
		{
			array_shift($urlArray);
		}

        // Apply php function urldecode to each element
        $urlArray=array_map('urldecode', $urlArray);

        // Check to see if a controller was passed
        if (empty($urlArray[0]))
        {
			// You must have a controller for the API
			exit();
        }

        // Check to see if an action was passed
        if (empty($urlArray[1]) || (isset($urlArray[1]) && is_numeric($urlArray[1])))
        {
			if (isset($urlArray[1]) && is_numeric($urlArray[1])) {
				$this->params['id']=$urlArray[1];
			}
			// No controller so check the request method for CRUD
			if ($_SERVER['REQUEST_METHOD']=='POST') {
            	$urlArray[1]='post';
			} else if ($_SERVER['REQUEST_METHOD']=='GET') {
				$urlArray[1]='get';
			} else if ($_SERVER['REQUEST_METHOD']=='PUT') {
				$urlArray[1]='put';
			} else if ($_SERVER['REQUEST_METHOD']=='DELETE') {
				$urlArray[1]='delete';
			} else {
				// If no action was passed and no Server Method then stop the program
				exit();
			}
        }

        return $urlArray;
    }

	public function checkApiPerms($controller,$action,$program_name)
	{
		// Check to see if there are any access restrictions
		$permissions=json_decode(file_get_contents(API_PERMS), true);

		// If the permission is set - else don't worry about permissions
		if (isset($permissions[$controller][$action]) && (is_array($permissions[$controller][$action])) && (count($permissions[$controller][$action])))
		{
			if (in_array($program_name, $permissions[$controller][$action]))
			{
				// Ok you have permission
			} else {
				echo 'You do not have permission to consume this resource';
				http_response_code(401);
				exit;
			}
		}
	}

    public function go()
    {
		// Check the permissions
		$this->checkApiPerms($this->controller, $this->action, $this->api_program_name);

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
