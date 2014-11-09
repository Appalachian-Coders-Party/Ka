<?php
class KaDispatcher
{
    private $uri;           // the entire passed uri
    private $controller;    // the controller object from the url (string)
    private $action;        // the action from the url (string)
    protected $params;        // the rest of the parameters from the url (array)

    public function __construct($urlUri)
    {
		// Remove the Base Slug if one is set in the config file
		if (defined('BASE_SLUG') && BASE_SLUG != '')
		{
			$reg='/^\\'.BASE_SLUG.'/';
			$urlUri=preg_replace($reg,'',$urlUri);
		}

		if (defined('PRETTY_URL') && PRETTY_URL==1)
		{
			$pretty=new KaUrl;
			$pretty->load(array('slug'=>$urlUri));
			$mvc=$pretty->get();
			if (count($mvc))
			{
				$urlUri='/'.$mvc[0]['controller'].'/'.$mvc[0]['action'];
				if (!empty($mvc[0]['params']))
				{
					$urlUri.='/'.$mvc[0]['params'];
				}
			}
		}

		// Parse the passed URI into an array
		$urlArray=$this->parseUri($urlUri);

		// Add 'controller' and 'action' strings
		$urlArray=$this->addPrepends($urlArray);


		// Setters
		$this->setController($urlArray);
		$this->setAction($urlArray);
		$this->setParams($urlArray);

        // Test to see if the controller and action exist
        try 
        {
            if (!class_exists($this->controller))
            {
                throw new Exception("We don't like the url you requested.");
            } else {
                $controller=new $this->controller;
            }

            if (!method_exists($controller,$this->action))
            {
                throw new Exception("We don't like the url you requested.");
            }

        } catch(Exception $e) {
            include(KA.'/error_docs/Exception.php');
            exit();
        }
    }

	public function prettyUrl()
	{
	}

    public function setParams($urlArray)
    {
        // Shift it twice to get rid of the controller and action spots
        array_shift($urlArray);
        array_shift($urlArray);
        $this->params=array();

        for ($i=0; $i<ceil(count($urlArray)/2); $i++)
        {
            if ($i==0) 
            {
                $this->params[current($urlArray)]=next($urlArray);
            } else {
                $this->params[next($urlArray)]=next($urlArray);
            }
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
        $urlArray[1]='action'.ucfirst($urlArray[1]);

        return $urlArray;
    }

    private function parseUri($urlUri)
    {
        // Caste as string and strip any shitty characters
        $this->uri=(string)preg_replace('/\?.+$/i','',$urlUri);

        // Break the string into an array
        $urlArray=explode('/', $this->uri);

        // Remove the empty first element
        array_shift($urlArray);

        // Apply php function urldecode to each element
        $urlArray=array_map('urldecode', $urlArray);

        // Check to see if a controller was passed
        if (empty($urlArray[0]))
        {
            $urlArray[0]='default';
        }

        // Check to see if an action was passed
        if (empty($urlArray[1]))
        {
            $urlArray[1]='default';
        }

        return $urlArray;
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
