<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
	/**
	 * @dataProvider provideAccessibleUrls
	 */
	public function testPageIsSuccessful($url)
	{
		$client = static::createClient();
		$client->catchExceptions(false);
		$client->request('GET', $url);
		$response = $client->getResponse();

		$this->assertSame(200, $response->getStatusCode());
	}

	/**
	 * @dataProvider provideUnaccessibleUrls
	 */
	public function testPageIsNotSuccessful($url)
	{
		$client = static::createClient();
		//$client->catchExceptions(false);
		$client->request('GET', $url);
		$response = $client->getResponse();

		$this->assertSame(302, $response->getStatusCode());
	}

	public function provideAccessibleUrls()
	{
		return [
			['/'],
			['/trick1/azerty'],
			['/login'],
			['/register'],
			['/legal'],
			['/privacy']
		];
	}

	public function provideUnaccessibleUrls()
	{
		return [
			['/user/trick/new'],
			['/user/trick/edit1'],
			['/user/tricks'],
			['/user/trick/report1'],
			['/user/reportView1'],
			['/user/dashboard/user'],
			['/user/profile/user'],
			['/user/edit/user'],
			['/users']
		];
	}
}