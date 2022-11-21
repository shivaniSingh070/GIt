<?php
/**
 * In dieser Datei liegen Funktionen, die von PM immer wieder und in den verschiedenen Projekten verwendet werden können.
 * Sie dürfen nicht abhängig sein von speziellen CMS- oder Framework-Funktionen
 * ÄNDERUNGEN HIER +++ nur +++ in Absprache mit RH
 *
 * @requires: PHP >=5.4
 * @changes: 29.05.2015 PM RH 
 **/

// is true, when XML_HTTP_REQUEST is performed
    define("IS_AJAX", (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));
    define("IS_IFRAME", (isset($_GET['tmpl']) && $_GET['tmpl'] == "component"));
 
    
/**
 * siehe entity pm_contact, Feld "nachricht"
 * Siehe https://www.spiralscripts.co.uk/support/joomla-tips/modal-windows-in-joomla-3.html
 * @param type $content
 */
    function bootstrapPopover($content) {
        $hash = md5($content);
        $id = "modal-".uniqid($hash, true);
        
        $html = '<a href="#'.$id.'" data-toggle="modal" class="btn">Launch modal</a>';
        $params = array();
        $params['title']  = "Modal Content";
        $params['url']    = "#";
        $params['height'] = 400;
        $params['width']  = "100%";
        $html .=JHtml::_('bootstrap.renderModal', $id, $params, $content);
        return $html;
    }
    
    
/**
 * Ersetzt alle Vorkamen der KEYS im Array $vars im String $input durch den Wert des VALUES im Array $vars.
 * @param string $string zB "In diesem Text wird %var1% ersetzt.
 * @param array $vars  zB array("var1" => "variable 1")
 * @return string $output der "übersetzte" String.
 * PM RH 15.12.2017
 */
    function replaceVariablesInString($input, $vars) {
        if (!is_array($vars)) {
            return $input;
        }
        if (empty($vars)) {
            return $input;
        }
        
        $output = $input;
        foreach ($vars as $key => $value) {
            $key = "%{$key}%";
            $output = str_replace($key, $value, $output);
        }
        
        return $output;
    }
    
    
    
    function parseURLsInString($string) {
        // $anz = preg_match_all('@((https?://)?([-\\w]+\\.[-\\w\\.]+)+\\w(:\\d+)?(/([-\\w/_\\.]*(\\?\\S+)?)?)*)@',$string, $matches);
        $anz = preg_match_all('@(https?://[^\s]*)@',$string, $matches);
        // preprint($matches, $anz);
        if ($anz>0) {
            foreach ($matches[0] as $link) {
                $replacement = "<a href='$link' target='_blank'>".urldecode($link)."</a>";
                $string = str_replace($link, $replacement, $string);
            }
        }
        return $string;
    }
    

/**
 * Liefert einen String zurück, der direkt an CSS und JS Dateien angehangen wird, um dem Browser mitzuteilen, um welche Version es sich handelt.
 * Gilt aber nur wenn ENVIRONMENT=="production", ansosnten wird der Timestamp (sekundengenau) verwendet. Dies entspricht "keinem" Cache
 * PM_VERSION: siehe defines.php
 */
    function getPMVersionAddon() {
   	
		$version_addon = '?v='.time();
		if (ENVIRONMENT=="production") {
            $version_addon = '?v='.PM_VERSION;
        }
        return $version_addon;
    }

    
    /**
     * replaces < and > by it's html-entites (&lt;)
     * @param string $val
     * @return string
     */
        function htmltags_convert($val) {
            return str_replace(array("<", ">"), array("&lt;", "&gt;"), $val);
        }
        
        
    /**
     * Prüft einen String nach ungültigen Zeichen/Sonderzeichen und entfernt diese
     * Erlaubte Zeichen: A-Z, a-z, 0-9, Leerzeichen, Binde- und Unterstriche
     * LL, 01.12.2017 für testbench-API-Helper.
     */
        function normalizeString($string) {
            $withoutSpecialChars = preg_replace('/[^A-Za-z0-9\-_ ]/', '', $string);
            $trimmedString = trim($withoutSpecialChars);

            return $trimmedString;
        }
        
        
    /**
     * Merges the second array in to the first, but only when the values are not ""
     * @param array $ar1
     * @param array $ar2
     * @return array
     */
        function array_merge_noneempty($ar1, $ar2) {
            
            // $return = array_merge($ar1, $ar2);
            $return = $ar1;
            foreach ($ar2 as $key => $val) {
                $val = trim($val);
                if ($val!=="") {
                    $return[$key] = $val;
                }
            }
            
            return $return;
        }
        
        

    /**
     * Liefert den Key der ersten Ebene zurück, wenn in einem der tieferen Arrays ein VALUE gefunden werden.
     * @param type $array Array(array(key => value))
     * @param type $value
     * @return $key
     */
        function array_search_recursive($array, $value) {
            $key = false;
            foreach ($array as $key => $ar) {
                $k = array_search($value, $ar);
                if ($k) {
                    return $key;
                }
            }
            return $key;
        }


    /**
     * Converts the input with strtotime to a timestamp and then uses php date()-function for the output.
     * @param string $input example '-12 months' or '20170711' and so on. 
     * @param string $format example: "d.m.Y" or "H:i:s"
     * @return string
     */
        function convertDate($input, $format="d.m.Y") {
            if ($input<=0) {
                return ""; // is also the case when "0000-00-00 00:00:00"
            }
            
            
            // INput konvertieren in Timestamp. Falls Umwandlung negativ, dann war es bereits ein Timestamp.
                $output = strtotime($input);
                if ($output=="" && $input>0) {
                    $output = $input;
                }
                // preprint("$input -> $output", __FILE__.__LINE__);
                
            if (strpos($format, "%")!==false) {
                $output = strftime($format, $output);
            } 
            else {
                $output = date($format, $output);
            }
            return $output;
        }
        function convertDatetime($input, $format="d.m.Y H:i:s") {
            return convertDate($input, $format);
        }

    /**
     * Entfernt aus einem Zeitstempel einfach nur die Datumsangabe und liefert die Zeit zurück.
     * @param string $input
     * @return string
     */
        function convertDateToTime($input) {
            return convertDate($input, "H:i");
        }
        
    /**
     * Converts a given Time-String to a mysql-datetime field in the format: YYYY-MM-DD HH:mm:ss
     * @param string $input
     * @return string
     */
        function convertDateToMysql($input) {
            return convertDate($input, "Y-m-d H:i:s");
        }
        
        
        
    /**
     * Wandelt Sekunden in ein menschenlesbares Format um.
     * PM RH
     * @link https://snippetsofcode.wordpress.com/2012/08/25/php-function-to-convert-seconds-into-human-readable-format-months-days-hours-minutes/
     * 
     * @param int $ss Seconds
     * @param int $hours_per_day. Default=24. Could be 8, to calculate with workinghours only
     * @param int $day_per_month. Default=30. Could be 21, to calculate without weekends
     * @return string
     */     
        function seconds2human($ss, $hours_per_day=24, $day_per_month = 30) {
            
            $seconds_per_min = 60;
            $seconds_per_hour = 60*$seconds_per_min;
            $seconds_per_day = $hours_per_day * $seconds_per_hour;
            $seconds_per_month = $day_per_month*$seconds_per_day;
            
            $s = $ss%60;
            $m = floor(($ss%3600)/$seconds_per_min);
            $h = floor(($ss%86400)/ $seconds_per_hour );
            $d = floor(($ss%2592000)/$seconds_per_day);
            $M = floor($ss/$seconds_per_month);
            $output = "";
            if ($M>0) $output .= "$M Monate ";
            if ($d>0) $output .= "$d Tage ";
            if ($h>0) $output .= "$h Stunden ";
            if ($m>0) $output .= "$m Minuten ";
            if ($s>0) $output .= "$s Sekunden";
            return $output;
        }
        
    /**
     * Wandelt Sekunden in ein lesbares Format um, verwendet aber einen 8h Tag und einen 21-Tage Monat: Arbeitszeiten only.
     * @param int $seconds
     * @return string
     */
        function seconds2humanWorkdaysOnly($seconds) {
            return seconds2human($seconds, 8, 21);
        }
        
    /**
     * Wandelt Sekunden in dezimale Stunden um.
     * PM RH
     **/
        function seconds2hours($ss, $round=2) {
            $ss=trim($ss);
            if ($ss=="" || $ss<=0) {
                return "";
            }
            $output = $ss/60/60;
            if ($round!=="") {
               $output = round($output, $round, PHP_ROUND_HALF_UP);
            }
            return $output;
        }
        
    /**
     * Wandelt mSekunden in Sekunden um 
     * PM RH
     **/
        function mSecToSec($s) {
            return $s/1000;
        }

        
	
	/**
     * Für com_pm/gallery benötigt
     * @param type $folder
     * @return boolean
     */
        function getImagesFromFolder($folder, $extensions="jpg,png,gif,jpeg") {
            if (!is_dir($folder)) return false;
            $pattern = $folder."/*.{".$extensions."}"; // PM RH: nicht nur JPGs erlauben.
            $files = glob($pattern, GLOB_BRACE);
            // preprint($files, $pattern); 
            return $files;
        }
        
    /**
     * Listet Ordner rekursiv auf. Von http://php.net/manual/de/function.scandir.php#119422
     */
        // List files in tree, matching wildcards * and ?
        function foldertree($path, $strip=""){
          
            $folders = glob($path);
            $output = array();
    
            //Iterate through all the results
            if (!empty($folders)) {
                
                foreach ($folders as $i => $result) {
                    
                    if (!is_dir($result)) {
                        continue;
                    }
                    $subs = foldertree($result."/*", $strip);
                    
                    if ($strip!=="") {
                       $result = str_replace($strip, "", $result);
                    }
                    $output[] = $result;
                    
                    if (!empty($subs)) {
                        $output = array_merge($output, $subs);
                        // preprint($subs, "subs");
                    }
                            
                }
            }

            return $output;
        }
        
        
        
		

		
		
	/**
	 * Liefert die Lat/Long Koordinaten für einen Adress-String. 
	 * PM RH, 08.07.2015
	 **/
		function getCoordsForAdresse($adresse) {
			$data = coords_api_call_nominatim($adresse);
			if (empty($data)) $data = coords_api_call_google($adresse);
			return $data;
		}
		
	/**
	 * Ruft die nominatim API auf, um eine Adresse zu finden.
	 * @param type $adresse
	 * @return type
	 */	
		function coords_api_call_nominatim($adresse) {
			$adresse = urlencode($adresse);
			$url = "http://nominatim.openstreetmap.org/search?q={$adresse}&format=json&polygon=1&addressdetails=1";
			$data = json_decode(curl_get($url));
			// preprint($data, $url); die();
			return $data;
		}
		
	/**
	 * Hier verwenden wir die kostenlose Google-API. Aber sie ist beschränkt auf 20.000 Calls pro Tag
	 * @link https://developers.google.com/maps/documentation/geocoding/intro?hl=de
	 * @param type $adresse
	 * @return type
	 */
		function coords_api_call_google($adresse) {
			$adresse = urlencode($adresse);
			$apikey = "";
			$url = "https://maps.googleapis.com/maps/api/geocode/json?address={$adresse}";
			$data = json_decode(curl_get($url));
			return $data;
		}
		
		
		/**
     * Convert ini Filesize in Bytes, z.B. bei upload_max_filesize=2G
     * http://stackoverflow.com/questions/6846445/get-byte-value-from-shorthand-byte-notation-in-php-ini
     * PM AS
     **/
        if( !function_exists("getBytesFromShorthand")) {
            function getBytesFromShorthand($val) {
                $val = trim($val);
                $last = strtolower($val[strlen($val)-1]);

                switch($last) {
                    case 'g':
                        $val *= 1024;
                    case 'm':
                        $val *= 1024;
                    case 'k':
                        $val *= 1024;
                }

                return $val;
            }
        }
        
    /**
     * Rechnet die Byte in größere Einheit um
     * https://www.php.de/forum/webentwicklung/php-einsteiger/php-tipps-2007/44325-byte-in-kbyte-mbyte-etc-umrechnen
     * PM AS
     */
		if( !function_exists("convertByte")) {
        function convertByte($byte) { 
            if($byte < 1024) { 
                $ergebnis = round($byte, 2). ' Byte'; 
            }elseif($byte >= 1024 and $byte < pow(1024, 2)) { 
                $ergebnis = round($byte/1024, 2).' KB'; 
            }elseif($byte >= pow(1024, 2) and $byte < pow(1024, 3)) { 
                $ergebnis = round($byte/pow(1024, 2), 2).' MB'; 
            }elseif($byte >= pow(1024, 3) and $byte < pow(1024, 4)) { 
                $ergebnis = round($byte/pow(1024, 3), 2).' GB'; 
            }elseif($byte >= pow(1024, 4) and $byte < pow(1024, 5)) { 
                $ergebnis = round($byte/pow(1024, 4), 2).' TB'; 
            }elseif($byte >= pow(1024, 5) and $byte < pow(1024, 6)) { 
                $ergebnis = round($byte/pow(1024, 5), 2).' PB'; 
            }elseif($byte >= pow(1024, 6) and $byte < pow(1024, 7)) { 
                $ergebnis = round($byte/pow(1024, 6), 2).' EB'; 
            } 

            return $ergebnis; 
        } 
		} 


		
	/**
	 * Konvertiert einen String, der durch Kommas getrennt ist in ein Array. Kann dabei auch zB Leerzeichen strippen.
	 * @param string $string
	 * @return array
	 */
		function convertCommaStringToArray($string, $search="", $replace="") {
			$outputArray = trim($string);

			// Es gabe keine Eintragung in dem Feld, also der Liste hinzufügen.
				if (empty($outputArray)) {
					return array();
				}

			if ($search!=="") {
				$outputArray = str_replace($search, $replace, $outputArray); // Leerzeichen entfernen.
			}
			$outputArray = explode(",", $outputArray); // Array der kommagetrennten Werte, zB array(23) oder array(23, 24, 25)
			return $outputArray;
		}
		


	/**
	 * Removes all new-lines from the value, to avoid serialization-errors.
	 * Used to save the k2 extrafields in k2-helper
	 * PM RH 27.06.2016
	 */
		if( !function_exists("strip_newlines")) {
			function strip_newlines($value) {
				$value = str_replace(array("\n", "\r", "\t"), "", trim($value));
				return $value;
			}
		}

	/**
	 * Benchmark-Funktionen. PM RH, siehe https://bitbucket.org/pixelmechanics/dev-fella-werke/commits/1d5c536323e3d9bc17cf6e1fea932004076e2811
	 */
		function bench_start() {
			$GLOBALS["bench_start"] = microtime(true);
		}
		function bench_stop($key="") {
			$GLOBALS["bench_stop"] = microtime(true);
			if ($key!=="") $GLOBALS["bench_stop_key"] = $key;
		}
		function bench_print() {
			$GLOBALS["bench_gesamt"] = (($GLOBALS["bench_stop"] - $GLOBALS["bench_start"])/1000);
			$output = "<div class='pm_bench'>";
				$output .= "<p><strong>Dauer:</strong>: ".$GLOBALS["bench_gesamt"]." msek</p>";
				$output .= "<p><strong>Start:</strong>: ".$GLOBALS["bench_start"]."</p>";
				$output .= "<p><strong>Ende:</strong>: ".$GLOBALS["bench_stop"];
					if (isset($GLOBALS["bench_stop_key"])) $output .= " (".$GLOBALS["bench_stop_key"].")";
				$output .= "</p>";
			$output .= "</div>";
			print $output;
		}
	
	
/**
 * Mobile Abfrage, damit Video mobil ausgeblendet wird
 * http://stackoverflow.com/questions/4117555/simplest-way-to-detect-a-mobile-device
 * PM AS
 **/
	if( !function_exists("is_mobile")) {
		function is_mobile() {
			$is_mobile = 0;

			$useragent=$_SERVER['HTTP_USER_AGENT'];

			if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
					$is_mobile = 1;
			}

			return $is_mobile;
		}
	}
		

/**
 * Testet, ob die aktuelle Seite per https oder http aufgerufen wurde. Geht davon aus, dass der Port bei https / SSL auch 443 ist.
 * PM RH, 09.11.2015
 * von http://stackoverflow.com/questions/1175096/how-to-find-out-if-you-are-using-https-without-serverhttps
 **/
	function isSecure() { 
	  return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')   ||    $_SERVER['SERVER_PORT'] == 443;
	}

	
	
// von http://stackoverflow.com/questions/5501427/php-filesize-mb-kb-conversion
	if( !function_exists("filesize_formatted")) {
	function filesize_formatted($size) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$power = $size > 0 ? floor(log($size, 1024)) : 0;
		return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
	}
	}

/**
 * Used to convert a Datetimepicker to mysql-format
 * @deprecated use convertDateToMysql instead.
 **/
	if( !function_exists("convert_date_to_mysql")) {
	function convert_date_to_mysql($value) {
		$value = str_replace("/", ".", $value); // convert "16/01/2015 10:05" to "2015-01-16 10:05:00"
		$return = date("Y-m-d H:i:s", strtotime($value));
		return $return;
	}	
	}	


/**
 * Prüft ob ein Array numerische Indexe hat. Bei assoziativen Arrays kommt also FALSE zurück.
 * von http://php.net/manual/de/function.is-numeric.php#109083
 * PM, RH 29.09.2014
 **/
	if( !function_exists("is_numeric_array")) {
	function is_numeric_array($array) {
		foreach ($array as $key => $value) {
			if (!is_numeric($key)) {
                return false;
            }
		}
		return true;
	}
	}
	
/**
 * Prüft ob ein Array assoziativ statt numerisch ist.
 * von http://php.net/manual/de/function.is-array.php#89332
 * PM, RH 29.09.2014
 **/
	function is_assoc($var) {
        return is_array($var) && array_diff_key($var, array_keys(array_keys($var)) );
	}
	
    
/**
 * wenn $var == false oder NULL oder "" ist, dann wird TRUE returned.
 * PM, RH 30.07.2017
 * @param string $var
 * @return boolean
 **/
	function is_empty($var) {
        if ($var===false || is_null($var) || ($var==="") ) {
            return true;
        }
        return false;
	}
	
/**
 * Wird von array_filter verwendet, um leere Elemente aus einem Array zu entfernen, nicht aber 0 oder "0".
 * @param string $var
 * @return boolean
 */
	function is_not_empty($var) {
        return !is_empty($var);
    }
	


/**
 * Entspricht die IP des Besuchers der angegebenen IP?
 * Usage: if (is_ip("178.7.26.90")) ENVIRONMENT=="dev";
 * @changelog: 2015-02-19 PM RH. Wenn hinter einem Proxy, dann die IP von HTTP_X_FORWARDED_FOR auslesen. zB bei Galabau wegen Varnish
 **/	
	if( !function_exists("is_ip")) {
	function is_ip($ip) {
			$currentip = $_SERVER["REMOTE_ADDR"];
			if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) $currentip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			return ($currentip == $ip);
		}
	}


/**
 * Formt alle Eingaben so um, dass sie als Link in einem A-Tag eingegeben werden können
 * http://php.net/manual/de/function.parse-url.php
 **/	
	if( !function_exists("unparse_url")) {
	function unparse_url($url) { 
		$parsed_url = parse_url($url);
		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
		$host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
		$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
		$user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
		$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
		$pass     = ($user || $pass) ? "$pass@" : ''; 
		$path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
		$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
		return "$scheme$user$pass$host$port$path$query$fragment"; 
	} 
	} 
	

/**
 * Ausgabe nur machen, wenn PHP über Konsole gestartet wird.
 * Siehe http://stackoverflow.com/questions/1042501/how-to-check-with-php-if-the-script-is-being-run-from-the-console-or-browser-req
 * PM, RH
 **/
	if( !function_exists("print_console")) {
	function print_console($s, $where) {
	
		if (!defined("STDIN") || php_sapi_name() != 'cli') {
            // return; // Postman Chrome-Plugin uses $_SERVER["HTTP_POSTMAN_TOKEN"].
        }
		
        $where = str_replace(array("<br/>", "<br>", "<br />"), "\n", $where);
        $where = strip_tags($where);
        
		if (!empty($where)) {
            print "\n\n-------- ";
			print "".str_replace($_SERVER["DOCUMENT_ROOT"], "", $where);
            print " --------- \n";
		} else {
            print "\n\n--------------- PREPRINT Konsole --------------- \n";
			print "BACKTRACE: ";
            debug_print_backtrace(); // Better debugging backtrace.
            print "\n\n\n";
		}
        
        $s = str_replace(array("<br/>", "<br>", "<br />"), "\n", $s);
        // $s = strip_tags($s)."\n"; Das hier funktioniert leider mit reinen Array-Preprints nicht.
        print_r($s);
		// print "\n--------------- /PREPRINT Konsole ---------------";
		print "\n";
    }
    }

/**
 * Debuggingausgabe von Objekten und Arrays
 * PM RH, 21.08.2014: Ergänzung um den Backtrace, was $where überflüßig macht.
 * PM RH, 29.05.2015: Ergänzung wegen ENVIRONMENT. Soll NUR im DEV-Modus etwas ausgeben. 
 * PM RH, 13.08.2017: if IS_AJAX ergänzt. Siehe auch Konstante ganz oben.
 **/	
	if( !function_exists("preprint")) {
	function preprint($s, $where="", $force_output=false) {
	
		/**
         * Aufruf über die Konsole darf keinen HTML-Inhalt ausgeben. Environment ist hierbei auch egal. PM RH 03.12.2016
         * PMRH 03.01.2018: Postman Chrome-Plugin uses $_SERVER["HTTP_POSTMAN_TOKEN"].
         */
			if (php_sapi_name() == 'cli' || isset($_SERVER["HTTP_POSTMAN_TOKEN"]) ) {
				print_console($s, $where);
				return;
			}
            
		/**** das hier  NIE löschen! ****
		 * Manchmal werden im Code preprint-Ausgaben vergessen, das hier sorgt dafür dass die Live- und Test-Seite funktioniert.
		 *
		 * Stattdessen in index.php oder defines.php die ENV-Abfrage anpassen.
		 **/
			if (ENVIRONMENT!="development" && $force_output!==true) return;
		
		if (!empty($where)) {
            $where = " -- Datei: ".str_replace($_SERVER["DOCUMENT_ROOT"], "", $where);
        }
        	
        // When using Ajax, we normally output things like this in the console.log. There we don't ant HTML, so use print_r only.
            if (IS_AJAX) {
                print $where."\n";
                print_r($s);
                return;
            }
        
		print "<style type='text/css'>pre { font-size: 12px; font-family: courier; } </style>";
		print "<pre><small>".ENVIRONMENT."-System PREPRINT-Ausgabe".$where."</small>\n<strong>";
		print_r($s);
		print "</strong></pre>";
		if (empty($where)) {
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
			print "<pre>BACKTRACE: ";
                // print_r($trace[1]);
                debug_print_backtrace(); // Better debugging backtrace.
            print "</pre><br/><br/>";
		}
	}
	}
	
	
/**
 * Liefert die oktalen Schreibrechte eines Ordners oder einer Datei zurück. Von http://php.net/manual/en/function.fileperms.php
 * @author: RH
 * @version: 1
 * @since: 28.10.2013
 **/
	if( !function_exists("file_perms")) {
	function file_perms($file, $octal = true) {
		if(!file_exists($file)) return false;

		$perms = fileperms($file);

		$cut = $octal ? 2 : 3;

		return substr(decoct($perms), $cut);
	}
	}
 

/**
 * Liefert den benutzten Browser zurück: IE, IE7, Firefox, Safari, usw
 * von http://www.php.net/manual/de/function.get-browser.php#101125
 *
 * @todo: Später evtl das hier verwenden? http://chrisschuld.com/projects/browser-php-detecting-a-users-browser-from-php.html
 *
 * @version 2
 * @changes
 *		16.12.2013, PM RH: Erkennung für MSIE 11 hinzugefügt, da dieser nicht mehr MSIE im UA-String ausgibt.
 */
	if( !function_exists("getBrowser")) {
	function getBrowser() {
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$ub = 'Unknown';
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";
		$pattern= "";
		// preprint($u_agent, __FILE__.__LINE__, true); 
		// IE11 = "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko"
		// PM RH 18.05.2016: Edge = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586"
		
		 
		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		}
		elseif (preg_match('/iPad/i', $u_agent)) {
			$platform = 'iPad';
		}
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		}
		elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}
		// PM RH 28.07. Firefox mobile auf Android liefert "android" als OS aus, statt Linux.
		elseif (preg_match('/android/i', $u_agent)) {
			$platform = 'android';
		}
		
	   
		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
		{
			$bname = 'InternetExplorer';
			$ub = "MSIE";
		}
		elseif(preg_match('/Edge/i',$u_agent))
		{
			$bname = 'Edge';
			$ub = "MSEDGE";
			
			$version = explode("Edge/", $u_agent);
			$version = (float) $version[1];
		}
		elseif(preg_match('/Trident/i',$u_agent))
		{
			$bname = 'InternetExplorer';
			$ub = "MSIE";
			
			$version = explode("rv:", $u_agent);
			$version = (float) $version[1];
		}
		elseif(preg_match('/Firefox/i',$u_agent))
		{
			$bname = 'Firefox';
			$ub = "Firefox";
		}
		elseif(preg_match('/Chrome/i',$u_agent))
		{
			$bname = 'Chrome';
			$ub = "Chrome";
		}
		elseif(preg_match('/Safari/i',$u_agent))
		{
			$bname = 'Safari';
			$ub = "Safari";
		}
		elseif(preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Opera';
			$ub = "Opera";
		}
		elseif(preg_match('/Netscape/i',$u_agent))
		{
			$bname = 'Netscape';
			$ub = "Netscape";
		}
	   
		// finally get the correct version number
		if (isset($ub)) {
			$known = array('Version', $ub, 'other', "rv:");
			$pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
			if (!preg_match_all($pattern, $u_agent, $matches)) {
				// we have no matching number just continue
			}
			
			// see how many we have
			$i = count($matches['browser']);
			// preprint($matches);
			if ($i != 1) {
				//we will have two since we are not using 'other' argument yet
				//see if version is before or after the name
				if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
					if (isset($matches['version'][0])) $version= $matches['version'][0];
				}
				else {
					if (isset($matches['version'][1])) $version= $matches['version'][1];
				}
			}
			else {
				if (isset($matches['version'][0])) $version= $matches['version'][0];
			}
			
		}
	   
		
	   
		// check if we have a number
		if ($version==null || $version=="") {$version="?";}
	   
		$return = array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'    => $pattern
		);
		//  preprint($return);
		return $return;
	}  // function getBrowser
	}  
	
	
	
	
	
/**
 * Kürzen eines Textes
 * Der Text wird beim ersten Leerzeichen gekürzt welches vor dem Limit kommt
 * @author: PM BE
 * @deprecated !! DO NOT USE, da abhängig von Joomla. Ist jetzt nur noch ein Alias für string_limit_words(), welches unabhängig ist. PM RH 29.05.2015
 **/
	if( !function_exists("truncate")) {
	function truncate($text, $length = 0) {
		if( !function_exists("string_limit_words")) mb_substr($text, 0, $length); // Fallback
		return string_limit_words($text, $length = 0);
	}
	}

	
/**
 * Text auf Anzahl Wörter kürzen
 * Siehe http://www.wpsite.net/limit-excerpt-length-words-wordpress/
 * @author: PM, RH
 **/
	if( !function_exists("string_limit_words")) {
	function string_limit_words($string, $word_limit) { 
	  $words = explode(' ', $string, ($word_limit + 1));
	  $addon = "";
	  if(count($words) > $word_limit) {
		array_pop($words);
		$addon = " [...] ";
		if (ENVIRONMENT=="dev") $addon = " <span title='".__FILE__."'>[...]</span>";
		}
	  return implode(' ', $words).$addon;
	  
	}
	}
	
/**
 * Siehe https://trello.com/c/DzYdfKrl/493-shorten-docman-titles-via-language-key
 * @author: Hari Narayan Shanker Sharma.
 * @return: shortened String for a given length and add "..." at the end.
 **/
	if( !function_exists("string_limit_char")) {
	function string_limit_char($string, $word_limit) { 
		$stringlength = strlen($string);
		if($word_limit > $stringlength){
			return $string;
		}
		else{
			$addon = " [...] ";
			if (ENVIRONMENT=="dev") $addon = " <span title='".__FILE__.__LINE__."'>[...]</span>";
			$string = substr($string,0,$word_limit).$addon; 
			return $string;
		}	  
	}
	}
    
    
    /**
     * Test a string, if it is a valid JSON. 
     * @link https://subinsb.com/php-check-if-string-is-json/
     * @param type $string
     * @return bool
     */
    function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
     }
	

	
if( !function_exists("curl_get")) {
/**
 * Lädt den Datensatz einer URL per cURL und gibt diesen als STRING zurück. Muss dann meistens noch json_decoded werden.
 * inkl SSL-Optionen
 * @param string url URL, die aufgerufen werden soll.
 * @param string auth_string für HTTP-Auth die Benutzerdaten im Format "username:password"
 * @param array $headers
 * @author: PM, RH
 * @changes 13.10.2016 RH: auth_string ergänzt. 
 **/
	function curl_get( $url, $auth_string="", $authMethod="", $headers=array()){
		$ch = curl_init( $url );
		
		// Die Optionen für den Request setzen.
			curl_set_globaloptions($ch);
			
		if ($auth_string!=="") {
            if ($authMethod=="") $authMethod = CURLOPT_USERPWD;
			curl_setopt($ch, $authMethod, $auth_string);
		}
        if (!empty($headers) && is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		$result = curl_exec($ch);  
        // preprint($headers, __FILE__.__LINE__); preprint(curl_getinfo($ch), __FILE__.__LINE__);
		curl_close($ch);
		return $result;
	}
}

	
if( !function_exists("curl_post")) {
/**
 * Postet Daten an eine URL. Siehe https://lornajane.net/posts/2011/posting-json-data-with-php-curl
 * @todo das hier auch für den Entrypage-Helper kopieren
 * @author: PM, RH 01.03.2017
 * 
 * @param string $url
 * @param string $data_string should already be converted with http_build_query() or json-encode.
 * @param string $auth_string
 * @param string $authMethod
 * @param array $headers
 * @return mixed
 */
	function curl_post( $url, $data_string, $auth_string="", $authMethod="", $headers=array()){
		$ch = curl_init( $url );
		
		// Die Optionen für den Request setzen.
			curl_set_globaloptions($ch);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                  
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string); 
        
        if (!empty($headers) && is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
        if ($auth_string!=="") {
            if ($authMethod=="") $authMethod = CURLOPT_USERPWD;
			curl_setopt($ch, $authMethod, $auth_string);
		}
        
        // preprint(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL), __FILE__.__LINE__);
		$result = curl_exec($ch);  
		curl_close($ch);
		return $result;
	}
}

/**
 * Quasi eine Kopie von "curl_post". Unterscheided sich nur durch die Option CURLOPT_CUSTOMREQUEST "PATCH"
 * Postet Daten an eine URL. Siehe https://lornajane.net/posts/2011/posting-json-data-with-php-curl
 * @todo das hier auch für den Entrypage-Helper kopieren
 * @author: PM, RH 01.03.2017
 **/
if( !function_exists("curl_patch")) {
    function curl_patch( $url, $data_string, $auth_string="", $authMethod="", $headers=array()){
        $ch = curl_init( $url );
		
		// Die Optionen für den Request setzen.
			curl_set_globaloptions($ch);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        if (!empty($headers) && is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($auth_string!=="") {
            if ($authMethod=="") $authMethod = CURLOPT_USERPWD;
            curl_setopt($ch, $authMethod, $auth_string);
        }

        // preprint(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL), __FILE__.__LINE__);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

/**
 * Setzt die cURL Optionen, die immer für alle cURL Aufrufe gelten.
 * Ist jetzt in separater Funktion notwendig, da wir 3 verschiedene cURL-Funktionen haben.
 * @see http://php.net/manual/de/function.curl-setopt.php
 * @author PM RH 20.12.2017
 **/
	function curl_set_globaloptions($ch) {
		
            
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		
		// Maximale Zeit in Sekunden für den Verbindungsaufbau
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		
	}
		


/**
 * Wandelt geladene Datensätze so um, dass sie assoziativ über die ID zugreifbar sind.
 * Welche ID es ist, wird mit dem Parameter $key_field_name festgelegt
 * $flat = true bewirkt, dass Einträge zu $return[$id] hinzugefügt werden (zB $return[$id][0] und $return[$id][1], usw).
 *
 * @param array $rows
 * @param string $key_field_name
 * @param string $flat_field_name String oder Array von Strings (PM, RH seit 26.03.2014)
 * @return array
 */
	if( !function_exists("create_select_array")) {
	function create_select_array($rows, $key_field_name, $flat = true, $flat_field_name="") {
		
		$return = array();
		if (empty($key_field_name)) {
			preprint("key_field_name darf nicht leer sein!", __FILE__.__LINE__);
		}
		
		if(!empty($rows)) {
			foreach ($rows as $row) {
				// Handelt es sich um ein Objekt, statt ein Array?
					if (is_object($row)) {
						if (!isset($row->$key_field_name)) {
							preprint("$key_field_name nicht in \$row gefunden");
							continue;
						}
						$id = $row->$key_field_name;
						$flat_value = "";
						if (is_array($flat_field_name)) {
                            $flat_value = array();
							foreach ($flat_field_name as $ffn) {
								if (isset($row->$ffn)) {
                                    $flat_value[] = $row->$ffn;
                                }
							}
                            $flat_value = implode(" // ", $flat_value);
						} else {
							if (isset($row->$flat_field_name)) {
                                $flat_value = $row->$flat_field_name;
                            }
						}
					}
					
				// Ist $row ein Array und kein Objekt?
					if (is_array($row)) {
						if (!isset($row[$key_field_name])) {
							preprint("$key_field_name nicht in \$row gefunden");
							continue;
						}
						$id = $row[$key_field_name];
						$flat_value = "";
						if (is_array($flat_field_name)) {
							$flat_value = array();
							foreach ($flat_field_name as $ffn) {
								if (isset($row[$ffn])) {
                                    $flat_value[] = $row[$ffn];
                                }
							}
                            $flat_value = implode(" // ", $flat_value);
						} else {
							if (isset($row[$flat_field_name])) {
                                $flat_value = $row[$flat_field_name];
                            }
						}
					}

                    
                // Achtung: das "&" ist scheinbar als Key nicht erlaubt. $clients["Deffner & Johann"] liefert nichts zurück 
                // $id = str_replace("&", "_", $id);
                
				if ($flat) {
					if ($flat_field_name!=="") $return[$id] = $flat_value;
					else $return[$id] = $row;
				}
				else {
					if (!isset($return[$id])) $return[$id] = array();
					$return[$id][] = $row;
				}
			}
		}
		return $return;
	}
	}
	
	

/**
 * Automatisches Einfügen einer Sitemap-Referenz in die robots.txt
 * Wird jedes mal beim Aufrufen der Website ausgeführt
 * Magento hat standardmäßig keine Robots.txt. Siehe https://www.byte.nl/blog/magento-robots-txt/
 * @author: pm, pk, 22.07.2014
 * @changes: PM RH 29.05.2015: Erweitert um Default-Robots-Content und Anpassung des Sitemap-Paths, damit auch für andere Tools von PM nutzbar (zB Codeigniter und Magento)
 **/
	if( !function_exists("checkRobotsTXT")) {
	function checkRobotsTXT($sitemap_path = "/sitemap.xml") {
		
		$file = "robots.txt";
		$secondary_file = "robots.txt.dist"; 
		
		// Falls die Datei robots.txt nicht existiert: neu anlegen
			if (!file_exists($file)) {
				
				// Siehe http://de.wikipedia.org/wiki/Robots_Exclusion_Standard
					$default_content = "User-agent: * \n";
					$default_content = "Disallow: /administrator/ \n";
					$default_content = "Disallow: /admin/ \n";
					$default_content = "Disallow: /cgi-bin/ \n";
					$default_content = "Disallow: /cache/ \n";
					$default_content = "Disallow: /usage/ \n";
					$default_content = "Disallow: /log/ \n";
					$default_content = "Disallow: /logs/ \n";
					$default_content .= "Crawl-delay: 120 \n"; // 
				
				//Falls die Datei robots.txt.dist existiert: Daten in die robots.txt kopieren				
					if (file_exists($secondary_file)) $default_content = file_get_contents($secondary_file);
				
				// Die robots.txt neu anlegen und mit Inhalt füllen
					file_put_contents($file, $default_content); 
				
			}
		
		// Inhalt aus der robots.txt auslesen
			$file_content = file_get_contents($file); 
		
		//Falls Sitemap noch nicht in robots.txt: Sitemap mit URL unten anfügen 
			if (stristr($file_content, 'Sitemap:') === FALSE){
			
				//Bei Port 443 HTTPS verwenden
				$prot = "http";
				if ($_SERVER["REMOTE_PORT"]==443){
					$prot = "https";
				}
				$sitemap_str = $prot."://".$_SERVER['HTTP_HOST']."/".sitemap_path;
				$file_content .= "\n\nSitemap: ".$sitemap_str; 
				file_put_contents($file, $file_content); 
			}
	}
	} // checkRobotsTXT
	
	

	
	/**
	 * Create directories recursive, if not exists.
	 **/
		if( !function_exists("mkdir_recursive")) {
		function mkdir_recursive($folder, $rights=0777) {
			
			// return mkdir($folder, $rights, true); // recursive, siehe http://php.net/manual/de/function.mkdir.php			
			
			$parts = explode("/",$folder);
			$root = ".";
			
			foreach ($parts as $part) {
				if (empty($part)) continue;
				$root .= "/".$part;
				
				if (!is_dir($root)) {
					// print "<br/>Lege an: $root: ";
					mkdir($root, $rights); 
				}
			}
			
			return is_dir($folder);
		}
		}
		
	/** 
	* Recursively delete a directory 
	* von http://php.net/manual/de/function.unlink.php#87045
	* @param string $dir Directory name 
	* @param boolean $deleteRootToo Delete specified top-level directory as well 
	*/ 
		function unlinkRecursive($dir, $deleteRootToo)  { 
			if(!$dh = @opendir($dir))  { 
				return false;
			} 
			
			while (false !== ($obj = readdir($dh))) { 
				if($obj == '.' || $obj == '..')  { 
					continue; 
				} 

				if (!@unlink($dir . '/' . $obj)) { 
					unlinkRecursive($dir.'/'.$obj, true); 
				} 
			} 

			closedir($dh); 
			
			if ($deleteRootToo)  { 
				@rmdir($dir); 
			} 
			
			return; 
		} 
				
		
	/**
	 * Used to convert a mysql-date(time) value into a usable format for tables or form-fields.
	 * if "%" is part of the format-string, the function strftime() is used. Otherwise date() 
	 **/
		if( !function_exists("convert_mysqldate_date")) {
		function convert_mysqldate_date($value, $format) {
			$value = trim($value);
            
            // Separat handling of 0 values from mysql. Must return "" by default.
                $null_values = array("0000-00-00 00:00:00", "0000-00-00", "00:00:00", "0", "", "NULL", null);
                if (in_array($value, $null_values)) {
                    return "";
                }
            
            // preprint($value, $format);
            // preprint("Konvertiere $value in Format $format", __FILE__.__LINE__);
            if (strpos($format, "%")!==false) {
                $value = strftime($format, strtotime($value)); // convert to mysql-DATE format
            }
            else {
                $value = date($format, strtotime($value)); // convert to mysql-DATE format
            }
			
			return $value;
		}	
		}	
		

	/**
	 * Macht aus "GEMUESE FRISCH / BLUMENKOHL" => "Gemuese Frisch / Blumenkohl"
	 **/ 
		if( !function_exists("str_beautify")) {
		function str_beautify($string) {
			$string = strtolower($string); 
			$string = str_replace(" // ", ", ", $string); 
			$string = ucwords($string); // siehe http://de.php.net/ucwords
			return $string;
		}
		}
        
    /**
     * Test, if a string begins with another string.
     * @param type $str
     * @param type $beginsWith
     * @return boolean
     * @author PM RH 22.08.2017
     */
        function strBeginsWith($str, $beginsWith) {
           if (strpos($str, $beginsWith)===0) {
               return true;
           }
           return false;
       }

		
	/**
	 * Kürzt einen Text auf die angegebene Länge und fügt "Suffix" hinten an. zB ...
	 * @RH kürzt das nur auf wörter??
	 **/ 
		if( !function_exists("shortText")) {
		function shortText($string, $length, $suffix="...") {
			if(strlen($string) > $length) {
				$string = mb_substr($string,0,$length).$suffix;
				$string_ende = mb_strrchr($string, " ");
				$string = str_replace($string_ende, $suffix, $string);
			}
			return $string;
		}
		}


	

	/**
	 * Konvertiert einen String im Format xx,yy zu einer Währung im Format "x,xxx.yy €".
	 * Siehe http://php.net/manual/en/function.money-format.php
     * On windows, money_format is not existing. So we use this fake-function.
     * @link http://stackoverflow.com/questions/6369887/alternative-to-money-format-function-in-php-on-windows-platform
	 **/
		function money($input, $currency="EUR") {
            $val = str_replace(",", ".", $input);
            $val = (float)$val;
            // preprint($val, __FILE__.__LINE__);
            
			if(function_exists("money_format")) {
				setlocale(LC_MONETARY, 'de_DE');
				$return = money_format('%.2n', $val);
			}
			else {
				$val = number_format($val, 2, ",", ".");
			}
            
            $return = $val." ".$currency;
            $return = str_replace("EUR", "&euro;", $return);
			return $return;
		}
		
	
	/**
     * String für Dateinamen sanieren
	 * Umlaute umwandeln, ungültige Zeichen ersetzen
	 *
	 ***/
		if( !function_exists("sanitize_string")) {
		function sanitize_string($str) {
		 
			$str = str_replace(array("ä","ü","ö","Ä","Ü","Ö", "."), array("ae","ue","oe","Ae","Ue","Oe", "_"), $str);
			$str = strtolower(preg_replace("/[^a-zA-Z0-9]/", "_", $str)); // ungültige Zeichen mit einem "_" ersetzen
			$str = preg_replace('/[_]{2,}/', '_', $str); // mehrere nacheinander folgende Unterstriche zu einem zusammenfassen
			return $str;	 
		 
		 }	
		 }	
		 
		 
		

	/**
	 * Sichert einen eingegebenen String zB aus Formularen ab. Ist aber nur ganz Basicmäßig und sollte so alleine nicht verwendet werden.
	 **/
		if( !function_exists("secure")) {
		function secure($s, $strip_tags = false){
            $s = trim($s);
            if ($strip_tags===true) {
                $s = strip_tags($s);
            }
            
			return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
		}
		}
			
		
		
	/**
	 * $rows = array(assoc-array) <- beinhaltet alle Daten
	 * $keys = array(key => titel) <- hiermit kann festgelegt werden welche Spalten angezeigt werden sollen, und wie diese benannt werden.
	 * Teilweise von http://www.php.net/manual/en/function.fputcsv.php#104980
	 * @changes: 22.06.2015 PM RH - "forExcel" hinzugefügt, um eine CSV-Datei sofort ohne Konvertierung in MS Excel importieren zu können.
	 **/
		if( !function_exists("export_csv")) {
			function export_csv($array, $fields=array(), $filename="", $forExcel = false) { // Von http://codeigniter.com/forums/viewreply/776191/
				// preprint($array); preprint($fields); return; 
				
				$charset = "utf-8";
				if ($forExcel===true) $charset = "UTF-16LE";
				$delimiter = ";";
				$enclosure = '"';
				$search = array($delimiter, $enclosure);
				$replace= array(",", "\"");
				
				if (empty($filename)) $filename = "csv_export.csv";
				else $filename = basename($filename);
					 
				/**
                 * Fields = Spaltennamen. Wenn diese nicht übergeben wurden, dann aus der 1. Zeile des Assoc-Arrays verwenden.
                 */		
					if (empty($fields)) {
                        $fields = array_keys((array)$array[0]);
                    }
					
                
				/**
				 * Feldtitel in die erste Zeile
				 **/
                    $titles = array();
    				$output_array = array();
                    foreach ($fields as $key => $fieldname) {
                        $titles[] = $fieldname;
                    }
                    $output_array[0] = $titles;
					
				
				/**
				 * Die Daten durchlaufen
				 **/
					$i = 1;
					foreach ($array as $data) {
						$row = array();
                        $data = (array) $data;
                        // preprint($fields, __FILE__.__LINE__); preprint($data, __FILE__.__LINE__);
                        
						foreach ($fields as $key => $title) {
                            // preprint($key, $title.__FILE__.__LINE__);
							$value=" ";
							if (isset($data[$key])) {
                                $value = str_replace($search, $replace, $data[$key]);
                            } elseif (isset($data[$title])) {
                                $value = str_replace($search, $replace, $data[$title]);
                            }
							
							/**
							 * Für MS Excel umkonvertieren. Siehe http://stackoverflow.com/questions/4348802/how-can-i-output-a-utf-8-csv-in-php-that-excel-will-read-properly
							 * PM RH 22.06.2015
							 **/
								if ($forExcel===true) {
									$value = mb_convert_encoding($value, 'UTF-16LE', 'UTF-8');
								}
								
							$row[] = $value;
						}
                        
						$output_array[$i] = $row;
						$i++;
					}
					
					// preprint($output_array, __FILE__.__LINE__); die();
					
				/**
				 * OUTPUT der Daten
				 **/
					// header("Content-type: application/vnd.ms-excel; charset=UTF-16LE" ); 
					// header("Content-type: application/x-msdownload");
					// header("Content-Disposition: attachment; filename=".$filename.".xlsx");
					
					header( 'Content-Type: text/csv' );
					header("Content-Type: text/csv; charset=".$charset);
					header( 'Content-Disposition: attachment;filename='.$filename);
					$fp = fopen('php://output', 'w');
					
					/**
					 * Für MS Excel umkonvertieren. Siehe http://stackoverflow.com/questions/4348802/how-can-i-output-a-utf-8-csv-in-php-that-excel-will-read-properly
					 * PM RH 22.06.2015
					 **/
						if ($forExcel===true) {
							fputs($fp, chr(255).chr(254));
						}
						
					
					
					foreach ($output_array as $row) {
						fputcsv($fp, $row, $delimiter, $enclosure);
					}
					
					fclose($fp);
					die();
			}
		} // export CSV
		
		
	/**
	 * Sendet eine Datei direkt an den Browser. Sinnvoll für zB verschleierte Download-Links. 
	 * PM RH 08.06.2015
	 * @param type $file
	 * @param type $mimetype
	 */
		function sendFileToBrowser($file, $mimetype="") {
			if ($mimetype=="") {
				$file_extension = strtolower(substr(strrchr($file,"."),1));

				switch ($file_extension) {
					case "pdf": $mimetype="application/pdf"; break;
					case "exe": $mimetype="application/octet-stream"; break;
					case "zip": $mimetype="application/zip"; break;
					case "doc": $mimetype="application/msword"; break;
					case "xls": $mimetype="application/vnd.ms-excel"; break;
					case "ppt": $mimetype="application/vnd.ms-powerpoint"; break;
					case "gif": $mimetype="image/gif"; break;
					case "png": $mimetype="image/png"; break;
					case "jpe": case "jpeg":
					case "jpg": $mimetype="image/jpg"; break;
					default: 
						$mimetype="application/x-download"; // application/force-download";
				}
			}
			
			header('Content-Type: '.$mimetype );
			header("Content-Type: {$mimetype}; charset=".$charset);
			
			header('Content-Disposition: attachment;filename='.basename($file));
			
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // some day in the past
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Content-Transfer-Encoding: binary");
			
			$fp = fopen('php://output', 'w');
			readfile($file);
			fclose($fp);
			die();
		}
		
		
	/**
	 * $rows = array(assoc-array) <- beinhaltet alle Daten
	 * $keys = array(key => titel) <- hiermit kann festgelegt werden welche Spalten angezeigt werden sollen, und wie diese benannt werden.
	 **/
		if( !function_exists("export_excel")) {
		function export_excel($array, $fields=array(), $filename="") { // Von http://codeigniter.com/forums/viewreply/776191/
			// preprint($array); preprint($fields); return; 
			
			$cols = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
			$debug_array = array(); // in der Schleifen unten wird das hier befüllt, um anschliessend zu prüfen, ob alles korrekt eingetragen wurde.
			
			if (empty($filename)) $filename = "excel_export";
			else $filename = basename($filename);
				 
				// header("Content-Type: text/html; charset=utf-8");
				// header("Content-type: application/vnd.ms-excel; charset=UTF-16LE" ); 
				// header("Content-type: application/x-msdownload");
				// header("Content-Disposition: attachment; filename=".$filename.".xlsx");
			
			if (defined("JPATH_BASE")) $base_path = JPATH_BASE;
			else $base_path = __DIR__;
			
			require_once($base_path."/libraries/phpexcel/PHPExcel.php");
			require_once($base_path."/libraries/phpexcel/PHPExcel/IOFactory.php");
			
			$objPHPExcel = new PHPExcel();
			
			// $objPHPExcel->getProperties()->setTitle("title")->setDescription("description");
			$objPHPExcel->setActiveSheetIndex(0); // Erstes Arbeitsblatt
			
			$row = 0;
			$col = 0;
			if (!empty($fields)) {
				foreach ($fields as $key => $fieldname) {
					$cell = $cols[$col].$row++;
					$debug_array[$cell] = $fieldname;
					$objPHPExcel->getActiveSheet()->setCellValue($cell, $fieldname);
					// $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
				}
				
				$row = 0;
			}
			
			foreach ($array as $data) {
				$col++;
				$row = 0;
				foreach ($fields as $key => $title) {
					if (isset($data[$key])) $value = $data[$key];
					else $value=" ";
					
					$cell = $cols[$col].$row++; // zB A1, B1, A2, B2, usw
					$debug_array[$cell] = $value;
					$objPHPExcel->getActiveSheet()->setCellValue($cell, $value);
					// $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					// $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
					// $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(30);
				}
			}
			
			// Aus PHPExcel Developer Doc, Seite 25, 4.6.26
			// $objPHPExcel->getActiveSheet()->getProtection()->setPassword(md5("blablalba"));
			// $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true); // Erstmal alles sperren
			// $objPHPExcel->getActiveSheet()->getStyle('B6:B50')->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED); // Hier können Eingaben gemacht werden
			
			
			// Schön machen
			// $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
			// $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
			
			
			// Save it as an excel 2003 file. Von http://www.mikeborozdin.com/post/PHPExcel-Manipulate-Excel-Spreadsheets-with-PHP-on-Linux.aspx
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007"); // Excel2007, Excel5
			
			header("Content-type: application/vnd.ms-excel; charset=UTF-16LE" ); 
			header("Content-Disposition: attachment; filename=".$filename.".xlsx");
			$objWriter->save("php://output");
		}
		}
			


	
	
/**
 * -----------------------------------------------------------------------------------------
 * Based on `https://github.com/mecha-cms/mecha-cms/blob/master/system/kernel/converter.php`
 * -----------------------------------------------------------------------------------------
 */

		// HTML Minifier
		function minify_html($input) {
			if(trim($input) === "") return $input;
			// Remove extra white-spaces between HTML attributes
			$input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
				return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
			}, $input);
			// Minify inline CSS declarations
			if(strpos($input, ' style=') !== false) {
				$input = preg_replace_callback('#\s+style=([\'"]?)(.*?)\1(?=[\/\s>])#s', function($matches) {
					return ' style=' . $matches[1] . minify_css($matches[2]) . $matches[1];
				}, $input);
			}
			return preg_replace(
				array(
					// Remove HTML comments except IE comments
					'#\s*(<\!--(?=\[if).*?-->)\s*|\s*<\!--.*?-->\s*#s',
					// Do not remove white-space after image and
					// input tag that is followed by a tag open
					'#(<(?:img|input)(?:\/?>|\s[^<>]*?\/?>))\s+(?=\<[^\/])#s',
					// Remove two or more white-spaces between tags
					'#(<\!--.*?-->)|(>)\s{2,}|\s{2,}(<)|(>)\s{2,}(<)#s',
					// Proofing ...
					// o: tag open, c: tag close, t: text
					// If `<tag> </tag>` remove white-space
					// If `</tag> <tag>` keep white-space
					// If `<tag> <tag>` remove white-space
					// If `</tag> </tag>` remove white-space
					// If `<tag>    ...</tag>` remove white-spaces
					// If `</tag>    ...<tag>` remove white-spaces
					// If `<tag>    ...<tag>` remove white-spaces
					// If `</tag>    ...</tag>` remove white-spaces
					// If `abc <tag>` keep white-space
					// If `<tag> abc` remove white-space
					// If `abc </tag>` remove white-space
					// If `</tag> abc` keep white-space
					// TODO: If `abc    ...<tag>` keep one white-space
					// If `<tag>    ...abc` remove white-spaces
					// If `abc    ...</tag>` remove white-spaces
					// TODO: If `</tag>    ...abc` keep one white-space
					'#(<\!--.*?-->)|(<(?:img|input)(?:\/?>|\s[^<>]*?\/?>))\s+(?!\<\/)#s', // o+t | o+o
					'#(<\!--.*?-->)|(<[^\/\s<>]+(?:>|\s[^<>]*?>))\s+(?=\<[^\/])#s', // o+o
					'#(<\!--.*?-->)|(<\/[^\/\s<>]+?>)\s+(?=\<\/)#s', // c+c
					'#(<\!--.*?-->)|(<([^\/\s<>]+)(?:>|\s[^<>]*?>))\s+(<\/\3>)#s', // o+c
					'#(<\!--.*?-->)|(<[^\/\s<>]+(?:>|\s[^<>]*?>))\s+(?!\<)#s', // o+t
					'#(<\!--.*?-->)|(?!\>)\s+(<\/[^\/\s<>]+?>)#s', // t+c
					'#(<\!--.*?-->)|(?!\>)\s+(?=\<[^\/])#s', // t+o
					'#(<\!--.*?-->)|(<\/[^\/\s<>]+?>)\s+(?!\<)#s', // c+t
					'#(<\!--.*?-->)|(\/>)\s+(?!\<)#', // o+t
					// Replace `&nbsp;&nbsp;&nbsp;` with `&nbsp; &nbsp;`
					'#(?<=&nbsp;)(&nbsp;){2}#',
					// Proofing ...
					'#(?<=\>)&nbsp;(?!\s|&nbsp;|<\/)#',
					'#(?<=--\>)(?:\s|&nbsp;)+(?=\<)#'
				),
				array(
					'$1',
					'$1&nbsp;',
					'$1$2$3$4$5',
					'$1$2&nbsp;', // o+t | o+o
					'$1$2', // o+o
					'$1$2', //c+c
					'$1$2$4', // o+c
					'$1$2', // o+t
					'$1$2', // t+c
					'$1$2 ', // t+o
					'$1$2 ', // c+t
					'$1$2 ', // o+t
					' $1',
					' ',
					""
				),
			trim($input));
		}

		// CSS Minifier => http://ideone.com/Q5USEF + improvement(s)
		function minify_css($input, $method=3) {
			if(trim($input) === "") return $input;
			
						
			/**
			 * Methode 1 (von http://manas.tungare.name/software/css-compression-in-php/)
			 **/
			 if ($method==1) {
				$input = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $input); // Remove comments
				$input = str_replace(': ', ':', $input); // Remove space after colons
				$input = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $input); // Remove whitespace
				return $input;
			 }
				
			
			/**
			 * Methode 2 (Siehe  https://ikreativ.com/combine-minify-css-with-php/)
			 **/
			 if ($method==2) {
				$input = preg_replace( "!/\*[^*]*\*+([^/][^*]*\*+)*/!", '', $input ); // Remove comments
				$input = str_replace( array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $input ); // remove tabs, spaces, newlines, etc.
				return $input;
			 }
				
			// methode 3:
			return preg_replace(
				array(
					// Remove comments
					'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)#s',
					// Remove unused white-spaces
					'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
					// Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
					'#(?<=[:\s])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
					// Replace `:0 0 0 0` with `:0`
					'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
					// Replace `background-position:0` with `background-position:0 0`
					'#(background-position):0(?=[;\}])#si',
					// Replace `0.6` with `.6`, but only when preceded by `:`, `-`, `,` or a white-space
					'#(?<=[:\-,\s])0+\.(\d+)#s',
					// Minify string value
					'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
					'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
					// Minify HEX color code
					'#(?<=[:\-,\s]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
					// Remove empty selectors
					'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
				),
				array(
					'$1',
					'$1$2$3$4$5$6$7',
					'$1',
					':0',
					'$1:0 0',
					'.$1',
					'$1$3',
					'$1$2$4$5',
					'$1$2$3',
					'$1$2'
				),
			trim($input));
		}

		// JavaScript Minifier
		function minify_js($input, $method=3) {
			if(trim($input) === "") return $input;
			
						
			/**
			 * Methode 1 (von http://manas.tungare.name/software/css-compression-in-php/)
			 **/
			 if ($method==1) {
				$input = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $input); // Remove comments
				$input = str_replace(': ', ':', $input); // Remove space after colons
				// $input = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $input); // Remove whitespace
				return $input;
			 }
				
			
			/**
			 * Methode 2 (Siehe  https://ikreativ.com/combine-minify-css-with-php/)
			 **/
			 if ($method==2) {
				$input = preg_replace( "!/\*[^*]*\*+([^/][^*]*\*+)*/!", '', $input ); // Remove comments
				$input = str_replace( array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $input ); // remove tabs, spaces, newlines, etc.
				return $input;
			 }
			 
			 
			return preg_replace(
				array(
					// '#(?<!\\\)\\\\\"#',
					// '#(?<!\\\)\\\\\'#',
					// Remove comments
					'#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*\s*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*[\n\r]*#',
					// Remove unused white-space characters outside the string and regex
					'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\.,;]|[gimuy]))|\s*([+\-\=\/%\(\)\{\}\[\]<>\|&\?\!\:;\.,])\s*#s',
					// Remove the last semicolon
					'#;+\}#',
					// Replace `true` with `!0`
					// '#\btrue\b#',
					// Replace `false` with `!1`
					// '#\bfalse\b#',
					// Minify object attribute except JSON attribute. From `{'foo':'bar'}` to `{foo:'bar'}`
					'#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
					// --ibid. From `foo['bar']` to `foo.bar`
					'#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
				),
				array(
					// '\\u0022',
					// '\\u0027',
					'$1',
					'$1$2',
					'}',
					// '!0',
					// '!1',
					'$1$3',
					'$1.$3'
				),
			trim($input));
		}
	
        
        
        
    /**
     * Converts the string-output of print_r back to an array.
     * @link http://www.php.net/manual/en/function.print-r.php#93529
     * @param STRING $in. Muss in Zeile 1 "Array" alleine stehen haben. In Zeile 2 folgt dann "("
     * @return Array
     */
        function print_r_reverse($in) {
            $lines = explode("\n", trim($in));
            // bottomed out to something that isn't an array or object 
            if (trim($lines[0]) != 'Array' && trim($lines[0] != 'stdClass Object')) {
              return $in;
            }
            
            // this is an array or object, lets parse it 
              $match = array();
                  
          // this is a tested array/recursive call to this function 
              if (preg_match("/(\s{5,})\(/", $lines[1], $match)) {
                // take a set of spaces off the beginning 
                $spaces = $match[1];
                $spaces_length = strlen($spaces);
                $lines_total = count($lines);
                for ($i = 0; $i < $lines_total; $i++) {
                  if (substr($lines[$i], 0, $spaces_length) == $spaces) {
                    $lines[$i] = substr($lines[$i], $spaces_length);
                  }
                }
              }
              $is_object = trim($lines[0]) == 'stdClass Object';
              array_shift($lines); // Array 
              array_shift($lines); // ( 
              array_pop($lines); // ) 
              $in = implode("\n", $lines);
              $matches = array();
          // make sure we only match stuff with 4 preceding spaces (stuff for this array and not a nested one) 
              preg_match_all("/^\s{4}\[(.+?)\] \=\> /m", $in, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
              $pos = array();
              $previous_key = '';
              $in_length = strlen($in);
          // store the following in $pos: 
          // array with key = key of the parsed array's item 
          // value = array(start position in $in, $end position in $in) 
              foreach ($matches as $match) {
                $key = $match[1][0];
                $start = $match[0][1] + strlen($match[0][0]);
                $pos[$key] = array($start, $in_length);
                if ($previous_key != '') {
                  $pos[$previous_key][1] = $match[0][1] - 1;
                }
                $previous_key = $key;
              }
              $ret = array();
              
            // recursively see if the parsed out value is an array too 
                foreach ($pos as $key => $where) {
                    $ret[$key] = print_r_reverse(substr($in, $where[0], $where[1] - $where[0]));
                }
                
                
            return $is_object ? (object) $ret : $ret;
          }

          
    /**
     * Diese Funktion kann als rel="countries" in unseren Entity-XMLs eingetragen werden, damit bei Fieldtypen "Liste" die Länder nicht immer im XML komplett drin stehen.
     * PM RH 11.01.2018
     * @return array
     */
        function get_countries() {
            $countries = array();
            $countries[""] = "";
            $countries["AF"] = "AFGHANISTAN";
            $countries["AX"] = "ALANDINSELN";
            $countries["AL"] = "ALBANIEN";
            $countries["DZ"] = "ALGERIEN";
            $countries["UM"] = "AMERIKANISCHOZEANIEN";
            $countries["AS"] = "AMERIKANISCHSAMOA";
            $countries["VI"] = "AMERIKANISCHEJUNGFERNINSELN";
            $countries["AD"] = "ANDORRA";
            $countries["AO"] = "ANGOLA";
            $countries["AI"] = "ANGUILLA";
            $countries["AQ"] = "ANTARKTIS";
            $countries["AG"] = "ANTIGUAUNDBARBUDA";
            $countries["AR"] = "ARGENTINIEN";
            $countries["AM"] = "ARMENIEN";
            $countries["AW"] = "ARUBA";
            $countries["AZ"] = "ASERBAIDSCHAN";
            $countries["AU"] = "AUSTRALIEN";
            $countries["BS"] = "BAHAMAS";
            $countries["BH"] = "BAHRAIN";
            $countries["BD"] = "BANGLADESCH";
            $countries["BB"] = "BARBADOS";
            $countries["BE"] = "BELGIEN";
            $countries["BZ"] = "BELIZE";
            $countries["BJ"] = "BENIN";
            $countries["BM"] = "BERMUDA";
            $countries["BT"] = "BHUTAN";
            $countries["BO"] = "BOLIVIEN";
            $countries["BQ"] = "BONAIRESINTEUSTATIUSUNDSABA";
            $countries["BA"] = "BOSNIENUNDHERZEGOWINA";
            $countries["BW"] = "BOTSUANA";
            $countries["BV"] = "BOUVETINSEL";
            $countries["BR"] = "BRASILIEN";
            $countries["VG"] = "BRITISCHEJUNGFERNINSELN";
            $countries["IO"] = "BRITISCHESTERRITORIUMIMINDISCHENOZEAN";
            $countries["BN"] = "BRUNEIDARUSSALAM";
            $countries["BG"] = "BULGARIEN";
            $countries["BF"] = "BURKINAFASO";
            $countries["BI"] = "BURUNDI";
            $countries["CL"] = "CHILE";
            $countries["CN"] = "CHINA";
            $countries["CK"] = "COOKINSELN";
            $countries["CR"] = "COSTARICA";
            $countries["CW"] = "CURACAO";
            $countries["CD"] = "DEMOKRATISCHEREPUBLIKKONGO";
            $countries["KP"] = "DEMOKRATISCHEVOLKSREPUBLIKKOREA";
            $countries["DE"] = "DEUTSCHLAND";
            $countries["DM"] = "DOMINICA";
            $countries["DO"] = "DOMINIKANISCHEREPUBLIK";
            $countries["DJ"] = "DSCHIBUTI";
            $countries["DK"] = "DANEMARK";
            $countries["EC"] = "ECUADOR";
            $countries["SV"] = "ELSALVADOR";
            $countries["CI"] = "ELFENBEINKUSTE";
            $countries["ER"] = "ERITREA";
            $countries["EE"] = "ESTLAND";
            $countries["FK"] = "FALKLANDINSELN";
            $countries["FJ"] = "FIDSCHI";
            $countries["FI"] = "FINNLAND";
            $countries["FR"] = "FRANKREICH";
            $countries["GF"] = "FRANZOSISCHGUAYANA";
            $countries["PF"] = "FRANZOSISCHPOLYNESIEN";
            $countries["TF"] = "FRANZOSISCHESUDUNDANTARKTISGEBIETE";
            $countries["FO"] = "FAROER";
            $countries["GA"] = "GABUN";
            $countries["GM"] = "GAMBIA";
            $countries["GE"] = "GEORGIEN";
            $countries["GH"] = "GHANA";
            $countries["GI"] = "GIBRALTAR";
            $countries["GD"] = "GRENADA";
            $countries["GR"] = "GRIECHENLAND";
            $countries["GB"] = "GROSSBRITANNIEN";
            $countries["GL"] = "GRONLAND";
            $countries["GP"] = "GUADELOUPE";
            $countries["GU"] = "GUAM";
            $countries["GT"] = "GUATEMALA";
            $countries["GG"] = "GUERNSEY";
            $countries["GN"] = "GUINEA";
            $countries["GW"] = "GUINEABISSAU";
            $countries["GY"] = "GUYANA";
            $countries["HT"] = "HAITI";
            $countries["HM"] = "HEARDUNDMCDONALDINSELN";
            $countries["HN"] = "HONDURAS";
            $countries["HK"] = "HONGKONGSARCHINA";
            $countries["IN"] = "INDIEN";
            $countries["ID"] = "INDONESIEN";
            $countries["IM"] = "INSELMAN";
            $countries["IQ"] = "IRAK";
            $countries["IR"] = "IRAN";
            $countries["IE"] = "IRLAND";
            $countries["IS"] = "ISLAND";
            $countries["IL"] = "ISRAEL";
            $countries["IT"] = "ITALIEN";
            $countries["JM"] = "JAMAIKA";
            $countries["JP"] = "JAPAN";
            $countries["YE"] = "JEMEN";
            $countries["JE"] = "JERSEY";
            $countries["JO"] = "JORDANIEN";
            $countries["KY"] = "KAIMANINSELN";
            $countries["KH"] = "KAMBODSCHA";
            $countries["CM"] = "KAMERUN";
            $countries["CA"] = "KANADA";
            $countries["CV"] = "KAPVERDE";
            $countries["KZ"] = "KASACHSTAN";
            $countries["QA"] = "KATAR";
            $countries["KE"] = "KENIA";
            $countries["KG"] = "KIRGISISTAN";
            $countries["KI"] = "KIRIBATI";
            $countries["CC"] = "KOKOSINSELN";
            $countries["CO"] = "KOLUMBIEN";
            $countries["KM"] = "KOMOREN";
            $countries["CG"] = "KONGO";
            $countries["KR"] = "KOREA";
            $countries["HR"] = "KROATIEN";
            $countries["CU"] = "KUBA";
            $countries["KW"] = "KUWAIT";
            $countries["LA"] = "LAOS";
            $countries["LS"] = "LESOTHO";
            $countries["LV"] = "LETTLAND";
            $countries["LB"] = "LIBANON";
            $countries["LR"] = "LIBERIA";
            $countries["LY"] = "LIBYEN";
            $countries["LI"] = "LIECHTENSTEIN";
            $countries["LT"] = "LITAUEN";
            $countries["LU"] = "LUXEMBURG";
            $countries["MO"] = "MACAUSARCHINA";
            $countries["MG"] = "MADAGASKAR";
            $countries["MW"] = "MALAWI";
            $countries["MY"] = "MALAYSIA";
            $countries["MV"] = "MALEDIVEN";
            $countries["ML"] = "MALI";
            $countries["MT"] = "MALTA";
            $countries["MA"] = "MAROKKO";
            $countries["MH"] = "MARSCHALLINSELN";
            $countries["MQ"] = "MARTINIQUE";
            $countries["MR"] = "MAURETANIEN";
            $countries["MU"] = "MAURITIUS";
            $countries["YT"] = "MAYOTTE";
            $countries["MK"] = "MAZEDONIEN";
            $countries["MX"] = "MEXIKO";
            $countries["FM"] = "MIKRONESIEN";
            $countries["MC"] = "MONACO";
            $countries["MN"] = "MONGOLEI";
            $countries["ME"] = "MONTENEGRO";
            $countries["MS"] = "MONTSERRAT";
            $countries["MZ"] = "MOSAMBIK";
            $countries["MM"] = "MYANMAR";
            $countries["NA"] = "NAMIBIA";
            $countries["NR"] = "NAURU";
            $countries["NP"] = "NEPAL";
            $countries["NC"] = "NEUKALEDONIEN";
            $countries["NZ"] = "NEUSEELAND";
            $countries["NI"] = "NICARAGUA";
            $countries["NL"] = "NIEDERLANDE";
            $countries["AN"] = "NIEDERLANDISCHEANTILLEN";
            $countries["NE"] = "NIGER";
            $countries["NG"] = "NIGERIA";
            $countries["NU"] = "NIUE";
            $countries["NF"] = "NORFOLKINSEL";
            $countries["NO"] = "NORWEGEN";
            $countries["MP"] = "NORDLICHEMARIANEN";
            $countries["OM"] = "OMAN";
            $countries["TL"] = "OSTTIMOR";
            $countries["PK"] = "PAKISTAN";
            $countries["PW"] = "PALAU";
            $countries["PS"] = "PALASTINA";
            $countries["PA"] = "PANAMA";
            $countries["PG"] = "PAPUANEUGUINEA";
            $countries["PY"] = "PARAGUAY";
            $countries["PE"] = "PERU";
            $countries["PH"] = "PHILIPPINEN";
            $countries["PN"] = "PITCAIRN";
            $countries["PL"] = "POLEN";
            $countries["PT"] = "PORTUGAL";
            $countries["PR"] = "PUERTORICO";
            $countries["MD"] = "REPUBLIKMOLDAU";
            $countries["RE"] = "REUNION";
            $countries["RW"] = "RUANDA";
            $countries["RO"] = "RUMANIEN";
            $countries["RU"] = "RUSSLAND";
            $countries["BL"] = "SAINTBARTHELEMY";
            $countries["MF"] = "SAINTMARTIN";
            $countries["SB"] = "SALOMONEN";
            $countries["ZM"] = "SAMBIA";
            $countries["WS"] = "SAMOA";
            $countries["SM"] = "SANMARINO";
            $countries["SA"] = "SAUDIARABIEN";
            $countries["SE"] = "SCHWEDEN";
            $countries["CH"] = "SCHWEIZ";
            $countries["SN"] = "SENEGAL";
            $countries["RS"] = "SERBIEN";
            $countries["SC"] = "SEYCHELLEN";
            $countries["SL"] = "SIERRALEONE";
            $countries["ZW"] = "SIMBABWE";
            $countries["SG"] = "SINGAPUR";
            $countries["SX"] = "SINTMAARTEN";
            $countries["SK"] = "SLOWAKEI";
            $countries["SI"] = "SLOWENIEN";
            $countries["SO"] = "SOMALIA";
            $countries["ES"] = "SPANIEN";
            $countries["LK"] = "SRILANKA";
            $countries["SH"] = "STHELENA";
            $countries["KN"] = "STKITTSUNDNEVIS";
            $countries["LC"] = "STLUCIA";
            $countries["PM"] = "STPIERREUNDMIQUELON";
            $countries["VC"] = "STVINZENTUNDDIEGRENADINEN";
            $countries["SD"] = "SUDAN";
            $countries["SR"] = "SURINAME";
            $countries["SJ"] = "SVALBARDUNDJANMAYEN";
            $countries["SZ"] = "SWASILAND";
            $countries["SY"] = "SYRIEN";
            $countries["ST"] = "SAOTOMEUNDPRINCIPE";
            $countries["ZA"] = "SUDAFRIKA";
            $countries["GS"] = "SUDGEORGIENUNDDIESUDLICHENSANDWICHINSELN";
            $countries["SS"] = "SUDSUDAN";
            $countries["TJ"] = "TADSCHIKISTAN";
            $countries["TW"] = "TAIWAN";
            $countries["TZ"] = "TANSANIA";
            $countries["TH"] = "THAILAND";
            $countries["TG"] = "TOGO";
            $countries["TK"] = "TOKELAU";
            $countries["TO"] = "TONGA";
            $countries["TT"] = "TRINIDADUNDTOBAGO";
            $countries["TD"] = "TSCHAD";
            $countries["CZ"] = "TSCHECHISCHEREPUBLIK";
            $countries["TN"] = "TUNESIEN";
            $countries["TM"] = "TURKMENISTAN";
            $countries["TC"] = "TURKSUNDCAICOSINSELN";
            $countries["TV"] = "TUVALU";
            $countries["TR"] = "TURKEI";
            $countries["US"] = "USA";
            $countries["UG"] = "UGANDA";
            $countries["UA"] = "UKRAINE";
            $countries["HU"] = "UNGARN";
            $countries["UY"] = "URUGUAY";
            $countries["UZ"] = "USBEKISTAN";
            $countries["VU"] = "VANUATU";
            $countries["VA"] = "VATIKANSTAAT";
            $countries["VE"] = "VENEZUELA";
            $countries["AE"] = "VEREINIGTEARABISCHEEMIRATE";
            $countries["VN"] = "VIETNAM";
            $countries["WF"] = "WALLISUNDFUTUNA";
            $countries["CX"] = "WEIHNACHTSINSEL";
            $countries["BY"] = "WEISSRUSSLAND";
            $countries["EH"] = "WESTSAHARA";
            $countries["CF"] = "ZENTRALAFRIKANISCHEREPUBLIK";
            $countries["CY"] = "ZYPERN";
            $countries["EG"] = "AGYPTEN";
            $countries["GQ"] = "AQUATORIALGUINEA";
            $countries["ET"] = "ATHIOPIEN";
            $countries["AT"] = "OSTERREICH";
            
            return $countries;
        }

    /**
     * Definieren der RegularExpression für die Passwort Validierung
     * https://trello.com/c/9I58C1Hd/46-imbus-testbench-registrierung
     * Wird außerdem im Javascript von 'Edit Logindata' und 'Register' verwendet
     * PM AS, 13.03.2018
     **/
        function get_password_regex() {
            $regex = '/[!@#$%^°&,`´.:;?~|=§+{}()\[\]\<\>&\/\-\_*]/';

            return $regex;
        }

    /**
     * Passwort Validierung mit vorgegebenen Anforderungen
     * https://trello.com/c/9I58C1Hd/46-imbus-testbench-registrierung
     * PM AS, 13.03.2018
     **/
        function test_password($password) {
            // Testen, ob eine Zahl im Passwort vorhanden ist
                $number = preg_match('@[0-9]@', $password);

            // Testen, ob ein Sonderzeichen im Passwort vorhanden ist
                $regex = get_password_regex();
                $special_char = preg_match($regex, $password);

            // Erfült das Passwort die formalen Kriterien?
                if(strlen($password) < 8 || !$number || !$special_char) {
                    return false;
                }

            return true;
        }   

    /**
     * confirm_password und password werden auf Übereinstimmung geprüft
     * https://trello.com/c/9I58C1Hd/46-imbus-testbench-registrierung
     * PM AS, 13.03.2018
     **/
        function compare_passwords($password, $confirm_password) {
            // Stimmen beide Passwörter überein?
                if ($password!==$confirm_password) {
                    return false;
                }

            return true;
        }    