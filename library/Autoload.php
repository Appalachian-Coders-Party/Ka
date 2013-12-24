<?php

function ka_autoloader($className)
{
    if (file_exists(APPLICATION.'/controllers/'.$className.'.php'))
    {
        require_once(APPLICATION.'/controllers/'.$className.'.php');
    }

    if (file_exists(APPLICATION.'/models/'.$className.'.php'))
    {
        require_once(APPLICATION.'/models/'.$className.'.php');
    }

    if (file_exists(KA.'/library/'.$className.'.php'))
    {
        require_once(KA.'/library/'.$className.'.php');
    }
}

spl_autoload_register('ka_autoloader');
?>
