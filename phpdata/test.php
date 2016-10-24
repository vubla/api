<?php 
	$to      = 'info@vubla.com';
	
	$subject = 'Vubla crawler nu din webshop';
	$message = "Dit website bliver i dette øjeblik crawlet. Du vil modtage en email når dette er fuldført.<br>Hvis du mod forventning ikke modtager en email indenfor 2 timer, så skriv til os på info@vubla.com.<br><br>med venlig hilsen<br>Vubla teamet";
	//$subject = 'Anmodning om crawl er accepteret';
	//$message = "Dit website bliver i dette øjeblik crawlet. Du vil modtage en email når det er fuldført.\r\nHvor lang tid dette tager for typiske webshops har vi endnu ingen statistik på, men hvis du ikke har modtaget en mail indenfor 6 timer, så skriv til os på info@vubla.com\r\n\r\nHar du spørgsmål, eller andet, så tøv heller ikke med at skrive.\r\n\r\n- Vubla";
	$headers = 'From: info@vubla.com' . "\r\n" .
	    'Reply-To: info@vubla.com' . "\r\n" .
		"Content-Type:text/html; charset=\"utf-8\"\n" .
	    'X-Mailer: vublamailer';
	
	$body = "<html>\n";
    $body .= "<body style=\"font-family:Helvetica, Verdana, Geneva, sans-serif; font-size:12px;\">\n";
    $body .= $message;
    $body .= "</body>\n";
    $body .= "</html>\n";
	
	$bool = mail($to, $subject, $body, $headers);
	


?>