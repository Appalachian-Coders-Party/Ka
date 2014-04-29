<?php
    // We have to make this class to handle the views and shits
    class KaController
    {
		public function renderInternal($view, $data=array())
		{
			ob_start();
			require($view);
			$contents=ob_get_contents();
			ob_end_clean();

			return $contents;
		}

		public function renderData($well, $data)
		{
			$GLOBALS['view_stack'][$well]=$data;
		}

        public function render($view, $well, $data=array())
        {
            if ($this->viewExists($view))
            {
                ob_start();
                require($view);
                $GLOBALS['view_stack'][$well]=ob_get_contents();
                ob_end_clean();
            } else {
				echo "view does not exit";
				exit();
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

		public function renderAjaxJson($data=array())
		{
				$GLOBALS['use_template']=0;
				if (is_array($data))
				{
					echo json_encode($data);
					exit();
				} else {
					echo $data;
					exit();
				}
		}

        public function viewExists($view)
        {
            return file_exists($view);
        }
    }
?>
