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

	.on {
		color: green;
	}

	.off {
		color: red;
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

	$success = $alert = "";
	$step = ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["step"]) && (int)$_GET["step"]) ? (int)$_GET["step"] : 1;

	// load stuff on page load to use in the HTML part
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
	elseif ($step == 101) {

		$webhooks = $ac->api("webhook/list");

		$webhooks = get_object_vars($webhooks);
		$webhooks_ = array();

		foreach ($webhooks as $k => $webhook) {
			if (is_int($k)) {
				// avoid "result_code", "result_message", etc items
				$webhooks_[] = $webhook;
			}
		}

		$webhooks = $webhooks_;

	}

	// form submitted
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		$form_step = $step = (int)$_POST["step"];

		if ($form_step == 1) {

			$webhook_name = $_POST["webhook_name"];
			$webhook_url = $_POST["webhook_url"];
			$webhook_list = (int)$_POST["webhook_list"];
			$webhook_actions = (array)$_POST["webhook_action"];
			$webhook_inits = (array)$_POST["webhook_init"];

			// add webhook

			$webhook = get_object_vars(json_decode('{
			  "name": "' . $webhook_name . '",
			  "url": "' . $webhook_url . '",
			  "lists[' . $webhook_list . ']": "' . $webhook_list . '"
			}'));

			$webhook["action"] = $webhook_actions;
			$webhook["init"] = $webhook_inits;

			$webhook = $ac->api("webhook/add", $webhook);

			if (!(int)$webhook->success) {
				$alert = $webhook->error;
			}
			else {
				$success = $webhook->result_message;
			}

		}
		elseif ($step == 101) {



		}

	}

?>

<div style="color: green; font-weight: bold; margin: 20px 0;<?php if (!$success) echo " display: none;"; ?>"><?php echo $success; ?></div>
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
				<input type="checkbox" name="webhook_action[subscribe]" id="action_subscribe" value="subscribe" />
				<label for="action_subscribe">New Subscription</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[unsubscribe]" id="action_unsubscribe" value="unsubscribe" />
				<label for="action_unsubscribe">New Unsubscription</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[update]" id="action_update" value="update" />
				<label for="action_update">Subscriber Updated</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[sent]" id="action_sent" value="sent" />
				<label for="action_sent">Campaign Starts Sending</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[open]" id="action_open" value="open" />
				<label for="action_open">Campaign Opened</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[click]" id="action_click" value="click" />
				<label for="action_click">Link Clicked</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[forward]" id="action_forward" value="forward" />
				<label for="action_forward">Campaign Forwarded</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[share]" id="action_share" value="share" />
				<label for="action_share">Campaign Shared</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[bounce]" id="action_bounce" value="bounce" />
				<label for="action_bounce">Email Bounce</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[reply]" id="action_reply" value="reply" />
				<label for="action_reply">Email Reply</label>
			</p>

			<h2 style="margin-top: 30px;">Initialize From</h2>

			<p>
				<input type="checkbox" name="webhook_init[public]" id="init_public" value="public" />
				<label for="init_public">By a subscriber</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_init[admin]" id="init_admin" value="admin" />
				<label for="init_admin">By an admin user</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_init[api]" id="init_api" value="api" />
				<label for="init_api">By the API</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_init[system]" id="init_system" value="system" />
				<label for="init_system">By system processes</label>
			</p>

			<?php

		}
		elseif ($step == 101) {

			?>

			<h1>View/Edit/Delete Webhooks</h1>

			<table border="1" cellspacing="0" cellpadding="3">

				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Create Date</th>
					<th>List ID</th>
					<th>URL</th>
					<th>Type: New Subscription</th>
					<th>Type: New Unsubscription</th>
					<th>Type: Subscriber Updated</th>
					<th>Type: Campaign Starts Sending</th>
					<th>Type: Campaign Opened</th>
					<th>Type: Link Clicked</th>
					<th>Type: Campaign Forwarded</th>
					<th>Type: Campaign Shared</th>
					<th>Type: Email Bounce</th>
					<th>Type: Email Reply</th>
					<th>Init: By a subscriber</th>
					<th>Init: By an admin user</th>
					<th>Init: By the API</th>
					<th>Init: By system processes</th>
					<th>Edit?</th>
					<th>Delete?</th>
				</tr>

				<?php

					foreach ($webhooks as $webhook) {

						?>

						<tr>
							<td><?php echo $webhook->id; ?></td>
							<td><?php echo $webhook->name; ?></td>
							<td><?php echo date("m/d/Y", strtotime($webhook->cdate)); ?></td>
							<td><?php echo $webhook->listid; ?></td>
							<td><a href="<?php echo $webhook->url; ?>">Link</a></td>
							<td><?php echo ((int)$webhook->subscribe) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->unsubscribe) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->update) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->sent) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->open) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->click) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->forward) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->share) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->bounce) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->reply) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->init_public) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->init_admin) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->init_api) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><?php echo ((int)$webhook->init_system) ? "<span class='on'>on</span>" : "<span class='off'>off</span>"; ?></td>
							<td><input type="radio" name="edit[]" value="<?php echo $webhook->id; ?>" /></td>
							<td><input type="checkbox" name="cancels[]" value="<?php echo $webhook->id; ?>" /></td>
						</tr>

						<?php

					}

				?>

			</table>

			<?php

		}

	?>

	<p style="<?php if ($step == 3) echo "display: none; "; ?>margin-top: 30px;">
		<input type="submit" name="submit" value="Submit" style="background-color: green; color: white;" />
	</p>

</form>