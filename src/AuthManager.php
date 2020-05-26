<?php
	namespace equilibrium;
	
	class AuthManager
	{
	    static public function isAuthorized($route) {
	        $allowedRoles = $route['roles']??['*'];
            $user = \Equilibrium::user();
	        
	        $isAuthorized = in_array( '*', $allowedRoles)
    	        //Visitante
	           || (in_array( '?', $allowedRoles) && $user->isGuest())
	           
    	        //Autenticado
	           || (in_array( '@', $allowedRoles) && $user->isAuthenticated())
	           
	           //Cumple un rol específico
	           || self::someone_in_array($user->getRoles(), $allowedRoles);
	        
	        return $isAuthorized && $route['allow'];
	    }
		
		protected static function someone_in_array($needles, $haystack)
		{
			foreach ($needles as $needle)
			{
				$res = in_array($needle, $haystack);
				if ($res) return true;
			}
			return false;
		}
	}