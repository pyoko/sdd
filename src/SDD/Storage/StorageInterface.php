<?php

namespace App\SDD\Storage;

interface StorageInterface 
{
	function store($token, $data);
	function get($token);
	function update($token, $data);
	function delete($token);
}