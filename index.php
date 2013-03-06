<?php

	$api_url = "";
	$api_key = "";

	define("ACTIVECAMPAIGN_URL", $api_url);
	define("ACTIVECAMPAIGN_API_KEY", $api_key);

	require_once("../../activecampaign-api-php/includes/ActiveCampaign.class.php");
	$ac = new ActiveCampaign(ACTIVECAMPAIGN_URL, ACTIVECAMPAIGN_API_KEY);
	
	if (!(int)$ac->credentials_test()) {
		print_r("Invalid credentials (URL or API Key).");
		exit();
	}
	
?>

<style type="text/css">

	body {
		font-family: Arial;
		font-size: 12px;
		margin: 30px;
	}

</style>

<?php
	
	$alert = "";
	$step = ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["step"]) && (int)$_GET["step"]) ? (int)$_GET["step"] : 1;

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
		$form_step = $step = (int)$_POST["step"];
		
		if ($form_step == 1) {
		
		}
		elseif ($form_step == 2) {
		
		}
		elseif ($form_step == 3) {
		
		}
		elseif ($step == 101) {
		
		}
	
	}

?>

<div style="color: red; font-weight: bold; margin: 20px 0;<?php if (!$alert) echo " display: none;"; ?>"><?php echo $alert; ?></div>

<form method="get">
	Action:
	<select name="step" onchange="this.form.submit();">
		<option value="1"<?php if ($step >= 1 && $step <= 100) echo " selected=\"selected\""; ?>>Add Webhook</option>
		<option value="101"<?php if ($step >= 101 && $step <= 200) echo " selected=\"selected\""; ?>>View/Edit/Delete Webhooks</option>
	</select>
</form>

<form method="POST">

	<input type="hidden" name="step" value="<?php echo $step; ?>" />
	
	<?php
	
		if ($step == 1) {
		
			?>

			<h1>Add Webhook</h1>
			
			<?php		
		
		}
	
	?>

</form>