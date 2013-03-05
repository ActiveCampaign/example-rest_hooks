ActiveCampaign Custom API Script: Interact with ActiveCampaign webhooks via our REST API.

## Requirements

1. [Our PHP API library](https://github.com/ActiveCampaign/activecampaign-api-php)
2. A web server where you can run PHP code

## Installation and Usage

You can install **example-rest_hooks** by downloading (or cloning) the source.

Input your ActiveCampaign URL and API Key at the top of the script. Example below:

<pre>
$api_url = "https://ACCOUNT.api-us1.com";
$api_key = "4f3c6d12f0.....00ca273778dc893";
</pre>

Also make sure the path to the PHP library is correct:

<pre>
require_once("../../activecampaign-api-php/includes/ActiveCampaign.class.php");
</pre>

Refresh the page and you should see