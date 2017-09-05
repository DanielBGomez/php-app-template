<?php
	class Configurations {
		function __construct($config = []){
			/*
			** Declaración de las variables de configuración que
			** no se pueden condicionar después.
			**
			**
			** $parentDomain : Dominio principal donde la App 
			** funciona como raíz en un subdominio.
			**
			** $subdomain : Subdominio donde la App funciona
			** como raíz.
			*/
			$parentDomain = (isset($config['parentDomain']) && $config['parentDomain']) ? $config['parentDomain'] : false;
			$subdomain = (isset($config['subdomain']) && $config['subdomain']) ? $config['subdomain'] : false;
			/*
			**
			** CONFIGURACIONES DEL SERVIDOR.
			**
			** - Establecer la zona horaria como 'Mexico City'.
			**
			** - Asegurarse de que todo el código se ejecute 
			** como UTF-8.
			*/
			date_default_timezone_set("America/Mexico_City");
			/////////////////////////////////////////////////
			mb_internal_encoding('UTF-8');
			mb_http_output('UTF-8');
			mb_http_input('UTF-8');
			mb_language('uni');
			mb_regex_encoding('UTF-8');
			ob_start('mb_output_handler');
			//////////////////////////////
			/*
			** DEFINICIÓN DE CONSTANTES PARA EL SISTEMA.
			**
			** APP_DIR : (string) Directorio de la aplicación si no
			** se encuentra en la raíz del servidor.
			**
			** LIVE_SERVER : (bool) Contiene el valor de si la 
			** aplicación se encuentra en un servidor en linea.
			**
			** APP_IN_ROOT : (bool) Contiene el valor de si la 
			** aplicación se encuentra en la raíz del servidor.
			**
			** SECURE : (bool) Contiene el valor de si la aplicación
			** utilizará solo HTTPS.
			**
			** SCHEME : (string) Protocolo por el cual se solicita
			** la aplicación.
			**
			** HTTP_URL : (string) URL base de la aplicación.
			**
			** DISK_URL : (string) Directorio base de la aplicación.
			**
			** URI : (array) Arreglo del recurso solicitado por el
			** navegador separado por slash (/) y question mark (=).
			**
			** HTTP_URI : (string) URL completo solicitado por el
			** navegador.
			**
			** PAGE : (array) Arreglo del recurso solicitado por
			** el navegador utilizando el sistema de control por
			** carpetas, ignorando el primer valor.
			**
			*/
			define('APP_DIR', (isset($config['appDir']) && $config['appDir']) ? $config['appDir'].'/' : '' );
			define('LIVE_SERVER', (
					$_SERVER['HTTP_HOST'] != "localhost" &&
					!$this->isIP($_SERVER['HTTP_HOST'])
				) );
			define('APP_IN_ROOT', (
					($parentDomain && preg_match("/$parentDomain/", $_SERVER['HTTP_HOST']) ) ||
					( $subdomain && preg_match("/$subdomain/", $_SERVER['HTTP_HOST']) )
				) );
			define('SECURE', (LIVE_SERVER && isset($config['alwaysUseHTTPS']) && $config['alwaysUseHTTPS']) );
			define('SCHEME', (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'http');
			define('HTTP_URL', (APP_IN_ROOT) ? SCHEME."://".$_SERVER['HTTP_HOST']."/" : SCHEME."://".$_SERVER['HTTP_HOST']."/".APP_DIR);
			define('DISK_URL', $_SERVER['DOCUMENT_ROOT'].'/'.APP_DIR);
			define('URI', $this->getPage());
			define('HTTP_URI', $this->get_http_uri());
			define('PAGE',$this->get_pagina());
			/*
			**
			** DEFINICIÓN DE CONSTANTES PERSONALIZADAS.
			**
			**
			** STATUS : (array) Arreglo de la traducción de un
			** status en entero a cadena.
			**
			** HTTP_RESPONSE_CODE : (array) Arreglo que contiene 
			** varios Códigos de Respuesta HTTP con sus nombres y
			** descripciones.
			*/
			define('STATUS',[
					-1 => 'Eliminado',
					0 => 'Borrador',
					1 => 'Publicado'
				]);
			define('HTTP_RESPONSE_CODE', [
					'200'=>['code'=>'200','name'=>'Ok','description'=>'Ok'],
					'400'=>['code'=>'400','name'=>'Bad Request','description'=>'Bad Request'],
					'403'=>['code'=>'403','name'=>'Forbidden','description'=>'Forbidden'],
					'404'=>['code'=>'404','name'=>'Not Found','description'=>'Not Found'],
					'408'=>['code'=>'408','name'=>'Timeout','description'=>'Timeout'],
					'500'=>['code'=>'500','name'=>'Internal Server Error','description'=>'Internal Server Error']
				]);
			/*
			**
			** CONDICIONES DE CONFIGURACIÓN.
			**
			**
			** AlwaysUseHTTPS : Si se configúró que se fuerce el uso
			** de HTTPS dentro de toda la aplicación, se verifica
			** que se encuentre en un servidor en linea y si no está
			** utilizando HTTPS, se redirecciona para sí utilizarlo.
			**
			** UseControl : Si se configuró que se utilice el
			** sistema de control por carpetas, se llama a la
			** función que lo procesa.
			**
			** ErrorReporting : Si se configuró que se muestren los
			** errores, se muestran :v
			*/
			if(SECURE && !preg_match("/https:/", HTTP_URI)){
				header('Location: https://'.str_replace('http://','',HTTP_URI));
				exit;
			}
			if(!isset($config['useControl']) || $config['useControl']){
				$this->checkPage();
			} else {
				/* CÓDIGO PARA LLAMAR EL ARCHIVO SOLICITADO */
			}
			if(isset($config['errorReporting']) && $config['errorReporting']){
				error_reporting(1);
			}
			/*
			** EJECUCION DE FUNCIONES PRINCIPALES
			*/
			$this->sec_session_start();
		}
		function __destruct(){}

		private function getPage(){
			$find = (!empty(APP_DIR)) ? APP_DIR : '';
			$data = preg_split('/\//',str_replace( array($find,'//','?'), array('','','/'), $_SERVER['REQUEST_URI'] ));
			array_shift($data);
			return $data;
		}

		private function isIP($ip){
			return filter_var($ip, FILTER_VALIDATE_IP);
		}

		private function get_http_uri(){
			$find = (!empty(APP_DIR)) ? APP_DIR : '';
			$request_uri = str_replace($find, '', $_SERVER['REQUEST_URI']);
			return str_replace(array('://','//'),array(':///','/'),HTTP_URL.$request_uri);
		}

		private function get_pagina(){
			// Almacena el arreglo del url desde la configuración
			$return = URI;
			// Remueve el primer valor que es en el que se está y no se necesita
			array_shift($return);
			// Si el primer valor está vacio o no existe, regresa index, si no, el valor que tiene
			return (!isset($return[0]) || empty($return[0])) ? array('index') : $return;
		}

		private function checkPage(){
			$folder = (empty(URI[0])) ? 'index' : URI[0];
			// Si existe un 'control.php' del primer valor de la constante 'URI' en la ruta 'contents/#primervalor#' lo guarda como control
			if(file_exists('contents/'.strtolower($folder).'/control.php')){
				require('contents/'.strtolower($folder).'/control.php');
			} else { // En caso contrario muestra un error 404 -- ESTO SERÁ MODIFICADO --
				$this->error_code(404);
			}
		}

		private function sec_session_start() {
			$session_name = 'sec_session_id';   // Configura un nombre de sesión personalizado.
			$secure = SECURE;
			// Esto detiene que JavaScript sea capaz de acceder a la identificación de la sesión.
			$httponly = true;
			// Obliga a las sesiones a solo utilizar cookies.
			if (ini_set('session.use_only_cookies', 1) === FALSE) {
				header("Location: ".HTTP_URL.'no-secure-login');
				exit();
			}
			// Obtiene los params de los cookies actuales.
			$cookieParams = session_get_cookie_params();
			session_set_cookie_params($cookieParams["lifetime"],
				$cookieParams["path"], 
				$cookieParams["domain"], 
				$secure,
				$httponly);
			// Configura el nombre de sesión al configurado arriba.
			session_name($session_name);
			session_start();			// Inicia la sesión PHP.
			session_regenerate_id();	// Regenera la sesión, borra la previa. 
		}

		public function error_code($code = ''){
			echo $code;
			http_response_code($code);
			exit;
		}

		public function end_session(){
			// Desconfigura todos los valores de sesión.
			$_SESSION = array();
			// Obtiene los parámetros de sesión.
			$params = session_get_cookie_params();
			// Borra el cookie actual.
			setcookie(session_name(),
					'', time() - 42000, 
					$params["path"], 
					$params["domain"], 
					$params["secure"], 
					$params["httponly"]);
			// Destruye sesión. 
			session_destroy();
		}
	}