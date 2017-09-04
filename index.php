<?php
	/*
	** Solicitar los recursos del núcleo e inicializar la app
	*/
	require('contents/core/config.php');
	require('contents/core/functions.php');
	$config = new Configurations([
		/*
		** Directorio(s) donde se encuentra la raiz de la app
		**
		** Necesario solo si la aplicación no se encuentra en
		** la raíz del servidor.
		*/
		'appDir' => 'apptemplate',
		/*
		** Dominio principal donde la App funciona como raíz
		** en un subdominio.
		**
		** Utilizar si la aplicación es un subdominio de un 
		** dominio en el cual no se muestrará el directorio
		** dentro del URL.
		*/
		////// 'parentDomain' => 'domain.com',
		/*
		** Subdominio donde la App funciona como raíz.
		**
		** Utilizar si la aplicación es un subdominio de
		** varios dominios en el cual no se mostrará el
		** directorio dentro del URL.
		*/
		////// 'subdomain' => 'subdomain',
		/*
		** Parametro necesario si se utilzárá el sistema de
		** control por carpetas en 'contents', por defecto es
		** 'true'.
		*/
		////// 'useControl' => false,
		/*
		** Forzar el protocolo HTTPS en toda la App.
		*/
		'alwaysUseHTTPS' => true,
		/*
		** Reportar todos los errores.
		*/
		'errorReporting' => true
		]);
