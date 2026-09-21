<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use SwaggerBake\Controller\SwaggerController;

/**
 * Swagger UI for the REST API, served at /docs.
 *
 * The plugin's controller doesn't use the Authorization component, so the
 * AuthorizationMiddleware would reject it; the docs page itself is public
 * (the API endpoints it calls still require HTTP Basic credentials).
 */
class DocsController extends SwaggerController
{
    /**
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->request->getAttribute('authorization')?->skipAuthorization();
    }
}
