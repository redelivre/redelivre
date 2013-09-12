<?php

class SmartView
{
	/**
	 * [$tmp description]
	 * @var [type]
	 */
	private $tmp, $add;

	/**
	 * [__construct description]
	 * @param [type] $file [description]
	 */
	public function __construct($file = '')
	{
		return $this->load($file);
	}

	/**
	 * [__set description]
	 * @param [type] $nickName     [description]
	 * @param [type] $valueReplace [description]
	 */
	public function __set($nickName, $valueReplace)
	{
		$this->add[$nickName] = $valueReplace;
	}

	/**
	 * [__construct description]
	 * @param [type] $file [description]
	 */
	public function load($file)
	{
		if (is_file($file) && is_readable($file)) {
			$this->add = '';
			$this->tmp = file_get_contents($file);
		}

		return $this;
	}

	/**
	 * [render description]
	 * @return [type] [description]
	 */
	private function render()
	{
		if (is_array($this->add) && count($this->add) >= 1) {
			foreach ($this->add as $nickName => $valueReplace) {
				$this->tmp = str_replace('{{ '.$nickName.' }}', $valueReplace, $this->tmp);
			}

			////////////////////////////////////////////////////////////////////////////////
			// IMPORTANTE! Remove qualquer tag de template e script PHP para segurança //
			////////////////////////////////////////////////////////////////////////////////
			
			$this->tmp = preg_replace(array('/\{\{\s.*\s\}\}/i', '/\<\?[^?].*\?\>/i'), array('', ''), $this->tmp);
		}

		return $this->tmp;
	}

	/**
	 * [display description]
	 * @param  [type] $echo [description]
	 * @return [type]       [description]
	 */
	public function display($echo = false)
	{
		if ($echo === true) {
			echo $this->render();
		}
		else {
			return $this->render();
		}
	}
}