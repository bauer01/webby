<?php

namespace Webby\Presenter;

use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\Responses\ForwardResponse;
use Tracy\ILogger;
use Webby\System\Pages;

class ErrorPresenter extends \NetteModule\ErrorPresenter
{

    private $pages;

    public function __construct(ILogger $logger = NULL, Pages $pages)
    {
        $this->pages = $pages;
        parent::__construct($logger);
    }

    public function run(Request $appRequest)
    {
        $e = $appRequest->getParameter('exception');
        if ($e instanceof BadRequestException) {

            $link = $appRequest->getParameter("link");

            $appRequest->setPresenterName("Default");
            $appRequest->setParameters($appRequest->getParameters() + [
                "callback" => function (DefaultPresenter $presenter, Pages $pages) use ($link) {
                    return $pages->createPageResponse(
                        $presenter,
                        $link,
                        Pages::loadPageConfig($this->pages->getDir(), $this->pages->getErrorPage())
                    );
                }
            ]);
            return new ForwardResponse($appRequest);
        } else {
            return parent::run($appRequest);
        }
    }

}