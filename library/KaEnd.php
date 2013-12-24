<?php
	if ($GLOBALS['use_template'])
	{
		apply_template($GLOBALS['layout']);
	}

    function apply_template($layout)
    {
		ob_start();
		include(APPLICATION.'/layouts/'.$layout);
		$template=ob_get_contents();
		ob_end_clean();

        foreach ($GLOBALS['view_stack'] as $key => $value)
        {
            $template=str_replace('{__'.$key.'__}', $value, $template);
        }
        echo $template;
    }
?>
