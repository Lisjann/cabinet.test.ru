<?php

namespace Top10\CabinetBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Top10CabinetBundle extends Bundle
{
	
	public function getParent()
	{
		return 'FOSUserBundle';
	}
	
}
