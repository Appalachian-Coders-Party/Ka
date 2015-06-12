<?php
    class KaUrl extends KaModel
    {
        public function __construct()
        {
			parent::__construct(strtolower(__CLASS__));
        }
    }
?>
