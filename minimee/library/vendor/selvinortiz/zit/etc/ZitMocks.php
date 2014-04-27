<?php

class BaseMock
{
	protected $id;
	protected static $instanceCount = 0;

	public function __construct()
	{
		static::$instanceCount = static::$instanceCount + 1;
		$this->id = md5( get_called_class().static::$instanceCount );
	}

	public function getId()
	{
		return $this->id;
	}

	public function getInstanceCount()
	{
		static::$instanceCount;
	}
}

class SessionMock extends BaseMock
{
	protected static $instanceCount = 0;
}

class CartMock extends BaseMock
{
	protected static $instanceCount = 0;
}

class ProductMock extends BaseMock
{
	protected static $instanceCount = 0;
}
