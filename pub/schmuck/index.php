<?php

	//! Tradetracker Direct Linking Redirect Page.

	// Set domain name on which the redirect-page runs, WITHOUT "www.".
	$domainName = 'engelsrufer.de';

	// Set tracking group ID if provided by TradeTracker.
	$trackingGroupID = '';

	// Set the P3P compact policy.
	header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

	if (isset($_GET['tt']))
	{
		// Set parameters.
		$trackingParam = explode('_', $_GET['tt']);

		$campaignID = isset($trackingParam[0]) ? $trackingParam[0] : '';
		$materialID = isset($trackingParam[1]) ? $trackingParam[1] : '';
		$affiliateID = isset($trackingParam[2]) ? $trackingParam[2] : '';
		$reference = isset($trackingParam[3]) ? $trackingParam[3] : '';

		$redirectURL = isset($_GET['r']) ? $_GET['r'] : '';

		// Calculate MD5 checksum.
		$checkSum = md5('CHK_' . $campaignID . '::' . $materialID . '::' . $affiliateID . '::' . $reference);

		// Set tracking data.
		$trackingData = $materialID . '::' . $affiliateID . '::' . $reference . '::' . $checkSum . '::' . time();

		// Set regular tracking cookie.
		setcookie('TT2_' . $campaignID, $trackingData, time() + 31536000, '/', empty($domainName) ? null : '.' . $domainName);

		// Set session tracking cookie.
		setcookie('TTS_' . $campaignID, $trackingData, 0, '/', empty($domainName) ? null : '.' . $domainName);

		// Set tracking group cookie.
		if (!empty($trackingGroupID))
			setcookie('__tgdat' . $trackingGroupID, $trackingData . '_' . $campaignID, time() + 31536000, '/', empty($domainName) ? null : '.' . $domainName);

		// Set track-back URL.
		$trackBackURL = 'https://tc.tradetracker.net/?c=' . $campaignID . '&m=' . $materialID . '&a=' . $affiliateID . '&r=' . urlencode($reference) . '&u=' . urlencode($redirectURL);

		// Redirect to TradeTracker.
		header('Location: ' . $trackBackURL, true, 301);
	}

?>