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
	        
	        \Equilibrium::log()->debug('AUTH: ', ['Route' => $route, 'Token' => $user->getToken(), 'User' => $user->getUserData()]);
	           
	        return $isAuthorized && ($route['allow']??false);
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