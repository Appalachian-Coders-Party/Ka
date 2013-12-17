<?php
	if ($GLOBALS['use_template'])
	{
		apply_template(LAYOUT);
	}

    function apply_template($layout)
    {
        $template=file_get_contents($layout);
        foreach ($GLOBALS['view_stack'] as $key => $value)
        {
            $template=str_replace('{__'.$key.'__}', $value, $template);
        }
        echo $template;
    }
?>
