<?php
/**
 * General class for communicating with MS Navision / Dynamics NACV 365.
 * 
 * @Usage:  include this file (once) and create a new Object with it:
 * @example: Create a new Debitor
  			include "./NAVClass.php";
			$url = "https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/";
			$webServiceName = "Debitor";
			$userName = "b2c";
			$password = "b2c2018!";
			$soapAction = "default";
			$navHelper = new Nav($url, $webServiceName, $userName, $password, $soapAction);
			echo $navHelper->createDebitor(array("No" => "TestPm"));
 * @author: PM LB
 * @link: https://bitbucket.org/pixelmechanics/navision-import-test/src/master/pixelmechanics
 */ 
class Nav {
    
    
    public $error = "";

    /**
     * Konstruktor
     *
     * @param string $url: Page-URL of the Web Service, e.g. "https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/"
     * @param string $webServiceName: Name of the Web Web Service in the NAV Client, e.g. "Debitor" or "Artikel". check https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5d93575abf4dfa04588edc91 (German)
     * @param string $userName: User fpr NAV Login
     * @param string $password: NAV-Password
     * @param string $soapAction: Needs to be set foir curl, the content doesn't seem to matter -> "default"
     */
		function __construct($url, $webServiceName="", $userName="", $password="", $soapAction="default")  {
            include_once(MAGE_ROOT."/pm_helper.php");
			
            soapCURLHelper::$baseurl = $url;
			
			// Name des Lagers, aus dem die Ware verschickt werden soll. ist "_B2C" nach Klärung Rh mit A.Martini
				$this->locationCodeB2C = "_B2C";
			
			if ($webServiceName!=="") {
                soapCURLHelper::setWebservice($webServiceName);
            }
			
			if ($soapAction!=="") {
				soapCURLHelper::$soapAction = $soapAction;
			}
			if ($userName!=="") {
				soapCURLHelper::$userName = $userName;
			}
			
			if ($password!=="") {
				soapCURLHelper::$password = $password;
			}
			
		}
        
            
        
	
	 /**
     * Create a new NAV Debitor / Customer
     * @param array $inputArray Key/Value assoc Array with allowed fields for Debitor/Customer. For available fields check the link
	 * @link https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/Debitor
     * @return string/array 
     */
		public function createDebitor($inputArray) {
            
            /**
             * if the debitor was added before, the debitor-no. is stored in the customer-attribute "erp_id"
             * If that contains a value that is valid, we update the debitor and add the Sales-Order for that debitor
             * @link https://trello.com/c/ytbOL9Aw/291-new-customer-numbers
             */
                // $inputArray["erp_id"] = "BC100331";
                // $inputArray["Name_2"] = "TEST RH";
                if (isset($inputArray["erp_id"]) && !empty(trim($inputArray["erp_id"]))) {
                    
                    $erp_id = $inputArray["erp_id"];
                    unset($inputArray["erp_id"]);
                    try {
                        // preprint($inputArray, "Debitor '$erp_id' existiert bereits und wird geupdatet. ".__FILE__.__LINE__); die();
                        $updateArray=array("Filter" => array("No" => $erp_id), "UpdateFields" => $inputArray);
                        $result = $this->updateRecord($updateArray, "Debitor", "Update");
                        // preprint($this->error, "Error, ".__FILE__.__LINE__); preprint($result, "Update result, ".__FILE__.__LINE__); die();
                        if ($result!==false) {
                            $result["isUpdated"]=true;
                            return $result;
                        }
                    } catch (\Exception $e) {
                        $msg = "There was an error updateing the Debitor: ".$this->error;
                        /*
                        Dieser Code handelt das Problem, wenn ein Debitor nicht aktualisiert werden konnte: https://trello.com/c/eMV0PHQ5/35-debitor-speichern-der-navision-id-beim-magento-kunden-bestellung-kundenkonto-zuweisung-bc-nummer-zu-debitor-und-mage-kunde#comment-5dca8b1be533ae406852f719
                        Ist jetzt aber laut Simone kein Problem, wenn der Debitor dann halt neu angelegt wird.
                        PM RH 12.11.2019
                            if (ENVIRONMENT!="production") {
                                $msg .= "\nNOT creating a new one in DEV/TEST Environment. See ".__FILE__.__LINE__;   
                                throw new Exception($msg);
                                return false;
                            }
                            // Lieber einen neuen Debitor anlegen, als die Daten nicht im Navision zu haben. Siehe https://trello.com/c/eMV0PHQ5/35-debitor-speichern-der-navision-id-beim-magento-kunden-bestellung-kundenkonto-zuweisung-bc-nummer-zu-debitor-und-mage-kunde#comment-5dca8b1be533ae406852f719
                            else {
                                $msg .= "\nCreating a new one. ";
                                // preprint($msg, __FILE__.__LINE__); die();                            
                            }
                        */
                        $msg .= "\nCreating a new one. "; // Siehe https://trello.com/c/eMV0PHQ5/35-debitor-speichern-der-navision-id-beim-magento-kunden-bestellung-kundenkonto-zuweisung-bc-nummer-zu-debitor-und-mage-kunde#comment-5dca91f12e5eda3a06e9789d
                    }
                    
                }
                else {
                    // preprint($inputArray, "Debitor wird NICHT geupdatet, da ERP-ID nicht vorhanden, ".__FILE__.__LINE__); die();
                }
            
                // preprint($inputArray, "Debitor wird mit diesen Feldern neu angelegt, ".__FILE__.__LINE__); die();
            
            
            
			/**
             * First we set some Default-Values. These could also come from Magento-Settings later?
             */
				
				$inputArray["Datev_Account_No"] = "67777"; // @todo: IN WELCHES FELD? Wird hier erstmal nur als Standard fest eingetragen. Siehe https://trello.com/c/T1bCYqVB/31-navision-auftrag-erstellen in diesem Feld: https://trello-attachments.s3.amazonaws.com/5d39aa9c39cbe152bdb91be5/5d8df13dc4c6fd1de033d6de/614fa2cfceb6a5fbf7d7d385b5ad185c/image.png
                
                /**
                 * Das hier ist laut DOCX-Datei "INLAND", von Nicky per Zoom am 28.10.19 nochmal bestätigt.
                 * Nicht "EU", wie zuvor.
                 */
                 $inputArray["Copy_Sell_to_Addr_to_Qte_From"] = "Person"; // "Verkauf an Adr. in Ang. v. kop" soll "Person" sein
                
                
                /**
                  * Update 07.07.2021 LB: Nicht mehr aktuell, siehe https://projects.zoho.com/portal/pixelmechanics2#taskdetail/1781812000000381050/1781812000000400001/1781812000000992003
                  $inputArray["Gen_Bus_Posting_Group"] = "INLAND"; // Wird hier erstmal nur als Standard fest eingetragen.
                  $inputArray["Gen_Bus_Posting_Group"] = "INLAND"; // Wird hier erstmal nur als Standard fest eingetragen.
                  $inputArray["Customer_Posting_Group"] = "INLAND"; // Wird hier erstmal nur als Standard fest eingetragen.
                  */
                
                /**
                 * Feedback von Nicky, siehe https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5db179238a0b7134a92398f6
                 */
                    $inputArray["Location_Code"] = $this->locationCodeB2C; // Name des Lagers, aus dem die Ware verschickt werden soll. ist "_B2C" nach Klärung Rh mit A.Martini
                    $inputArray["Print_RRP"] = "1"; // = UVP drucken: ankreuzen
					
					
				/**
				 * Diese Felder erzeugen beim Export inzwischen einen Fehler beim Anlegen von Debitoren.
				 * Aber nur auf https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/Debitor...				
				 * Deshalb ins Entwicklungs-Navision nicht mehr schicken.
				 * PM RH 17.05.2020: https://trello.com/c/bXjkJ32I/66-2020-02-einl%C3%B6sen-eines-gutscheins-mit-anderem-sachkonto-f%C3%BCr-navision#comment-5eb40f4353c0ad1c2eb8e551
                 * UPDATE 03.06.2020: Diese Probleme hatten mit einem Merge-Konflikt durch Cosmo zu tun: https://trello.com/c/auYekDA8/63-index-out-of-bounds-seit-29052020#comment-5ed76396553fdc8f9fbcb97a
                 *                    Sobald das DEV-Navision aktualisiert wurde, soll das Feld Shortcut_Dimension_3_Code wieder nutzbar sein.
				 **/				
					if (strpos(soapCURLHelper::$baseurl, "entwicklung.schmuckzeiteurope.com") === false ) {
						$inputArray["Shortcut_Dimension_3_Code"] = "BC"; // Eigentlich ist nur das hier das Problem, 17.05. - 03.06.2020
						$inputArray["Customer_Price_Group"] = "B2C"; // Steht so im Screenshot der DOCX-Datei.
						$inputArray["RRP_Customer_Price_Group"] = "UVP-DE"; // = UVP-Debitorenpreisgruppe
					}
					
                
				/**
				 * Remove empty Fields and make sure that no field has more than 50 chars.
                 * hint: We have only 1 dimension here, so, it should fit.
				 **/
					foreach ($inputArray as $key => $value) {
                        $value = substr($value, 0, 50); // Darf nur max 50 Zeichen lang sein.
                        if (trim($value)=="") {
                            unset($inputArray[$key]);
                            continue;
                        }   
						$inputArray[$key] = $value;
					}
                    // preprint($inputArray, __FILE__.__LINE__); die();
				

			/**
             * POST-Body generieren mit XML-ungewandelten Feldern
             * 2 Dimensions, weil wir Produkte als Liste innerhalb der Liste von Feldern haben
             */
                $xmlFields = soapXMLHelper::createSingleDimensionXML($inputArray);
                
                // Post-XML erzeugen und API-Pfad definieren.
                $soapFunction = "Create";
                $webServiceName = "Debitor";
                $postBody = soapXMLHelper::generateXmlBody($xmlFields, $soapFunction, $webServiceName);
                $debitor = soapCURLHelper::doAPICall($postBody, $soapFunction, $webServiceName);
				// preprint(soapCURLHelper::$error, __FILE__.__LINE__);  preprint($debitor, __FILE__.__LINE__); die();
                if ($debitor===false) {
                    $this->error = soapCURLHelper::$error;
                    if (ENVIRONMENT!="production") {
                        
                        $msg = soapCURLHelper::$error;
                        $msg = "$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$msg;
                        $msg .= "\n\nPost-Body: $postBody\n\n";
                        $msg .= "\n\Debitor: $debitor\n\n";
                        $this->error = $msg;
                        return false;
                    }
                    return false;
                }
            
				
            // Nachdem ein Debitor angelegt wurde, muss man ihm zusätzliche Felder mit geben.
				$this->updateDebitorAfterCreate($debitor);
			
            $debitor["isNew"]=true;
			return $debitor;
		}
		
        
		
        /**
         * Nachdem ein Debitor angelegt wurde, muss man ihm zusätzliche Felder mit geben.
         * @param string  $debitorNo Kundennummer nach Anlegen in Nav.
         * @param array $productGroups zB: "ER,KT"
         */
            public function updateDebitorAfterCreate($debitor, $productGroups=array()) {
                
				$debitorNo = $debitor["No"]; // 
				
                /**
                 * @todo 1) Feld "Bill_to_Customer_No" muss nach dem Anlegen befüllt werden mit der Debitor-NO, die zurück geliefert wurde.
                 */
					$updateFields = array(
						"Bill_to_Customer_No" => $debitorNo,
					);
					
				/**
				 * Now update the Debitor
				 */
					$updateArray = array("Filter" => array("No" => $debitorNo), "UpdateFields" => $updateFields);
					$webServiceName = "Debitor";
					$updateResponse = $this->updateRecord($updateArray, $webServiceName);
					// preprint($updateResponse, "Result updateResponse: {$debitorNo}, ".__FILE__.__LINE__); preprint($debitor, "Debitor: , ".__FILE__.__LINE__); die();
					if ($updateResponse===false) {
						if (ENVIRONMENT=="development") {
                            $msg = $this->error;
							$msg = "$webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$msg;
                            throw new Exception($msg);
						}
						return false;
					}
					
				return $debitorNo;
                
        }  
        
        
        
		
    /**
     * Create a new Verkaufsauftrag / Sales Order
     * Liste der Felder, siehe https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/Verkaufsauftrag
     * @param string $debitorID zB "100023"
     * @param array $inputArray 
     * @param array $orderData = Original-Werte aus Magento-Exportorder, um Werte zu lesen, die in inputArray nicht vorhanden sind.
     * @return array
     */
        public function createVerkaufsauftrag($debitorID, $inputArray, $orderData) {

            if (empty(trim($debitorID))) {
                $this->error = "Debitor-ID muss übergeben werden, um Verkaufsauftrag anzulegen.";
                // throw new Exception($msg);
                return false;
            }

            
            
            // transaktions-IDs der Bestellung hinzufügen
                $inputArray = $this->addOrderStateToAuftrag($inputArray, $orderData);

            // Frachtkosten als neue Salesline hinzufügen. Wenn kein Versand möglich, werden alle Saleslines gelöscht.
                $inputArray = $this->addShippingToAuftrag($inputArray, $orderData);

            
            // Rabatte / Coupong-codes als Sachkonten ergänzen
                $inputArray = $this->addDiscountToAuftrag($inputArray, $orderData);

                
            //Remove the VAT_PROD_POSTING_GROUP from the array
                unset($inputArray["VAT_Prod_Posting_Group"]);
                
            /**
             * Remove empty Fields and make sure that no field has more than 50 chars.
             * hint: We have only 1 dimension here, so, it should fit.
             **/
                foreach ($inputArray as $key => $value) {
                    if (is_array($value)) {
                        continue;
                    }
                    
                    $value = substr($value, 0, 50); // Darf nur max 50 Zeichen lang sein.
                    if (trim($value)=="") {
                        unset($inputArray[$key]);
                        continue;
                    }   
                    $inputArray[$key] = $value;
                }
                // preprint($inputArray, __FILE__.__LINE__); die();
                
                
            /**
             * In SalesLines sind die Produkte drin, die angelegt werden sollen.
             * Wenn nichts enthalten ist, wird nur der Verkaufsauftrag angelegt und dessen No zurück geliefert.
             */
                //  preprint($inputArray, "Debugging, ".__FILE__.__LINE__); die();
                $saleslines = false;
                if (isset($inputArray["SalesLines"])) {
                    
                    $xmlFields = soapXMLHelper::createTwoDimensionXML($inputArray); // alle Artikel direkt hinzufügen.
                    
                    /* nachträglich hinzufügen wegen Sachkonten (Fracht / GIFTCARDS)
                     * nicht notwendig, da alle Saleslines bereits beim Anlegen des Verkaufsauftrags hinzugefügt werden können.
                     * Das einzige Problem sind diejenigen vom Typ G_L_ACCOUNT (Sachkonto)
                        $saleslines = $inputArray["SalesLines"];
                        unset($inputArray["SalesLines"]);
                        $xmlFields = soapXMLHelper::createSingleDimensionXML($inputArray);
                     /**/
                } else {
                    $xmlFields = soapXMLHelper::createSingleDimensionXML($inputArray);
                }
                
                
                

            // Post-XML erzeugen und API-Pfad definieren.
                $soapFunction = "Create";
                $webServiceName = "Verkaufsauftrag";
                $postBody = soapXMLHelper::generateXmlBody($xmlFields, $soapFunction, $webServiceName);
                $auftrag = soapCURLHelper::doAPICall($postBody, $soapFunction, $webServiceName);
                // preprint($this->error, "Error?, ".__FILE__.__LINE__); preprint($auftrag, "Result Auftrag: {$debitorID}, ".__FILE__.__LINE__); die();
                if ($auftrag===false) {
                    $msg = soapCURLHelper::$error;
                    if (ENVIRONMENT=="development") {
                        $msg = "$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$msg;
                        $msg .= "\nData:\n".print_r($inputArray, 1);
                    }
                    $this->error = $msg;
                    // throw new Exception($msg);
                    return false;
                }
                

            /**
             * Alle Artikel mit 1 Salesline-Udpate pushen, wenn der Auftrag angelegt werden konnte.
             * @todo: hier die GIFTCARDS und VERSANDKOSTEN als Sachkonto einem bestehenden Auftrag hinzufügen.
             */
                if ($saleslines!==false) {
                    try {
                        $this->addSaleslinesToVerkaufsautrag($auftrag, $saleslines);
                    } catch (\Exception $e) {
                        preprint ($e->getMessage(), __FILE__.__LINE__); die();
                    }
                }


            // do not return the passed $auftrag from this function-call, instead this updated "Auftrag" from navision that contains the KEY and No.
                return $auftrag;

        } //createVerkaufsauftrag()
        

        /**
         * Create a new createCreditMemo 
         * Trello, https://trello.com/c/5dHZOWuA/208-schnitt-stellen-erweiterung-retouren-info-auch-in-altem-trello-ticket
         * @param string $debitorID 
         * @param array $inputArray 
         * @return array
         */
        public function createCreditMemos ($debitorID, $inputArray) { 

            foreach ($inputArray as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                
                $value = substr($value, 0, 50); // Darf nur max 50 Zeichen lang sein.
                if (trim($value)=="") {
                    unset($inputArray[$key]);
                    continue;
                }   
                $inputArray[$key] = $value;
            }

        
            
             /**
             * create XML Field for Creditmemo 
             * created by Ha 
             * trello related to this card https://trello.com/c/5dHZOWuA/208-schnitt-stellen-erweiterung-retouren-info-auch-in-altem-trello-ticket
             */
            $xmlField = "<Sell_to_Customer_No>".$inputArray["Sell_to_Customer_No"]."</Sell_to_Customer_No>
            <Posting_Description>Retoure B2C</Posting_Description>
            <Credit_Memo_Type>Credit_Memo</Credit_Memo_Type>
            <!-- Optional -->
            <SalesLines>";
                foreach($inputArray["SalesLines"] as $key => $value){
                    $xmlField .= "<Sales_Cr_Memo_Line>";
                    if($value["Type"] =="Item" ){
                        $xmlField .="<VAT_Prod_Posting_Group>".$value["VAT_Prod_Posting_Group"]."</VAT_Prod_Posting_Group>";
                    }
                    
                    $xmlField .="<No>".$value["No"]."</No>
                    <Type>".$value["Type"]."</Type>
                    <Quantity>".$value["Quantity"]."</Quantity> 
                    <Unit_Price>".$value["Unit_Price"]."</Unit_Price>
                    </Sales_Cr_Memo_Line>";
                     
                }
            $xmlField .= "</SalesLines>";
            $soapFunction = "Create";
            $webServiceName = "Verkaufsgutschrift";
            $envelopeUrl ="http://schemas.xmlsoap.org/soap/envelope/";
            $xmlns = "urn:microsoft-dynamics-schemas/page/verkaufsgutschrift";
            
            $postBody = soapXMLHelper::generateXmlBody( $xmlField , $soapFunction, $webServiceName);
            $auftrag = soapCURLHelper::doAPICall($postBody , $soapFunction, $webServiceName);
            // preprint($this->error, "Error?, ".__FILE__.__LINE__); preprint($auftrag, "Result Auftrag: {$debitorID}, ".__FILE__.__LINE__); die();
            if ($auftrag===false) {
                $msg = soapCURLHelper::$error;
                if (ENVIRONMENT=="development") {
                    $msg = "$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$msg;
                    $msg .= "\nData:\n".print_r($inputArray, 1);
                }
                $this->error = $msg;
                // throw new Exception($msg);
                return false;
            }
        }
    
	/**
     * Create a new Verkaufsauftrag / Sales Order
     * Liste der Felder, siehe https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/Verkaufsauftrag
     * @param string $debitorID zB "100023"
     * @param array $inputArray 
     * @param array $orderData = Original-Werte aus Magento-Exportorder, um Werte zu lesen, die in inputArray nicht vorhanden sind.
     * @return array
     */
    
    public function createCreditMemo($debitorID, $inputArray) {

        if (empty(trim($debitorID))) {
            $this->error = "Debitor-ID muss übergeben werden, um Verkaufsauftrag anzulegen.";
            // throw new Exception($msg);
            return false;
        }

        
        
        // transaktions-IDs der Bestellung hinzufügen
          //  $inputArray = $this->addOrderStateToAuftrag($inputArray, $orderData);

        // Frachtkosten als neue Salesline hinzufügen. Wenn kein Versand möglich, werden alle Saleslines gelöscht.
           // $inputArray = $this->addShippingToAuftrag($inputArray, $orderData);

        
        // Rabatte / Coupong-codes als Sachkonten ergänzen
           // $inputArray = $this->addDiscountToAuftrag($inputArray, $orderData);

            
        //Remove the VAT_PROD_POSTING_GROUP from the array
            //unset($inputArray["VAT_Prod_Posting_Group"]);
            
        /**
         * Remove empty Fields and make sure that no field has more than 50 chars.
         * hint: We have only 1 dimension here, so, it should fit.
         **/
            foreach ($inputArray as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                
                $value = substr($value, 0, 50); // Darf nur max 50 Zeichen lang sein.
                if (trim($value)=="") {
                    unset($inputArray[$key]);
                    continue;
                }   
                $inputArray[$key] = $value;
            }
            // preprint($inputArray, __FILE__.__LINE__); die();
            
            
        /**
         * In SalesLines sind die Produkte drin, die angelegt werden sollen.
         * Wenn nichts enthalten ist, wird nur der Verkaufsauftrag angelegt und dessen No zurück geliefert.
         */
            //  preprint($inputArray, "Debugging, ".__FILE__.__LINE__); die();
            $saleslines = false;
            if (isset($inputArray["SalesLines"])) {
                
                $xmlFields = soapXMLHelper::createTwoDimensionXML($inputArray); // alle Artikel direkt hinzufügen.
                
                /* nachträglich hinzufügen wegen Sachkonten (Fracht / GIFTCARDS)
                 * nicht notwendig, da alle Saleslines bereits beim Anlegen des Verkaufsauftrags hinzugefügt werden können.
                 * Das einzige Problem sind diejenigen vom Typ G_L_ACCOUNT (Sachkonto)
                    $saleslines = $inputArray["SalesLines"];
                    unset($inputArray["SalesLines"]);
                    $xmlFields = soapXMLHelper::createSingleDimensionXML($inputArray);
                 /**/
            } else {
                $xmlFields = soapXMLHelper::createSingleDimensionXML($inputArray);
            }
            
           
            

        // Post-XML erzeugen und API-Pfad definieren.
            $soapFunction = "Create";
            $webServiceName = "Verkaufsgutschrift";
            $postBody = soapXMLHelper::generateXmlBody($xmlFields, $soapFunction, $webServiceName);
            $auftrag = soapCURLHelper::doAPICall($postBody, $soapFunction, $webServiceName);
            // preprint($this->error, "Error?, ".__FILE__.__LINE__); preprint($auftrag, "Result Auftrag: {$debitorID}, ".__FILE__.__LINE__); die();
            if ($auftrag===false) {
                $msg = soapCURLHelper::$error;
                if (ENVIRONMENT=="development") {
                    $msg = "$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$msg;
                    $msg .= "\nData:\n".print_r($inputArray, 1);
                }
                $this->error = $msg;
                // throw new Exception($msg);
                return false;
            }
            
           
        /**
         * Alle Artikel mit 1 Salesline-Udpate pushen, wenn der Auftrag angelegt werden konnte.
         * @todo: hier die GIFTCARDS und VERSANDKOSTEN als Sachkonto einem bestehenden Auftrag hinzufügen.
         */

         /** 
            if ($saleslines!==false) {
                try {
                    $this->addSaleslinesToVerkaufsautrag($auftrag, $saleslines);
                } catch (\Exception $e) {
                    preprint ($e->getMessage(), __FILE__.__LINE__); die();
                }
            }
        */

        // do not return the passed $auftrag from this function-call, instead this updated "Auftrag" from navision that contains the KEY and No.
            return $auftrag;

    } //createVerkaufsauftrag()
    

    
    /**
	 * updates some Values for an existing Sales Order ($auftrag["Key"] or ["No"] must be given)
     * 
     * @param array $auftrag result from Navision. At least it must contain $auftrag["No"]
     * @param array $updateFields
     * @return boolean
     */
		public function updateVerkaufsauftrag($auftrag, $updateFields) {
			
            $auftragNo = $auftrag["No"];
			// preprint($updateFields, "updateVerkaufsauftrag($auftragNo), ".__FILE__.__LINE__); die();

            $webServiceName = "Verkaufsauftrag";
            $updateArray = array("Filter" => array("No" => $auftragNo), "UpdateFields" => $updateFields);
            $updateResponse = $this->updateRecord($updateArray, $webServiceName);
            // preprint($updateResponse, "Result updateResponse: {$orderNumber}, ".__FILE__.__LINE__); die();
            if ($updateResponse===false) {
                $msg = $this->error;
                if (ENVIRONMENT=="development") {
                    $msg = "$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$msg;
                    // throw new Exception($msg);
                }
                return $msg;
            }
				
			return $updateResponse;
		}
	
    
    /**
	 * Add the sales lines for an existing Verkaufsauftrag
     * 
     * @param type $auftrag
     * @param type $saleslines
     * @return boolean
     * 
     * @todo prüfen ob das hier wegen GIFTCARDS und VERSANDKOSTEN / FRACH mit einem Sachkonto funktioniert.
     */
		public function addSaleslinesToVerkaufsautrag($auftrag, $saleslines) {
			// preprint($saleslines, "Füge Saleslines dem Auftrag hinzu: addSaleslinesToVerkaufsautrag(), ".__FILE__.__LINE__); preprint($auftrag, "Auftrag, ".__FILE__.__LINE__); die();
            if (!is_array($saleslines)) {
                $msg = "Saleslines können nicht hinzugefügt werden, da es kein Array ist.";
                if (ENVIRONMENT!="production") {
                    $msg .= "\nSiehe ".__FILE__.__LINE__;
                    $msg .= "\n\$saleslines = ".print_r($saleslines, 1);
                }
                $this->error = $msg;
                return false;
            }
            
            
            if (!is_array($auftrag) || !isset($auftrag["No"]) || !isset($auftrag["Key"])) {
                $msg = "Übergebener Auftrag-Paramater ist ungültig (kein Array, No oder KEY fehlt).";
                if (ENVIRONMENT!="production") {
                    $msg .= "\nSiehe ".__FILE__.__LINE__;
                    $msg .= "\n\$auftrag = ".print_r($auftrag, 1);
                }
                $this->error = $msg;
                return false;
            }
            
            /**
             * Convert the Saleslines, add the Order-No and make it XML
             */
                $orderNumber = $auftrag["No"];
                $auftragKEY = $auftrag["Key"];
                foreach ($saleslines as $i => $line) {
                    $line["Document_No"] = $orderNumber;
                    // evtl das hier auch manuell setzen? $line["Line_no"] = 10000+x;
                    $saleslines[$i] = array("Sales_Order_Line" => $line); //
                }
                // $updateArray = array("Filter" => array("Key" => $auftragKEY), "SalesLines" => $saleslines);
                $updateArray = array("Key" => $auftragKEY, "SalesLines" => $saleslines);
            
			/**
			 * We need to set the field "External_Document_No" to ["Webshop Bestellung" + DebitorID]
			 * Siehe https://trello-attachments.s3.amazonaws.com/5d39aa9c39cbe152bdb91be5/5db6ae2f84d3bb03dd581e9b/61dd6e15c755dbba7cfb2d166ff6ab25/image.png
			 * We can only do that by updating the Sales order we just created because we need the Order Number
			 */
                $webServiceName = "Verkaufsauftrag";
                $soapFunction = "Update";
                
            /**
             * Use soapCURLHelper::doAPICall() to communicate witht the API.
             */
                $xmlFields= soapXMLHelper::convertArrayToXML($updateArray, 0);
                $postBody = soapXMLHelper::generateXmlBody($xmlFields, $soapFunction, $webServiceName);
// @todo: zum testen was das problem ist, kann man hier diese zeile einmal fest verwenden:
    /** /
    $postBody = "
        <Envelope xmlns='http://schemas.xmlsoap.org/soap/envelope/'>
        <Body>
            <Update xmlns='urn:microsoft-dynamics-schemas/page/verkaufsauftrag'>
                <Verkaufsauftrag>
                    <Key>$auftragKEY</Key>
                    <!-- Optional -->
                    <SalesLines>

                        <Sales_Order_Line>
                            <No>8408</No>
                            <Type>G_L_ACCOUNT</Type>
                            <Description>Standard - Versand an Ihre Lieferadresse</Description>
                            <Quantity>1</Quantity>
                            <Unit_Price>14.96</Unit_Price>
                            <!-- <Document_No>AT144036</Document_No> -->

                        </Sales_Order_Line>
                    </SalesLines>
                </Verkaufsauftrag>
            </Update>
        </Body>
    </Envelope>
        ";
     /**/
                $return = soapCURLHelper::doAPICall($postBody, $soapFunction, $webServiceName);
                if ($return===false) {
                    $msg = soapCURLHelper::$error;
                    $this->error = $msg;
                    if (ENVIRONMENT=="development") {
                        preprint(htmlspecialchars($msg), "ERROR, ".__FILE__.__LINE__); 
                        preprint(htmlspecialchars($postBody), "Post Body, ".__FILE__.__LINE__); preprint($updateArray, "update-Array, ".__FILE__.__LINE__); // preprint($auftrag, "navision Auftrag result, should contain 'Key', ".__FILE__.__LINE__);
                        die();
                        // $msg = "$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$msg;
                    }
                    throw new Exception($msg);
                    return false;
                }


                return $return;
                
                
                
                
                /*
				$updateResponse = $this->updateRecord($updateArray, $webServiceName);
				preprint($updateResponse, "Result updateResponse: {$orderNumber}, ".__FILE__.__LINE__); die();
				if ($updateResponse===false) {
					$msg = $this->error;
					if (ENVIRONMENT=="development") {
						// $msg = "$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$msg;
                        // throw new Exception($msg);
					}
					return false;
				}
				
                return $updateResponse;
                /**/
		}
    
		

	/**
	 * "FRACHT B2C" aus dem Debitor dem Auftrag hinzufügen, wenn Shipping-Kosten anfallen.
	 * evtl nur als neue Salesline?
	 * @link https://trello.com/c/iOUIKksw/41-verkaufsauftrag-versandkosten-fracht-shipping-tracking-navision-teil-2
	 **/
		public function addShippingToAuftrag($auftrag, $orderData) {
		
			//Get the VAT amount for shipping
                $VatProdPostingGroup = $auftrag["VAT_Prod_Posting_Group"];
			
			// $orderData["Shipping Price"] ist zB "4.96".
				// preprint($orderData, __LINE__);
				$hasShipping = isset($orderData["Shipping Price"]) && !empty($orderData["Shipping Price"]);
				$hasShippingAdress = isset($orderData["Shipping Address"]) && !empty(trim($orderData["Shipping Address"]));
                
            // die Bezeichnung des Versands. zB "Standard - Versand an Ihre Lieferadresse"
				$shippingMethod = "Standardversand";
                if (isset($orderData["Shipping Method"])) {
                    $shippingMethod = trim($orderData["Shipping Method"]);
                }
                    
                
            // Versnadkosten: 5,90 normal und 16€ Express
                $shippingCost = 0;
                if (isset($orderData["Shipping Price"])) {
                    $shippingCost = $orderData["Shipping Price"]; //Price ohne Steuer
                }
                
			/*/ Adresse abfragen
                if (!$hasShippingAdress) {
                    if (ENVIRONMENT=="development") {
                        $this->error = "Order has no Shipping-Adress, perhaps only digital-good should be sold.";
                    }
                    return $auftrag;
                }
            // kein Problem, ist halt kostenlos.
                if (!$hasShipping) {
                    $shippingCost = $orderData["Shipping Price"]; //Price ohne Steuer
                    // $this->error = "Order has no Shipping-Costs";
                    return $auftrag;
                }
                /**/
                
			
			/**
             * Fracht manuell als Produkt hinzufügen, statt über einen Button in NAV
             * 
             */
            
                // 1: als Sachkonto: momentan nicht möglich.
                    // preprint("Versandkosten können momentan nicht exportiert werden", __FILE__.__LINE__); return $auftrag;
                    $frachtSaleline = array(
                        "No" => "8408",
                        "Type" => "G_L_Account", //PM LB muss hier so geschrieben werden
                        "Description" => "Versandkostenpauschale",
                        "Description_2" => substr($shippingMethod, 0, 50), // macht sonst Probleme bei "Ihr Vorteil: Bis 12:00 Uhr am gleichen Tag bestellt, am nächsten Tag geliefert. - DHL Express"
                        // "Location_Code" => $this->locationCodeB2C, // Bei engelsrufer.de immer "_B2C"
                        "Quantity" => "1",
                        // "OptionString" => "36;qgAAAAJ7/0YAUgBBAEMASABUACAAQgAyAEM=9;2305537360;",
                        // "Qty_to_Ship" => "1",
                        // "Qty_to_Invoice" => "1",
                        "Unit_Price" => $shippingCost,
                        "VAT_Prod_Posting_Group" => $VatProdPostingGroup,
                        // "Line_Amount" => "4.96",
                        // "VAT_Prod_Posting_Group" => "VAT19",
                        // "ShortcutDimCode_x005B_3_x005D_" => "BC",
                    );
                    
                /*/ 2. als Artikel mit der No "FRACHTB2C"
                    $frachtSaleline = array(
                        "No" => "FRACHTB2C",
                        "Type" => "Item", // Name
                        "Quantity" => "1", // ordered qty
                        "Unit_Price" => $shippingCost,
                        "Description" => $shippingMethod
                    );
                    /**/
					
					
				/**
				 * From 01.07.2020 to 01.01.2021 because of VAT-Change in Germany/EU, we have to set this to another number temporarily.
				 * PM RH 03.07.2020
				 **/				
					
					$now = date("Ymd");
					if ($now<20210101) {
						// Funktioniert auf DEV-Navision evtl nicht, weil die 8400 dort nicht angelegt wurde.
						$now = date("Ymd");
						$frachtSaleline["No"] = "8400";
					}
					
					
                    
                    
			$auftrag["SalesLines"][] = $frachtSaleline;
			// $auftrag["ship_to_code"]= "FRACHT B2C";
			
			// preprint($frachtSaleline, "now: $now, ".__FILE__.__LINE__, true); preprint($auftrag, __FILE__.__LINE__, true); preprint($orderData, __FILE__.__LINE__, true); die();
            
			return $auftrag;
			
		}
        
        
        
        
		

	/**
	 * Rabatte sollen als Sachkonto-Eintrag hinzugefügt werden.
	 * @link https://trello.com/c/iOUIKksw/41-verkaufsauftrag-versandkosten-fracht-shipping-tracking-navision-teil-2
	 **/
		public function addDiscountToAuftrag($auftrag, $orderData) {
		
			// preprint($orderData, __FILE__.__LINE__); die();
			return $auftrag; 
            
			/*
			// $orderData["Shipping Price"] ist zB "4.96".
				// preprint($orderData, __LINE__);
				$hasShipping = isset($orderData["Shipping Price"]) && !empty($orderData["Shipping Price"]);
				$hasShippingAdress = isset($orderData["Shipping Address"]) && !empty(trim($orderData["Shipping Address"]));
                
            // die Bezeichnung des Versands. zB "Standard - Versand an Ihre Lieferadresse"
				$shippingMethod = "Standardversand";
                if (isset($orderData["Shipping Method"])) {
                    $shippingMethod = trim($orderData["Shipping Method"]);
                }
                    
                
            // Versnadkosten: 5,90 normal und 16€ Express
                $shippingCost = 0;
                if (isset($orderData["Shipping Price"])) {
                    $shippingCost = $orderData["Shipping Price"]; //Price ohne Steuer
                }
                
			// Adresse abfragen
                if (!$hasShippingAdress) {
                    if (ENVIRONMENT=="development") {
                        $this->error = "Order has no Shipping-Adress, perhaps only digital-good should be sold.";
                        $this->error .= "ACHTUNG: Versandkosten sollten eigentlich als Sachkonto angelegt werden, geht aber aktuell nur als Artikel.";
                    }
                    return $auftrag;
                }
			
            
            // kein Problem, ist halt kostenlos.
                if (!$hasShipping) {
                    $shippingCost = $orderData["Shipping Price"]; //Price ohne Steuer
                    // $this->error = "Order has no Shipping-Costs";
                    return $auftrag;
                }
			*/
			
			// preprint($auftrag, __FILE__.__LINE__); preprint($orderData, __FILE__.__LINE__); die();
            
			return $auftrag;
			
		}
        
        
        
        

    /**
     * Feld "Vorauszahlung Buchungsbeschreibung"
     * Bisher Payment TXID. Aber ab 15.11.2019 packen wir hier den Order-Status rein.
     * @link https://trello.com/c/P0ohPHrv/41-order-export-nur-wenn-payment-durch-ist-und-order-status-auf-processing-gesetzt-wird#comment-5dce96ffb89afb3d0538efd8
     */
		public function addOrderStateToAuftrag($auftrag, $orderData) {
            
                $content = $orderData["Order Status"]; // zB "pending", "processing". Siehe 
                $auftrag["Prepmt_Posting_Description"] = substr($content, 0, 50); // max Länge des Feldes
            
                return $auftrag;
                
        }
                
                
                
        

	/**
     * Adds the information from Paypal and Stripe Creditcard to the order for navision.
     * @todo: Perhaps this needs to update an existing Sales-Order, because the TX-ID might be received later then the order-export runs?
     * @todo: Move Stripe and Paypal Check to the Export-MODEL, so this function is independend of Magento.
	 * @link https://trello.com/c/8wR8h9RA/42-verkaufsauftrag-payment-id-navision-teil-2#action-5dc161fd9a58753d57f268dc
	 **/
		public function addPaymentToAuftrag($auftrag, $orderData) {
                // @todo: Zu klären, wo die TransaktionsID etzt hinkommt. Debitor?  https://trello.com/c/LTm6Hpgl/32-payment-id-transaktions-id#comment-5dcd09dbef4a172471acc236
                return $auftrag;
            
            /**
             * Feld "Vorauszahlung Buchungsbeschreibung"
             * Bisher Payment TXID. Aber ab 15.11.2019 packen wir hier den Order-Status rein.
             * @link https://trello.com/c/P0ohPHrv/41-order-export-nur-wenn-payment-durch-ist-und-order-status-auf-processing-gesetzt-wird#comment-5dce96ffb89afb3d0538efd8
             */
                $content = "TX: ".$orderData["Payment Transaction-ID"]; // Zahlungstranskationsnr
                $content .= " (".$orderData["Payment_data"]["method"].", ".$orderData["State"].")"; // zB "stripecreditcards, pending"
                // Feld "teposten im Debitor" verwenden
                // $auftrag["Prepmt_Posting_Description"] = substr($content, 0, 50); // max Länge des Feldes
                
                
                
            
            /**
             * Da alle Felder in dem tab e-commerce readonly sind, kann die API diese leider nicht befüllen.
             * Evtl später mal, aber für jetzt muss es ausreichen die TX-ID im Feld oben zu listen.
             * @link https://trello.com/c/LTm6Hpgl/32-payment-id-transaktions-id#action-5dc183963cea7b88e3927e02
             */

                // tab eCommerce in NAV
                    $auftrag["Webshop_Payment_Transaction_Id"] = $orderData["Payment Transaction-ID"]; // Zahlungstranskationsnr
                    $auftrag["Webshop_Payment_ID"] = $orderData["txData"]["payment_id"]; // Online Zahlungs-ID, zB "9179"
                    $auftrag["Your_Reference"] = "TX-Code: ".$orderData["txData"]["txn_type"]; // "Ihre Referenz", zB "authorization"
                    $auftrag["Webshop_Order_No"] = $orderData["Order Increment Id"]; // order-Nr, zB "000000313"

                /**
                 * Stripe Credit-Card
                 */
                    if ($orderData["Payment_data"]["method"]=="stripecreditcards") {

                        // Tab fakturierung in NAV
                        // Erwartet einen vorgegebeen Wert: $auftrag["Credit_Card_No"] = $orderData["Payment Code"]; // Vorauszahlung-Buchungsbeschreibung. Hier zB: "stripepayment" 


                        // tab eCommerce in NAV
                        $auftrag["Webshop_Payment_ID"] = $orderData["txData"]["payment_id"]; // zB "9179"

                        /**
                        * @todo: How to get the last 4 CC-Digits? Example "2955" from http://engelsrufer.rh/pmadmin/sales/order/view/order_id/9179/
                        * @link https://stackoverflow.com/questions/30447026/getting-last4-digits-of-card-using-customer-object-stripe-api-with-php?rq=1
                        */
                        // $auftrag["GetCreditcardNumber"] = $orderData["Payment_data"]["cc_last_4"]; // zB "2225"

                    }



                /**
                 * Paypal
                 */
                    if ($orderData["Payment_data"]["method"]=="paypal") {

                        // Tab fakturierung in NAV
                        $auftrag["Credit_Card_No"] = $orderData["Payment_data"]["additional_information"]["method_title"]; // zB "Kreditkarte (Visa, Mastercard, American Express)"
                        $auftrag["GetCreditcardNumber"] = $orderData["Payment_data"]["cc_last_4"]; // zB "2225"

                        // tab eCommerce in NAV
                        $auftrag["Webshop_Payment_ID"] = $orderData["txData"]["payment_id"]; // zB "9179"
                    }


                /**
                 * Transaction State. If complete, c
                 */
                    switch ($orderData["txData"]["txn_type"]) {

                        case "": $auftrag["Credit_Card_No"] = $orderData["Payment_data"]["additional_information"]["method_title"]; // zB "Kreditkarte (Visa, Mastercard, American Express)"
                                break;

                    }
            
            
			
            // preprint($orderData, "Payment_data, ".__FILE__.__LINE__);  preprint($auftrag, __FILE__.__LINE__); die();
			return $auftrag;
			
		}
	
	
	
    /**
     * Get the quantity of every Item stored in NAV 
     *
     * @param string $locationFilter => Name of the Inventory-Location. zB "B2C" oder "ZENTRAL", siehe auch https://trello.com/c/ds9h7Vo1/34-za-api-anbindung-navision-navision-entwicklung-teil-2a-import-produkt-qty#comment-5dc32101cc144e141861a11c
     * @param string $setSize => We don't want too many items returned at once
     * @return array -> Array of all products (see below for structure) 
     * Array (
     *  [0] => Array 
     *      (
     *          [No] => {SKU},
     *          [Qty] => {Quantity}
     *          ....
     *      )
     * )
     * 
     * Manuell im Browser mit Addon "Wizdler": https://b2c.engelsrufer.de:9047/Webshop_Sync/WS/Martini%20Europe%20GmbH/Page/Artikel
     * Eingabe: <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
                <Body>
                    <ReadMultiple xmlns="urn:microsoft-dynamics-schemas/page/artikel">
                        <filter>
                            <Field>Location_Filter</Field>
                            <Criteria>_B2C</Criteria>
                        </filter>
                        <filter>
                            <Field>Item_Category_Code</Field>
                            <Criteria>KT</Criteria>
                        </filter>
                        <filter>
                            <Field>Item_Category_Code</Field>
                            <Criteria>ER</Criteria>
                        </filter>
                        <setSize>10</setSize>
                    </ReadMultiple>
                </Body>
            </Envelope>
     */
    public function getAllProductQuantities($filterArray=array(), $perPage = "40", $bookmarkKey="") {

        $webservice = "Artikel";


        // Mindestanforderungen an den Filter.
        if (!is_array($filterArray) || empty($filterArray)) {
            $filterArray = array("Location_Filter" => "_B2C");
        }


        /**
         * We only need these fields to be returned
         * @see https://b2c.engelsrufer.de:9047/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/Artikel where you can view the fields in the node:  <xsd:simpleType name="Artikel_Fields">
         */
            $returnFields = array(
                "Key" => "Key", //We need the Key for Pagination / Bookmark
                "No" => "sku",
                "Inventory" => "qty",
                "Blocked_for_Purchase" => "Blocked_for_Purchase", // PM RH 17.02.20, siehe https://trello.com/c/zqdB5UY5/64-ausl%C3%A4ufer-produkte-disablen-wenn-nie-mehr-verf%C3%BCgbar#action-5e4ab8235b7b8e679ae97d38
                "Description" => "Description", // PM RH 17.02.20, siehe https://trello.com/c/zqdB5UY5/64-ausl%C3%A4ufer-produkte-disablen-wenn-nie-mehr-verf%C3%BCgbar#action-5e4ab8235b7b8e679ae97d38
            );



        $i = 0;
        $itemsReturned = true;
        $resultArray = array(); // Wird von dieser funktion returned und beinhaltet alle Results von allen Seiten
        $bookmarkKey = ""; //For the first call, $bookmarkKey is empty to return the first entry. It is used for paginated loading from navision

        while ($itemsReturned) {

            /*/ FOR DEVELOPMENT ONLY, to speed up loading * /
            if ($i>1)  {
                preprint("Breche WHILE ab nach $i Durchläufen", __FILE__.__LINE__);
                break;
            }
            /**/

            $start = microtime(true);
            print "Page #{$i} loading ($perPage per Page)";

            // @todo: lieber auslagern?
                $readMultipleResponse = $this->getArticles($filterArray, $perPage, $bookmarkKey, $returnFields);
                if($readMultipleResponse === false || count($readMultipleResponse) == 0) {
                    $itemsReturned = false;
                    continue;
                }


            foreach ($readMultipleResponse as $row) {
                // preprint($row, __FILE__.__LINE__); die();
                $x = array();
                foreach ($returnFields as $responseKey => $returnKey) {
                    $x[$returnKey] = $row[$responseKey];
                }
                $resultArray[] = $x;
            }

            // Set the Bookmark Key as the last returned Item, so the next loop loads the next "page"
            $bookmarkKey = end($readMultipleResponse)["Key"];
            unset($readMultipleResponse);


            $end = microtime(true);
            $duration = $end - $start;
            print "\rPage #{$i} took $duration seconds.\n";
            $i++;


        } // while

        // preprint($resultArray, __FILE__.__LINE__);
        return $resultArray;
    }


    /**
     * Load Articles by Page / Bookmark-Key
     * Used by getAllProductQuantities()
     * @param $filterArray
     * @param string $perPage
     * @param string $bookmarkKey
     * @param array $returnFields
     */
        public function getArticles($filterArray, $perPage = "40", $bookmarkKey="",$returnFields=array()) {

            $webservice = "Artikel";

            // Read the items from the page
                $readMultipleResponse = $this->readMultiple($filterArray, $perPage, $bookmarkKey, $returnFields, $webservice);
                if (!empty($this->error)) {
                    preprint($this->error, "Errors");
                    $this->errors = "";
                }

            // Nothing was returned.
                if($readMultipleResponse === false || count($readMultipleResponse) == 0) {
                    // preprint($readMultipleResponse, __FILE__.__LINE__);
                    return array();
                }

                return $readMultipleResponse;

        }







    /********************** internal methods **************************/
    
        
        

    
    
    /**
     * Create a filter for calling "ReadMultiple" SOAP function
     * @todo Lorenz: Bitte hier Link ergänzen, wo man nachlesen kann, wie man die Filter-Kriterien ergänzen oder verknüpfen kann (AND, OR ? )
     * 
     * @param array $filterArray: single dimension associative array of filter criteria
     *  Array(
     *      [Field] => [Criteria] 
     * ) 
     * @param string $bookmarkKey Pagination, number of the last record on the previous page
     * @param string $setSize Number of returned records
     * @return string
     */
        private function createReadMultipleFilter ($filterArray, $bookmarkKey="", $setSize) {

            //Leerer String für XML
            $filterFields = "";

            //Alle Filterkriterien in XML umformen
            foreach ($filterArray as $field => $criteria) {
                // zB: "Item_Category_Code" => array("ER", "KT","GOLD");
                    if (is_array($criteria)) {
                        foreach ($criteria as $subcriteria) {
                            $filterFields .= "<filter>\r\n <Field>".$field."</Field>\r\n <Criteria>".$subcriteria."</Criteria>\r\n</filter>\r\n";
                        }
                    }
                // Einfache Zeile hinzufügen
                    else {
                        $filterFields .= "<filter>\r\n <Field>".$field."</Field>\r\n <Criteria>".$criteria."</Criteria>\r\n</filter>\r\n";
                    }
            }

            //Bookmark Key und setSize anhängen
            if (!empty($bookmarkKey)) {
                $filterFields .= "<bookmarkKey>".$bookmarkKey."</bookmarkKey>\r\n";
            }
            if (!empty($setSize)) {
                $filterFields .= "<setSize>".$setSize."</setSize>\r\n";
            }

            return $filterFields;

        }
    
    
    /**
     * Get the NAV Key for a product SKU from Magento
     * @param string $itemNo SKU aus Magento, zB "ERO-LFL-01"
     * @param string $webServiceName: Name of the Web Web Service in the NAV Client, e.g. "Debitor" or "Artikel". check https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5d93575abf4dfa04588edc91 (German)
     * @return String, zB "48;JQAAAACLAQAAAAJ7/0EAVAAxADQAMwA3ADgAMgAAAACHECc=9;2681658340;"
     */
    private function getKey($recordNo, $webServiceName) {

        //Wir filtern nur nach der (eindeutigen) Artikelnummer/-bezeichnung
            $filterArray = array("No" => $recordNo);
            $bookmarkKey = ""; //Pagination, hier egal
            $setSize = 2; //2 Einträge, um große Übertragungsmengen zu vermeiden


        //SOAP-Funktion, die aufgerufen werden soll
            $soapFunction = "ReadMultiple";

        //XML für den SOAP-Methodenaufruf im POST-Body generieren
            $xmlFields = $this->createReadMultipleFilter($filterArray, $bookmarkKey, $setSize);

        //Für den POST-Body können wir hier nicht die Funktion generateXmlBody() nutzen, da der Body bei ReadMultiple anders aufgebaut ist
            $postBody = soapXMLHelper::generateXmlBody($xmlFields, $soapFunction, $webServiceName);

        
        $result = soapCURLHelper::doAPICall($postBody, $soapFunction, $webServiceName);
        if (!$result) {
            $this->error = soapCURLHelper::$error;
            return false;
        }
        return $result["Key"];
        
    } // getKey()


    /**
     * Helper function for checking if an array has string Keys
     *
     * @param array $array
     * @return boolean
     */
    private function hasStringKeys(array $array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Read one or more records in NAV and return the results as an array
     *
     * @param string $readMultipleFilter: XML for the SOAP Body 
     * @param array $returnFields: Associative array of the fields that should be returned and their respective output names
       * Example:
       * Array(
         *  [Name of field in SOAP response] => [Name of field in output array]
         *  ["Inventory"] => ["Qty"]
       * ) 
     * @param string $webServiceName: Name of the Web Web Service in the NAV Client, e.g. "Debitor" or "Artikel". check https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5d93575abf4dfa04588edc91 (German)
     * @return array
     */
    private function readMultiple($filterArray, $setSize, $bookmarkKey="", $returnFields, $webServiceName) {    

        //We are using the bulit-in ReadMultiple function provided by the NAV Web Service
        $soapFunction = "ReadMultiple";

        //create the XML for the filter
        $readMultipleFilter = $this->createReadMultipleFilter($filterArray, $bookmarkKey, $setSize);

        /**
         *  Populate the POST-Body
         * We do this manually since the ReadMultiple Request XML is different from the other XML body structures
         */

            //Für xmlns-Feld muss Web Service Name klein geschrieben sein
            // Muss hier übergeben werden, da zwichendurch andere Calls laufen, die mit $this->webServiceNamge sonst nicht funktionieren würden
            $xmlnsField = strtolower($webServiceName);

            $this->postBody = "<Envelope xmlns=\"http://schemas.xmlsoap.org/soap/envelope/\"> <Body>\n        <".
            $soapFunction." xmlns=\"urn:microsoft-dynamics-schemas/page/".
            $xmlnsField."\">\n".
            $readMultipleFilter."\n       </".
            $soapFunction.">\n    </Body>\n</Envelope>";

        // execute curl
        $response = soapCURLHelper::doAPICall($this->postBody, $soapFunction, $webServiceName);
        // preprint($response, __FILE__.__LINE__);
        if ($response===false) {
            $this->error = soapCURLHelper::$error;
            if (ENVIRONMENT=="development") {
                
                $msg = soapCURLHelper::$error;
                $msg = "$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$msg;
                $msg .= "\n\nPost-Body: $this->postBody\n\n";
                $msg .= "\n\response: $response\n\n";
                // throw new Exception($msg);
                preprint($msg, __FILE__.__LINE__);
                
            }
            return false;
        }
        
        // ist der Fall bei genau 1 Result.
            if (isset($response["Key"])){
                $response = array($response);
            }

        /**
         * We need to check wether
             * No item has been returned
             * One item has been returned
             * More than one items have been returned
             * 
         * For checking wether one or more entries have been returned, we can check if the returned array of entries has Integer Keys
             * If it has, there were more than one returned records
             * If not, only one record was returned
         */
                
            //each returned Item
            if (!is_array($response) || empty($response)) { // auf letzter Seite kommt nie etwas zurück. Ist ok so, wir returnen nur FALSE
                // preprint($response, "Not countable!! ".__FILE__.__LINE__);
                return false;
            }
            return $response;



            $returnArray = array();
            foreach ($response as $i => $returnFields) {

                //New array, only the fields we want
                if (is_array($returnFields)) {
                    foreach ($returnFields as $responseKey => $returnKey) {

                        $returnArray[$i][$returnKey] = $response[$i][$responseKey];

                    }
                }
            }

        return $returnArray;

    } //readMultiple()


    /**
	 - Wenn der Kunde Fracht zahlen soll (Versandkosten bei Einkaufswert < 75€), dann muss der Code ergänzt werden.
     * Set the Field "Standard Customer Sales Code" when creating a new Customer 
     * The Page "StandardCustomerSalesCodes" is a seperate published Web Service
     * Publishing Web Services: https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5d93575abf4dfa04588edc91 (German)
     * 
     * @link https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5db179238a0b7134a92398f6
     * @param string $customerNo New Customer Number (Field "No")
     * @param string $code Code to be set, standard is "Fracht B2C"
     * @param string $fieldname "Customer_No" for debitoren, "" for Verkaufsautrag
     * @return array
     */
		public function addStdCustSalesCode($debitorORAuftragNo, $code, $fieldname="Customer_No") {

			if (empty($debitorORAuftragNo)) {
				$msg = "Debitor- oder Auftragsnummer darf nicht leer sein!";
				if (ENVIRONMENT!=="production") {
					$msg .= "\nDEV-Hinweis: navision-helper::addStdCustSalesCode($debitorORAuftragNo, $code, $fieldname)";
				}
				$this->error = $msg;
				// throw new Exception($msg);
				return false;
			}
		

			//Values for creating Sales Code
				$inputArray = array($fieldname => $debitorORAuftragNo,
									"Code" => $code);

			// Use soapCURLHelper::doAPICall() instead. See createDebitor
				$xmlFields = soapXMLHelper::createSingleDimensionXML($inputArray);
				// preprint(htmlspecialchars($xmlFields), "XMLFields");
				
                // Post-XML erzeugen und API-Pfad definieren.
				$soapFunction = "Create";
				$webServiceName = "StandardCustomerSalesCodes";
                $postBody = soapXMLHelper::generateXmlBody($xmlFields, $soapFunction, $webServiceName);
                $response = soapCURLHelper::doAPICall($postBody, $soapFunction, $webServiceName);
				// preprint(htmlspecialchars($postBody), "Post Body, ".__FILE__.__LINE__); preprint(soapCURLHelper::$error, __FILE__.__LINE__);  preprint($response, __FILE__.__LINE__); die();
                if ($response===false) {
                    $this->error = soapCURLHelper::$error;
                    if (ENVIRONMENT=="development") {
                        /*
                        $msg = soapCURLHelper::$error;
                        $msg = "$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$msg;
                        $msg .= "\n\nPost-Body: $postBody\n\n";
                        $msg .= "\n\response: $response\n\n";
                        throw new Exception($msg);
                        */
                    }
                    return false;
                }
			
			
			// Antwort als Array zurückliefern
				$return = soapXMLHelper::responseToArray($response);
				// preprint($return, "Result of addStdCustSalesCode($debitorORAuftragNo, $code, ), $fieldname".__FILE__.__LINE__); die();
				return $return;
				
				
		} //addStdCustSalesCode()

		
		
    /**
     * Set the Field "Standard Customer Sales Code" when creating a new Customer
     * The Page "StandardCustomerSalesCodes" is a seperate published Web Service, Objekt-ID = 50002, Servicename = "CustomerItemCategories"
     * Publishing Web Services: https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5d93575abf4dfa04588edc91 (German)
     * 
     * @link https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5db179238a0b7134a92398f6
     * @param string $customerNo New Customer Number (Field "No")
     * @param string $code Code to be set, standard is "Fracht B2C"
     * @return array
     */
		public function addCustomerItemCategories($customerNo, $Item_Category_Codes) {
            
            // Convert STRING Input to an Array
                if (!is_array($Item_Category_Codes) && !empty($Item_Category_Codes)) {
                    $Item_Category_Codes = explode(",", $Item_Category_Codes); // ergibt: array("ER","ERWATCH","GOLDHE","GOLDER","HE","KT");
                }
            
            // Test if the submitted data is ok
                if (!is_array($Item_Category_Codes) || empty($Item_Category_Codes)) {
                    $this->error = "Die Debitor-Artikelgruppencodes waren nicht korrekt. Bitte kommagetrennte Werte verwenden, ZB ER,HE,KT";
                    return false;
                }
                
			// We have to ADD  the first set and then UPDATE the rest
                $i=0;
                $inputArray = array("CustomerItemCategoriesList" => array());
                foreach ($Item_Category_Codes as $code) {
                    // Leere Werte sind nicht erlaubt.
                        if (trim($code)=="") {
                            continue;
                        }

                    // Values for creating Sales Code. @link: https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/CustomerItemCategories
                    $inputArray["CustomerItemCategoriesList"][] = array("Customer_No" => $customerNo,
                                          "Item_Category_Code" => $code);
                }
                // preprint($inputArray, __FILE__.__LINE__);
			
				
			// Post-XML erzeugen und API-Pfad definieren.
				$xmlFields = soapXMLHelper::createTwoDimensionXML($inputArray, "CustomerItemCategories");
                $soapFunction = "CreateMultiple";
				$webServiceName = "CustomerItemCategories";
                $postBody = soapXMLHelper::generateXmlBody($xmlFields, $soapFunction, $webServiceName, false);
                $response = soapCURLHelper::doAPICall($postBody, $soapFunction, $webServiceName);
				// preprint(htmlspecialchars($xmlFields), "XMLFields");
				// preprint(htmlspecialchars($postBody), "Post Body, ".__FILE__.__LINE__); preprint(soapCURLHelper::$error, __FILE__.__LINE__);  preprint($response, __FILE__.__LINE__); die();
                if ($response===false) {
                    $msg = soapCURLHelper::$error;
                    if (ENVIRONMENT=="development") {                        
                        $msg .= "\n$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n";
                        // $msg .= "\n\nPost-Body: $postBody\n\n";
                        // $msg .= "\n\response: $response\n\n";
                        // throw new Exception($msg);                        
                    }
                    $this->error = $msg;
                    return false;
                }
				
			
			
			$return = soapXMLHelper::responseToArray($response);
			// preprint($response, __FILE__.__LINE__);  preprint($return, __FILE__.__LINE__); die();
            
			return $return;
		} //addCustomerItemCategories()


    /**
     * Update a record in NAV
     *
     * @param array $updateArray -> Array 
     * -> Array with: 
     *    * "No" field of the record that will be updated 
     *    * Fields to update and their new Values
     * 
     * Example:
     * Array
     * (
     *       (
     *           [Filter] => Array
     *               (
     *                   ["No"] => *Value*
     *               )
     * 
     *           [UpdateFields] => Array
     *               (
     *                   [*Field*] => *Value*
     *                   [*Field*] => *Value*
     *                   ...
     *               )
     *       )
     * )
     * @param string $webServiceName Name of the Web Web Service in the NAV Client, e.g. "Verkaufsauftrag", "Debitor" or "Artikel". check https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5d93575abf4dfa04588edc91 (German)
     * @return array
     */
        private function updateRecord($updateArray, $webServiceName, $soapFunction = "Update") {

            /**
             * Get the Key (=ID in Navision) for the update entry.
             * If the key is already in $updateArray, we don't need to search for it.
             */
                $filter = $updateArray["Filter"];
                if (isset($filter["Key"])) {
                    $keyString = $filter["Key"];
                }
                elseif (!isset($filter["Key"]) && isset($filter["No"])) {
                    $keyString = $this->getKey($filter["No"], $webServiceName);
                    if ( ($keyString == false) || strlen($keyString) < 1) {
                        $msg = "No matching entries found for Filter values.";
                        if (ENVIRONMENT=="development") {
                            $msg .= "\n\n<pre>".print_r($filter, 1)."</pre>";
                        }
                        throw new Exception($msg);
                        return false;
                    }
                } else {
                    $this->error = "UpdateRecord kann nur ausgeführt werden, wenn filter[key] oder filter[no] gesetzt ist.";
                    return false;
                }

            //get the fields that have to be updated and add the string to them.
                $updateFields = $updateArray["UpdateFields"];
                $updateFields += array("Key" => $keyString);

            /**
             * Use soapCURLHelper::doAPICall() to communicate witht the API.
             */
                $xmlFields = soapXMLHelper::createSingleDimensionXML($updateFields);
                $postBody = soapXMLHelper::generateXmlBody($xmlFields, $soapFunction, $webServiceName);
                $return = soapCURLHelper::doAPICall($postBody, $soapFunction, $webServiceName);
                // preprint(htmlspecialchars($postBody), __FILE__.__LINE__); preprint(htmlspecialchars($return), __FILE__.__LINE__); die();
                if ($return===false) {
                    // preprint($return, __FILE__.__LINE__); die();
                    $msg = soapCURLHelper::$error;
                    if (ENVIRONMENT=="development") {
                        $msg .= "$soapFunction $webServiceName fehlgeschlafen über API, ungültiges Result in ".__FILE__.__LINE__.":\n".$return;
                    }
                    $this->error = $msg;
                    throw new Exception($msg);
                    return false;
                }


                return $return;
        }

} // class NAV




/**
 * used by the NAV-Class to connect by cURL
 */
    class soapCURLHelper {
        

        /**
         * Default config-Settings.
         * @link https://www.lucidchart.com/documents/edit/c599d88a-a9ea-4988-af25-99f4598fedc1/0_0?beaconFlowId=D6ADB1A5F56BEA10
         **/
            public static $soapAction = "default"; //Needs to be set, the content doesn't seem to matter
            public static $userName = "b2c";
            public static $password = "b2c2018!";
            public static $baseurl = "https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/"; // LIVE = entwicklung.schmuckzeiteurope.com, DEV/TEST = b2c.engelsrufer.de:9047 
            public static $webServiceName  = "";
            public static $fullUrl = "https://entwicklung.schmuckzeiteurope.com/Webshop_Sync/WS/Schmuckzeit%20Europe%20GmbH/Page/"; // LIVE = entwicklung.schmuckzeiteurope.com, DEV/TEST = b2c.engelsrufer.de:9047 

            public static $curlOptions = array();
            public static $curlError = "";
            public static $error = "";
            
            

        /**
         * Set the webServiceName and the full URL for curl
         * We are setting these seperately since we need a different webServiceName in some functions (like "getKey")
         * @param string $webServiceName Name of the Web Web Service in the NAV Client, e.g. "Debitor" or "Artikel". check https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5d93575abf4dfa04588edc91 (German)
         */
            public static function setWebservice($webServiceName) {
                self::$webServiceName = $webServiceName;
                self::$fullUrl = self::$baseurl.$webServiceName;
            }
            
            

        /**
         * Ruft die API per Curl auf und handelt Fehler
         * @param array $postBody
         * @return array | false
         * @throws Exception
         */
            public static function doAPICall($postBody, $soapFunction, $webServiceName) {

                soapCURLHelper::setWebservice($webServiceName);

                //Curl initialisieren
                    $curl = curl_init();

                //Curl Einstellungen 
                    $curlOptions = soapCURLHelper::setCurlOptions(self::$fullUrl, $postBody);
                    curl_setopt_array($curl, $curlOptions);

                /**
                 * Curl ausführen
                 */
                    $response = curl_exec($curl);
                    self::$curlError = curl_error($curl);
                    curl_close($curl);

                // Antwort in Array umwandeln um die XML-Daten verwenden zu können
                    $return = soapXMLHelper::responseToArray($response);
                  // echo "<pre>";
                // print_r($return);die;
                if (self::$curlError || strpos($response, "faultcode")!==false) {
                    self::$error = "Es gab einen Fehler beim Ausführen von ".self::$fullUrl;
                    self::$error .= "\n".self::$curlError;
                    if(is_array($return)) {
                        self::$error .= "\nfaultstring = ".$return["sFault"]["faultstring"];
                    }
                    /*
                    elseif(ENVIRONMENT=="development") {
                        preprint(self::$error, "ErrorMsg, ".__FILE__.__LINE__);
                        preprint($response, "Response, ".__FILE__.__LINE__);
                        preprint($return, "Return (Response2Array), ");
                        preprint($postBody, "PostBody $soapFunction/$webServiceName");
                        preprint(self::$curlOptions, "this->curlOptions, ".__FILE__.__LINE__); 
                    }
                    */
                    return false;
                }
                
           

                /**
                 * @todo: Funktioniert das auch mit [$soapFunction."_Result"][$webServiceName) ? 
                 * für ReadMultiple_Result liefert Nav scheinbar sowas zurück: $return['ReadMultiple_Result']['ReadMultiple_Result'][$webServiceName];
                 */
                    if (!isset($return[$soapFunction."_Result"]) || !isset($return[$soapFunction."_Result"])) {
                        preprint($response, "Response, ".__FILE__.__LINE__);
                        preprint($return, "Return (=response als Array) ".__FILE__.__LINE__);
                        throw new Exception("Result could not be parsed from Nav-Response");
                        return false;
                    }

                // für Nicht-ReadMultiple..    
                    if (isset($return[$soapFunction."_Result"][$webServiceName])) {
                        return $return[$soapFunction."_Result"][$webServiceName];
                    }

                // für ReadMultiple..
                    if (isset($return[$soapFunction."_Result"][$soapFunction."_Result"][$webServiceName])) {
                        return $return[$soapFunction."_Result"][$soapFunction."_Result"][$webServiceName];
                    }

            }

        
        /**
         * Options for curl
         *
         * @param string $fullUrl: URL of the Web Service, check check https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5d93575abf4dfa04588edc91 (German)
         * @param string $postFields: XML string for calling the SOAP Method
         * @return array
         */
            public static function setCurlOptions($fullUrl, $postFields) {

                self::$curlOptions = array(
                    CURLOPT_URL => $fullUrl,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_HTTPAUTH => CURLAUTH_NTLM,
                    CURLOPT_USERPWD => self::$userName.":".self::$password,
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 180,
                    CURLOPT_CONNECTTIMEOUT => 320,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $postFields,
                    CURLOPT_HTTPHEADER => array(
                        "Accept: */*",
                        "Accept-Encoding: gzip, deflate",
                        "Cache-Control: no-cache",
                        "Connection: keep-alive",
                        "Content-Type: application/xml",
                        "SoapAction: ".self::$soapAction,
                        "cache-control: no-cache"
                    ),
                );

                return self::$curlOptions;
            }

        
    } // soapCURLHelper
            

/**
 * used by the NAV-Class to convert XML-Data
 */
    class soapXMLHelper {
            

        /**
         * Generate the Body for the curl POST-Body
         *
         * @param string $bodyXml Call for the SOAP-function in XML, generated by createSingleDimensionXml() or createTwoDimensionXml() (WSDL)
         * @param string $webServiceName: Name of the Web Web Service in the NAV Client, e.g. "Debitor" or "Artikel". check https://trello.com/c/eSxKD9AX/22-za-api-anbindung-navision-navision-entwicklung-teil-2b-export-order#comment-5d93575abf4dfa04588edc91 (German)
         * @return string 
         */
            public static function generateXmlBody($xmlFields, $soapFunction, $webServiceName, $addWebserviceToXML=true) {

                //Für xmlns-Feld muss Web Service Name klein geschrieben sein
                // Muss hier übergeben werden, da zwichendurch andere Calls laufen, die mit $this->webServiceName sonst nicht funktionieren würden
                $xmlnsField = strtolower($webServiceName);

                if ($addWebserviceToXML===true) {
    $xmlFields = "
                <{$webServiceName}>
                    {$xmlFields}
                </{$webServiceName}>
    ";
                }

                //POST-Body zusammenfügen
    $body = "<Envelope xmlns=\"http://schemas.xmlsoap.org/soap/envelope/\">
        <Body>
            <{$soapFunction} xmlns=\"urn:microsoft-dynamics-schemas/page/{$xmlnsField}\">
                {$xmlFields}
            </{$soapFunction}>
        </Body>
    </Envelope>";

                return $body;
            }


        /**
         * Convert XML to Array
         * Adapted fromm this https://www.php.net/manual/de/book.simplexml.php
         * and this (SOAP) https://stackoverflow.com/questions/21777075/how-to-convert-soap-response-to-php-array
         *
         * @param string $response SOAP response in XML
         * @return array
         */
            public static function responseToArray($response) {
                if (!is_string($response)) {
                    return $response;
                }
                if (trim($response)=="") {
                    return false;
                }

                try {
                    //Soap response XML für SimpleXMLElement umformen
                    $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response); 

                    //XML Element erstellen
                    $xml = new SimpleXMLElement($response);

                    //Nur Body ist interessant
                    if (!is_object($xml)) {
                        $this->err .= "XML-Konvertierung fehlgeschlagen! ".__FILE_.__LINE__;
                        // preprint($xml, "xml ".__FILE__.__LINE__); die();
                        return false;
                    } else {
                        if (isset($xml->xpath('//SoapBody')[0])) {
                            $body = $xml->xpath('//SoapBody')[0];
                        }
                        elseif (isset($xml->xpath('//sBody')[0])) {
                            $body = $xml->xpath('//sBody')[0];
                        }
                        elseif (isset($xml->xpath('//Body')[0])) {
                            $body = $xml->xpath('//Body')[0];
                        }
                        else {
                            preprint($xml, "no BODY Element in XML found. ".__FILE__.__LINE__); die();                    
                        }
                    }

                    //In Array umformen
                    $array = json_decode(json_encode((array)$body), TRUE);
                } catch (Exception $e) {
                    preprint($e->getMessage(), __FILE__.__LINE__);  preprint($response, __FILE__.__LINE__); die();
                    return $response;
                }

                return $array;
            }


        /**
         * Create XML for "single-dimensional" entries in NAV that don't have nested XML for Item Lists etc.

         * Example: "Debitor" or "Artikel"
         *
         * @param array $inputArray non-nested Key-Value associative array
         * @return string XML für SOAP method call
         */
            public static function createSingleDimensionXML($inputArray) {

                //Leerer String für XML
                $createFields = "";

                /**
                 * Array der zu erstellenden Felder in String für den POST-Body schreiben
                 */
                    // $artikelField befüllen
                    foreach ($inputArray as $key => $value) {
                        $value = trim($value);
                        $createFields .= "\t<".$key."><![CDATA[".$value."]]></".$key.">\n";
                    }

                return $createFields;
            }


        /**
         * Create XML for "two-dimensional" entries in NAV that have nested XML for Item Lists etc.																	   
         * Example: "Verkaufsauftrag" where we need to add Saleslines: 													   
         * @param array $inputArray Nested Array with DebitorNo and the Articles he wants. 
         * @param string $secondDimName zb "Sales_Order_Line" for articles for a Verkaufsauftrag
         * @return string XML für den Methodenaufruf mit SOAP
         **/
            public static function createTwoDimensionXML ($inputArray, $secondDimName="Sales_Order_Line") {
                //Leerer String für XML
                $createFields = "";

                // First dimension
                foreach ($inputArray as $key1 => $value1) {

                    if(gettype($value1) != "array") {
                        $value1 = trim($value1);
                        $createFields .= "\t<".$key1."><![CDATA[".$value1."]]></".$key1.">\n";
                    }

                    if(is_array($value1)) {
                        $createFields .= "\t<".$key1.">\n";

                        // Second dimension: Add products 
                        for ($i=0; $i < count($value1); $i++){

                            $createFields .= "\n<{$secondDimName}>";
                            foreach ($value1[$i] as $key2 => $value2) { // key can be "No", "Type", "Unit Price", "Quantity" and so on
                                $value2 = trim($value2);
                                $createFields .= "\t\n<{$key2}><![CDATA[".$value2."]]></{$key2}>\n";
                            }

                            $createFields .= "\n</{$secondDimName}>";
                        }
                        $createFields .= "\t</".$key1.">\n";
                    }
                }


                return $createFields;
            }


        /**
         * Create XML for "multi-dimensional" arrays         
         * 
         * @param assoc-array $array zB array("Filter" => array("Key" => "123465"), "Bil_to" => "Someone"))
         * @param int $tabDepth can be used to indent the XML-Nodes with a \t string/character for better reading.
         * @return string XML für den Methodenaufruf mit SOAP
         */
            public static function convertArrayToXML($array, $tabDepth=0) {
                //Leerer String für XML
                $xmlString = "";
                $tabIndent = str_repeat("\t", $tabDepth); // example: 1 => "\t", 2 => "\t\t", ...
                // First dimension
                foreach ($array as $key => $value) {
                    
                    // preprint($value, "$key -> ".__FILE__.__LINE__);
                    $xmlString .= $tabIndent."<{$key}>"; // tabDepth='$tabDepth'>";
                    
                        /**
                         * Value is a scalar, so it can be used as "value" here.
                         */
                            if(gettype($value) != "array") {
                                $value = trim($value);
                                $xmlString .= "{$value}";
                            }

                        /**
                         * value is an array, so we have to create sub-elements in XML
                         */
                            if(is_array($value)) {
                                $xmlString .= "\n";
                                    // example: array(0 => array("nodename" => array(values)), 1 => array("nodename" => array(othervalues)) // Sales_Order_Line
                                        if(is_numeric_array($value)) {
                                            foreach ($value as $j => $value2) {
                                                $xmlString .= $tabIndent.self::convertArrayToXML($value2, $tabDepth+1);
                                                continue;
                                            }
                                        }                                    
                                    // example: array(key => value, key2 => value2, ...). "SalesLines" => array
                                        else {                                    
                                            $xmlString .= $tabIndent.self::convertArrayToXML($value, $tabDepth+1);
                                        }

                                $xmlString .= "\n".$tabIndent;
                            }
                    
                    $xmlString .= "</{$key}>\n\n";
                    
                } // foreach 


                return $xmlString;
            }


            



    } // class soapXMLHelper
