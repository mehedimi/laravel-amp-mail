<?php

declare(strict_types=1);

namespace Mehedi\AmpMail\Tests\Mimes;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Mehedi\AmpMail\Mimes\Amp;
use Mehedi\AmpMail\Tests\Mailables\AmpWithAttachment;
use Mehedi\AmpMail\Tests\Mailables\AmpWithEmbed;
use Mehedi\AmpMail\Tests\Mailables\HtmlOnlyAmpMail;
use Mehedi\AmpMail\Tests\Mailables\NoAmpMail;
use Mehedi\AmpMail\Tests\Mailables\WelcomeAmpMail;
use Mehedi\AmpMail\Tests\TestCase;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\TextPart;

class AmpTest extends TestCase
{
    /** Send a mailable and return the raw mime message. */
    protected function sendAndCapture(Mailable $mailable): string
    {
        $sentMessage = Mail::to('receiver@example.com')->send($mailable);

        $this->assertNotNull($sentMessage);

        return $sentMessage->getSymfonySentMessage()->toString();
    }

    protected function contentTypes(string $raw): array
    {
        $types = [];

        foreach (preg_split('/\r?\n/', $raw) as $line) {
            if (preg_match('/^Content-Type:\s*([^;]+)/i', $line, $matches)) {
                $types[] = strtolower(trim($matches[1]));
            }
        }

        return $types;
    }

    public function test_amp_part_is_added_as_the_last_part_of_multipart_alternative(): void
    {
        $raw = $this->sendAndCapture(new WelcomeAmpMail);

        $this->assertSame(
            [
                'multipart/alternative',
                'text/plain',
                'text/html',
                'text/x-amp-html',
            ],
            $this->contentTypes($raw),
        );
    }

    public function test_amp_view_is_rendered_with_shared_view_data(): void
    {
        $raw = $this->sendAndCapture(new WelcomeAmpMail);

        $this->assertStringContainsString('Hello html, Mehedi Hasan!', $raw);
        $this->assertStringContainsString('Hello text, Mehedi Hasan!', $raw);
        $this->assertStringContainsString('Hello amp, Mehedi Hasan!', $raw);
    }

    public function test_amp_view_receives_the_data_passed_to_the_amp_method(): void
    {
        $mailable = new WelcomeAmpMail;

        $sentMessage = Mail::to('receiver@example.com')->send($mailable);
        $raw = $sentMessage->getSymfonySentMessage()->toString();

        // the data given to the amp method is only used by the amp view
        $this->assertStringContainsString('amp-data', $raw);
        $this->assertSame('amp-data', $mailable->viewData['extra']);
    }

    public function test_amp_method_is_fluent(): void
    {
        $mailable = new WelcomeAmpMail;

        $this->assertSame($mailable, $mailable->amp('tests::amp'));
        $this->assertSame('tests::amp', $mailable->ampView);
    }

    public function test_html_only_mailable_gets_an_alternative_part_with_amp(): void
    {
        $raw = $this->sendAndCapture(new HtmlOnlyAmpMail);

        $this->assertSame(
            [
                'multipart/alternative',
                'text/html',
                'text/x-amp-html',
            ],
            $this->contentTypes($raw),
        );
    }

    public function test_attachments_do_not_leave_the_alternative_part(): void
    {
        $raw = $this->sendAndCapture(new AmpWithAttachment);

        $this->assertSame(
            [
                'multipart/mixed',
                'multipart/alternative',
                'text/plain',
                'text/html',
                'text/x-amp-html',
                'application/pdf',
            ],
            $this->contentTypes($raw),
        );
    }

    public function test_embedded_images_keep_the_related_part(): void
    {
        $raw = $this->sendAndCapture(new AmpWithEmbed);

        $types = $this->contentTypes($raw);

        // without plain attachments symfony keeps the related part on top
        $this->assertSame('multipart/related', $types[0]);
        $this->assertContains('multipart/alternative', $types);
        $this->assertContains('text/x-amp-html', $types);
        $this->assertStringContainsString('Hello amp, Mehedi Hasan!', $raw);

        // the related part wraps the alternative part
        $this->assertLessThan(
            array_search('multipart/alternative', $types),
            array_search('multipart/related', $types),
        );
    }

    public function test_message_is_unchanged_without_the_amp_method(): void
    {
        $raw = $this->sendAndCapture(new NoAmpMail);

        $this->assertSame(
            [
                'multipart/alternative',
                'text/plain',
                'text/html',
            ],
            $this->contentTypes($raw),
        );
        $this->assertStringNotContainsString('text/x-amp-html', $raw);
    }

    public function test_mailable_stays_serializable_for_queueing(): void
    {
        $mailable = (new WelcomeAmpMail)->build();

        $restored = unserialize(serialize($mailable));

        $this->assertSame('tests::amp', $restored->ampView);
        $this->assertSame('amp-data', $restored->viewData['extra']);
    }

    public function test_queued_mailable_keeps_the_amp_part_when_processed(): void
    {
        $payload = serialize(new WelcomeAmpMail);

        $mailable = unserialize($payload);

        $raw = $this->sendAndCapture($mailable);

        $this->assertSame(
            [
                'multipart/alternative',
                'text/plain',
                'text/html',
                'text/x-amp-html',
            ],
            $this->contentTypes($raw),
        );
        $this->assertStringContainsString('Hello amp, Mehedi Hasan!', $raw);
    }

    public function test_mail_fake_still_records_the_mailable(): void
    {
        Mail::fake();

        Mail::to('receiver@example.com')->send(new WelcomeAmpMail);

        Mail::assertSent(WelcomeAmpMail::class);
    }

    public function test_mailable_can_be_queued(): void
    {
        Mail::fake();

        Mail::to('receiver@example.com')->queue(new WelcomeAmpMail);

        Mail::assertQueued(WelcomeAmpMail::class);
    }

    public function test_amp_part_helpers_rebuild_the_body_tree(): void
    {
        $mailable = new class extends Mailable
        {
            use Amp;

            public function build()
            {
                return $this->amp('tests::amp')->view('tests::html')->with('name', 'X');
            }
        };

        $sentMessage = Mail::to('receiver@example.com')->send($mailable);

        $body = $sentMessage->getSymfonySentMessage()->getOriginalMessage()->getBody();

        $this->assertInstanceOf(AlternativePart::class, $body);
        $this->assertCount(2, $body->getParts());

        $amp = $body->getParts()[1];
        $this->assertInstanceOf(TextPart::class, $amp);
        $this->assertSame('x-amp-html', $amp->getMediaSubtype());
        $this->assertSame('text', $amp->getMediaType());
    }
}
