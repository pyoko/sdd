<?php

namespace App\SDD;

interface TokenInterface
{
	function create($data);
	function get($token);
	function remove($token);
	function generateNewToken();
}