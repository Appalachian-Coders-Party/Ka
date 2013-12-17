<?php
    // We have to make this class to handle the views and shits
    class KaController
    {
        public function render($view, $well, $data=array())
        {
            if ($this->viewExists($view))
            {
                ob_start();
                require($view);
                $GLOBALS['view_stack'][$well]=ob_get_contents();
                ob_end_clean();
            }
        }

		public function renderAjax($view, $data=array())
		{
            if ($this->viewExists($view))
            {
                ob_start();
                require($view);
                $contents=ob_get_contents();
                ob_end_clean();

				// don't use the template since this is an ajax call
				$GLOBALS['use_template']=0;

				return $contents;
            }
		}

        public function viewExists($view)
        {
            return file_exists($view);
        }
    }
?>
