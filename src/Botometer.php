<?php


namespace Botometer;


use Abraham\TwitterOAuth\TwitterOAuth;
use Exception;
use Unirest\Request;
use Unirest\Request\Body;
use Unirest\Response;

class Botometer {
	/**
	 * @var TwitterOAuth $twitterApi
	 */
	protected $twitterApi;
	protected $botometerUrl = '';
	protected $rapidKey = '';
	protected $url;

	/**
	 * Botometer constructor.
	 *
	 * @param $consumerKey
	 * @param $consumerSecret
	 * @param $accessToken
	 * @param $accessTokenSecret
	 * @param $rapidApiKey
	 * @param bool $mashapeKey
	 * @param string $botometerApiUrl
	 * @param int $botometerApiVersion
	 *
	 * @throws Exception
	 */
	public function __construct(
		$consumerKey, $consumerSecret, $accessToken, $accessTokenSecret, $rapidApiKey,
		$mashapeKey = false, $botometerApiUrl = 'https://botometer-pro.p.rapidapi.com',
		$botometerApiVersion = 2
	) {
		$this->twitterApi   = new TwitterOAuth( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );
		$this->botometerUrl = "$botometerApiUrl/$botometerApiVersion/";
		$this->rapidKey     = $rapidApiKey ?: $mashapeKey;
	}

	public function _bomGet( $url, array $data ) {
		$headers   = $this->setRapidHeaders();
		$url       = $this->botometerUrl . $url;
		$this->url = $url;

		return Request::post( $url, $headers, $data );
	}

	private function setRapidHeaders() {
		return array(
			"X-RapidAPI-Host" => "botometer-pro.p.rapidapi.com",
			"X-RapidAPI-Key"  => $this->rapidKey,
			"Content-Type"    => "application/json",
			"Accept"          => "application/json"
		);
	}

	public function checkAccountsIn( array $accounts, $fullUserObject = false, $maxRetries = 3 ) {
		$results = [];
		foreach ( $accounts as $account ) {
			for ( $i = 0; $i <= $maxRetries; $i ++ ) {
				try {
					$result    = $this->checkAccount( $account, $fullUserObject );
					$results[$account] = $result;
					break;
				} catch ( Exception $e ) {
					if ( $i >= $maxRetries ) {
						$results[] = [
							$account => [
								'error' => $e->getMessage()
							]
						];
					} else {
						sleep( 2 * $i );
					}
				}
			}
		}

		return $results;
	}

	/**
	 * @param $user
	 * @param bool $fullUserObject
	 *
	 * @return string json
	 * @throws Exception
	 */
	public function checkAccount( $user, $fullUserObject = false ) {
		$payload = $this->getTwitterData( $user, $fullUserObject );

		if ( !$payload['timeline'] ) {
			throw new Exception( "User $user has no tweets in timeline" );
		}
		$response = $this->_bomPost( 'check_account', $payload );
		$this->raiseForStatus( $response );

		return $response->raw_body;
	}

	public function getTwitterData( $user, $fullUserObject = false ) {
		$name         = is_numeric( $user ) ? 'user_id' : 'screen_name';
		$userTimeline = $this->twitterApi->get( "statuses/user_timeline", [
			$name         => $user,
			"count"       => 200,
			'include_rts' => true
		] );

		if ( $userTimeline ) {
			$userData = $userTimeline[0]->user;
		} else {
			$userData = $this->twitterApi->get( 'users/show', [ "screen_name" => $user ] );

		}
		$screen_name = '@' . $userData->screen_name;
		try {
			$search = $this->twitterApi->get( "search/tweets", [ "q" => $screen_name, "count" => 100 ] );

		} catch ( Exception $e ) {
			throw new Exception( "no tweets fetched" );
		}

		$payload = [
			"mentions" => $search->statuses,
			"timeline" => $userTimeline,
			'user'     => $userData
		];

		if ( ! $fullUserObject ) {
			$payload['user'] = [
				'id_str'      => $userData->id_str,
				'screen_name' => $userData->screen_name,
			];
		}

		return $payload;
	}

	/**
	 * @param $url
	 * @param array $data
	 *
	 * @return Response
	 * @throws \Unirest\Exception
	 */
	public function _bomPost( $url, array $data ) {
		$headers   = $this->setRapidHeaders();
		$url       = $this->botometerUrl . $url;
		$this->url = $url;
		$body      = Body::json( $data );

		return Request::post( $url, $headers, $body );
	}

	/**
	 * @param Response $response
	 * @param $url
	 *
	 * @throws Exception
	 */
	private function raiseForStatus( Response $response ) {
		$status       = $response->code;
		$httpErrorMsg = '';
		$url          = $this->url;
		if ( $status >= 400 && $status < 500 ) {
			$httpErrorMsg = "Client Error : $status for url: $url";
		}
		if ( $status >= 500 && $status < 600 ) {
			$httpErrorMsg = "Server Error : $status for url: $url";
		}
		if ( $httpErrorMsg ) {
			throw new Exception( $httpErrorMsg );
		}
	}
}