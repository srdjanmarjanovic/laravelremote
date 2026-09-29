<?php

use Illuminate\Support\Facades\Mail;
use MailGazelle\Client;
use MailGazelle\Http\Request;
use MailGazelle\Http\Response;
use MailGazelle\Http\TransportInterface;
use MailGazelle\Laravel\Exceptions\ApiTokenIsMissing;

test('mail is sent through the Mail Gazelle API', function () {
    $transport = new class implements TransportInterface
    {
        public ?Request $request = null;

        public function send(Request $request): Response
        {
            $this->request = $request;

            return new Response(202, json_encode([
                'id' => '01JTEST',
                'status' => 'queued',
            ], JSON_THROW_ON_ERROR));
        }
    };

    config(['services.mailgazelle.token' => 'tes_test_token']);

    $this->app->instance(TransportInterface::class, $transport);
    $this->app->forgetInstance(Client::class);
    Mail::purge('mailgazelle');

    Mail::mailer('mailgazelle')->raw('Thanks for signing up.', function ($message): void {
        $message->to('user@example.com')->subject('Welcome');
    });

    expect($transport->request)->not->toBeNull()
        ->and($transport->request->method)->toBe('POST')
        ->and($transport->request->url)->toBe('https://mailgazelle.com/api/v1/emails')
        ->and($transport->request->headers['Authorization'])->toBe('Bearer tes_test_token');

    $payload = json_decode((string) $transport->request->body, true, 512, JSON_THROW_ON_ERROR);

    expect($payload['to'])->toBe([['email' => 'user@example.com']])
        ->and($payload['subject'])->toBe('Welcome')
        ->and($payload['text'])->toBe('Thanks for signing up.')
        ->and($payload['from']['email'])->toBe(config('mail.from.address'));
});

test('resolving the Mail Gazelle client without a token fails', function () {
    config([
        'services.mailgazelle.token' => null,
        'mailgazelle.token' => null,
    ]);

    $this->app->forgetInstance(Client::class);

    expect(fn () => $this->app->make(Client::class))->toThrow(ApiTokenIsMissing::class);
});
