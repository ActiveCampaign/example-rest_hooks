<?php

	$api_url = "https://mthommes6.api-us1.com";
	$api_key = "31478fa6ed91f39e63539d9cb7de24b873f8f9af86aee2f95f4d93f8123cdeb24a1d61d5";

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

	function dbg($var, $continue = 0, $element = "pre")
	{
	  echo "<" . $element . ">";
	  echo "Vartype: " . gettype($var) . "\n";
	  if ( is_array($var) ) echo "Elements: " . count($var) . "\n\n";
	  elseif ( is_string($var) ) echo "Length: " . strlen($var) . "\n\n";
	  print_r($var);
	  echo "</" . $element . ">";
		if (!$continue) exit();
	}

	$alert = "";
	$step = ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["step"]) && (int)$_GET["step"]) ? (int)$_GET["step"] : 1;

	if ($step == 1) {

		// get lists (for the drop-down)
		$lists = $ac->api("list/list?ids=all&full=0");

		$lists = get_object_vars($lists);
		$lists_ = array();

		foreach ($lists as $k => $list) {
			if (is_int($k)) {
				// avoid "result_code", "result_message", etc items
				$lists_[] = $list;
			}
		}

		$lists = $lists_;

	}

	// form submitted
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		$form_step = $step = (int)$_POST["step"];

		if ($form_step == 1) {

			$webhook_name = $_POST["webhook_name"];
			$webhook_url = $_POST["webhook_url"];
			$webhook_list = (int)$_POST["webhook_list"];

			// add webhook
			$webhook = get_object_vars(json_decode('{
			  "name": "' . $webhook_name . '",
			  "url": "' . $webhook_url . '",
			  "lists[' . $webhook_list . ']": "' . $webhook_list . '",
			  "action[subscribe]": "subscribe",
			  "action[unsubscribe]": "unsubscribe",
			  "action[update]": "update",
			  "action[sent]": "sent",
			  "action[open]": "open",
			  "action[click]": "click",
			  "action[forward]": "forward",
			  "action[share]": "share",
			  "action[bounce]": "bounce",
			  "init[public]": "public",
			  "init[admin]": "admin",
			  "init[api]": "api",
			  "init[system]": "system"
			}'));

			$webhook = $ac->api("webhook/add", $webhook);

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
		<option value="201"<?php if ($step >= 201 && $step <= 300) echo " selected=\"selected\""; ?>>View Webhook Event Types</option>
	</select>
</form>

<form method="POST">

	<input type="hidden" name="step" value="<?php echo $step; ?>" />

	<?php

		if ($step == 1) {

			?>

			<h1>Add Webhook</h1>

			<h2>Webhook Name</h2>
			<input type="text" name="webhook_name" size="30" />

			<h2 style="margin-top: 30px;">Webhook URL</h2>
			<input type="text" name="webhook_url" size="60" />

			<h2 style="margin-top: 30px;">List</h2>
			<select name="webhook_list">
				<?php

					foreach ($lists as $list) {
						?>

						<option value="<?php echo $list->id; ?>"><?php echo $list->name; ?></option>

						<?php
					}

				?>
			</select>

			<h2 style="margin-top: 30px;">Event/Type</h2>

			<p>
				<input type="checkbox" name="action[subscribe]" id="action_subscribe" value="subscribe" />
				<label for="action_subscribe">New Subscription</label>
			</p>

			<p>
				<input type="checkbox" name="action[unsubscribe]" id="action_unsubscribe" value="unsubscribe" />
				<label for="action_unsubscribe">New Unsubscription</label>
			</p>

			<p>
				<input type="checkbox" name="action[update]" id="action_update" value="update" />
				<label for="action_update">Subscriber Updated</label>
			</p>

			<p>
				<input type="checkbox" name="action[sent]" id="action_sent" value="sent" />
				<label for="action_sent">Campaign Starts Sending</label>
			</p>

			<p>
				<input type="checkbox" name="action[open]" id="action_open" value="open" />
				<label for="action_open">Campaign Opened</label>
			</p>

			<p>
				<input type="checkbox" name="action[click]" id="action_click" value="click" />
				<label for="action_click">Link Clicked</label>
			</p>

			<p>
				<input type="checkbox" name="action[forward]" id="action_forward" value="forward" />
				<label for="action_forward">Campaign Forwarded</label>
			</p>

			<p>
				<input type="checkbox" name="action[share]" id="action_share" value="share" />
				<label for="action_share">Campaign Shared</label>
			</p>

			<p>
				<input type="checkbox" name="action[bounce]" id="action_bounce" value="bounce" />
				<label for="action_bounce">Email Bounce</label>
			</p>

			<p>
				<input type="checkbox" name="action[reply]" id="action_reply" value="reply" />
				<label for="action_reply">Email Reply</label>
			</p>

			<h2 style="margin-top: 30px;">Initialize From</h2>

			<?php

		}

	?>

	<p style="<?php if ($step == 3) echo "display: none; "; ?>margin-top: 30px;">
		<input type="submit" name="submit" value="Submit" style="background-color: green; color: white;" />
	</p>

</form>