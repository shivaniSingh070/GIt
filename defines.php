<?php
/**
 * Hier bei Bedarf IP-Adressen eintragen, die für den DEV-Modus gelten sollen.
 * Ausserdem aufschreiben, welche Domains für DEV oder TEST Umgebunden gelten.
 * LIVE-Domain hier NICHT notieren! 
 **/
	 
	 // @todo: FIT THIS TO THE DEV- AND TEST-DOMAINS WE ARE USING
		$dev_and_test_domains = array(
			"testrelaunch.engelsrufer.world",
			"testrelaunch.engelsrufer.de",
			"devrelaunch.engelsrufer.de",
			"devrelaunch.engelsrufer.world",
			"engelsrufer.local",
			"engelsrufer.rh",
			"engelsrufer.nt",
			"engelsrufer.lb",
			"rh.engelsrufer.de"
		);
		
		
	// @todo: use http://myip.ch to determine your IP-adress. Only needed if you are not working in the PM-Headquarter (Homeoffice, or somewhere else)
	// Make the IP invalid after using it, with just adding an X in front.
		$zusaetzliche_dev_ips = array (
			"127.0.0.1",
			"87.191.181.237", // PM HQ
			"192.168.2.10", // PM-RH intern
			"192.168.2.55", // PM-LB intern
			"88.64.227.167", // RH, HO 06.05.20
			"192.168.2.147", // RH RH Laptop HQ intern
		);
                // print $_SERVER["REMOTE_ADDR"];
				
	// Hier nur für Entwickler nach GoLive relevant. auf "true" stellen, wenn auch auf der Live-Seite anhand der DEV-IPs DEV-Meldungen ausgegeben werden sollen.
		$erlaube_dev_auch_auf_live_seite = false; 

	
	/**
     * Adding the local domain automatically. Example: "entrypage.local" where {$projectname} = entrypage
     * First check if we are not in cli-mode, where $_SERVER["HTTP_HOST"] is not available 
     */        
        // When calling this via the console..
        if (php_sapi_name()=="cli") {
            $host = "";
        }
        
        if (isset($_SERVER["HTTP_HOST"])) {            
            $host = $_SERVER["HTTP_HOST"];
            if (strpos($host, ".local")) {
                $projectname = str_replace(".local", "", $host);
                $dev_and_test_domains[] = "{$projectname}.local";
            }
        }
		
	/*/ Umleitung für bestimmte Domains, die nicht in der htaccess liegen.
		if ($host=="test.testbench.com.w00e323f.kasserver.com") {
			header("HTTP/1.0 301 Moved Permanently"); 
			header("location: https://test.testbench.com");
			die();
		}
		/**/
		
		
	/**
	 * Wird in PM-Template verwendet, um den CSS- und JS-Dateien dies als Parameter anzuhängen und damit dem Browser zu sagen, dass es neue Inhalte gibt.
	 * Soll nicht im DEV-Modus greifen, hier arbeiten wir eh immer mit Browser-Refresh
	 * Neu seit 05.11.2015
	 * PM RH : 3.0 = Trial-Vereinfachung und EE-Lizenz im backend. 16.10.2018
	 **/
		define("PM_VERSION", "1.1"); // +1.0 = ReDesign / reLaunch.  +0.1 = Änderungen am CSS/JS only.
		
        
        define("MAGE_ROOT", __DIR__); // PM RH 18.10.2019
		

	
	// @todo: for joomla not nessecary. Bei Magento eher fest "sitemap.xml" verwenden.
		$path_to_sitemap = "sitemap.xml"; 

	
/** ------------------------------ AB HIER NICHTS MEHR ÄNDERN ------------------------ **/


	// Da sämtliche Meldungen von uns als UTF8 ausgegeben werden, kann es nicht schaden hier UTF8 zu verwenden.
		header("content-type: text/html; charset=UTF-8;");
	

    /**
     * wenn Einrichtung auf localhost, dann hier evtl DB automatisch anlegen.
     * Konvention: {projektname}.local, db-name = {projektname}, db-user und db-passwd = {projektname}.
     * PM RH 21.10.2016
     * @todo: Hier noch das Query ausführen, unabhängig von Joomla, CI oder Magento. Also mit PDO oder mysqli
     * /
        if (isset($projectname) && !empty($projectname)) {
            $sql = "CREATE USER '{$projectname}'@'localhost' IDENTIFIED BY '***';GRANT USAGE ON *.* TO '$projectname'@'localhost' IDENTIFIED BY '***' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;CREATE DATABASE IF NOT EXISTS `{$projectname}`;GRANT ALL PRIVILEGES ON `{$projectname}`.* TO '{$projectname}'@'localhost';";
            // @todo Wenn DB nicht gefunden wurde, oder wenn User nicht angelegt wurde, dann SQL ausführen
        }
		/**/
	 
	/**
	 * DEV, TEST oder LIVE-Mode?
	 * Funktioniert wie im Codeigniter
	 **/
		$env = 'production';		
		if (isset($_SERVER["HTTP_HOST"]) && in_array($_SERVER["HTTP_HOST"], $dev_and_test_domains)) {
			$env = 'testing';
		}
		
		/**
		 * IP von Pixelmechanics rausfinden und dann in den DEV-Modus schalten.
		 * Funktioniert nur mit Leitung 1, nicht 2, da dort kein dyndns läut logischerweise.
		 * Neu von PM RH 29.05.2015
		 **/
			if ($env=="testing") {
				$ip = gethostbyname('pixelmechanics.dyndns.info'); // IP Adresse von dyndns rausfinden.
				if ($_SERVER["REMOTE_ADDR"] == $ip) $env = "development"; // Wenn der aktuelle user diese IP Adresse hat, ist er grade im PM-HQ
			}
			
			// Anhand der DEV-IPs von oben ENVIRONMENT setzen, wenn man NICHT innerhalb vom PM-HQ sitzt.
				if ( ($env=="testing") OR $erlaube_dev_auch_auf_live_seite===true ) {
					if ( php_sapi_name() !== 'cli' && in_array($_SERVER["REMOTE_ADDR"], $zusaetzliche_dev_ips)) {
						$env = 'development';
					}
				}
		
		
		/**
		 * Wenn der PM Powercache auf Live-Seiten verwendet wird, dann benötigt dieser die Abfrage des Environments per GET-Parameter hier in der defines.php
		 * PM RH, 05.11.2015
		 **/
			if (isset($_GET["env"])) {
				$env = $_GET["env"]; // muss nicht zwingend hier gefiltert werden. Denn unten in der Switch-Anweisung stehen nur 3 feste Werte drin, mit denen verglichen wird.
			}
			
		define('ENVIRONMENT', $env);

	 
	 
		
		
	/**
	 * Diese Datei wird von index.php inkludiert und beinhaltet Funktionen, die Systemweit aufgerufen werden können
	 * Gilt für ALLE PM-Produkte: Joomla,Magento, Codeigniter...
	 * @author: RH
	 **/
		include_once("pm_helper.php");
          /** 
           * Added to include Customized function
           * @autor: AA
           * Date: 05.08.2019
          **/
	        include_once("pm_mage_helper.php");		
		// Werden nicht mehr verwendet, da zu viele Fehler in komprimiertem JS vorkommen. include_once("jshrink.php"); include_once("jspacker.php");
	
	 
		
	/**
	 * Anhand des oben rausgefundenen ENVIRONMENTs hier die Einstellungen jeweils setzen: Fehlerausgabe, Robots.txt prüfen etc.
	 **/
		switch (ENVIRONMENT) {
			case 'development':
								error_reporting(E_ALL ^ E_NOTICE);
								ini_set('display_errors', '1');
								break;

			case 'testing':
								error_reporting(E_ERROR);
								ini_set('display_errors', '1');
								
								// Sitemap-Anweisung der Robots.txt hinzufügen.
									// hier nicht, da URL nicht die Live-URL ist, das gibt evtl nur Fehler... if (function_exists("checkRobotsTXT")) checkRobotsTXT($path_to_sitemap);			
								break;
				
			case 'production':
								error_reporting(E_ERROR);
								// Sitemap-Anweisung der Robots.txt hinzufügen.
									// if (function_exists("checkRobotsTXT")) checkRobotsTXT($path_to_sitemap);			
								break;

			default:
				exit('The application environment is not set correctly. defines.php');
				@mail("dev@pixelmechanics.de", "ACHTUNG BEI ".$_SERVER["HTTP_HOST"], __FILE__."\n\nkein Environment gesetzt!");
		}
		
		
		
