<?php
	namespace equilibrium;
	require_once __DIR__.'/exceptions/ErrorException.php';

	use Equilibrium;
	use equilibrium\AuthManager;
	use equilibrium\RouteManager;
use equilibrium\exceptions\NotFoundException;
use equilibrium\responses\HttpResponse;
use equilibrium\exceptions\EquilibriumException;
            	
	class Bootstrap {
		
		static public $appPath = null;
		static protected $vendorPath = null;
		static public $equilibriumPath = null;
		
		protected $_http = '/';
		protected $_basePath = '';
		
		protected $endpointNamespace = 'app\endpoints';
		
		public function __construct()
		{
		    $equilibrium_path = realpath(__DIR__);
		    $vendor_path = realpath(dirname(dirname(dirname($equilibrium_path))));
		    $app_path = realpath(dirname($vendor_path));
		    		
		    static::$appPath = $app_path;
		    static::$vendorPath = $vendor_path;
		    static::$equilibriumPath = $equilibrium_path;
		    
			$this->_basePath = $app_path;
		}
		
		protected function setup()
		{
            \Equilibrium::$appPath = static::$appPath;
            \Equilibrium::$equilibriumPath = static::$equilibriumPath;
		    
            if (\Equilibrium::config()->application->disable_sessions??true) session_write_close();
		    
            $httpDir = \Equilibrium::config()->application->prefix??'/';
            $this->_http = $httpDir; //TODO:Empieza y termina con /
            \Equilibrium::$prefix = $httpDir;
			
            $charset = Equilibrium::config()->application->charset;
            header('Content-Type:text/html; charset='.$charset);
            ini_set('default_charset',$charset);
            date_default_timezone_set(Equilibrium::config()->application->timezone);
			
            if (Equilibrium::config()->application->debug) {
                ini_set('display_errors','1');
                error_reporting(E_ALL);
            } else {
                ini_set('display_errors','0');
            }
			
            require_once 'InitializeDatabaseConfig.php';
		}
		
		protected function fileEndpointExists($class)
		{
		    $path = $this->_basePath ."/endpoints/$class.php";
		    return $this->fileExists($path);
		}

		protected function fileExists($filename)
		{
		    return is_file($filename) && is_readable($filename);
		}
		
		public function run()
		{
            $this->setup();
            
            $executionResult = null;
			
            try {
                //CORS headers
                $config = \Equilibrium::config()->toArray();
                $cors = $config['application']?($config['application']['cors']??[]):[];
                foreach ($cors as $header => $value) {
                    $value = is_array($value)?implode(', ', $value): $value;
                    header("$header: $value");
                }
                unset ($cors);
                
                RouteManager::loadRoutes($this->_http);
                $uri = explode('?',$_SERVER['REQUEST_URI']);
                $uri = $uri[0];
    			
                $route = RouteManager::match($uri);
                if (empty($route)) throw new NotFoundException();
    			
                //Si el método fue OPTIONS, y la ruta existe y estoy autorizado, devolvemos que todo esá bien
                if (strtolower($_SERVER['REQUEST_METHOD']) == 'options')
                    throw new EquilibriumException('Está todo bien', 200);                
                
                //Authorization
                if (! AuthManager::isAuthorized($route) )
                    throw new EquilibriumException('Unauthorized' , 401);

                //Incluir el controlador y llamar al método
                list($endpoint, $method) = explode('::', $route['handler']);
                
                if (! $this->fileEndpointExists($endpoint)) 
                    throw new \Exception("No existe el endpoint $endpoint");
                
                $handler = $this->endpointNamespace . '\\' . $endpoint;
                $handler = new $handler();
                
                if (! is_callable([$handler,$method])) 
                    throw new \Exception("No existe el método $method del endpoint $endpoint");

                \Equilibrium::$currentController = $handler;
                \Equilibrium::$currentControllerName = explode('\\', get_class($handler));
                \Equilibrium::$currentControllerName = end(\Equilibrium::$currentControllerName);
                
                $executionResult = $handler->{$method}($route['args']??[]);
                
                if (empty($executionResult))
                    throw new \Exception("$endpoint::$method debe devolver un objeto IResponse");
                
            } catch (NotFoundException $e) {
                $executionResult = new HttpResponse();
                $executionResult->setCode(404)->setBody('Not found');
		        
            } catch (EquilibriumException $e) {
                $executionResult = new HttpResponse();
                $executionResult->setCode($e->getCode())->setBody('error');
		        
            } catch (\Exception $e) {
                \Equilibrium::log()->critical($e);
                $executionResult = new HttpResponse();
                $executionResult->setCode(500)->setBody('Internal server error');
            }
		    
            try {
                $executionResult->execute();
		        
            } catch (\Exception $e) {
                \Equilibrium::log()->critical($e);
                if (Equilibrium::config()->application->debug) {
                    var_dump($e);
                } else {
                    echo 'Ocurrió un error grave en el servidor. Por favor reintente más tarde';
                }
                http_response_code(500);
            }

            die ();
		}
	}