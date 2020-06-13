<?php

namespace Mehedi\Mimes;

use Illuminate\Support\Facades\View;

trait Amp
{
    /**
     * The amp view to use for the message.
     *
     * @var string $ampView
     */
    public $ampView;

    /**
     * Set the amp view and view data for the message.
     *
     * @param string $view
     * @param array $data
     * @return $this
     */
    public function amp($view, array $data = [])
    {
        $this->ampView = $view;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Run the callbacks for the message.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function runCallbacks($message)
    {
        parent::runCallbacks($message);

        $message->getSwiftMessage()
            ->addPart($this->renderAmpView(), 'text/x-amp-html', 'utf-8');

        return $this;
    }

    /**
     * Render amp view
     *
     * @return string
     */
    protected function renderAmpView()
    {
        return View::make($this->ampView, $this->buildViewData())->render();
    }
}
