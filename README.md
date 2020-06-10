# Botometer PHP API
Converted from [Botometer PHP API](https://github.com/IUNetSci/botometer-python)

```
$botometer = new \Botometer\Botometer(
		$consumerKey, $consumerSecret, $accessToken, $accessTokenSecret, $rapidApiKey
	);

// Check a single account by screen name
$result = $botometer->checkAccount( '@clayadavis' );

// Check a single account by id
$result = $botometer->checkAccount( 1548959833 );

$accounts = ['@clayadavis', '@onurvarol', '@jabawack'];
$results = $botometer->checkAccountsIn( $accounts);
foreach ($results as $screenName => $result) {
	//Do stuff with `$screenName` and `$result`
}

```

Result 
```json
{
  "cap": {
    "english": 0.0011785984309163565,
    "universal": 0.0016912294273666159
  },
  "categories": {
    "content": 0.058082395351262375,
    "friend": 0.044435259626385865,
    "network": 0.07064549990637549,
    "sentiment": 0.07214003430676995,
    "temporal": 0.07924665710801207,
    "user": 0.027817972609638725
  },
  "display_scores": {
    "content": 0.3,
    "english": 0.1,
    "friend": 0.2,
    "network": 0.4,
    "sentiment": 0.4,
    "temporal": 0.4,
    "universal": 0.1,
    "user": 0.1
  },
  "scores": {
    "english": 0.0215615093045025,
    "universal": 0.0254864249403189
  },
  "user": {
    "id_str": "1548959833",
    "screen_name": "clayadavis",
    "...": "..."
  }
}
```
For more information on this response object, consult the [API Overview](https://rapidapi.com/OSoMe/api/botometer-pro/details) on RapidAPI.
## Install instructions

## Dependencies
* [abraham/twitteroauth](https://github.com/abraham/twitteroauth)
* [mashape/unirest-php](https://github.com/Kong/unirest-php)

### RapidAPI key
Our API is served via [RapidAPI](//rapidapi.com). You must sign up
for a free account in order to obtain a RapidAPI secret key. The easiest way to
get your secret key is to visit
[our API endpoint page](https://rapidapi.com/OSoMe/api/botometer-pro/endpoints)
and look in the endpoint's header parametsrs for the "X-RapidAPI-Key" as shown below:

![Screenshot of RapidAPI header parameters](/docs/rapidapi_key.png)
    
### Twitter app
In order to access Twitter's API, one needs to have/create a [Twitter app](https://apps.twitter.com/).
Once you've created an app, the authentication info can be found in the "Keys and Access Tokens" tab of the app's properties:
![Screenshot of app "Keys and Access Tokens"](/docs/twitter_app_keys.png)

## Authentication

By default, Botometer uses **user authentication** when interacting with Twitter's API as it is the least restrictive and the ratelimit matches with Botometer's **Pro** plan: 180 requests per 15-minute window.
One can instead use Twitter's **application authentication** in order to take advantage of the higher ratelimit that matches our **Ultra** plan: 450 requests per 15-minute window. Do note the differences between user and app-only authentication found under the header "Twitter API Authentication Model" in [Twitter's docs on authentication](https://developer.twitter.com/en/docs/basics/authentication/overview/oauth).