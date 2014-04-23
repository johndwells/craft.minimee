<?php
namespace Craft;

interface Minimee_IAssetModel
{
	public function getContents();

	public function getLastTimeModified();

	public function exists();
}