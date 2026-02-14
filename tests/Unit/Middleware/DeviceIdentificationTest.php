<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\DeviceIdentification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class DeviceIdentificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_device_id_when_none_provided()
    {
        $request = new Request();
        $middleware = new DeviceIdentification();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $this->assertNotNull($request->attributes->get('device_id'));
        $this->assertStringStartsWith('web-', $request->attributes->get('device_id'));
    }

    /** @test */
    public function it_uses_device_id_from_header()
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_DEVICE_ID' => 'tablet-test-001'
        ]);
        
        $middleware = new DeviceIdentification();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $this->assertEquals('tablet-test-001', $request->attributes->get('device_id'));
    }

    /** @test */
    public function it_uses_device_id_from_query_parameter()
    {
        $request = Request::create('/?device_id=tablet-query-001');
        
        $middleware = new DeviceIdentification();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $this->assertEquals('tablet-query-001', $request->attributes->get('device_id'));
    }

    /** @test */
    public function it_stores_device_id_in_session()
    {
        $request = new Request();
        $middleware = new DeviceIdentification();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $this->assertNotNull(Session::get('device_id'));
        $this->assertEquals($request->attributes->get('device_id'), Session::get('device_id'));
    }

    /** @test */
    public function it_uses_device_id_from_session_if_not_in_request()
    {
        Session::put('device_id', 'session-device-001');
        
        $request = new Request();
        $middleware = new DeviceIdentification();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $this->assertEquals('session-device-001', $request->attributes->get('device_id'));
    }

    /** @test */
    public function header_takes_precedence_over_session()
    {
        Session::put('device_id', 'session-device-001');
        
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_DEVICE_ID' => 'header-device-001'
        ]);
        
        $middleware = new DeviceIdentification();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $this->assertEquals('header-device-001', $request->attributes->get('device_id'));
    }

    /** @test */
    public function generated_device_id_contains_required_components()
    {
        $request = new Request();
        $middleware = new DeviceIdentification();
        
        $middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $deviceId = $request->attributes->get('device_id');
        
        // Should start with 'web-'
        $this->assertStringStartsWith('web-', $deviceId);
        
        // Should contain multiple parts separated by hyphens
        $parts = explode('-', $deviceId);
        $this->assertGreaterThanOrEqual(3, count($parts));
        
        // Should have reasonable length
        $this->assertGreaterThan(10, strlen($deviceId));
    }
}
