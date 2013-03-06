<?php

	session_start();

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

	function do_deletes($post) {

		$delete_results = array();

		if (isset($post["webhook_deletes"])) {

			$deletes = $post["webhook_deletes"];

			foreach ($deletes as $webhook_id) {

				$delete = $GLOBALS["ac"]->api("webhook/delete?id={$webhook_id}");

				if (!(int)$delete->success) {
					$delete_results[$webhook_id] = $delete->error;
				}
				else {
					$delete_results[$webhook_id] = $delete->result_message;
				}

				sleep(5);

			}

			// so it re-fetches them
			unset($_SESSION["webhooks"]);

		}

		return $delete_results;

	}

	function do_add_edit($action, $post) {

		$webhook_name = $post["webhook_name"];
		$webhook_url = $post["webhook_url"];
		$webhook_list = (int)$post["webhook_list"];
		$webhook_actions = (array)$post["webhook_action"];
		$webhook_inits = (array)$post["webhook_init"];

		// add/edit webhook

		$webhook = get_object_vars(json_decode('{
		  "name": "' . $webhook_name . '",
		  "url": "' . $webhook_url . '",
		  "lists[' . $webhook_list . ']": "' . $webhook_list . '"
		}'));

		$webhook["action"] = $webhook_actions;
		$webhook["init"] = $webhook_inits;

		if ($action == "edit") $webhook["id"] = $post["webhook_id"];

		$webhook = $GLOBALS["ac"]->api("webhook/{$action}", $webhook);

		if (!(int)$webhook->success) {
			$alert = $webhook->error;
		}
		else {
			$success = $webhook->result_message;
		}

		// so it re-fetches them
		unset($_SESSION["webhooks"]);

	}

	$success = $alert = "";
	$step = ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["step"]) && (int)$_GET["step"]) ? (int)$_GET["step"] : 1;

	// form submitted
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		$form_step = $step = (int)$_POST["step"];

		if ($form_step == 1) {

			// add webhook
			$edit = do_add_edit("add", $_POST);

		}
		elseif ($step == 101) {

			// editing a webhook
			if (isset($_POST["webhook_edit"])) {

				$edit = (int)$_POST["webhook_edit"];
				$webhook_view = $ac->api("webhook/view?id={$edit}");

				$step = 102;

			}

			// deletes
			$delete_results = do_deletes($_POST);

		}
		elseif ($step == 102) {

			// do edits and deletes (if any)
			$edit = do_add_edit("edit", $_POST);
			$delete_results = do_deletes($_POST);

		}

	}

	// load stuff on page load to use in the HTML part
	if ($step == 1) {

		if (!isset($_SESSION["lists"]) || !$_SESSION["lists"]) {

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

			$_SESSION["lists"] = $lists_;

		}

	}
	elseif ($step == 101 || $step == 102) {

		if (!isset($_SESSION["webhooks"]) || !$_SESSION["webhooks"]) {

			$webhooks = $ac->api("webhook/list");

			$webhooks = get_object_vars($webhooks);
			$webhooks_ = array();

			foreach ($webhooks as $k => $webhook) {
				if (is_int($k)) {
					// avoid "result_code", "result_message", etc items
					$webhooks_[] = $webhook;
				}
			}

			$_SESSION["webhooks"] = $webhooks_;

		}

	}
	elseif ($step == 201) {

		$webhook_events = $ac->api("webhook/events");

		$webhook_events = get_object_vars($webhook_events);
		$webhook_events_ = array();

		foreach ($webhook_events as $k => $webhook_event) {
			if ($k != "result_code" && $k != "result_message" && $k != "result_output" && $k != "success") {
				// avoid "result_code", "result_message", etc items
				$webhook_events_[] = $webhook_event;
			}
		}

		$webhook_events = $webhook_events_;

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

		if ($step == 1 || (isset($edit) && $edit)) {

			?>

			<h1><?php echo (isset($edit) && $edit) ? "Edit" : "Add"; ?> Webhook</h1>

			<input type="hidden" name="webhook_id" value="<?php if (isset($webhook_view)) echo $webhook_view->id; ?>" />

			<h2>Webhook Name</h2>
			<input type="text" name="webhook_name" size="30" value="<?php if (isset($webhook_view)) echo $webhook_view->name; ?>" />

			<h2 style="margin-top: 30px;">Webhook URL</h2>
			<input type="text" name="webhook_url" size="60" value="<?php if (isset($webhook_view)) echo $webhook_view->url; ?>" />

			<h2 style="margin-top: 30px;">List</h2>
			<select name="webhook_list">
				<?php

					foreach ($_SESSION["lists"] as $list) {
						?>

						<option value="<?php echo $list->id; ?>"<?php if (isset($webhook_view) && $webhook_view->listid == $list->id) echo " selected='selected'"; ?>><?php echo $list->name; ?></option>

						<?php
					}

				?>
			</select>

			<h2 style="margin-top: 30px;">Event/Type</h2>

			<p>
				<input type="checkbox" name="webhook_action[subscribe]" id="action_subscribe" value="subscribe" <?php if (isset($webhook_view) && (int)$webhook_view->subscribe) echo " checked='checked'"; ?> />
				<label for="action_subscribe">New Subscription</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[unsubscribe]" id="action_unsubscribe" value="unsubscribe" <?php if (isset($webhook_view) && (int)$webhook_view->unsubscribe) echo " checked='checked'"; ?> />
				<label for="action_unsubscribe">New Unsubscription</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[update]" id="action_update" value="update" <?php if (isset($webhook_view) && (int)$webhook_view->update) echo " checked='checked'"; ?> />
				<label for="action_update">Subscriber Updated</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[sent]" id="action_sent" value="sent" <?php if (isset($webhook_view) && (int)$webhook_view->sent) echo " checked='checked'"; ?> />
				<label for="action_sent">Campaign Starts Sending</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[open]" id="action_open" value="open" <?php if (isset($webhook_view) && (int)$webhook_view->open) echo " checked='checked'"; ?> />
				<label for="action_open">Campaign Opened</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[click]" id="action_click" value="click" <?php if (isset($webhook_view) && (int)$webhook_view->click) echo " checked='checked'"; ?> />
				<label for="action_click">Link Clicked</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[forward]" id="action_forward" value="forward" <?php if (isset($webhook_view) && (int)$webhook_view->forward) echo " checked='checked'"; ?> />
				<label for="action_forward">Campaign Forwarded</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[share]" id="action_share" value="share" <?php if (isset($webhook_view) && (int)$webhook_view->share) echo " checked='checked'"; ?> />
				<label for="action_share">Campaign Shared</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[bounce]" id="action_bounce" value="bounce" <?php if (isset($webhook_view) && (int)$webhook_view->bounce) echo " checked='checked'"; ?> />
				<label for="action_bounce">Email Bounce</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_action[reply]" id="action_reply" value="reply" <?php if (isset($webhook_view) && (int)$webhook_view->reply) echo " checked='checked'"; ?> />
				<label for="action_reply">Email Reply</label>
			</p>

			<h2 style="margin-top: 30px;">Initialize From</h2>

			<p>
				<input type="checkbox" name="webhook_init[public]" id="init_public" value="public" <?php if (isset($webhook_view) && (int)$webhook_view->init_public) echo " checked='checked'"; ?> />
				<label for="init_public">By a subscriber</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_init[admin]" id="init_admin" value="admin" <?php if (isset($webhook_view) && (int)$webhook_view->init_admin) echo " checked='checked'"; ?> />
				<label for="init_admin">By an admin user</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_init[api]" id="init_api" value="api" <?php if (isset($webhook_view) && (int)$webhook_view->init_api) echo " checked='checked'"; ?> />
				<label for="init_api">By the API</label>
			</p>

			<p>
				<input type="checkbox" name="webhook_init[system]" id="init_system" value="system" <?php if (isset($webhook_view) && (int)$webhook_view->init_system) echo " checked='checked'"; ?> />
				<label for="init_system">By system processes</label>
			</p>

			<?php

		}

		if ($step == 101 || $step == 102) {

			?>

			<h1>View/Edit/Delete Webhooks</h1>

			<?php

				if (isset($edit)) {

				}

				if (isset($delete_results) && $delete_results) {

					?>

					<h2 style="margin-top: 30px;">Delete Results</h2>

					<?php

					foreach ($delete_results as $webhook_id => $api_result) {

						?>

						<p><b>Webhook ID <?php echo $webhook_id; ?>:</b> <?php echo $api_result; ?></p>

						<?php
					}

				}

			?>

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

					foreach ($_SESSION["webhooks"] as $webhook) {

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
							<td><input type="radio" name="webhook_edit" value="<?php echo $webhook->id; ?>" /></td>
							<td><input type="checkbox" name="webhook_deletes[]" value="<?php echo $webhook->id; ?>" /></td>
						</tr>

						<?php

					}

				?>

			</table>

			<?php

		}

		if ($step == 201) {

			$counter = 1;

			foreach ($webhook_events as $event) {

				?>

				<h3 style="<?php if ($counter > 1) echo "margin-top: 30px;"; ?>"><?php echo $event->description; ?></h3>

				<p>Short name: <code><?php echo $event->db_field; ?></code></p>

				<h4>Fields</h4>

				<ul>

					<?php

						foreach ($event->fields as $field => $sub_fields) {

							$sub_fields_display = "";
							if (is_array($sub_fields)) {
								$sub_fields_display = ": ";
								$sub_fields_display .= print_r($sub_fields,1);
							}

							?>

							<li><?php echo $field; ?><?php echo $sub_fields_display; ?></li>

							<?php

						}

					?>

				</ul>

				<h4>Example Response (field: value)</h4>

				<ul>

					<?php

						foreach ($event->example_response as $field => $value) {

							?>

							<li>
								<?php echo $field . ": "; ?>
								<?php

									if (is_object($value)) {
										$value = get_object_vars($value);
										print_r($value);
									}
									else {
										echo $value;
									}

								?>
							</li>

							<?php

						}

					?>

				</ul>

				<?php

				$counter++;

			}

		}

	?>

	<p style="<?php if ($step == 3) echo "display: none; "; ?>margin-top: 30px;">
		<input type="submit" name="submit" value="Submit" style="background-color: green; color: white;" />
	</p>

</form>