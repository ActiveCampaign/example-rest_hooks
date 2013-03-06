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

Refresh the page and you should see options to Add Webhook, View/Edit/Delete Webhooks, and View Webhook Event Types:

![Webhook script options](http://d226aj4ao1t61q.cloudfront.net/8zirqg5sm_screenshot2013-03-06at11.44.28am.jpg)

## Documentation and Links

* [ActiveCampaign webhook documentation](http://www.activecampaign.com/api/webhooks.php)

## Reporting Issues

We'd love to help if you have questions or problems. Report issues using the [Github Issue Tracker](issues) or email help@activecampaign.com.