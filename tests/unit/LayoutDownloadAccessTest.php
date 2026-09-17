<?php

use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use PHPUnit\Framework\TestCase;
use Serendipity\Villas\Classes\LayoutDownloadAccess;

require_once __DIR__.'/../../plugins/serendipity/villas/classes/LayoutDownloadAccess.php';

class LayoutDownloadAccessTest extends TestCase
{
    private function session(): Store
    {
        return new Store('layout-test', new ArraySessionHandler(120));
    }

    public function testDownloadRequiresAGrantInTheRequestingSession(): void
    {
        $access = new LayoutDownloadAccess($this->session(), 'test-key');
        $this->assertFalse($access->allows(9, 'unknown', (string) (time() + 60)));

        $grant = $access->grant(9);
        $this->assertTrue($access->allows(9, $grant['signature'], (string) $grant['expires']));

        $otherSession = new LayoutDownloadAccess($this->session(), 'test-key');
        $this->assertFalse($otherSession->allows(9, $grant['signature'], (string) $grant['expires']));
    }

    public function testVillaSignatureAndExpiryCannotBeChanged(): void
    {
        $access = new LayoutDownloadAccess($this->session(), 'test-key');
        $grant = $access->grant(9);
        $this->assertFalse($access->allows(10, $grant['signature'], (string) $grant['expires']));
        $this->assertFalse($access->allows(9, str_repeat('0', 64), (string) $grant['expires']));
        $this->assertFalse($access->allows(9, $grant['signature'], (string) ($grant['expires'] + 60)));
        $this->assertFalse($access->allows(9, $grant['signature'], 'invalid'));
    }

    public function testExpiredGrantIsRejectedEvenWithAValidSignature(): void
    {
        $session = $this->session();
        $grant = ['expires' => time() - 1, 'token' => 'expired-test-token'];
        $session->put('serendipity.layout_downloads.9', $grant);
        $signature = hash_hmac('sha256', 'villa-layouts|9|'.$grant['expires'].'|'.$grant['token'], 'test-key');
        $access = new LayoutDownloadAccess($session, 'test-key');
        $this->assertFalse($access->allows(9, $signature, (string) $grant['expires']));
    }

    public function testNewRequestReplacesThePreviousGrant(): void
    {
        $access = new LayoutDownloadAccess($this->session(), 'test-key');
        $first = $access->grant(9);
        $second = $access->grant(9);
        $this->assertNotSame($first['signature'], $second['signature']);
        $this->assertFalse($access->allows(9, $first['signature'], (string) $first['expires']));
        $this->assertTrue($access->allows(9, $second['signature'], (string) $second['expires']));
    }
}
