<?php

namespace App\Http\Livewire;

use Livewire\Component;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\RequestException;

class UpdateRoutes extends Component
{
	public $sessionId;
	public $garminSsoGuid;
	public $garminSsoCustGuid;
	public $message;

	private $messages;

    public function render()
    {
        return view('livewire.update-routes');
    }

    public function garminFormSubmit(): void
    {
    	$this->message = '';
    	$this->messages = [];

    	$cookieJar = $this->setCookies();
    	
    	$client = new Client();

    	$endpoint = 'https://connect.garmin.com/modern/proxy/activitylist-service/activities/search/activities?limit=20&start=0&_=1612292293885';

    	try {
    		$response = $client->request('GET', $endpoint, ['cookies' => $cookieJar]);
    	} catch (RequestException $requestException) {
    		$this->message = $requestException->getMessage();
    		return;
    	}

    	$this->messages[] = 'Fetched routes ' . date('H:i:s');

		$content = $response->getBody();

		$routes = json_decode($content);

		$ids = [];
		foreach ($routes as $route) {
			$ids[$route->activityId] = $route->activityId;
		}
		$this->processRoutes($ids);

		$this->message = implode(' | ', $this->messages);
    }

    /**
     * 
     */
    private function setCookies(): CookieJar
    {
    	$cookieJar = new CookieJar();

    	$cookieJar->setCookie(new SetCookie([
            'Domain'  => 'connect.garmin.com',
            'Name'    => 'SESSIONID',
            'Value'   => $this->sessionId,
            'Discard' => true
        ]));

        $cookieJar->setCookie(new SetCookie([
            'Domain'  => '.garmin.com',
            'Name'    => 'GARMIN-SSO-GUID',
            'Value'   => $this->garminSsoGuid,
            'Discard' => true
        ]));

        $cookieJar->setCookie(new SetCookie([
            'Domain'  => '.garmin.com',
            'Name'    => 'GARMIN-SSO-CUST-GUID',
            'Value'   => $this->garminSsoCustGuid,
            'Discard' => true
        ]));

        $cookieJar->setCookie(new SetCookie([
            'Domain'  => '.garmin.com',
            'Name'    => 'GARMIN-SSO',
            'Value'   => 1,
            'Discard' => true
        ]));

        return $cookieJar;
    }

    /**
     * 
     */
    private function processRoutes(array $routeIds)
    {
    	foreach ($routeIds as $routeId) {
    		$this->processRoute($routeId);
    	}
    }

    /**
     * 
     */
    private function processRoute($routeId)
    {

    	$cookieJar = $this->setCookies();
    	
    	$client = new Client();

    	$endpoint = sprintf('https://connect.garmin.com/modern/proxy/download-service/export/tcx/activity/%d', $routeId);

    	try {
    		$response = $client->request('GET', $endpoint, ['cookies' => $cookieJar]);
    	} catch (RequestException $requestException) {
    		$this->messages[] = sprintf('Failed (%s)', $routeId);
    		return;
    	}

    	$this->messages[] = sprintf('Fetched (%s)', $routeId);
    }
}
