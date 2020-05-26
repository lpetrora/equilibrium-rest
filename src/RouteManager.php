<?php
namespace equilibrium;
use Equilibrium;

class RouteManager
{
    protected static $wildcards = ['int' => '/^[0-9]+$/', 'any' => '/^[0-9A-Za-z]+$/'];
	protected static $routes = [];
	protected static $prefix = '/';
	
	public static function loadRoutes($prefix) {
	    static::$prefix = $prefix;
	 
	    if ( (static::$routes == []) && (is_readable(Equilibrium::$appPath.'/config/routes.php')))
	    {	        
	        static::$routes = (include Equilibrium::$appPath.'/config/routes.php')['routes'];
	    }

	    if (empty(static::$routes)) throw new \Exception('No routes defined');
                
        //Si el prefijo es distinto, tengo que anexarlo
        if ($prefix != '/')
            foreach (static::$routes as &$route)
                $route['route'] = $prefix . $route['route'];
        
	}
	
	public static function match($uri) {
	    $result = null;
	    foreach (static::$routes as $route) {
	        $match = static::_match_widlcards($route, $uri);
	        if ($match && static::_match_method($route)) {
    	        $result = $match;
    	        break;
	        }
	    }
	    return $result;
	}
	
	protected static function _match_method ($route, $include_method_options = true) {
	    
	    $rm = null;
	    $request_method = strtolower($_SERVER['REQUEST_METHOD']);
	    if (! isset($route['method'])) {
	        $rm = [$request_method];
	    
	    } elseif (is_array($route['method'])) {
	        $rm = $route['method'];
	        
	    } else {
	        $rm = [$route['method']];
	    }
	    if ($include_method_options) $rm [] = 'options';
	    array_map('strtolower', $rm);
	    
	    return in_array($request_method, $rm);
	}
	
	protected static function _match_widlcards ($route, $uri) {
	    $variables = [];
	    
	    $exp_request = explode('/', $uri);
	    $exp_route = explode('/', $route['route']??null);
	    
	    if (count($exp_request) == count($exp_route)) {
	        foreach ($exp_route as $key => $value) {
	            
	            if ($value == $exp_request[$key]) {
	                // So far the routes are matching
	                continue;
	                
	            } elseif ($value[0] == '{' && substr($value, -1) == '}') {
	                // A wild card has been supplied in the route at this position
	                $strip = str_replace(['{', '}'], '', $value);
	                $exp = explode(':', $strip);
	                
	                $wc_type = $exp[0];
	                
	                if (array_key_exists($wc_type, static::$wildcards)) {
	                    // Check if the regex pattern matches the supplied route segment
	                    $pattern = static::$wildcards[$wc_type];
	                    
	                    if (preg_match($pattern, $exp_request[$key])) {
	                        if (isset($exp[1])) {
	                            // A variable was supplied, let's assign it
	                            $variables[$exp[1]] = $exp_request[$key];
	                        }
	                        
	                        // We have a matching pattern
	                        continue;
	                    }
	                    
	                } else {
	                    $variables[$exp[0]] = $exp_request[$key];
	                    continue;
	                }
	            }
	            
	            // There is a mis-match
	            return null;
	        }
	        
	        // All segments match
	        return [
	            'handler' => $route['handler'],
	            'method' => $route['method']??$_SERVER['REQUEST_METHOD'],
	            'roles' => $route['roles']??['*'],
	            'args' => $variables,
	            'allow' => $route['allow'],
	        ];
	    }
	    
	    return null;
	}	
}