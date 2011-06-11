<?php

require_once(PACKAGE_DIRECTORY."ImportExport/Importer.php");

class poMMoImporter extends Importer{
    
    function poMMoImporter(){
		$this->Importer();
		$this->default_encoding = 'macintosh';
		$this->default_delimiter = 'comma';
		
		$Bootstrap = Bootstrap::getBootstrap();
		$Package = $Bootstrap->usePackage('poMMo');
        
        $this->setContainer('poMMoContactContainer');
        $this->setUniqueKey(array('poMMoContactID','poMMoContactEmail','poMMoContactFirst Name','poMMoContactLast Name'));
        $this->setDisplayKey(array('poMMoContactEmail','poMMoContactFirst Name','poMMoContactLast Name'));
        $this->setSQLKey('poMMoContactID');
		$this->parameterPrefix = 'poMMoContact';

		$Bootstrap->primeAdminPage(); // I hate that I have to do this.
        $this->viewImportedRecordURL = $Bootstrap->makeAdminURL($Package,'entry')."&sid=";        
        $this->editImportedRecordURL = $Bootstrap->makeAdminURL($Package,'entry')."&sid=";        

		$ExtraParameters = array('Email','ID','Date');
		foreach ($this->getContainer()->colname as $parm => $pretty_parm){
			$ExtraParameters[] = $pretty_parm;
		}
		
        $this->ignoreParameter(array());
        $this->addExtraParameter($ExtraParameters);
    }
    
    function massageData(& $Object){
		foreach ($this->getContainer()->colname as $parm => $pretty_parm){
			$_pretty_parm = $pretty_parm;
			if ($pretty_parm == 'email') $_pretty_parm = 'Email';
			if ($pretty_parm == 'date') $_pretty_parm = 'Date';
			if ($pretty_parm == 'id') $_pretty_parm = 'ID';
			if (array_key_exists($_pretty_parm,$Object->params) and $Object->getParameter($this->parameterPrefix.$_pretty_parm) == ""){
				$Object->setParameter($this->parameterPrefix.$_pretty_parm,$Object->getParameter($_pretty_parm));
			}
		}
		if ($Object->getParameter('poMMoContactEmail') == ''){
			if (defined('bm_BlankEmail')){
				$Object->setParameter('poMMoContactEmail',bm_BlankEmail);
			}
			else{
				return PEAR::raiseError('No email address found');
			}
		}
		
		// Set the Country/Province properly
		switch(strtoupper($Object->getParameter('poMMoContactCountry'))){
			case 'CA': case 'CAN': 
				$Object->setParameter('poMMoContactCountry','Canada');
				switch (strtoupper($Object->getParameter('poMMoContactProvince/State'))){
					case 'BC': $Object->setParameter('poMMoContactProvince/State','British Columbia'); break;
					case 'AB': $Object->setParameter('poMMoContactProvince/State','Alberta'); break;
					case 'SK': $Object->setParameter('poMMoContactProvince/State','Saskatchewan'); break;
					case 'MB': $Object->setParameter('poMMoContactProvince/State','Manitoba'); break;
					case 'ON': $Object->setParameter('poMMoContactProvince/State','Ontario'); break;
					case 'PQ': $Object->setParameter('poMMoContactProvince/State','Quebec'); break;
					case 'QC': $Object->setParameter('poMMoContactProvince/State','Quebec'); break;
					case 'NB': $Object->setParameter('poMMoContactProvince/State','New Brunswick'); break;
					case 'NS': $Object->setParameter('poMMoContactProvince/State','Nova Scotia'); break;
					case 'PEI': $Object->setParameter('poMMoContactProvince/State','Prince Edward Island'); break;
					case 'PE': $Object->setParameter('poMMoContactProvince/State','Prince Edward Island'); break;
					case 'NF': $Object->setParameter('poMMoContactProvince/State','Newfoundland and Labrador'); break;
					case 'NFLD': $Object->setParameter('poMMoContactProvince/State','Newfoundland and Labrador'); break;
					case 'NT': $Object->setParameter('poMMoContactProvince/State','Northwest Territories'); break;
					case 'NWT': $Object->setParameter('poMMoContactProvince/State','Northwest Territories'); break;
					case 'YK': $Object->setParameter('poMMoContactProvince/State','Yukon'); break;
					case 'NU': $Object->setParameter('poMMoContactProvince/State','Nunavut'); break;
				}
				break;
			case 'US': case 'USA': $Object->setParameter('poMMoContactCountry','United States');
				switch (strtoupper($Object->getParameter('poMMoContactProvince/State'))){
					case 'AL': $Object->setParameter('poMMoContactProvince/State','Alabama'); break;
					case 'AK': $Object->setParameter('poMMoContactProvince/State','Alaska'); break;
					case 'AZ': $Object->setParameter('poMMoContactProvince/State','Arizona'); break;
					case 'AR': $Object->setParameter('poMMoContactProvince/State','Arkansas'); break;
					case 'CA': $Object->setParameter('poMMoContactProvince/State','California'); break;
					case 'CO': $Object->setParameter('poMMoContactProvince/State','Colorado'); break;
					case 'CT': $Object->setParameter('poMMoContactProvince/State','Connecticut'); break;
					case 'DE': $Object->setParameter('poMMoContactProvince/State','Delaware'); break;
					case 'DC': $Object->setParameter('poMMoContactProvince/State','Dist. of Columbia'); break;
					case 'FL': $Object->setParameter('poMMoContactProvince/State','Florida'); break;
					case 'GA': $Object->setParameter('poMMoContactProvince/State','Georgia'); break;
					case 'HI': $Object->setParameter('poMMoContactProvince/State','Hawaii'); break;
					case 'ID': $Object->setParameter('poMMoContactProvince/State','Idaho'); break;
					case 'IL': $Object->setParameter('poMMoContactProvince/State','Illinois'); break;
					case 'IN': $Object->setParameter('poMMoContactProvince/State','Indiana'); break;
					case 'IA': $Object->setParameter('poMMoContactProvince/State','Iowa'); break;
					case 'KS': $Object->setParameter('poMMoContactProvince/State','Kansas'); break;
					case 'KY': $Object->setParameter('poMMoContactProvince/State','Kentucky'); break;
					case 'LA': $Object->setParameter('poMMoContactProvince/State','Louisiana'); break;
					case 'ME': $Object->setParameter('poMMoContactProvince/State','Maine'); break;
					case 'MD': $Object->setParameter('poMMoContactProvince/State','Maryland'); break;
					case 'MA': $Object->setParameter('poMMoContactProvince/State','Massachusetts'); break;
					case 'MI': $Object->setParameter('poMMoContactProvince/State','Michigan'); break;
					case 'MN': $Object->setParameter('poMMoContactProvince/State','Minnesota'); break;
					case 'MS': $Object->setParameter('poMMoContactProvince/State','Mississippi'); break;
					case 'MO': $Object->setParameter('poMMoContactProvince/State','Missouri'); break;
					case 'MT': $Object->setParameter('poMMoContactProvince/State','Montana'); break;
					case 'NE': $Object->setParameter('poMMoContactProvince/State','Nebraska'); break;
					case 'NV': $Object->setParameter('poMMoContactProvince/State','Nevada'); break;
					case 'NH': $Object->setParameter('poMMoContactProvince/State','New Hampshire'); break;
					case 'NJ': $Object->setParameter('poMMoContactProvince/State','New Jersey'); break;
					case 'NM': $Object->setParameter('poMMoContactProvince/State','New Mexico'); break;
					case 'NY': $Object->setParameter('poMMoContactProvince/State','New York'); break;
					case 'NC': $Object->setParameter('poMMoContactProvince/State','North Carolina'); break;
					case 'ND': $Object->setParameter('poMMoContactProvince/State','North Dakota'); break;
					case 'OH': $Object->setParameter('poMMoContactProvince/State','Ohio'); break;
					case 'OK': $Object->setParameter('poMMoContactProvince/State','Oklahoma'); break;
					case 'OR': $Object->setParameter('poMMoContactProvince/State','Oregon'); break;
					case 'PA': $Object->setParameter('poMMoContactProvince/State','Pennsylvania'); break;
					case 'RI': $Object->setParameter('poMMoContactProvince/State','Rhode Island'); break;
					case 'SC': $Object->setParameter('poMMoContactProvince/State','South Carolina'); break;
					case 'SD': $Object->setParameter('poMMoContactProvince/State','South Dakota'); break;
					case 'TN': $Object->setParameter('poMMoContactProvince/State','Tennessee'); break;
					case 'TX': $Object->setParameter('poMMoContactProvince/State','Texas'); break;
					case 'UT': $Object->setParameter('poMMoContactProvince/State','Utah'); break;
					case 'VT': $Object->setParameter('poMMoContactProvince/State','Vermont'); break;
					case 'VA': $Object->setParameter('poMMoContactProvince/State','Virginia'); break;
					case 'WA': $Object->setParameter('poMMoContactProvince/State','Washington'); break;
					case 'WV': $Object->setParameter('poMMoContactProvince/State','West Virginia'); break;
					case 'WI': $Object->setParameter('poMMoContactProvince/State','Wisconsin'); break;
					case 'WY': $Object->setParameter('poMMoContactProvince/State','Wyoming'); break;
				}
				break;
			case 'AF': case 'AFG': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Afghanistan'); break;
			case 'AX': case 'ALA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Aland Islands'); break;
			case 'AL': case 'ALB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Albania'); break;
			case 'DZ': case 'DZA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Algeria'); break;
			case 'AS': case 'ASM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','American Samoa'); break;
			case 'AD': case 'AND': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Andorra'); break;
			case 'AO': case 'AGO': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Angola'); break;
			case 'AI': case 'AIA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Anguilla'); break;
			case 'AQ': case 'ATA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Antarctica'); break;
			case 'AG': case 'ATG': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Antigua and Barbuda'); break;
			case 'AR': case 'ARG': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Argentina'); break;
			case 'AM': case 'ARM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Armenia'); break;
			case 'AW': case 'ABW': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Aruba'); break;
			case 'AU': case 'AUS': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Australia'); break;
			case 'AT': case 'AUT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Austria'); break;
			case 'AZ': case 'AZE': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Azerbaijan'); break;
			case 'BS': case 'BHS': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Bahamas'); break;
			case 'BH': case 'BHR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Bahrain'); break;
			case 'BD': case 'BGD': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Bangladesh'); break;
			case 'BB': case 'BRB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Barbados'); break;
			case 'BY': case 'BLR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Belarus'); break;
			case 'BE': case 'BEL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Belgium'); break;
			case 'BZ': case 'BLZ': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Belize'); break;
			case 'BJ': case 'BEN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Benin'); break;
			case 'BM': case 'BMU': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Bermuda'); break;
			case 'BT': case 'BTN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Bhutan'); break;
			case 'BO': case 'BOL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Bolivia'); break;
			case 'BA': case 'BIH': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Bosnia and Herzegovina'); break;
			case 'BW': case 'BWA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Botswana'); break;
			case 'BV': case 'BVT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Bouvet Island'); break;
			case 'BR': case 'BRA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Brazil'); break;
			case 'IO': case 'IOT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','British Indian Ocean Territory'); break;
			case 'BN': case 'BRN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Brunei Darussalam'); break;
			case 'BG': case 'BGR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Bulgaria'); break;
			case 'BF': case 'BFA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Burkina Faso'); break;
			case 'BI': case 'BDI': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Burundi'); break;
			case 'KH': case 'KHM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Cambodia'); break;
			case 'CM': case 'CMR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Cameroon'); break;
			case 'CA': case 'CAN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Canada'); break;
			case 'CV': case 'CPV': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Cape Verde'); break;
			case 'KY': case 'CYM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Cayman Islands'); break;
			case 'CF': case 'CAF': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Central African Republic'); break;
			case 'TD': case 'TCD': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Chad'); break;
			case 'CL': case 'CHL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Chile'); break;
			case 'CN': case 'CHN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','China'); break;
			case 'CX': case 'CXR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Christmas Island'); break;
			case 'CC': case 'CCK': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Cocos (Keeling) Islands'); break;
			case 'CO': case 'COL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Colombia'); break;
			case 'KM': case 'COM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Comoros'); break;
			case 'CG': case 'COG': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Congo'); break;
			case 'CD': case 'COD': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Congo'); break;
			case 'CK': case 'COK': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Cook Islands'); break;
			case 'CR': case 'CRI': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Costa Rica'); break;
			case 'CI': case 'CIV': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Cote d\'Ivoire'); break;
			case 'HR': case 'HRV': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Croatia'); break;
			case 'CU': case 'CUB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Cuba'); break;
			case 'CY': case 'CYP': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Cyprus'); break;
			case 'CZ': case 'CZE': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Czech Republic'); break;
			case 'DK': case 'DNK': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Denmark'); break;
			case 'DJ': case 'DJI': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Djibouti'); break;
			case 'DM': case 'DMA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Dominica'); break;
			case 'DO': case 'DOM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Dominican Republic'); break;
			case 'EC': case 'ECU': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Ecuador'); break;
			case 'EG': case 'EGY': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Egypt'); break;
			case 'SV': case 'SLV': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','El Salvador'); break;
			case 'GQ': case 'GNQ': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Equatorial Guinea'); break;
			case 'ER': case 'ERI': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Eritrea'); break;
			case 'EE': case 'EST': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Estonia'); break;
			case 'ET': case 'ETH': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Ethiopia'); break;
			case 'FK': case 'FLK': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Falkland Islands (Malvinas)'); break;
			case 'FO': case 'FRO': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Faroe Islands'); break;
			case 'FJ': case 'FJI': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Fiji'); break;
			case 'FI': case 'FIN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Finland'); break;
			case 'FR': case 'FRA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','France'); break;
			case 'GF': case 'GUF': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','French Guiana'); break;
			case 'PF': case 'PYF': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','French Polynesia'); break;
			case 'TF': case 'ATF': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','French Southern Territories'); break;
			case 'GA': case 'GAB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Gabon'); break;
			case 'GM': case 'GMB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Gambia'); break;
			case 'GE': case 'GEO': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Georgia'); break;
			case 'DE': case 'DEU': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Germany'); break;
			case 'GH': case 'GHA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Ghana'); break;
			case 'GI': case 'GIB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Gibraltar'); break;
			case 'GR': case 'GRC': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Greece'); break;
			case 'GL': case 'GRL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Greenland'); break;
			case 'GD': case 'GRD': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Grenada'); break;
			case 'GP': case 'GLP': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Guadeloupe'); break;
			case 'GU': case 'GUM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Guam'); break;
			case 'GT': case 'GTM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Guatemala'); break;
			case 'GG': case 'GGY': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Guernsey'); break;
			case 'GN': case 'GIN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Guinea'); break;
			case 'GW': case 'GNB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Guinea-Bissau'); break;
			case 'GY': case 'GUY': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Guyana'); break;
			case 'HT': case 'HTI': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Haiti'); break;
			case 'HM': case 'HMD': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Heard Island and McDonald Islands'); break;
			case 'VA': case 'VAT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Holy See (Vatican City State)'); break;
			case 'HN': case 'HND': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Honduras'); break;
			case 'HK': case 'HKG': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Hong Kong'); break;
			case 'HU': case 'HUN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Hungary'); break;
			case 'IS': case 'ISL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Iceland'); break;
			case 'IN': case 'IND': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','India'); break;
			case 'ID': case 'IDN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Indonesia'); break;
			case 'IR': case 'IRN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Iran'); break;
			case 'IQ': case 'IRQ': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Iraq'); break;
			case 'IE': case 'IRL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Ireland'); break;
			case 'IM': case 'IMN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Isle of Man'); break;
			case 'IL': case 'ISR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Israel'); break;
			case 'IT': case 'ITA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Italy'); break;
			case 'JM': case 'JAM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Jamaica'); break;
			case 'JP': case 'JPN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Japan'); break;
			case 'JE': case 'JEY': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Jersey'); break;
			case 'JO': case 'JOR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Jordan'); break;
			case 'KZ': case 'KAZ': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Kazakhstan'); break;
			case 'KE': case 'KEN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Kenya'); break;
			case 'KI': case 'KIR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Kiribati'); break;
			case 'KP': case 'PRK': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Democratic People\'s Republic of Korea'); break;
			case 'KR': case 'KOR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Republic of Korea'); break;
			case 'KW': case 'KWT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Kuwait'); break;
			case 'KG': case 'KGZ': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Kyrgyzstan'); break;
			case 'LA': case 'LAO': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Lao People\'s Democratic Republic'); break;
			case 'LV': case 'LVA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Latvia'); break;
			case 'LB': case 'LBN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Lebanon'); break;
			case 'LS': case 'LSO': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Lesotho'); break;
			case 'LR': case 'LBR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Liberia'); break;
			case 'LY': case 'LBY': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Libyan Arab Jamahiriya'); break;
			case 'LI': case 'LIE': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Liechtenstein'); break;
			case 'LT': case 'LTU': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Lithuania'); break;
			case 'LU': case 'LUX': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Luxembourg'); break;
			case 'MO': case 'MAC': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Macao'); break;
			case 'MK': case 'MKD': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Macedonia'); break;
			case 'MG': case 'MDG': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Madagascar'); break;
			case 'MW': case 'MWI': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Malawi'); break;
			case 'MY': case 'MYS': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Malaysia'); break;
			case 'MV': case 'MDV': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Maldives'); break;
			case 'ML': case 'MLI': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Mali'); break;
			case 'MT': case 'MLT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Malta'); break;
			case 'MH': case 'MHL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Marshall Islands'); break;
			case 'MQ': case 'MTQ': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Martinique'); break;
			case 'MR': case 'MRT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Mauritania'); break;
			case 'MU': case 'MUS': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Mauritius'); break;
			case 'YT': case 'MYT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Mayotte'); break;
			case 'MX': case 'MEX': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Mexico'); break;
			case 'FM': case 'FSM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Micronesia'); break;
			case 'MD': case 'MDA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Moldova'); break;
			case 'MC': case 'MCO': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Monaco'); break;
			case 'MN': case 'MNG': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Mongolia'); break;
			case 'ME': case 'MNE': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Montenegro'); break;
			case 'MS': case 'MSR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Montserrat'); break;
			case 'MA': case 'MAR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Morocco'); break;
			case 'MZ': case 'MOZ': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Mozambique'); break;
			case 'MM': case 'MMR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Myanmar'); break;
			case 'NA': case 'NAM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Namibia'); break;
			case 'NR': case 'NRU': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Nauru'); break;
			case 'NP': case 'NPL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Nepal'); break;
			case 'NL': case 'NLD': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Netherlands'); break;
			case 'AN': case 'ANT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Netherlands Antilles [note 1]'); break;
			case 'NC': case 'NCL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','New Caledonia'); break;
			case 'NZ': case 'NZL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','New Zealand'); break;
			case 'NI': case 'NIC': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Nicaragua'); break;
			case 'NE': case 'NER': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Niger'); break;
			case 'NG': case 'NGA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Nigeria'); break;
			case 'NU': case 'NIU': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Niue'); break;
			case 'NF': case 'NFK': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Norfolk Island'); break;
			case 'MP': case 'MNP': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Northern Mariana Islands'); break;
			case 'NO': case 'NOR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Norway'); break;
			case 'OM': case 'OMN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Oman'); break;
			case 'PK': case 'PAK': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Pakistan'); break;
			case 'PW': case 'PLW': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Palau'); break;
			case 'PS': case 'PSE': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Palestinian Territory'); break;
			case 'PA': case 'PAN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Panama'); break;
			case 'PG': case 'PNG': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Papua New Guinea'); break;
			case 'PY': case 'PRY': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Paraguay'); break;
			case 'PE': case 'PER': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Peru'); break;
			case 'PH': case 'PHL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Philippines'); break;
			case 'PN': case 'PCN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Pitcairn'); break;
			case 'PL': case 'POL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Poland'); break;
			case 'PT': case 'PRT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Portugal'); break;
			case 'PR': case 'PRI': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Puerto Rico'); break;
			case 'QA': case 'QAT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Qatar'); break;
			case 'RE': case 'REU': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Reunion'); break;
			case 'RO': case 'ROU': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Romania'); break;
			case 'RU': case 'RUS': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Russian Federation'); break;
			case 'RW': case 'RWA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Rwanda'); break;
			case 'BL': case 'BLM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Saint Barthélemy'); break;
			case 'SH': case 'SHN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Saint Helena'); break;
			case 'KN': case 'KNA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Saint Kitts and Nevis'); break;
			case 'LC': case 'LCA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Saint Lucia'); break;
			case 'MF': case 'MAF': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Saint Martin (French part)'); break;
			case 'PM': case 'SPM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Saint Pierre and Miquelon'); break;
			case 'VC': case 'VCT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Saint Vincent and the Grenadines'); break;
			case 'WS': case 'WSM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Samoa'); break;
			case 'SM': case 'SMR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','San Marino'); break;
			case 'ST': case 'STP': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Sao Tome and Principe'); break;
			case 'SA': case 'SAU': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Saudi Arabia'); break;
			case 'SN': case 'SEN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Senegal'); break;
			case 'RS': case 'SRB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Serbia'); break;
			case 'SC': case 'SYC': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Seychelles'); break;
			case 'SL': case 'SLE': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Sierra Leone'); break;
			case 'SG': case 'SGP': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Singapore'); break;
			case 'SK': case 'SVK': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Slovakia'); break;
			case 'SI': case 'SVN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Slovenia'); break;
			case 'SB': case 'SLB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Solomon Islands'); break;
			case 'SO': case 'SOM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Somalia'); break;
			case 'ZA': case 'ZAF': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','South Africa'); break;
			case 'GS': case 'SGS': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','South Georgia and the South Sandwich Islands'); break;
			case 'ES': case 'ESP': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Spain'); break;
			case 'LK': case 'LKA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Sri Lanka'); break;
			case 'SD': case 'SDN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Sudan'); break;
			case 'SR': case 'SUR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Suriname'); break;
			case 'SJ': case 'SJM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Svalbard and Jan Mayen'); break;
			case 'SZ': case 'SWZ': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Swaziland'); break;
			case 'SE': case 'SWE': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Sweden'); break;
			case 'CH': case 'CHE': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Switzerland'); break;
			case 'SY': case 'SYR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Syrian Arab Republic'); break;
			case 'TW': case 'TWN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Taiwan'); break;
			case 'TJ': case 'TJK': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Tajikistan'); break;
			case 'TZ': case 'TZA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Tanzania'); break;
			case 'TH': case 'THA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Thailand'); break;
			case 'TL': case 'TLS': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Timor-Leste'); break;
			case 'TG': case 'TGO': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Togo'); break;
			case 'TK': case 'TKL': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Tokelau'); break;
			case 'TO': case 'TON': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Tonga'); break;
			case 'TT': case 'TTO': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Trinidad and Tobago'); break;
			case 'TN': case 'TUN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Tunisia'); break;
			case 'TR': case 'TUR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Turkey'); break;
			case 'TM': case 'TKM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Turkmenistan'); break;
			case 'TC': case 'TCA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Turks and Caicos Islands'); break;
			case 'TV': case 'TUV': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Tuvalu'); break;
			case 'UG': case 'UGA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Uganda'); break;
			case 'UA': case 'UKR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Ukraine'); break;
			case 'AE': case 'ARE': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','United Arab Emirates'); break;
			case 'GB': case 'GBR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','United Kingdom'); break;
			case 'US': case 'USA': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','United States'); break;
			case 'UM': case 'UMI': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','United States Minor Outlying Islands'); break;
			case 'UY': case 'URY': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Uruguay'); break;
			case 'UZ': case 'UZB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Uzbekistan'); break;
			case 'VU': case 'VUT': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Vanuatu'); break;
			case 'VE': case 'VEN': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Venezuela'); break;
			case 'VN': case 'VNM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Viet Nam'); break;
			case 'VG': case 'VGB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','British Virgin Islands'); break;
			case 'VI': case 'VIR': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','U.S. Virgin Islands'); break;
			case 'WF': case 'WLF': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Wallis and Futuna'); break;
			case 'EH': case 'ESH': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Western Sahara'); break;
			case 'YE': case 'YEM': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Yemen'); break;
			case 'ZM': case 'ZMB': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Zambia'); break;
			case 'ZW': case 'ZWE': $Object->setParameter('poMMoContactCountry','Other'); $Object->setParameter('poMMoContactOther Country','Zimbabwe'); break;
			default: break;
		}
		
		foreach ($Object->params as $key => $value){
			if ($value == ''){
				unset($Object->params[$key]);
			}
		}

   	}

	function postImport(& $poMMoTerm){
	}
	
	function postPerformImport(){
	}
	    
}