<?php

declare(strict_types=1);

namespace Mehedi\AmpMail\Mimes;

use Illuminate\Support\Facades\View;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\AbstractMultipartPart;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Symfony\Component\Mime\Part\Multipart\RelatedPart;
use Symfony\Component\Mime\Part\TextPart;

trait Amp
{
    /**
     * The amp view to use for the message.
     */
    public string $ampView = '';

    /**
     * Set the amp view and view data for the message.
     *
     * @param  string  $view
     * @param  array  $data
     * @return $this
     */
    public function amp(string $view, array $data = [])
    {
        $this->ampView = $view;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Add the amp part to the message.
     *
     * The amp part is attached right after all of the message attachments
     * have been built, so the whole message body can be restructured into
     * the multipart structure required by gmail amp for email:
     *
     * multipart/alternative
     *   |-- text/plain
     *   |-- text/html
     *   `-- text/x-amp-html
     *
     * This hook does not register any closures on the mailable, which keeps
     * the mailable serializable and safe to send through queues.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function buildAttachments($message)
    {
        parent::buildAttachments($message);

        if ('' !== $this->ampView) {
            $this->addAmpPart($message->getSymfonyMessage());
        }

        return $this;
    }

    /**
     * Add the amp part to the underlying symfony message.
     *
     * @param  \Symfony\Component\Mime\Email  $message
     * @return void
     */
    protected function addAmpPart(Email $message)
    {
        if ([] === $message->getAttachments()
            && null === $message->getHtmlBody()
            && null === $message->getTextBody()) {
            return;
        }

        $message->setBody($this->withAmpPart($message->getBody(), $this->ampPart()));
    }

    /**
     * Rebuild the message body with the amp part placed as the last part
     * of the innermost multipart/alternative part.
     *
     * @param  \Symfony\Component\Mime\Part\AbstractPart  $body
     * @param  \Symfony\Component\Mime\Part\TextPart  $amp
     * @return \Symfony\Component\Mime\Part\AbstractPart
     */
    protected function withAmpPart(AbstractPart $body, TextPart $amp)
    {
        $rewritten = $this->insertAmpPart($body, $amp);

        if (null !== $rewritten) {
            return $rewritten;
        }

        if ($body instanceof AbstractMultipartPart) {
            return $this->multipartPart([...$body->getParts(), $amp], $body->getMediaSubtype());
        }

        return new AlternativePart($body, $amp);
    }

    /**
     * Walk the part tree and insert the amp part, or return null when the
     * part does not contain a place for the amp part.
     *
     * @param  \Symfony\Component\Mime\Part\AbstractPart  $part
     * @param  \Symfony\Component\Mime\Part\TextPart  $amp
     * @return \Symfony\Component\Mime\Part\AbstractPart|null
     */
    protected function insertAmpPart(AbstractPart $part, TextPart $amp)
    {
        if ($part instanceof AlternativePart) {
            return new AlternativePart(...array_merge($part->getParts(), [$amp]));
        }

        if ($part instanceof AbstractMultipartPart) {
            $changed = false;
            $parts = [];

            foreach ($part->getParts() as $child) {
                $replaced = $this->insertAmpPart($child, $amp);

                if (null !== $replaced) {
                    $changed = true;
                }

                $parts[] = $replaced ?? $child;
            }

            return $changed ? $this->multipartPart($parts, $part->getMediaSubtype()) : null;
        }

        if ($part instanceof TextPart && 'html' === $part->getMediaSubtype()) {
            return new AlternativePart($part, $amp);
        }

        return null;
    }

    /**
     * Create a multipart part for the given media subtype.
     *
     * @param  list<\Symfony\Component\Mime\Part\AbstractPart>  $parts
     * @param  string  $subtype
     * @return \Symfony\Component\Mime\Part\AbstractMultipartPart
     */
    protected function multipartPart(array $parts, string $subtype)
    {
        return match ($subtype) {
            'alternative' => new AlternativePart(...$parts),
            'related' => new RelatedPart(...$parts),
            default => new MixedPart(...$parts),
        };
    }

    /**
     * Create the text/x-amp-html part for the message.
     *
     * @return \Symfony\Component\Mime\Part\TextPart
     */
    protected function ampPart()
    {
        return new TextPart($this->renderAmpView(), 'utf-8', 'x-amp-html');
    }

    /**
     * Render the amp view.
     *
     * @return string
     */
    protected function renderAmpView()
    {
        return View::make($this->ampView, $this->buildViewData())->render();
    }
}
