<?php
namespace Onedrop\Form\Hubspot\Form\Finisher;

use Neos\Flow\Annotations as Flow;
use Neos\Form\Core\Model\AbstractFinisher;
use Neos\Form\Core\Model\AbstractFormElement;
use Onedrop\Form\Hubspot\Service\FormsService;

/**
 * Class HubSpotFinisher
 */
class HubSpotFinisher extends AbstractFinisher
{
    /**
     * @Flow\Inject()
     * @var FormsService
     */
    protected $formsService;

    /**
     *
     */
    protected function executeInternal()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $formDefinition = $formRuntime->getFormDefinition();
        $httpRequest = $formRuntime->getRequest()->getHttpRequest();

        $formData = [];
        foreach ($formDefinition->getPages() as $page) {
            foreach ($page->getElementsRecursively() as $element) {
                if ($element instanceof AbstractFormElement) {
                    $identifier = $element->getIdentifier();
                    $formData[$identifier] = $formRuntime->getFormState()->getFormValue($identifier);
                }
            }
        }

        $hubspotContext = [
            'ipAddress' => $httpRequest->getClientIpAddress(),
            'pageUrl'   => $httpRequest->getUri(),
            'pageName'  => $formRuntime->getFormState()->getFormValue('page') ?? '',
        ];
        if ($httpRequest->hasCookie('hubspotutk')) {
            $hubspotContext['hutk'] = $httpRequest->getCookie('hubspotutk');
        }
        $formData['hs_context'] = urlencode(json_encode($hubspotContext));

        $this->formsService->submit($formDefinition->getIdentifier(), $formData);
    }
}
