<?php

function ka_autoloader($className)
{
    if (file_exists(BASE.'/application/controllers/'.$className.'.php'))
    {
        require_once(BASE.'/application/controllers/'.$className.'.php');
    }

    if (file_exists(BASE.'/application/models/'.$className.'.php'))
    {
        require_once(BASE.'/application/models/'.$className.'.php');
    }

    if (file_exists(KA.'/library/'.$className.'.php'))
    {
        require_once(KA.'/library/'.$className.'.php');
    }
}

spl_autoload_register('ka_autoloader');
?>
